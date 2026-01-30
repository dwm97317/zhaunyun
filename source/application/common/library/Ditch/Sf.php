<?php

namespace app\common\library\Ditch;

/**
 * 顺丰快递开放平台对接（ditch_no=10010）
 * 配置来源于渠道商：app_key、app_token、api_url、customer_code（月结卡号）
 * 下单接口：EXP_RECE_CREATE_ORDER
 * 路由查询：EXP_RECE_SEARCH_ROUTES
 * 官方文档：https://open.sf-express.com/
 */
class Sf
{
    private $config;
    /** @var string */
    private $error;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * 路由查询（轨迹查询）
     * @param string $express_no 运单号
     * @return array 统一格式 [['logistics_describe'=>,'status_cn'=>,'created_time'=>], ...]
     */
    public function query($express_no)
    {
        $baseUrl = isset($this->config['apiurl']) && $this->config['apiurl'] !== ''
            ? rtrim($this->config['apiurl'], '/')
            : 'https://sfapi.sf-express.com/std/service';

        $msgData = [
            'trackingType' => '1',
            'trackingNumber' => [(string) $express_no],
            'methodType' => '1',
        ];

        $requestData = [
            'partnerID' => isset($this->config['key']) ? $this->config['key'] : '',
            'requestID' => $this->generateRequestId(),
            'serviceCode' => 'EXP_RECE_SEARCH_ROUTES',
            'timestamp' => time(),
            'msgData' => json_encode($msgData, JSON_UNESCAPED_UNICODE),
        ];

        $requestData['msgDigest'] = $this->generateSignature($requestData);

        $resp = $this->httpPost($baseUrl, http_build_query($requestData));
        if ($resp === false) {
            return [];
        }

        $data = json_decode($resp, true);
        if (!is_array($data)) {
            $this->error = '响应解析失败';
            return [];
        }

        if (!isset($data['apiResultCode']) || $data['apiResultCode'] !== 'A1000') {
            $this->error = isset($data['apiErrorMsg']) ? $data['apiErrorMsg'] : '查询失败';
            return [];
        }

        $msgData = isset($data['msgData']) ? json_decode($data['msgData'], true) : [];
        $routes = isset($msgData['routeResps']) && is_array($msgData['routeResps']) ? $msgData['routeResps'] : [];
        
        $loglist = [];
        foreach ($routes as $route) {
            if (isset($route['routes']) && is_array($route['routes'])) {
                foreach ($route['routes'] as $r) {
                    $loglist[] = [
                        'logistics_describe' => isset($r['remark']) ? $r['remark'] : '',
                        'status_cn'          => isset($r['opCode']) ? $r['opCode'] : '',
                        'created_time'       => isset($r['acceptTime']) ? $r['acceptTime'] : '',
                    ];
                }
            }
        }

        return $loglist;
    }

    /**
     * 获取最新运单状态（语义化）
     * @param string $express_no
     * @return array|null ['code'=>opCode, 'status'=>'collected|delivering|signed|exception', 'time'=>..., 'msg'=>...]
     */
    public function getLastStatus($express_no)
    {
        $routes = $this->query($express_no);
        if (empty($routes) || !is_array($routes)) {
            return null;
        }

        // 假设 query 返回的列表是按时间或者API顺序。为了保险，最好按时间排序，但这里先取最后一条
        $latest = end($routes);
        
        return $this->parseStatus($latest);
    }

    /**
     * 解析状态码
     * @param array $routeItem
     * @return array
     */
    private function parseStatus($routeItem)
    {
        if (empty($routeItem)) return null;
        
        // 注意：query 方法中 status_cn 字段存的是 opCode
        $opCode = isset($routeItem['status_cn']) ? (string)$routeItem['status_cn'] : '';
        $time   = isset($routeItem['created_time']) ? $routeItem['created_time'] : '';
        $msg    = isset($routeItem['logistics_describe']) ? $routeItem['logistics_describe'] : '';
        
        $status = 'transporting'; // 默认为运输中

        // 顺丰 OpCode 映射表
        // 50: 已揽收
        // 44: 派送中
        // 80: 已签收
        // 30: 拒收
        // 99: 异常
        $collectedCodes = ['50', '3036', '607'];
        $deliveringCodes = ['44'];
        $signedCodes = ['80', '8000'];
        $exceptionCodes = ['30', '34', '99'];

        if (in_array($opCode, $collectedCodes)) {
            $status = 'collected';
        } elseif (in_array($opCode, $deliveringCodes)) {
            $status = 'delivering';
        } elseif (in_array($opCode, $signedCodes)) {
            $status = 'signed';
        } elseif (in_array($opCode, $exceptionCodes)) {
            $status = 'exception';
        }

        return [
            'code'   => $opCode,
            'status' => $status,
            'time'   => $time,
            'msg'    => $msg
        ];
    }

