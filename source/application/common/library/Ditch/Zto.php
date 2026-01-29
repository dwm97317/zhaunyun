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
            $msg .= '。请确认：1) 开放平台应用已开通「创建订单」；2) 客户编号、AppKey、AppSecret 正确；3) 正式/测试环境与 api_url 一致';
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
