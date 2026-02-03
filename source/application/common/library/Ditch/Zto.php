<?php

namespace app\common\library\Ditch;

use app\common\library\Ditch\MessageBuilder;
use app\common\library\zto\ZtoAuth;
use app\common\library\zto\ZtoConfig;
use app\common\library\zto\ZtoClients;

/**
 * 中通快递开放平台对接（ditch_no=10009）
 * 配置来源于渠道商：app_key、app_token、api_url、customer_code（客户编号，集团必填）
 * 轨迹查询：zto.merchant.waybill.track.query（api.zto.com）
 * 创建订单：zto.open.createOrder（japi.zto.com / japi-test.zto.com）
 * 官方文档：https://open.zto.com 创建订单接口
 * 
 * @method array query(string $express_no) 轨迹查询
 * @method array createOrder(array $params) 创建订单（自动识别标准中通/中通管家）
 * @method string getError() 获取错误信息
 */
class Zto
{
    private $config;
    /** @var string */
    private $error;
    /** @var ZtoClients */
    private $client;

    /**
     * 构造函数
     * @param array $config 配置数组
     *   - key: app_key (必填)
     *   - token: app_token (必填)
     *   - apiurl: API地址 (可选)
     *   - customer_code: 客户编号 (集团必填)
     *   - ditch_type: 渠道类型 2=标准中通, 3=中通管家
     * 
     * 调用者：
     *   - source/application/web/controller/Track.php (轨迹查询)
     *   - source/application/web/controller/Home.php (轨迹查询)
     *   - source/application/common/model/Logistics.php (轨迹查询)
     *   - source/application/store/controller/TrOrder.php (创建订单)
     *   - source/application/api/controller/Package.php (轨迹查询)
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new ZtoClients();
    }

    /**
     * 轨迹查询
     * @param string $express_no 运单号
     * @return array 统一格式 [['logistics_describe'=>,'status_cn'=>,'created_time'=>], ...]
     * 
     * 调用者：
     *   - source/application/web/controller/Track.php::index() - 前端轨迹查询页面
     *   - source/application/web/controller/Home.php::index() - 前端首页轨迹查询
     *   - source/application/common/model/Logistics.php::getZdList() - 物流模型统一查询接口
     *   - source/application/api/controller/Package.php::getLogistics() - API轨迹查询
     * 
     * 使用的辅助类：
     *   - ZtoAuth::generateDigest() - 生成签名
     *   - ZtoAuth::buildHeaders() - 构建请求头
     *   - ZtoConfig::getApiUrl() - 获取API地址
     *   - ZtoClients::post() - 发送HTTP请求
     *   - ZtoClients::parseResponse() - 解析响应
     */
    public function query($express_no)
    {
        $url = ZtoConfig::getApiUrl($this->config, 'track');
        $body = json_encode(['billCode' => (string) $express_no]);
        
        $appKey = ZtoConfig::get($this->config, 'key', '');
        $appSecret = ZtoConfig::get($this->config, 'token', '');
        $digest = ZtoAuth::generateDigest($body, $appSecret);
        $headers = ZtoAuth::buildHeaders($appKey, $digest);

        $resp = $this->client->post($url, $body, $headers);
        if ($resp === false) {
            $this->error = $this->client->getError();
            return [];
        }

        $data = $this->client->parseResponse($resp);
        if ($data === false) {
            $this->error = $this->client->getError();
            return [];
        }

        if (!$this->client->isSuccess($data)) {
            $this->error = $this->client->getMessage($data) ?: '查询失败';
            return [];
        }

        $list = isset($data['result']) && is_array($data['result']) ? $data['result'] : [];
        $loglist = [];
        foreach ($list as $v) {
            $desc = isset($v['desc']) ? $v['desc'] : (isset($v['scanType']) ? $v['scanType'] : (isset($v['StatusDescription']) ? $v['StatusDescription'] : ''));
            $loc = isset($v['scanCity']) ? $v['scanCity'] : (isset($v['Details']) ? $v['Details'] : (isset($v['location']) ? $v['location'] : ''));
            $time = isset($v['scanDate']) ? $v['scanDate'] : (isset($v['Date']) ? $v['Date'] : (isset($v['created_time']) ? $v['created_time'] : ''));
            $loglist[] = [
                'logistics_describe' => $desc,
                'status_cn'          => $loc,
                'created_time'       => $time,
            ];
        }

        return $loglist;
    }

