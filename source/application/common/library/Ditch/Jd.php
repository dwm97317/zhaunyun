<?php
namespace app\common\library\Ditch;

// Check Guzzle
if (!class_exists('GuzzleHttp\Client')) {
    // Try source/vendor first (Standard)
    $vendorAutoload = dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
    
    if (!file_exists($vendorAutoload)) {
         // Fallback to project root vendor if implied
        $vendorAutoload = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/vendor/autoload.php';
    }
    if (file_exists($vendorAutoload)) {
        require_once $vendorAutoload;
    }
}

// SDK Autoloader
spl_autoload_register(function ($class) {
    if (strpos($class, 'Lop\LopOpensdkPhp\\') === 0) {
        $prefix = 'Lop\LopOpensdkPhp\\';
        $base_dir = __DIR__ . '/../Jdl/lop-opensdk-php/src/';
        $len = strlen($prefix);
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

use Lop\LopOpensdkPhp\Support\DefaultClient;
use Lop\LopOpensdkPhp\Support\GenericRequest;
use Lop\LopOpensdkPhp\Filters\IsvFilter;
use Lop\LopOpensdkPhp\Options;

/**
 * 京东物流接口库 (使用官方 SDK)
 * Class Jd
 * @package app\common\library\Ditch
 */
class Jd
{
    private $config;

    /**
     * Jd constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        // 修正字段名保持兼容
        if (!isset($this->config['app_key'])) $this->config['app_key'] = '';
        if (!isset($this->config['app_secret'])) $this->config['app_secret'] = '';
        if (!isset($this->config['access_token'])) $this->config['access_token'] = '';
        if (!isset($this->config['api_url'])) $this->config['api_url'] = 'https://uat-api.jdl.com/ecap/v1/orders/create';
    }


    /**
     * 创建运单 (符合 commonCreateOrderV1 规范)
     * @param $data
     * @return array
     */
    public function createOrder($data)
    {
        // 1. 构造请求体 (Payload) - 确保不含 warmLayer 等冲突字段
        $payload = [
            'orderOrigin' => 1, // 1-B2C
            'customerCode' => isset($this->config['customer_code']) ? $this->config['customer_code'] : '',
            'orderId'      => $data['order_sn'],
            'productsReq'  => [
                'productCode' => isset($data['product_code']) ? $data['product_code'] : 'ed-m-0001',
            ],
            'senderContact' => [
                'name'        => isset($data['sender_name']) ? $data['sender_name'] : 'Sender',
                'mobile'      => isset($data['sender_mobile']) ? $data['sender_mobile'] : (isset($data['sender_phone']) ? $data['sender_phone'] : ''),
                'phone'       => isset($data['sender_phone']) ? $data['sender_phone'] : '',
                'fullAddress' => isset($data['sender_address']) ? $data['sender_address'] : '',
            ],
            'receiverContact' => [
                'name'        => isset($data['name']) ? $data['name'] : 'Receiver',
                'mobile'      => isset($data['phone']) ? $data['phone'] : (isset($data['mobile']) ? $data['mobile'] : ''),
                'phone'       => isset($data['phone']) ? $data['phone'] : '',
                'fullAddress' => (isset($data['province']) ? $data['province'] : '') . 
                                 (isset($data['city']) ? $data['city'] : '') . 
                                 (isset($data['region']) ? $data['region'] : '') . 
                                 (isset($data['detail']) ? $data['detail'] : ''),
            ],
            'cargoes'     => [
                [
                    'name'     => '商品', 
                    'quantity' => isset($data['quantity']) ? (int)$data['quantity'] : 1,
                    'weight'   => isset($data['weight']) ? (float)$data['weight'] : 1.0,
                ]
            ],
            'settleType'  => 3, // 月结
            'grossWeight' => isset($data['weight']) ? (float)$data['weight'] : 0.0,
        ];

        // API expects List<Order>
        return $this->sendRequest('/ecap/v1/orders/create', [$payload]);
    }

    /**
     * 查询轨迹
     * @param $waybillCode
     * @return array
     */
    public function queryTrace($waybillCode)
    {
        $payload = [
            'waybillCode'  => $waybillCode,
            'customerCode' => isset($this->config['customer_code']) ? $this->config['customer_code'] : '',
            'orderOrigin'  => 1, // 1-B2C (必填)
        ];
        // 接口地址: /ecap/v1/orders/trace/query
        return $this->sendRequest('/ecap/v1/orders/trace/query', [$payload]);
    }

    /**
     * 发送 SDK 请求
     * @param $apiPath
     * @param $payload (Array or Object)
     * @return array
     */
    private function sendRequest($apiPath, $payload)
    {
        // 解析 Base URI
        $parsedUrl = parse_url($this->config['api_url']);
        $baseUri = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        if (isset($parsedUrl['port'])) $baseUri .= ':' . $parsedUrl['port'];
        
        // 如果调用的是非 create 接口，path 可能需要调整? 
        // 假设 api_url 只是 Base URL 或者 Create URL。我们这里只取 Host，Path 由参数传入。
        
        try {
            $client = new DefaultClient($baseUri);
            
            $request = new GenericRequest();
            $request->setMethod("POST");
            $request->setPath($apiPath);
            $request->setBody(json_encode($payload, JSON_UNESCAPED_UNICODE));
            $request->setHeader("Content-Type", "application/json;charset=utf-8");
            
            // 明确指定 LOP-DN 为 ECAP
            $request->setQuery("LOP-DN", "ECAP");

            // 认证 Filter
            $isvFilter = new IsvFilter(
                $this->config['app_key'],
                $this->config['app_secret'],
                $this->config['access_token']
            );
            $request->addFilter($isvFilter);

            // 选项 (MD5-Salt)
            $options = new Options();
            $options->setAlgorithm(Options::MD5_SALT);

            // 执行请求
            $response = $client->execute($request, $options);
            
            // 解析结果
            $responseBody = $response->getBody();
            return $this->parseResponse($responseBody);

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (empty($msg) && $e->getPrevious()) {
                $msg = $e->getPrevious()->getMessage();
            }
            return ['code' => 0, 'msg' => 'JD SDK Error [' . get_class($e) . ']: ' . $msg . ' Trace: ' . substr($e->getTraceAsString(), 0, 200)];
        }
    }

    /**
     * 解析响应
     * @param $responseJson
     * @return array
     */
    private function parseResponse($responseJson)
    {
        $res = json_decode($responseJson, true);
        if (!$res) {
            return ['code' => 0, 'msg' => 'API Response Error (Not JSON): ' . $responseJson];
        }

        // 成功情况 1: { "code": 100, "data": { "waybillCode": "..." } }
        if (isset($res['code']) && $res['code'] == 100 && isset($res['data'])) {
             return [
                 'code' => 1,
                 'data' => [
                     'waybillCode' => $res['data']['waybillCode'],
                     'originData'  => $res['data']
                 ]
             ];
        } 
        // 成功情况 2: { "code": 0, "data": { ... } } (Standard LOP common)
        if (isset($res['code']) && ($res['code'] === 0 || $res['code'] === '0') && isset($res['data']['waybillCode'])) {
            return [
                 'code' => 1,
                 'data' => [
                     'waybillCode' => $res['data']['waybillCode']
                 ]
             ];
        }
        
        // 成功情况 3: 轨迹查询 { "code": 0, "data": { "traceDetails": ... } }
        if (isset($res['code']) && ($res['code'] === 0 || $res['code'] === '0') && isset($res['data']['traceDetails'])) {
            return [
                 'code' => 1,
                 'data' => $res['data']['traceDetails'] // 直接返回轨迹列表
            ];
        }

        // 失败
        $msg = isset($res['message']) ? $res['message'] : (isset($res['msg']) ? $res['msg'] : 'Unknown Error');
        // 子错误信息
        if (isset($res['subMsg'])) {
            $msg .= ' (' . $res['subMsg'] . ')';
        }
        if (isset($res['error_response'])) {
            $err = $res['error_response'];
            $msg = (isset($err['zh_desc']) ? $err['zh_desc'] : '') . (isset($err['en_desc']) ? ' ' . $err['en_desc'] : '');
        }

        return ['code' => 0, 'msg' => $msg, 'raw' => $res];
    }
}
