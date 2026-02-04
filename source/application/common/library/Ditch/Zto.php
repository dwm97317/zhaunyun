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
        // 1. 获取订单信息（使用 getExpressData 加载完整的关联数据）
        $inpackModel = new \app\store\model\Inpack();
        $order = $inpackModel->getExpressData($order_id);
        if (!$order) {
            $this->error = '订单不存在';
            return false;
        }
        
        // 转换为数组
        $orderArray = is_object($order) ? $order->toArray() : $order;
        
        // 🔧 检查打印状态，判断是否需要原单重打
        $printStatus = isset($orderArray['print_status_jhd']) ? (int)$orderArray['print_status_jhd'] : 0;
        $isRepetition = ($printStatus === 1); // 如果已打印成功过，则为原单重打
        
        // 记录原单重打判断日志
        \think\Log::info('ZTO Cloud Print - Repetition Check: ' . json_encode([
            'order_id' => $order_id,
            'print_status_jhd' => $printStatus,
            'is_repetition' => $isRepetition
        ], JSON_UNESCAPED_UNICODE));
        
        // 将原单重打标识传递给 buildPrintInfo
        $orderArray['_is_repetition'] = $isRepetition;
        
        // 添加 sellerMessage 参数（如果提供）
        if (isset($options['sellerMessage']) && !empty($options['sellerMessage'])) {
            $orderArray['sellerMessage'] = $options['sellerMessage'];
        }
        
        // 调试日志：检查 address 数据
        \think\Log::info('ZTO Cloud Print - Order Data: ' . json_encode([
            'order_id' => $order_id,
            'has_address' => isset($orderArray['address']),
            'address_id' => isset($orderArray['address_id']) ? $orderArray['address_id'] : 'N/A',
            'address_data' => isset($orderArray['address']) ? $orderArray['address'] : 'NULL'
        ], JSON_UNESCAPED_UNICODE));
        
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
        
        // 记录打印数据用于调试
        \think\Log::info('ZTO Cloud Print - Print Infos: ' . json_encode($printInfos, JSON_UNESCAPED_UNICODE));
        
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
        
        // 🔍 调试日志：记录完整的 API 请求数据
        \think\Log::info('ZTO Cloud Print - API Request: ' . json_encode([
            'url' => $url,
            'app_key' => substr($appKey, 0, 8) . '***', // 只显示前8位
            'digest' => substr($digest, 0, 16) . '***', // 只显示前16位
            'headers' => array_map(function($h) {
                // 隐藏敏感信息
                if (strpos($h, 'x-companyid') !== false || strpos($h, 'x-appkey') !== false) {
                    $parts = explode(':', $h, 2);
                    if (count($parts) === 2) {
                        return $parts[0] . ': ' . substr(trim($parts[1]), 0, 8) . '***';
                    }
                }
                return $h;
            }, $headers),
            'request_body' => $requestData, // 完整的请求体（已解析为数组）
            'request_body_json' => $body, // JSON 字符串格式
            'request_size' => strlen($body) . ' bytes'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        
        $resp = $this->client->post($url, $body, $headers);
        if ($resp === false) {
            $this->error = $this->client->getError() ?: '请求失败';
            
            // 🔍 调试日志：记录请求失败
            \think\Log::error('ZTO Cloud Print - Request Failed: ' . json_encode([
                'error' => $this->error,
                'url' => $url
            ], JSON_UNESCAPED_UNICODE));
            
            return false;
        }
        
        // 🔍 调试日志：记录原始响应
        \think\Log::info('ZTO Cloud Print - Raw Response: ' . json_encode([
            'response_length' => strlen($resp) . ' bytes',
            'response_preview' => substr($resp, 0, 500) . (strlen($resp) > 500 ? '...' : ''),
            'response_full' => $resp
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        
        $data = $this->client->parseResponse($resp);
        if ($data === false) {
            $this->error = $this->client->getError();
            
            // 🔍 调试日志：记录解析失败
            \think\Log::error('ZTO Cloud Print - Parse Failed: ' . json_encode([
                'error' => $this->error,
                'raw_response' => $resp
            ], JSON_UNESCAPED_UNICODE));
            
            return false;
        }
        
        // 🔍 调试日志：记录解析后的响应数据
        \think\Log::info('ZTO Cloud Print - Parsed Response: ' . json_encode([
            'success' => isset($data['status']) ? $data['status'] : 'N/A',
            'message' => isset($data['message']) ? $data['message'] : 'N/A',
            'statusCode' => isset($data['statusCode']) ? $data['statusCode'] : 'N/A',
            'has_result' => isset($data['result']),
            'result_type' => isset($data['result']) ? gettype($data['result']) : 'N/A',
            'full_data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        
        // 8. 处理响应
        $success = $this->client->isSuccess($data);
        $message = $this->client->getMessage($data);
        
        $result = isset($data['result']) && is_array($data['result']) ? $data['result'] : [];
        
        // 🔍 调试日志：记录处理后的结果
        \think\Log::info('ZTO Cloud Print - Processed Result: ' . json_encode([
            'success' => $success,
            'message' => $message,
            'result_count' => is_array($result) ? count($result) : 0,
            'result_keys' => is_array($result) ? array_keys($result) : [],
            'result_data' => $result
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        
        // 🔧 打印成功后更新打印状态
        if ($success) {
            try {
                \think\Db::name('inpack')->where('id', $order_id)->update([
                    'print_status_jhd' => 1,
                    'updated_time' => date('Y-m-d H:i:s')
                ]);
                
                \think\Log::info('ZTO Cloud Print - Status Updated: ' . json_encode([
                    'order_id' => $order_id,
                    'print_status_jhd' => 1
                ], JSON_UNESCAPED_UNICODE));
            } catch (\Exception $e) {
                // 更新状态失败不影响打印结果返回
                \think\Log::error('ZTO Cloud Print - Status Update Failed: ' . $e->getMessage());
            }
        }
        
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
        // 调试日志：检查配置中的发件人信息
        \think\Log::info('ZTO Cloud Print - Sender Config: ' . json_encode([
            'has_sender_name' => isset($this->config['sender_name']),
            'sender_name' => isset($this->config['sender_name']) ? $this->config['sender_name'] : 'N/A',
            'has_sender_phone' => isset($this->config['sender_phone']),
            'sender_phone' => isset($this->config['sender_phone']) ? $this->config['sender_phone'] : 'N/A',
            'config_keys' => array_keys($this->config)
        ], JSON_UNESCAPED_UNICODE));
        
        // 构建发件人信息 - 从渠道配置中读取
        $sender = [
            'name' => \app\common\library\zto\ZtoConfig::get($this->config, 'sender_name', \app\common\library\zto\ZtoConfig::getDefault('sender_name')),
            'mobile' => \app\common\library\zto\ZtoConfig::get($this->config, 'sender_phone', \app\common\library\zto\ZtoConfig::getDefault('sender_mobile')),
            'prov' => \app\common\library\zto\ZtoConfig::get($this->config, 'sender_province', \app\common\library\zto\ZtoConfig::getDefault('sender_province')),
            'city' => \app\common\library\zto\ZtoConfig::get($this->config, 'sender_city', \app\common\library\zto\ZtoConfig::getDefault('sender_city')),
            'county' => \app\common\library\zto\ZtoConfig::get($this->config, 'sender_district', \app\common\library\zto\ZtoConfig::getDefault('sender_district')),
            'address' => \app\common\library\zto\ZtoConfig::get($this->config, 'sender_address', \app\common\library\zto\ZtoConfig::getDefault('sender_address')),
        ];
        
        // 构建收件人信息 - 从订单的 address 关联中读取
        $receiver = [
            'name' => '',
            'mobile' => '',
            'prov' => '',
            'city' => '',
            'county' => '',
            'address' => '',
        ];
        
        // 优先从 address 关联获取（getExpressData 返回的数据结构）
        if (isset($order['address']) && is_array($order['address'])) {
            $addr = $order['address'];
            $receiver['name'] = isset($addr['name']) ? $addr['name'] : '';
            $receiver['mobile'] = isset($addr['phone']) ? $addr['phone'] : '';
            $receiver['prov'] = isset($addr['province']) ? $addr['province'] : '';
            $receiver['city'] = isset($addr['city']) ? $addr['city'] : '';
            $receiver['address'] = isset($addr['detail']) ? $addr['detail'] : '';
            
            // 处理 region (区县) 字段
            $region = isset($addr['region']) ? trim($addr['region']) : '';
            $detail = $receiver['address'];
            
            // 如果 region 不为空，直接使用
            if (!empty($region)) {
                $receiver['county'] = $region;
            }
            // 如果 region 为空，尝试从 detail 中提取区县信息
            elseif (!empty($detail)) {
                // 尝试从 detail 中提取区县信息（匹配"XX区"或"XX县"）
                // 使用 Unicode 字符类匹配中文字符
                if (preg_match('/([\x{4e00}-\x{9fa5}]+[区县])/u', $detail, $matches)) {
                    $receiver['county'] = $matches[1];
                } else {
                    // 如果无法提取，使用 city 作为 county（兜底方案）
                    $receiver['county'] = $receiver['city'];
                }
            }
            // 如果 region 和 detail 都为空，使用 city 作为 county（兜底方案）
            else {
                $receiver['county'] = $receiver['city'];
            }
        }
        // 兼容旧的字段名（如果 address 不存在）
        elseif (isset($order['consignee_name'])) {
            $receiver['name'] = $order['consignee_name'];
            $receiver['mobile'] = isset($order['consignee_mobile']) ? $order['consignee_mobile'] : (isset($order['consignee_telephone']) ? $order['consignee_telephone'] : '');
            $receiver['prov'] = isset($order['consignee_state']) ? $order['consignee_state'] : '';
            $receiver['city'] = isset($order['consignee_city']) ? $order['consignee_city'] : '';
            $receiver['county'] = isset($order['consignee_suburb']) ? $order['consignee_suburb'] : '';
            $receiver['address'] = isset($order['consignee_address']) ? $order['consignee_address'] : '';
            
            // 如果 county 为空，使用 city 作为 county（兜底方案）
            if (empty($receiver['county']) && !empty($receiver['city'])) {
                $receiver['county'] = $receiver['city'];
            }
        }
        
        // 构建物品信息
        $goods = [
            'goodsName' => '商品',
            'weight' => isset($order['weight']) && (float)$order['weight'] > 0 ? (int)round((float)$order['weight'] * 1000) : 1000, // 转换为克
        ];
        
        // 添加备注 - 使用与中通管家相同的逻辑
        $pushConfig = isset($this->config['push_config_json']) ? json_decode($this->config['push_config_json'], true) : [];
        
        // 准备构建数据 - 映射 inpack 订单字段到 MessageBuilder 可用的字段
        $buildData = $order;
        
        // 添加商品信息
        if (isset($order['orderInvoiceParam'])) {
            $buildData['items'] = $order['orderInvoiceParam'];
        }
        
        // 添加收件人信息
        $buildData['receiver'] = [
            'name' => $receiver['name'],
            'mobile' => $receiver['mobile'],
            'phone' => $receiver['mobile'],
            'address' => $receiver['address'],
            'city' => $receiver['city']
        ];
        
        // 🔧 字段映射：将数据库字段映射到视图中定义的字段名
        // 这样用户在配置时可以使用友好的字段名
        $buildData['order_sn'] = isset($order['order_sn']) ? $order['order_sn'] : '';
        $buildData['create_time'] = isset($order['created_time']) ? $order['created_time'] : '';
        $buildData['pay_time'] = isset($order['pay_time']) ? $order['pay_time'] : '';
        $buildData['pay_status'] = isset($order['is_pay']) ? ($order['is_pay'] == 1 ? '已支付' : '未支付') : '';
        $buildData['weight'] = isset($order['weight']) ? $order['weight'] : 0;
        $buildData['volume_weight'] = isset($order['cale_weight']) ? $order['cale_weight'] : 0;
        $buildData['chargeable_weight'] = isset($order['cale_weight']) ? $order['cale_weight'] : 0; // 计费重量使用体积重
        $buildData['seller_remark'] = isset($order['remark']) ? $order['remark'] : '';
        $buildData['buyer_remark'] = isset($order['usermark']) ? $order['usermark'] : '';
        
        // 🔧 额外字段映射（处理视图中定义但数据库中不存在或需要特殊处理的字段）
        // apply_time: 申请打包时间 - 使用 created_time 作为替代
        $buildData['apply_time'] = isset($order['created_time']) ? $order['created_time'] : '';
        
        // service_items: 打包服务项目 - 从 pack_services_id 获取
        $buildData['service_items'] = isset($order['pack_services_id']) ? $order['pack_services_id'] : '';
        
        // warehouse_name: 寄送仓库 - 从关联数据获取（如果有）
        if (isset($order['storage']) && is_array($order['storage']) && isset($order['storage']['storage_name'])) {
            $buildData['warehouse_name'] = $order['storage']['storage_name'];
        } else {
            // 如果没有关联数据，使用 storage_id 作为替代
            $buildData['warehouse_name'] = isset($order['storage_id']) ? '仓库' . $order['storage_id'] : '';
        }
        
        // sub_order_count: 子订单数量 - 统计 package 表中的记录数
        if (isset($order['id'])) {
            $subOrderCount = \think\Db::name('package')->where('inpack_id', $order['id'])->count();
            $buildData['sub_order_count'] = $subOrderCount;
        } else {
            $buildData['sub_order_count'] = 0;
        }
        
        // goods_name: 商品名称 - 从 orderInvoiceParam 或 items 中获取第一个商品名称
        if (isset($order['orderInvoiceParam']) && is_array($order['orderInvoiceParam']) && !empty($order['orderInvoiceParam'])) {
            $firstItem = $order['orderInvoiceParam'][0];
            $buildData['goods_name'] = isset($firstItem['invoice_title']) ? $firstItem['invoice_title'] : (isset($firstItem['sku']) ? $firstItem['sku'] : '商品');
        } elseif (isset($order['items']) && is_array($order['items']) && !empty($order['items'])) {
            $firstItem = $order['items'][0];
            $buildData['goods_name'] = isset($firstItem['invoice_title']) ? $firstItem['invoice_title'] : (isset($firstItem['sku']) ? $firstItem['sku'] : '商品');
        } else {
            $buildData['goods_name'] = '商品';
        }
        
        // 构建备注 - 优先使用 sellerMessage (卖家留言)
        $remark = '';
        
        // 1. 如果配置启用了 sellerMessage schema，使用 MessageBuilder 构建
        // 注意：中通快递使用 ztoSellerSchema，中通管家使用 sellerSchema
        $sellerSchema = isset($pushConfig['ztoSellerSchema']) ? $pushConfig['ztoSellerSchema'] : (isset($pushConfig['sellerSchema']) ? $pushConfig['sellerSchema'] : null);
        if (isset($pushConfig['enableSellerMessage']) && $pushConfig['enableSellerMessage'] && !empty($sellerSchema)) {
            $remark = MessageBuilder::build($buildData, $sellerSchema);
        }
        // 2. 如果传递了 sellerMessage 参数，使用参数值
        elseif (!empty($order['sellerMessage'])) {
            $remark = $order['sellerMessage'];
        }
        // 3. 如果有 remark 字段，使用 remark
        elseif (!empty($order['remark'])) {
            $remark = $order['remark'];
        }
        // 4. 如果都没有，但有收件人信息，构建默认备注（包含收件人姓名和电话）
        else {
            $remarkParts = [];
            if (!empty($receiver['name'])) {
                $remarkParts[] = '收件人：' . $receiver['name'];
            }
            if (!empty($receiver['mobile'])) {
                $remarkParts[] = '电话：' . $receiver['mobile'];
            }
            if (!empty($remarkParts)) {
                $remark = implode(' ', $remarkParts);
            }
        }
        
        // 构建打印参数
        // 从配置获取 paramType 和相关参数 (pushConfig 已在上面定义)
        $printerConfig = isset($pushConfig['ztoPrinterConfig']) ? $pushConfig['ztoPrinterConfig'] : [];
        
        // 获取 paramType，默认为 DEFAULT_PRINT
        $paramType = isset($printerConfig['paramType']) ? $printerConfig['paramType'] : 'DEFAULT_PRINT';
        
        // 根据 paramType 构建不同的 printParam
        $printParam = [
            'paramType' => $paramType,
            'mailNo' => $waybillNo,
        ];
        
        // 🔧 原单重打支持：自动判断是否需要原单重打
        // 如果订单已经打印成功过（print_status_jhd = 1），则自动添加 repetition = true
        if (isset($order['_is_repetition']) && $order['_is_repetition']) {
            $printParam['repetition'] = true;
            \think\Log::info('ZTO Cloud Print - Auto Repetition Enabled: ' . json_encode([
                'waybill_no' => $waybillNo,
                'order_sn' => isset($order['order_sn']) ? $order['order_sn'] : 'N/A'
            ], JSON_UNESCAPED_UNICODE));
        }
        
        // 根据不同的 paramType 添加必需字段
        switch ($paramType) {
            case 'ELEC_MARK':
                // 指定电子面单和指定大头笔信息
                // 🔧 如果配置中没有大头笔信息，尝试自动获取
                if (empty($printerConfig['printMark']) || empty($printerConfig['printBagaddr'])) {
                    $bagAddrMark = $this->getBagAddrMark($sender, $receiver);
                    if ($bagAddrMark && isset($bagAddrMark['mark']) && isset($bagAddrMark['bagAddr'])) {
                        $printParam['printMark'] = $bagAddrMark['mark'];
                        $printParam['printBagaddr'] = $bagAddrMark['bagAddr'];
                        
                        \think\Log::info('ZTO Cloud Print - Auto Get BagAddrMark: ' . json_encode([
                            'waybill_no' => $waybillNo,
                            'printMark' => $bagAddrMark['mark'],
                            'printBagaddr' => $bagAddrMark['bagAddr']
                        ], JSON_UNESCAPED_UNICODE));
                    } else {
                        // 如果自动获取失败，使用配置中的值（可能为空）
                        $printParam['printMark'] = isset($printerConfig['printMark']) ? $printerConfig['printMark'] : '';
                        $printParam['printBagaddr'] = isset($printerConfig['printBagaddr']) ? $printerConfig['printBagaddr'] : '';
                    }
                } else {
                    // 使用配置中的值
                    $printParam['printMark'] = $printerConfig['printMark'];
                    $printParam['printBagaddr'] = $printerConfig['printBagaddr'];
                }
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
        // 🔧 原单重打支持：如果是重打，在 partnerCode 后面添加时间戳，避免"不能重复打印"错误
        $partnerCode = isset($order['order_sn']) ? $order['order_sn'] : '';
        if (isset($order['_is_repetition']) && $order['_is_repetition'] && !empty($partnerCode)) {
            $partnerCode .= '_R' . time(); // 添加 _R 前缀和时间戳，例如：2026012166685911_R1738660947
        }
        
        $printInfo = [
            'partnerCode' => $partnerCode,
            'printParam' => $printParam,
            'sender' => $sender,
            'receiver' => $receiver,
            'goods' => $goods,
            'payType' => 'CASH', // 现付（默认）
            'sheetMode' => 'PRINT_SHEET', // 标准一联单
        ];
        
        // ✅ 根据中通云打印 API 文档，remark 是 printInfo 的顶层字段，不是 goods 的子字段
        if (!empty($remark)) {
            $printInfo['remark'] = $remark;
        }
        
        // 添加增值服务（如果配置启用）
        if (isset($pushConfig['ztoPrinterConfig']['appreciationEnabled']) && $pushConfig['ztoPrinterConfig']['appreciationEnabled']) {
            if (!empty($pushConfig['ztoPrinterConfig']['appreciationDTOS'])) {
                $printInfo['appreciationDTOS'] = $pushConfig['ztoPrinterConfig']['appreciationDTOS'];
            }
        }
        
        return $printInfo;
    }
    
    /**
     * 获取大头笔信息
     * @param array $sender 发件人信息
     * @param array $receiver 收件人信息
     * @return array|false 返回大头笔信息或 false
     * 
     * 调用接口: zto.innovate.bagAddrMark
     * 返回格式: ['mark' => '600-', 'bagAddr' => '成都']
     */
    private function getBagAddrMark($sender, $receiver)
    {
        $url = \app\common\library\zto\ZtoConfig::getApiUrl($this->config, 'bagAddrMark');
        
        // 构建请求参数
        $requestData = [
            'send_province' => isset($sender['prov']) ? $sender['prov'] : '',
            'send_city' => isset($sender['city']) ? $sender['city'] : '',
            'send_district' => isset($sender['county']) ? $sender['county'] : '',
            'send_address' => isset($sender['address']) ? $sender['address'] : '',
            'receive_province' => isset($receiver['prov']) ? $receiver['prov'] : '',
            'receive_city' => isset($receiver['city']) ? $receiver['city'] : '',
            'receive_district' => isset($receiver['county']) ? $receiver['county'] : '',
            'receive_address' => isset($receiver['address']) ? $receiver['address'] : '',
            'unionCode' => time() . rand(1000, 9999), // 唯一标识
        ];
        
        $body = json_encode($requestData, JSON_UNESCAPED_UNICODE);
        
        $appKey = \app\common\library\zto\ZtoConfig::get($this->config, 'key', '');
        $appSecret = \app\common\library\zto\ZtoConfig::get($this->config, 'token', '');
        $digest = \app\common\library\zto\ZtoAuth::generateDigest($body, $appSecret);
        $headers = \app\common\library\zto\ZtoAuth::buildHeaders($appKey, $digest);
        
        // 记录请求日志
        \think\Log::info('ZTO BagAddrMark - API Request: ' . json_encode([
            'url' => $url,
            'request_data' => $requestData
        ], JSON_UNESCAPED_UNICODE));
        
        $resp = $this->client->post($url, $body, $headers);
        if ($resp === false) {
            \think\Log::error('ZTO BagAddrMark - Request Failed: ' . $this->client->getError());
            return false;
        }
        
        $data = $this->client->parseResponse($resp);
        if ($data === false) {
            \think\Log::error('ZTO BagAddrMark - Parse Failed: ' . $this->client->getError());
            return false;
        }
        
        // 记录响应日志
        \think\Log::info('ZTO BagAddrMark - API Response: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
        
        // 检查响应状态
        if (!$this->client->isSuccess($data)) {
            $message = $this->client->getMessage($data);
            \think\Log::error('ZTO BagAddrMark - API Error: ' . $message);
            return false;
        }
        
        // 提取大头笔信息
        $result = isset($data['result']) && is_array($data['result']) ? $data['result'] : [];
        if (isset($result['mark']) && isset($result['bagAddr'])) {
            return [
                'mark' => $result['mark'],
                'bagAddr' => $result['bagAddr']
            ];
        }
        
        return false;
    }
}