    /**
     * 创建订单（统一入口）
     * 根据 ditch_type 自动识别：
     *   - ditch_type=2: 标准中通 (调用 createStandardOrder)
     *   - ditch_type=3: 中通管家 (调用 createManagerOrder)
     * 
     * @param array $params 订单参数
     * @return array 统一响应格式 ['ack'=>'true/false', 'tracking_number'=>'', 'message'=>'', 'order_id'=>'']
     * 
     * 调用者：
     *   - source/application/store/controller/TrOrder.php::printlabel() - 打印面单时创建订单
     *     * 单包裹模式：直接调用一次
     *     * 多包裹模式：循环调用，为每个箱子创建独立订单
     * 
     * 内部调用：
     *   - createStandardOrder() - 标准中通订单
     *   - createManagerOrder() - 中通管家订单
     */
    public function createOrder(array $params)
    {
        if (isset($this->config['ditch_type']) && (int)$this->config['ditch_type'] === 3) {
            return $this->createManagerOrder($params);
        }
        
        return $this->createStandardOrder($params);
    }

    /**
     * 创建中通管家订单 (ditch_type=3)
     * @param array $params 订单参数
     * @return array 统一响应格式
     * 
     * 调用者：
     *   - createOrder() - 当 ditch_type=3 时自动调用
     * 
     * 使用的辅助类：
     *   - ZtoConfig::getApiUrl() - 获取管家API地址
     *   - ZtoConfig::get() - 获取配置值
     *   - ZtoAuth::generateDigest() - 生成签名
     *   - ZtoAuth::buildManagerHeaders() - 构建管家请求头
     *   - ZtoClients::post() - 发送HTTP请求
     *   - ZtoClients::parseResponse() - 解析响应
     *   - ZtoClients::buildResponse() - 构建统一响应
     *   - MessageBuilder::build() - 构建买家/卖家留言（如果配置启用）
     * 
     * 内部调用：
     *   - buildSenderInfo() - 构建发件人信息
     *   - buildReceiveInfo() - 构建收件人信息
     * 
     * 注意：中通管家接口只负责接单，不直接返回运单号
     */
    private function createManagerOrder(array $params)
    {
        $url = ZtoConfig::getApiUrl($this->config, 'managerOrder');
        $partnerOrderCode = isset($params['partnerOrderCode']) ? $params['partnerOrderCode'] : (isset($params['order_customerinvoicecode']) ? $params['order_customerinvoicecode'] : (isset($params['order_sn']) ? $params['order_sn'] : ''));

        // 构造商品列表
        $goodInfoList = [];
        if (isset($params['orderInvoiceParam']) && is_array($params['orderInvoiceParam'])) {
            foreach ($params['orderInvoiceParam'] as $item) {
                 $goodInfoList[] = [
                    'goodsNum' => isset($item['invoice_pcs']) ? (int)$item['invoice_pcs'] : 1,
                    'goodsTitle' => isset($params['goodsTitle']) ? $params['goodsTitle'] : (isset($item['sku']) ? $item['sku'] : (isset($item['invoice_title']) ? $item['invoice_title'] : '商品')),
                    'unitPrice' => 1,
                    'goodsPath' => isset($params['goodsPath']) && !empty($params['goodsPath']) ? $params['goodsPath'] : '123',
                    'skuPropertiesName' => isset($params['skuPropertiesName']) ? $params['skuPropertiesName'] : '',
                 ];
            }
        } else {
             $goodInfoList[] = [
                'goodsNum' => isset($params['quantity']) ? (int)$params['quantity'] : 1,
                'goodsTitle' => isset($params['goodsTitle']) ? $params['goodsTitle'] : '商品',
                'unitPrice' => 1,
                'goodsPath' => isset($params['goodsPath']) && !empty($params['goodsPath']) ? $params['goodsPath'] : '123',
                'skuPropertiesName' => isset($params['skuPropertiesName']) ? $params['skuPropertiesName'] : '',
             ];
        }

        // 发件人信息
        $sender = $this->buildSenderInfo($params);
        // 收件人信息
        $receiver = $this->buildReceiveInfo($params);

        $bodyArr = [
            'orderType' => 0, // 普通订单
            'orderId' => $partnerOrderCode,
            'shopKey' => ZtoConfig::get($this->config, 'shop_key', ''),
            'sendCompany' => '中通物流',
            'sendMan' => $sender['senderName'],
            'sendPhone' => isset($sender['senderPhone']) ? $sender['senderPhone'] : (isset($sender['senderMobile']) ? $sender['senderMobile'] : ''),
            'sendMobile' => isset($sender['senderMobile']) ? $sender['senderMobile'] : (isset($sender['senderPhone']) ? $sender['senderPhone'] : ''),
            'sendZip' => isset($params['sender_postcode']) ? $params['sender_postcode'] : (isset($params['sendZip']) ? $params['sendZip'] : ZtoConfig::getDefault('sender_postcode')),
            'sendProvince' => $sender['senderProvince'],
            'sendCity' => $sender['senderCity'],
            'sendCounty' => !empty($sender['senderDistrict']) ? $sender['senderDistrict'] : $sender['senderCity'],
            'sendAddress' => $sender['senderAddress'],
            'receiveCompany' => '个人',
            'receiveMan' => $receiver['receiverName'],
            'receivePhone' => isset($receiver['receiverPhone']) ? $receiver['receiverPhone'] : (isset($receiver['receiverMobile']) ? $receiver['receiverMobile'] : ''),
            'receiveMobile' => isset($receiver['receiverMobile']) ? $receiver['receiverMobile'] : (isset($receiver['receiverPhone']) ? $receiver['receiverPhone'] : ''),
            'receiveZip' => isset($params['consignee_postcode']) ? $params['consignee_postcode'] : (isset($params['receiveZip']) ? $params['receiveZip'] : ZtoConfig::getDefault('receiver_postcode')),
            'receiveProvince' => $receiver['receiverProvince'],
            'receiveCity' => $receiver['receiverCity'],
            'receiveCounty' => !empty($receiver['receiverDistrict']) ? $receiver['receiverDistrict'] : $receiver['receiverCity'],
            'receiveAddress' => $receiver['receiverAddress'],
            'payment' => isset($params['real_payment']) ? (float)$params['real_payment'] : (isset($params['payment']) ? (float)$params['payment'] : 0.0),
            'orderDate' => date('Y-m-d H:i:s'),
            'goodInfoList' => $goodInfoList,
        ];

        // 补充可选字段 (增强逻辑)
        $pushConfig = isset($this->config['push_config_json']) ? json_decode($this->config['push_config_json'], true) : [];
        $buildData = $params;
        if (isset($params['orderInvoiceParam'])) {
            $buildData['items'] = $params['orderInvoiceParam'];
        }
        // Map receiver info for builder
        $buildData['receiver'] = [
            'name' => isset($receiver['receiverName']) ? $receiver['receiverName'] : '',
            'mobile' => isset($receiver['receiverMobile']) ? $receiver['receiverMobile'] : '',
            'phone' => isset($receiver['receiverPhone']) ? $receiver['receiverPhone'] : '',
            'address' => isset($receiver['receiverAddress']) ? $receiver['receiverAddress'] : '',
            'city' => isset($receiver['receiverCity']) ? $receiver['receiverCity'] : ''
        ];

        // Buyer Message
        if (isset($pushConfig['enableBuyerMessage']) && $pushConfig['enableBuyerMessage'] && !empty($pushConfig['buyerSchema'])) {
             $bodyArr['buyerMessage'] = MessageBuilder::build($buildData, $pushConfig['buyerSchema']);
        } elseif (!empty($params['buyerMessage'])) {
             $bodyArr['buyerMessage'] = $params['buyerMessage'];
        }

        // Seller Message
        if (isset($pushConfig['enableSellerMessage']) && $pushConfig['enableSellerMessage'] && !empty($pushConfig['sellerSchema'])) {
             $bodyArr['sellerMessage'] = MessageBuilder::build($buildData, $pushConfig['sellerSchema']);
        } elseif (!empty($params['sellerMessage'])) {
             $bodyArr['sellerMessage'] = $params['sellerMessage'];
        }

        if (!empty($params['payDate'])) $bodyArr['payDate'] = $params['payDate'];
        
        // 确保手机/电话必填其一
        if (empty($bodyArr['sendMobile']) && empty($bodyArr['sendPhone'])) {
             $bodyArr['sendMobile'] = ZtoConfig::getDefault('sender_mobile');
        }
         if (empty($bodyArr['receiveMobile']) && empty($bodyArr['receivePhone'])) {
             $bodyArr['receiveMobile'] = ZtoConfig::getDefault('receiver_mobile');
        }

        $body = json_encode($bodyArr, JSON_UNESCAPED_UNICODE);
        $appKey = ZtoConfig::get($this->config, 'key', '');
        $appSecret = ZtoConfig::get($this->config, 'token', '');
        $digest = ZtoAuth::generateDigest($body, $appSecret);
        $headers = ZtoAuth::buildManagerHeaders($appKey, $digest);

        $resp = $this->client->post($url, $body, $headers);
         if ($resp === false) {
            return ZtoClients::buildResponse(false, '', $this->client->getError() ?: '请求失败', '');
        }

        $data = $this->client->parseResponse($resp);
        if ($data === false) {
            return ZtoClients::buildResponse(false, '', $this->client->getError(), '');
        }

        // 中通管家接口说明：
        // 该接口只负责接单，不直接返回运单号。运单号需在管家系统中生成。
        // 因此此处 tracking_number 返回空是正常的，系统层不应报错，仅记录 order_id.
        $ok = isset($data['status']) && $data['status'] === true;
        $msg = $this->client->getMessage($data);
        $orderId = isset($params['partnerOrderCode']) ? $params['partnerOrderCode'] : ''; 

        return ZtoClients::buildResponse($ok, '', $msg !== '' ? $msg : ($ok ? '推送成功' : '推送失败'), $orderId);
    }