    /**
     * 创建订单（下单接口）
     * 对接 EXP_RECE_CREATE_ORDER
     * @param array $params 含 partnerOrderCode、consignee信息、sender信息等
     * @return array ['ack'=>'true'|'false', 'tracking_number'=>'', 'message'=>'', 'order_id'=>'']
     */
    public function createOrder(array $params)
    {
        $baseUrl = isset($this->config['apiurl']) && $this->config['apiurl'] !== ''
            ? rtrim($this->config['apiurl'], '/')
            : 'https://sfapi.sf-express.com/std/service';

        $partnerOrderCode = isset($params['partnerOrderCode']) 
            ? $params['partnerOrderCode'] 
            : (isset($params['order_sn']) ? $params['order_sn'] : '');

        // 构建收件人信息
        $consigneeInfo = [
            'contact'  => isset($params['consignee_name']) ? $params['consignee_name'] : '收件人',
            'tel'      => isset($params['consignee_mobile']) ? $params['consignee_mobile'] : 
                         (isset($params['consignee_telephone']) ? $params['consignee_telephone'] : ''),
            'province' => isset($params['consignee_state']) ? $params['consignee_state'] : '',
            'city'     => isset($params['consignee_city']) ? $params['consignee_city'] : '',
            'county'   => isset($params['consignee_suburb']) ? $params['consignee_suburb'] : '',
            'address'  => isset($params['consignee_address']) ? $params['consignee_address'] : '',
        ];

        // 构建发件人信息
        $senderInfo = [
            'contact'  => isset($params['sender_name']) ? $params['sender_name'] : '发件人',
            'tel'      => isset($params['sender_mobile']) ? $params['sender_mobile'] : 
                         (isset($params['sender_phone']) ? $params['sender_phone'] : ''),
            'province' => isset($params['sender_province']) ? $params['sender_province'] : '上海',
            'city'     => isset($params['sender_city']) ? $params['sender_city'] : '上海市',
            'county'   => isset($params['sender_district']) ? $params['sender_district'] : '青浦区',
            'address'  => isset($params['sender_address']) ? $params['sender_address'] : '',
        ];

        // 构建货物信息
        $cargoDetails = [];
        if (isset($params['orderInvoiceParam']) && is_array($params['orderInvoiceParam'])) {
            foreach ($params['orderInvoiceParam'] as $item) {
                $cargoDetails[] = [
                    'name'  => isset($item['invoice_title']) ? $item['invoice_title'] : 
                              (isset($item['sku']) ? $item['sku'] : '商品'),
                    'count' => isset($item['invoice_pcs']) ? (int)$item['invoice_pcs'] : 1,
                ];
            }
        } else {
            $cargoDetails[] = [
                'name'  => '商品',
                'count' => isset($params['quantity']) ? (int)$params['quantity'] : 1,
            ];
        }

        // 构建订单主体
        // 获取快递产品类型：优先使用配置中的sf_express_type，其次使用params中的expressTypeId，最后默认为1（标准快递）
        $expressTypeId = isset($this->config['sf_express_type']) && $this->config['sf_express_type'] > 0
            ? (int)$this->config['sf_express_type']
            : (isset($params['expressTypeId']) ? (int)$params['expressTypeId'] : 1);
            
        $msgData = [
            'orderId'         => $partnerOrderCode,
            'expressTypeId'   => $expressTypeId,
            'payMethod'       => isset($params['payMethod']) ? $params['payMethod'] : 1, // 1-寄方付
            'cargoDetails'    => $cargoDetails,
            'monthlyCard'     => (strpos($baseUrl, 'sbox') !== false) ? '7551234567' : (isset($this->config['customer_code']) ? $this->config['customer_code'] : ''),
            'language'        => 'zh_CN',
        ];

        // 构建联系人列表（contactInfoList）- 必须包含寄件人和收件人
        $contactInfoList = [];
        
        // 添加寄件人（contactType=1）
        if (!empty($senderInfo['contact'])) {
            $contactInfoList[] = [
                'contactType' => 1, // 1-寄件人
                'contact'     => $senderInfo['contact'],
                'tel'         => $senderInfo['tel'],
                'province'    => $senderInfo['province'],
                'city'        => $senderInfo['city'],
                'county'      => $senderInfo['county'],
                'address'     => $senderInfo['address'],
                'country'     => 'CN',
            ];
        }
        
        // 添加收件人（contactType=2）
        $consigneeContact = [
            'contactType' => 2, // 2-收件人
            'contact'     => $consigneeInfo['contact'],
            'tel'         => $consigneeInfo['tel'],
            'province'    => $consigneeInfo['province'],
            'city'        => $consigneeInfo['city'],
            'county'      => $consigneeInfo['county'],
            'address'     => $consigneeInfo['address'],
            'country'     => 'CN',
        ];
        
        // 如果有收件公司名称
        if (isset($params['consignee_company']) && !empty($params['consignee_company'])) {
            $consigneeContact['company'] = $params['consignee_company'];
        }
        
        $contactInfoList[] = $consigneeContact;
        $msgData['contactInfoList'] = $contactInfoList;

        // 如果有重量信息
        if (isset($params['weight']) && (float)$params['weight'] > 0) {
            $msgData['totalWeight'] = (float)$params['weight'];
        }

        // 如果有备注
        if (!empty($params['remark'])) {
            $msgData['remark'] = $params['remark'];
        }

        // 子母单支持
        if (isset($params['is_mother_child']) && in_array($params['is_mother_child'], [1, 2])) {
            $msgData['isMother'] = (string)$params['is_mother_child'];
            if ($params['is_mother_child'] == 2 && !empty($params['mother_waybill_no'])) {
                $msgData['motherWaybillNo'] = $params['mother_waybill_no'];
            }
        }

        $requestData = [
            'partnerID'   => isset($this->config['key']) ? $this->config['key'] : '',
            'requestID'   => $this->generateRequestId(),
            'serviceCode' => 'EXP_RECE_CREATE_ORDER',
            'timestamp'   => time(),
            'msgData'     => json_encode($msgData, JSON_UNESCAPED_UNICODE),
        ];

        $requestData['msgDigest'] = $this->generateSignature($requestData);
        
        $resp = $this->httpPost($baseUrl, http_build_query($requestData));

        if ($resp === false) {
            return [
                'ack'             => 'false',
                'tracking_number' => '',
                'message'         => $this->getError() ?: '请求失败',
                'order_id'        => '',
            ];
        }

        $data = json_decode($resp, true);
        if (!is_array($data)) {
            return [
                'ack'             => 'false',
                'tracking_number' => '',
                'message'         => '响应解析失败',
                'order_id'        => '',
            ];
        }

        $ok = isset($data['apiResultCode']) && $data['apiResultCode'] === 'A1000';
        $msg = isset($data['apiErrorMsg']) ? $data['apiErrorMsg'] : '';

        $waybillNo = '';
        $orderId = $partnerOrderCode;

        if ($ok && isset($data['apiResultData'])) {
            $apiResultData = json_decode($data['apiResultData'], true);
            
            // 检查业务层是否成功
            if (isset($apiResultData['success']) && $apiResultData['success'] === false) {
                $ok = false;
                $msg = isset($apiResultData['errorMsg']) ? $apiResultData['errorMsg'] : '业务处理失败';
            }
            
            if ($ok && is_array($apiResultData) && isset($apiResultData['msgData'])) {
                $msgDataResp = $apiResultData['msgData'];
                if (is_array($msgDataResp)) {
                    $waybillNo = isset($msgDataResp['waybillNoInfoList'][0]['waybillNo']) 
                        ? $msgDataResp['waybillNoInfoList'][0]['waybillNo'] 
                        : '';
                    $orderId = isset($msgDataResp['orderId']) ? $msgDataResp['orderId'] : $orderId;
                }
            }
        }

        return [
            'ack'             => $ok ? 'true' : 'false',
            'tracking_number' => (string)$waybillNo,
            'message'         => $msg !== '' ? $msg : ($ok ? '下单成功' : '下单失败'),
            'order_id'        => (string)$orderId,
        ];
    }

    /**
     * 生成请求ID（唯一标识）
     * @return string
     */
    private function generateRequestId()
    {
        return 'SF' . date('YmdHis') . mt_rand(1000, 9999);
    }

    /**
     * 生成顺丰签名
     * @param array $data
     * @return string
     */
    private function generateSignature(array $data)
    {
        $appSecret = isset($this->config['token']) ? $this->config['token'] : '';
        
        // 顺丰签名规则：msgData + timestamp + appSecret
        $msgData = isset($data['msgData']) ? $data['msgData'] : '';
        $timestamp = isset($data['timestamp']) ? $data['timestamp'] : '';
        
        $signStr = $msgData . $timestamp . $appSecret;
        
        // 官方 SDK Demo 逻辑：先 urlencode 再 md5
        return base64_encode(md5(urlencode($signStr), true));
    }

    /**
     * @param string $url
     * @param string $body
     * @param array  $headers
     * @return string|false
     */
    protected function httpPost($url, $body, array $headers = [])
    {
        if (empty($headers)) {
            $headers = [
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            ];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
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
