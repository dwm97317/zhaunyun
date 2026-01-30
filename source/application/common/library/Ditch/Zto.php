<?php

namespace app\common\library\Ditch;

/**
 * 中通快递开放平台对接（ditch_no=10009）
 * 配置来源于渠道商：app_key、app_token、api_url、customer_code（客户编号，集团必填）
 * 轨迹查询：zto.merchant.waybill.track.query（api.zto.com）
 * 创建订单：zto.open.createOrder（japi.zto.com / japi-test.zto.com）
 * 官方文档：https://open.zto.com 创建订单接口
 */
class Zto
{
    private $config;
    /** @var string */
    private $error;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * 轨迹查询
     * @param string $express_no 运单号
     * @return array 统一格式 [['logistics_describe'=>,'status_cn'=>,'created_time'=>], ...]
     */
    public function query($express_no)
    {
        $baseUrl = isset($this->config['apiurl']) && $this->config['apiurl'] !== ''
            ? rtrim($this->config['apiurl'], '/')
            : 'https://api.zto.com';
        $path = '/zto.merchant.waybill.track.query';
        $url = (strpos($baseUrl, 'track') !== false) ? $baseUrl : $baseUrl . $path;

        $body = json_encode(['billCode' => (string) $express_no]);
        $appSecret = isset($this->config['token']) ? $this->config['token'] : '';
        $digest = base64_encode(md5($body . $appSecret, true));

        $headers = [
            'Content-Type: application/json; charset=UTF-8',
            'x-appKey: ' . (isset($this->config['key']) ? $this->config['key'] : ''),
            'x-datadigest: ' . $digest,
        ];

        $resp = $this->httpPost($url, $body, $headers);
        if ($resp === false) {
            return [];
        }

        $data = json_decode($resp, true);
        if (!is_array($data)) {
            $this->error = '响应解析失败';
            return [];
        }

        if (empty($data['status'])) {
            $this->error = isset($data['message']) ? $data['message'] : '查询失败';
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
     * 创建订单（推送至渠道）
     * 对接 zto.open.createOrder，正式 japi.zto.com，测试 japi-test.zto.com
     * @param array $params 含 partnerOrderCode、senderInfo、receiveInfo、weight、accountInfo 等
     * @return array ['ack'=>'true'|'false', 'tracking_number'=>'', 'message'=>'', 'order_id'=>'']
     */
    public function createOrder(array $params)
    {
        if (isset($this->config['ditch_type']) && (int)$this->config['ditch_type'] === 3) {
            // 推送到中通管家 (ZTO Manager)
            $baseUrl = isset($this->config['apiurl']) && $this->config['apiurl'] !== ''
                ? rtrim($this->config['apiurl'], '/')
                : 'https://japi.zto.com';
            $path = '/zto.ehk.receiveOpenOrder'; // 中通管家接单接口
            // 如果apiurl本身包含了方法名，就不追加
            $url = (strpos($baseUrl, 'receiveOpenOrder') !== false) ? $baseUrl : $baseUrl . $path;

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
                'shopKey' => isset($this->config['shop_key']) ? $this->config['shop_key'] : '',
                'sendCompany' => '中通物流', // 可配置
                'sendMan' => $sender['senderName'],
                'sendPhone' => isset($sender['senderPhone']) ? $sender['senderPhone'] : (isset($sender['senderMobile']) ? $sender['senderMobile'] : ''),
                'sendMobile' => isset($sender['senderMobile']) ? $sender['senderMobile'] : (isset($sender['senderPhone']) ? $sender['senderPhone'] : ''),
                'sendZip' => isset($params['sender_postcode']) ? $params['sender_postcode'] : (isset($params['sendZip']) ? $params['sendZip'] : '000000'),
                'sendProvince' => $sender['senderProvince'],
                'sendCity' => $sender['senderCity'],
                'sendCounty' => !empty($sender['senderDistrict']) ? $sender['senderDistrict'] : $sender['senderCity'],
                'sendAddress' => $sender['senderAddress'],
                'receiveCompany' => '个人',
                'receiveMan' => $receiver['receiverName'],
                'receivePhone' => isset($receiver['receiverPhone']) ? $receiver['receiverPhone'] : (isset($receiver['receiverMobile']) ? $receiver['receiverMobile'] : ''),
                'receiveMobile' => isset($receiver['receiverMobile']) ? $receiver['receiverMobile'] : (isset($receiver['receiverPhone']) ? $receiver['receiverPhone'] : ''),
                'receiveZip' => isset($params['consignee_postcode']) ? $params['consignee_postcode'] : (isset($params['receiveZip']) ? $params['receiveZip'] : '000000'),
                'receiveProvince' => $receiver['receiverProvince'],
                'receiveCity' => $receiver['receiverCity'],
                'receiveCounty' => !empty($receiver['receiverDistrict']) ? $receiver['receiverDistrict'] : $receiver['receiverCity'],
                'receiveAddress' => $receiver['receiverAddress'],
                'payment' => isset($params['real_payment']) ? (float)$params['real_payment'] : (isset($params['payment']) ? (float)$params['payment'] : 0.0),
                'orderDate' => date('Y-m-d H:i:s'),
                'goodInfoList' => $goodInfoList,
            ];

            // 补充可选字段
            if (!empty($params['buyerMessage'])) $bodyArr['buyerMessage'] = $params['buyerMessage'];
            if (!empty($params['sellerMessage'])) $bodyArr['sellerMessage'] = $params['sellerMessage'];
            if (!empty($params['payDate'])) $bodyArr['payDate'] = $params['payDate'];

            // --- 快递管家推送增强 Start ---
            if (!empty($this->config['push_config_json'])) {
                $pushConfig = json_decode($this->config['push_config_json'], true);
                
                // 仅当配置有效时尝试生成
                if (json_last_error() === JSON_ERROR_NONE && (!empty($pushConfig['buyerMessage']) || !empty($pushConfig['sellerMessage']))) {
                    try {
                        // 尝试查找订单
                        $inpackModel = new \app\common\model\Inpack();
                        // 尝试用 partnerOrderCode (order_sn) 查找
                        $inpack = $inpackModel->with(['user', 'address', 'line'])->where('order_sn', $partnerOrderCode)->find();
                        
                        if ($inpack) {
                            $inpackData = $inpack->toArray();
                            
                            // 渲染 buyerMessage
                            if (!empty($pushConfig['buyerMessage'])) {
                                $generatedBuyerMsg = \app\common\service\ditch\PushConfig::renderMessage($pushConfig['buyerMessage'], $inpackData);
                                if (!empty($generatedBuyerMsg)) {
                                    $bodyArr['buyerMessage'] = $generatedBuyerMsg;
                                }
                            }
                            
                            // 渲染 sellerMessage
                            if (!empty($pushConfig['sellerMessage'])) {
                                $generatedSellerMsg = \app\common\service\ditch\PushConfig::renderMessage($pushConfig['sellerMessage'], $inpackData);
                                if (!empty($generatedSellerMsg)) {
                                    $bodyArr['sellerMessage'] = $generatedSellerMsg;
                                }
                            }

                            // 特殊开关处理 (兼容旧配置)
                            if (isset($pushConfig['enablePayDate']) && $pushConfig['enablePayDate']) {
                                if (!empty($inpackData['pay_time'])) {
                                    $bodyArr['payDate'] = date('Y-m-d H:i:s', $inpackData['pay_time']);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // 容错：生成失败不影响主流程，记录日志
                        \think\Log::error('PushConfig Render Error: ' . $e->getMessage());
                    }
                }
            }
            // --- 快递管家推送增强 End ---
            
            // 确保手机/电话必填其一
            if (empty($bodyArr['sendMobile']) && empty($bodyArr['sendPhone'])) {
                 $bodyArr['sendMobile'] = '13800138000';
            }
             if (empty($bodyArr['receiveMobile']) && empty($bodyArr['receivePhone'])) {
                 $bodyArr['receiveMobile'] = '13900139000';
            }

            $body = json_encode($bodyArr, JSON_UNESCAPED_UNICODE);
            $appSecret = isset($this->config['token']) ? $this->config['token'] : '';
            // 中通管家签名方式 x-dataDigest
            $digest = base64_encode(md5($body . $appSecret, true));
            $headers = [
                'Content-Type: application/json; charset=UTF-8',
                'x-appKey: ' . (isset($this->config['key']) ? $this->config['key'] : ''),
                'x-dataDigest: ' . $digest,
            ];

            $resp = $this->httpPost($url, $body, $headers);
             if ($resp === false) {
                return ['ack' => 'false', 'tracking_number' => '', 'message' => $this->getError() ?: '请求失败', 'order_id' => ''];
            }

            $data = json_decode($resp, true);
            if (!is_array($data)) {
                return ['ack' => 'false', 'tracking_number' => '', 'message' => '响应解析失败', 'order_id' => ''];
            }

            // 中通管家接口说明：
            // 该接口只负责接单，不直接返回运单号。运单号需在管家系统中生成。
            // 因此此处 tracking_number 返回空是正常的，系统层不应报错，仅记录 order_id.
            $ok = isset($data['status']) && $data['status'] === true;
            $msg = isset($data['message']) ? $data['message'] : '';
            $orderId = isset($params['partnerOrderCode']) ? $params['partnerOrderCode'] : ''; 

            return [
                'ack'             => $ok ? 'true' : 'false',
                'tracking_number' => '', // 明确：接口不直接返回运单号，此处留空是安全的
                'message'         => $msg !== '' ? $msg : ($ok ? '推送成功' : '推送失败'),
                'order_id'        => (string) $orderId,
            ];

        }

        // --- 以下为原有标准中通逻辑 ---
        $baseUrl = isset($this->config['apiurl']) && $this->config['apiurl'] !== ''
            ? rtrim($this->config['apiurl'], '/')
            : 'https://japi.zto.com';
        $path = '/zto.open.createOrder';
        $url = (strpos($baseUrl, 'createOrder') !== false) ? $baseUrl : $baseUrl . $path;

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
                'accountPassword' => isset($params['accountPassword']) ? $params['accountPassword'] : 'ZTO123',
                'type'            => isset($params['accountType']) ? $params['accountType'] : 1,
            ];
        }
// DUMP($url);DIE;
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
        $appSecret = isset($this->config['token']) ? $this->config['token'] : '';
        $digest = base64_encode(md5($body . $appSecret, true));
        $headers = [
            'Content-Type: application/json; charset=UTF-8',
            'x-appKey: ' . (isset($this->config['key']) ? $this->config['key'] : ''),
            'x-dataDigest: ' . $digest,
        ];

        $resp = $this->httpPost($url, $body, $headers);
        if ($resp === false) {
            return ['ack' => 'false', 'tracking_number' => '', 'message' => $this->getError() ?: '请求失败', 'order_id' => ''];
        }

        $data = json_decode($resp, true);
        if (!is_array($data)) {
            return ['ack' => 'false', 'tracking_number' => '', 'message' => '响应解析失败', 'order_id' => ''];
        }

        $ok = !empty($data['status']);
        $msg = isset($data['message']) ? $data['message'] : (isset($data['msg']) ? $data['msg'] : '');
        if ($msg === '无权限访问' || (is_string($msg) && strpos($msg, '无权限') !== false)) {
            $msg .= '。DEBUG_RAW_JSON: ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $res = isset($data['result']) && is_array($data['result']) ? $data['result'] : [];
        $waybill = isset($res['billCode']) ? $res['billCode'] : '';
        $orderId = isset($res['orderCode']) ? $res['orderCode'] : (isset($res['orderId']) ? $res['orderId'] : '');

        return [
            'ack'             => $ok ? 'true' : 'false',
            'tracking_number' => (string) $waybill,
            'message'         => $msg !== '' ? $msg : ($ok ? 'ok' : '创建失败'),
            'order_id'        => (string) $orderId,
        ];
    }

    private function buildSenderInfo(array $params)
    {
        $name = isset($params['sender_name']) ? $params['sender_name'] : (isset($params['senderName']) ? $params['senderName'] : '');
        $phone = isset($params['sender_phone']) ? $params['sender_phone'] : (isset($params['senderPhone']) ? $params['senderPhone'] : '');
        $mobile = isset($params['sender_mobile']) ? $params['sender_mobile'] : (isset($params['senderMobile']) ? $params['senderMobile'] : $phone);
        $province = isset($params['sender_province']) ? $params['sender_province'] : (isset($params['senderProvince']) ? $params['senderProvince'] : '上海');
        $city = isset($params['sender_city']) ? $params['sender_city'] : (isset($params['senderCity']) ? $params['senderCity'] : '上海市');
        $district = isset($params['sender_district']) ? $params['sender_district'] : (isset($params['senderDistrict']) ? $params['senderDistrict'] : '青浦区');
        $address = isset($params['sender_address']) ? $params['sender_address'] : (isset($params['senderAddress']) ? $params['senderAddress'] : '');

        $info = [
            'senderName'     => $name ?: '发件人',
            'senderProvince' => $province,
            'senderCity'     => $city,
            'senderDistrict' => $district,
            'senderAddress'  => $address ?: '详细地址',
        ];
        if ($mobile !== '') {
            $info['senderMobile'] = $mobile;
        } elseif ($phone !== '') {
            $info['senderPhone'] = $phone;
        } else {
            $info['senderMobile'] = '13800138000';
        }
        return $info;
    }

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
            $province = '上海';
            $city = '上海市';
            $district = '闵行区';
            $address = $address ?: '收件地址';
        }

        $info = [
            'receiverName'     => $name ?: '收件人',
            'receiverProvince' => $province,
            'receiverCity'     => $city,
            'receiverDistrict' => $district,
            'receiverAddress'  => $address ?: '详细地址',
        ];
        if ($mobile !== '') {
            $info['receiverMobile'] = $mobile;
        } elseif ($phone !== '') {
            $info['receiverPhone'] = $phone;
        } else {
            $info['receiverMobile'] = '13900139000';
        }
        return $info;
    }

    /**
     * @param string $url
     * @param string $body
     * @param array  $headers
     * @return string|false
     */
    protected function httpPost($url, $body, array $headers = [])
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (ZTO_MANAGER_SDK)',
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_HTTPHEADER     => $headers,
        ]);
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            $this->error = $err;
            return false;
        }
        return $result;
    }

    public function getError()
    {
        return $this->error;
    }
}