    /**
     * 创建标准中通订单 (ditch_type=2)
     * @param array $params 订单参数
     * @return array 统一响应格式
     * 
     * 调用者：
     *   - createOrder() - 当 ditch_type=2 或未设置时自动调用
     * 
     * 使用的辅助类：
     *   - ZtoConfig::getApiUrl() - 获取标准API地址
     *   - ZtoConfig::get() - 获取配置值
     *   - ZtoAuth::generateDigest() - 生成签名
     *   - ZtoAuth::buildHeaders() - 构建请求头
     *   - ZtoClients::post() - 发送HTTP请求
     *   - ZtoClients::parseResponse() - 解析响应
     *   - ZtoClients::buildResponse() - 构建统一响应
     * 
     * 内部调用：
     *   - buildSenderInfo() - 构建发件人信息
     *   - buildReceiveInfo() - 构建收件人信息
     */
    private function createStandardOrder(array $params)
    {
        $url = ZtoConfig::getApiUrl($this->config, 'createOrder');
        $partnerOrderCode = isset($params['partnerOrderCode']) ? $params['partnerOrderCode'] : (isset($params['order_customerinvoicecode']) ? $params['order_customerinvoicecode'] : (isset($params['order_sn']) ? $params['order_sn'] : ''));
        $customerCode = isset($this->config['customer_code']) ? trim((string) $this->config['customer_code']) : '';

        $bodyArr = [
            'partnerType'     => $customerCode !== '' ? '1' : '2',
            'orderType'       => isset($params['orderType']) ? $params['orderType'] : '2',
            'partnerOrderCode' => $partnerOrderCode,
            'senderInfo'      => $this->buildSenderInfo($params),
            'receiveInfo'     => $this->buildReceiveInfo($params),
        ];

        if ($customerCode !== '') {
            $bodyArr['accountInfo'] = ['customerId' => $customerCode];
        } elseif (!empty($params['accountId'])) {
            $bodyArr['accountInfo'] = [
                'accountId'       => $params['accountId'],
                'accountPassword' => isset($params['accountPassword']) ? $params['accountPassword'] : ZtoConfig::getDefault('account_password'),
                'type'            => isset($params['accountType']) ? $params['accountType'] : 1,
            ];
        }

        if (isset($params['weight']) && (float) $params['weight'] > 0) {
            $bodyArr['summaryInfo'] = array_merge(
                isset($bodyArr['summaryInfo']) && is_array($bodyArr['summaryInfo']) ? $bodyArr['summaryInfo'] : [],
                ['quantity' => isset($params['quantity']) ? (int) $params['quantity'] : 1]
            );
            $bodyArr['orderItems'] = [
                ['name' => '商品', 'weight' => (int) round((float) $params['weight'] * 1000), 'quantity' => 1],
            ];
        }

        if (!empty($params['remark'])) {
            $bodyArr['remark'] = $params['remark'];
        }

        $body = json_encode($bodyArr, JSON_UNESCAPED_UNICODE);
        $appKey = ZtoConfig::get($this->config, 'key', '');
        $appSecret = ZtoConfig::get($this->config, 'token', '');
        $digest = ZtoAuth::generateDigest($body, $appSecret);
        $headers = ZtoAuth::buildHeaders($appKey, $digest);

        $resp = $this->client->post($url, $body, $headers);
        if ($resp === false) {
            return ZtoClients::buildResponse(false, '', $this->client->getError() ?: '请求失败', '');
        }

        $data = $this->client->parseResponse($resp);
        if ($data === false) {
            return ZtoClients::buildResponse(false, '', $this->client->getError(), '');
        }

        $ok = $this->client->isSuccess($data);
        $msg = $this->client->getMessage($data);
        if ($msg === '无权限访问' || (is_string($msg) && strpos($msg, '无权限') !== false)) {
            $msg .= '。DEBUG_RAW_JSON: ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $res = isset($data['result']) && is_array($data['result']) ? $data['result'] : [];
        $waybill = isset($res['billCode']) ? $res['billCode'] : '';
        $orderId = isset($res['orderCode']) ? $res['orderCode'] : (isset($res['orderId']) ? $res['orderId'] : '');

        return ZtoClients::buildResponse($ok, $waybill, $msg !== '' ? $msg : ($ok ? 'ok' : '创建失败'), $orderId);
    }

    /**
     * 构建发件人信息
     * @param array $params 订单参数
     * @return array 发件人信息数组
     * 
     * 调用者：
     *   - createStandardOrder() - 标准中通订单
     *   - createManagerOrder() - 中通管家订单
     * 
     * 使用的辅助类：
     *   - ZtoConfig::getDefault() - 获取默认值
     * 
     * 字段映射：
     *   - sender_name/senderName → senderName
     *   - sender_phone/senderPhone → senderPhone
     *   - sender_mobile/senderMobile → senderMobile
     *   - sender_province/senderProvince → senderProvince
     *   - sender_city/senderCity → senderCity
     *   - sender_district/senderDistrict → senderDistrict
     *   - sender_address/senderAddress → senderAddress
     */
    private function buildSenderInfo(array $params)
    {
        $name = isset($params['sender_name']) ? $params['sender_name'] : (isset($params['senderName']) ? $params['senderName'] : '');
        $phone = isset($params['sender_phone']) ? $params['sender_phone'] : (isset($params['senderPhone']) ? $params['senderPhone'] : '');
        $mobile = isset($params['sender_mobile']) ? $params['sender_mobile'] : (isset($params['senderMobile']) ? $params['senderMobile'] : $phone);
        $province = isset($params['sender_province']) ? $params['sender_province'] : (isset($params['senderProvince']) ? $params['senderProvince'] : ZtoConfig::getDefault('sender_province'));
        $city = isset($params['sender_city']) ? $params['sender_city'] : (isset($params['senderCity']) ? $params['senderCity'] : ZtoConfig::getDefault('sender_city'));
        $district = isset($params['sender_district']) ? $params['sender_district'] : (isset($params['senderDistrict']) ? $params['senderDistrict'] : ZtoConfig::getDefault('sender_district'));
        $address = isset($params['sender_address']) ? $params['sender_address'] : (isset($params['senderAddress']) ? $params['senderAddress'] : '');

        $info = [
            'senderName'     => $name ?: ZtoConfig::getDefault('sender_name'),
            'senderProvince' => $province,
            'senderCity'     => $city,
            'senderDistrict' => $district,
            'senderAddress'  => $address ?: ZtoConfig::getDefault('sender_address'),
        ];
        if ($mobile !== '') {
            $info['senderMobile'] = $mobile;
        } elseif ($phone !== '') {
            $info['senderPhone'] = $phone;
        } else {
            $info['senderMobile'] = ZtoConfig::getDefault('sender_mobile');
        }
        return $info;
    }

    /**
     * 构建收件人信息
     * @param array $params 订单参数
     * @return array 收件人信息数组
     * 
     * 调用者：
     *   - createStandardOrder() - 标准中通订单
     *   - createManagerOrder() - 中通管家订单
     * 
     * 使用的辅助类：
     *   - ZtoConfig::getDefault() - 获取默认值
     * 
     * 字段映射：
     *   - consignee_name/receiverName → receiverName
     *   - consignee_mobile/consignee_telephone → receiverMobile/receiverPhone
     *   - consignee_state/receiverProvince → receiverProvince
     *   - consignee_city/receiverCity → receiverCity
     *   - consignee_suburb/receiverDistrict → receiverDistrict
     *   - consignee_address/receiverAddress → receiverAddress
     */
    private function buildReceiveInfo(array $params)
    {
        $name = isset($params['consignee_name']) ? $params['consignee_name'] : (isset($params['receiverName']) ? $params['receiverName'] : '');
        $phone = isset($params['consignee_mobile']) ? $params['consignee_mobile'] : (isset($params['consignee_telephone']) ? $params['consignee_telephone'] : '');
        $mobile = isset($params['consignee_telephone']) ? $params['consignee_telephone'] : (isset($params['consignee_mobile']) ? $params['consignee_mobile'] : $phone);
        $province = isset($params['consignee_state']) ? $params['consignee_state'] : (isset($params['receiverProvince']) ? $params['receiverProvince'] : '');
        $city = isset($params['consignee_city']) ? $params['consignee_city'] : (isset($params['receiverCity']) ? $params['receiverCity'] : '');
        $district = isset($params['consignee_suburb']) ? $params['consignee_suburb'] : (isset($params['receiverDistrict']) ? $params['receiverDistrict'] : '');
        $address = isset($params['consignee_address']) ? $params['consignee_address'] : (isset($params['receiverAddress']) ? $params['receiverAddress'] : '');

        if ($province === '' && $city === '' && $address === '') {
            $province = ZtoConfig::getDefault('receiver_province');
            $city = ZtoConfig::getDefault('receiver_city');
            $district = ZtoConfig::getDefault('receiver_district');
            $address = $address ?: ZtoConfig::getDefault('receiver_address');
        }

        $info = [
            'receiverName'     => $name ?: ZtoConfig::getDefault('receiver_name'),
            'receiverProvince' => $province,
            'receiverCity'     => $city,
            'receiverDistrict' => $district,
            'receiverAddress'  => $address ?: ZtoConfig::getDefault('receiver_address'),
        ];
        if ($mobile !== '') {
            $info['receiverMobile'] = $mobile;
        } elseif ($phone !== '') {
            $info['receiverPhone'] = $phone;
        } else {
            $info['receiverMobile'] = ZtoConfig::getDefault('receiver_mobile');
        }
        return $info;
    }

    /**
     * 获取错误信息
     * @return string 错误信息
     * 
     * 调用者：
     *   - 目前未被外部直接调用
     *   - 保留用于调试和错误处理
     * 
     * 注意：错误信息已通过响应的 message 字段返回，此方法主要用于内部调试
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 批量云打印（类似顺丰面单）
     * 对接 zto.print.batchCloudPrint 接口
     * 
     * @param int $order_id 订单ID（inpack_id）
     * @param array $options 打印选项
     *   - waybill_no: 运单号（可选，默认使用订单的运单号）
     *   - print_mode: 打印模式 'mother'(仅母单) | 'child'(仅子单) | 'all'(全部) 默认: 'mother'
     * @return array|false 返回打印结果或 false
     * 
     * 调用者：
     *   - source/application/store/controller/TrOrder.php::getPrintTask() - 订单列表打印
     * 
     * 使用的辅助类：
     *   - app\store\model\Inpack - 获取订单信息
     *   - app\store\model\Package - 获取子单信息
     *   - ZtoConfig::getPrinterConfig() - 获取打印机配置
     *   - ZtoConfig::validatePrinterConfig() - 验证配置
     *   - ZtoAuth::generateDigest() - 生成签名
     *   - ZtoAuth::buildHeaders() - 构建请求头
     *   - ZtoClients::post() - 发送HTTP请求
     * 
     * 返回格式：
     * [
     *     'success' => true/false,
     *     'message' => '打印成功/失败原因',
     *     'data' => [
     *         'printSuccessList' => ['运单号1', '运单号2', ...],
     *         'printErrorList' => [
     *             ['billCode' => '运单号', 'errorMsg' => '错误信息'],
     *             ...
     *         ]
     *     ]
     * ]
     */
    public function cloudPrint($order_id, $options = [])
    {
        // 1. 获取订单信息
        $order = \app\store\model\Inpack::detail($order_id);
        if (!$order) {
            $this->error = '订单不存在';
            return false;
        }
        
        // 转换为数组
        $orderArray = is_object($order) ? $order->toArray() : $order;
        
        // 2. 获取运单号
        $waybillNo = isset($options['waybill_no']) ? $options['waybill_no'] : '';
        if (empty($waybillNo)) {
            $waybillNo = isset($orderArray['t_order_sn']) ? $orderArray['t_order_sn'] : '';
        }
        
        if (empty($waybillNo)) {
            $this->error = '运单号不存在';
            return false;
        }
        
        // 3. 获取打印机配置
        $printerConfig = \app\common\library\zto\ZtoConfig::getPrinterConfig($this->config);
        
        // 验证配置
        $validation = \app\common\library\zto\ZtoConfig::validatePrinterConfig($printerConfig);
        if (!$validation['valid']) {
            $this->error = '打印机配置错误: ' . implode(', ', $validation['errors']);
            return false;
        }
        
        // 4. 解析打印模式
        $printMode = isset($options['print_mode']) ? $options['print_mode'] : 'mother';
        
        // 5. 构建打印数据
        $printInfos = [];
        
        if ($printMode === 'all') {
            // 打印全部：母单 + 所有子单
            // 母单
            $printInfos[] = $this->buildPrintInfo($orderArray, $waybillNo);
            
            // 所有子单
            $packages = \think\Db::name('package')->where('inpack_id', $order_id)->select();
            foreach ($packages as $pkg) {
                $childWaybillNo = isset($pkg['t_order_sn']) ? $pkg['t_order_sn'] : '';
                if (!empty($childWaybillNo) && $childWaybillNo !== $waybillNo) {
                    $printInfos[] = $this->buildPrintInfo($orderArray, $childWaybillNo);
                }
            }
        } else {
            // 打印单个运单（母单或子单）
            $printInfos[] = $this->buildPrintInfo($orderArray, $waybillNo);
        }
        
        // 6. 构建请求参数
        $requestData = [
            'printChannel' => $printerConfig['printChannel'],
            'printInfos' => $printInfos
        ];
        
        // 添加设备标识
        if (!empty($printerConfig['deviceId'])) {
            $requestData['deviceId'] = $printerConfig['deviceId'];
        } elseif (!empty($printerConfig['qrcodeId'])) {
            $requestData['qrcodeId'] = $printerConfig['qrcodeId'];
        }
        
        // 添加打印机名称（PC端必填）
        if (!empty($printerConfig['printerId'])) {
            $requestData['printerId'] = $printerConfig['printerId'];
        }
        
        // 7. 调用云打印接口
        $url = \app\common\library\zto\ZtoConfig::getApiUrl($this->config, 'cloudPrint');
        $body = json_encode($requestData, JSON_UNESCAPED_UNICODE);
        
        $appKey = \app\common\library\zto\ZtoConfig::get($this->config, 'key', '');
        $appSecret = \app\common\library\zto\ZtoConfig::get($this->config, 'token', '');
        $digest = \app\common\library\zto\ZtoAuth::generateDigest($body, $appSecret);
        $headers = \app\common\library\zto\ZtoAuth::buildHeaders($appKey, $digest);
        
        $resp = $this->client->post($url, $body, $headers);
        if ($resp === false) {
            $this->error = $this->client->getError() ?: '请求失败';
            return false;
        }
        
        $data = $this->client->parseResponse($resp);
        if ($data === false) {
            $this->error = $this->client->getError();
            return false;
        }
        
        // 8. 处理响应
        $success = $this->client->isSuccess($data);
        $message = $this->client->getMessage($data);
        
        $result = isset($data['result']) && is_array($data['result']) ? $data['result'] : [];
        
        return [
            'success' => $success,
            'message' => $message ?: ($success ? '打印成功' : '打印失败'),
            'data' => $result
        ];
    }
    
    /**
     * 构建单个打印项数据
     * @param array $order 订单数据
     * @param string $waybillNo 运单号
     * @return array 打印项数据
     */
    private function buildPrintInfo($order, $waybillNo)
    {
        // 构建发件人信息
        $sender = [
            'name' => isset($order['sender_name']) ? $order['sender_name'] : \app\common\library\zto\ZtoConfig::getDefault('sender_name'),
            'mobile' => isset($order['sender_mobile']) ? $order['sender_mobile'] : \app\common\library\zto\ZtoConfig::getDefault('sender_mobile'),
            'prov' => isset($order['sender_province']) ? $order['sender_province'] : \app\common\library\zto\ZtoConfig::getDefault('sender_province'),
            'city' => isset($order['sender_city']) ? $order['sender_city'] : \app\common\library\zto\ZtoConfig::getDefault('sender_city'),
            'county' => isset($order['sender_district']) ? $order['sender_district'] : \app\common\library\zto\ZtoConfig::getDefault('sender_district'),
            'address' => isset($order['sender_address']) ? $order['sender_address'] : \app\common\library\zto\ZtoConfig::getDefault('sender_address'),
        ];
        
        // 构建收件人信息
        $receiver = [
            'name' => isset($order['consignee_name']) ? $order['consignee_name'] : \app\common\library\zto\ZtoConfig::getDefault('receiver_name'),
            'mobile' => isset($order['consignee_mobile']) ? $order['consignee_mobile'] : (isset($order['consignee_telephone']) ? $order['consignee_telephone'] : \app\common\library\zto\ZtoConfig::getDefault('receiver_mobile')),
            'prov' => isset($order['consignee_state']) ? $order['consignee_state'] : \app\common\library\zto\ZtoConfig::getDefault('receiver_province'),
            'city' => isset($order['consignee_city']) ? $order['consignee_city'] : \app\common\library\zto\ZtoConfig::getDefault('receiver_city'),
            'county' => isset($order['consignee_suburb']) ? $order['consignee_suburb'] : \app\common\library\zto\ZtoConfig::getDefault('receiver_district'),
            'address' => isset($order['consignee_address']) ? $order['consignee_address'] : \app\common\library\zto\ZtoConfig::getDefault('receiver_address'),
        ];
        
        // 构建物品信息
        $goods = [
            'goodsName' => '商品',
            'weight' => isset($order['weight']) && (float)$order['weight'] > 0 ? (int)round((float)$order['weight'] * 1000) : 1000, // 转换为克
        ];
        
        // 添加备注
        if (!empty($order['remark'])) {
            $goods['remark'] = $order['remark'];
        }
        
        // 构建打印参数
        // 从配置获取 paramType 和相关参数
        $pushConfig = isset($this->config['push_config_json']) ? json_decode($this->config['push_config_json'], true) : [];
        $printerConfig = isset($pushConfig['ztoPrinterConfig']) ? $pushConfig['ztoPrinterConfig'] : [];
        
        // 获取 paramType，默认为 DEFAULT_PRINT
        $paramType = isset($printerConfig['paramType']) ? $printerConfig['paramType'] : 'DEFAULT_PRINT';
        
        // 根据 paramType 构建不同的 printParam
        $printParam = [
            'paramType' => $paramType,
            'mailNo' => $waybillNo,
        ];
        
        // 根据不同的 paramType 添加必需字段
        switch ($paramType) {
            case 'ELEC_MARK':
                // 指定电子面单和指定大头笔信息
                $printParam['printMark'] = isset($printerConfig['printMark']) ? $printerConfig['printMark'] : '';
                $printParam['printBagaddr'] = isset($printerConfig['printBagaddr']) ? $printerConfig['printBagaddr'] : '';
                break;
                
            case 'ELEC_NOMARK':
                // 指定电子面单和不指定大头笔信息
                // 只需要 paramType 和 mailNo
                break;
                
            case 'NOELEC_MARK':
                // 不指定电子面单和指定大头笔信息（需传电子面单账号密码获取运单号）
                $printParam['printMark'] = isset($printerConfig['printMark']) ? $printerConfig['printMark'] : '';
                $printParam['printBagaddr'] = isset($printerConfig['printBagaddr']) ? $printerConfig['printBagaddr'] : '';
                $printParam['elecAccount'] = isset($printerConfig['elecAccount']) ? $printerConfig['elecAccount'] : '';
                $printParam['elecPwd'] = isset($printerConfig['elecPwd']) ? $printerConfig['elecPwd'] : '';
                break;
                
            case 'NOELEC_NOMARK':
                // 不指定电子面单和不指定大头笔信息（需传电子面单账号密码获取运单号）
                $printParam['elecAccount'] = isset($printerConfig['elecAccount']) ? $printerConfig['elecAccount'] : '';
                $printParam['elecPwd'] = isset($printerConfig['elecPwd']) ? $printerConfig['elecPwd'] : '';
                break;
                
            case 'DEFAULT_PRINT':
            default:
                // 采用默认电子面单账号
                // 只需要 paramType 和 mailNo
                break;
        }
        
        // 构建打印项
        $printInfo = [
            'partnerCode' => isset($order['order_sn']) ? $order['order_sn'] : '',
            'printParam' => $printParam,
            'sender' => $sender,
            'receiver' => $receiver,
            'goods' => $goods,
            'payType' => 'CASH', // 现付（默认）
            'sheetMode' => 'PRINT_SHEET', // 标准一联单
        ];
        
        // 添加增值服务（如果配置启用）
        if (isset($pushConfig['ztoPrinterConfig']['appreciationEnabled']) && $pushConfig['ztoPrinterConfig']['appreciationEnabled']) {
            if (!empty($pushConfig['ztoPrinterConfig']['appreciationDTOS'])) {
                $printInfo['appreciationDTOS'] = $pushConfig['ztoPrinterConfig']['appreciationDTOS'];
            }
        }
        
        return $printInfo;
    }
}
