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
use app\common\library\Jdl\JdlAuth;

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
        $this->writeDebugLog("--- SDK 构造函数开始 ---");
        $this->config = $config;
        // 修正字段名保持兼容
        if (!isset($this->config['app_key'])) $this->config['app_key'] = '';
        if (!isset($this->config['api_url'])) $this->config['api_url'] = 'https://uat-api.jdl.com/ecap/v1/orders/create';
        
        $this->writeDebugLog("初始配置信息: " . json_encode([
            'api_url' => $this->config['api_url'],
            'app_key' => substr($this->config['app_key'], 0, 8) . '***',
            'customer_code' => isset($this->config['customer_code']) ? $this->config['customer_code'] : '未设置'
        ], JSON_UNESCAPED_UNICODE));

        // 京东物流认证参数映射：
        // - app_key: AppKey (应用密钥)
        // - shop_key: AppSecret (应用秘钥，用于签名)
        // - app_token: Access Token (访问令牌，OAuth获取)
        
        // 处理 app_secret (从 shop_key 字段读取)
        if (!isset($this->config['app_secret'])) {
            $this->config['app_secret'] = isset($this->config['shop_key']) ? $this->config['shop_key'] : '';
            $this->writeDebugLog("映射 AppSecret 来自 shop_key");
        }
        
        // 处理 access_token (从 app_token 字段读取)
        if (!isset($this->config['access_token'])) {
            $this->config['access_token'] = isset($this->config['app_token']) ? $this->config['app_token'] : '';
            $this->writeDebugLog("映射 AccessToken 来自 app_token");
        }
        
        // 如果 access_token 仍然为空，则自动获取（保留自动获取功能作为后备）
        if (empty($this->config['access_token'])) {
            $this->writeDebugLog("AccessToken 为空，尝试自动获取...");
            $isSandbox = (strpos($this->config['api_url'], 'uat-api') !== false || 
                         strpos($this->config['api_url'], 'sbox') !== false);
            $token = JdlAuth::getAccessToken(
                $this->config['app_key'], 
                $this->config['app_secret'], 
                $isSandbox
            );
            if ($token !== false) {
                $this->config['access_token'] = $token;
                $this->writeDebugLog("自动获取 AccessToken 成功");
            } else {
                $this->writeDebugLog("自动获取 AccessToken 失败");
            }
        }
        $this->writeDebugLog("--- SDK 构造函数结束 ---");
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
        
        // 子母件支持
        if (isset($data['is_mother_child'])) {
            $payload['isMother'] = (int)$data['is_mother_child']; // 1-母单, 2-子单
            
            // 如果是子单，需要提供母单号
            if ($data['is_mother_child'] == 2 && !empty($data['mother_waybill_no'])) {
                $payload['motherWaybillNo'] = $data['mother_waybill_no'];
            }
            
            $this->writeDebugLog("子母件模式: isMother=" . $payload['isMother'] . 
                               (isset($payload['motherWaybillNo']) ? ", motherWaybillNo=" . $payload['motherWaybillNo'] : ''));
        }

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
     * @param string $dn 域名节点 (默认 ECAP)
     * @return array
     */
    private function sendRequest($apiPath, $payload, $dn = 'ECAP')
    {
        // 写入请求启动日志
        $this->writeDebugLog(">>> 发起京东 SDK 请求: {$apiPath}");

        // 解析 Base URI
        $parsedUrl = parse_url($this->config['api_url']);
        $baseUri = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        if (isset($parsedUrl['port'])) $baseUri .= ':' . $parsedUrl['port'];
        
        $this->writeDebugLog("解析得 BaseUri: {$baseUri}");

        try {
            $this->writeDebugLog("1. 初始化 DefaultClient...");
            $client = new DefaultClient($baseUri);
            
            $this->writeDebugLog("2. 初始化 GenericRequest (Method: POST, Path: {$apiPath})...");
            $request = new GenericRequest();
            $request->setMethod("POST");
            $request->setPath($apiPath);
            $requestBody = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $request->setBody($requestBody);
            $request->setHeader("Content-Type", "application/json;charset=utf-8");
            
            // 明确指定 LOP-DN
            $this->writeDebugLog("3. 设置 Query 参数 LOP-DN={$dn}...");
            $request->setQuery("LOP-DN", $dn);

            // 认证 Filter
            $this->writeDebugLog("4. 配置 IsvFilter 认证组件...");
            $isvFilter = new IsvFilter(
                $this->config['app_key'],
                $this->config['app_secret'],
                $this->config['access_token']
            );
            $this->writeDebugLog("   Filter 参数: AppKey=" . substr($this->config['app_key'], 0, 5) . "..., Secret=" . substr($this->config['app_secret'], 0, 5) . "..., Token=" . substr($this->config['access_token'], 0, 5) . "...");
            $request->addFilter($isvFilter);

            // 选项 (MD5-Salt)
            $this->writeDebugLog("5. 配置算法选项 (MD5_SALT)...");
            $options = new Options();
            $options->setAlgorithm(Options::MD5_SALT);

            // 写入详细调试日志
            $this->writeDebugLog("--- 请求报文摘要 ---");
            $this->writeDebugLog("URL: {$baseUri}{$apiPath}?LOP-DN={$dn}");
            $this->writeDebugLog("Payload: " . $requestBody);

            // 记录请求信息
            if (class_exists('\think\Log')) {
                \think\Log::info('京东API请求: ' . $baseUri . $apiPath . ', 请求体: ' . $requestBody);
            }

            // 执行请求
            $this->writeDebugLog("6. 执行 SDK execute() 会话启动...");
            $response = $client->execute($request, $options);
            
            $this->writeDebugLog("7. SDK execute() 会话完成");
            
            // 解析结果
            $responseBody = $response->getBody();
            
            // 写入响应日志
            $this->writeDebugLog("--- 响应结果摘要 ---");
            $this->writeDebugLog("HTTP状态及SDK标识: " . ($response->isSucceed() ? '✅ SDK判定成功' : '❌ SDK判定失败'));
            $this->writeDebugLog("Body: " . $responseBody);
            $this->writeDebugLog("<<< 请求事务完成\n");
            
            // 记录响应信息
            if (class_exists('\think\Log')) {
                \think\Log::info('京东API响应: ' . $responseBody);
            }
            
            return $this->parseResponse($responseBody);

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (empty($msg) && $e->getPrevious()) {
                $msg = $e->getPrevious()->getMessage();
            }
            
            // 写入异常日志
            $this->writeDebugLog("❌ 异常发生");
            $this->writeDebugLog("异常类型: " . get_class($e));
            $this->writeDebugLog("异常消息: " . $msg);
            $this->writeDebugLog("堆栈跟踪:");
            $this->writeDebugLog($e->getTraceAsString());
            $this->writeDebugLog("========================================\n");
            
            // 记录异常信息
            if (class_exists('\think\Log')) {
                \think\Log::error('京东API异常: ' . $msg);
            }
            
            return ['code' => 0, 'msg' => 'JD SDK Error [' . get_class($e) . ']: ' . $msg . ' Trace: ' . substr($e->getTraceAsString(), 0, 200)];
        }
    }
    
    /**
     * 写入调试日志到 logs/jd/ 目录
     * @param string $message
     */
    private function writeDebugLog($message)
    {
        $logDir = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/logs/jd';
        
        // 确保目录存在
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/' . date('Ymd') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        
        @file_put_contents($logFile, $logMessage, FILE_APPEND);
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
            $this->writeDebugLog("解析失败: 响应内容不是有效的 JSON");
            return ['code' => 0, 'msg' => 'API Response Error (Not JSON): ' . $responseJson];
        }

        $this->writeDebugLog("解析逻辑判定中...");

        // 成功情况 1: 京东云打印 { "code": "1", "message": "操作成功！", "prePrintDatas": [...] }
        if (isset($res['code']) && ($res['code'] === 1 || $res['code'] === '1') && isset($res['prePrintDatas'])) {
            $this->writeDebugLog("命中判定 1 (京东云打印 code=1, prePrintDatas)");
            return [
                'code' => 1,
                'msg' => isset($res['message']) ? $res['message'] : '操作成功',
                'data' => [
                    'prePrintDatas' => $res['prePrintDatas'],
                    'objectId' => isset($res['objectId']) ? $res['objectId'] : ''
                ]
            ];
        }
        
        // 成功情况 2: { "code": 100, "data": { "waybillCode": "..." } }
        if (isset($res['code']) && $res['code'] == 100 && isset($res['data'])) {
             $this->writeDebugLog("命中判定 2 (code=100)");
             return [
                 'code' => 1,
                 'data' => [
                     'waybillCode' => $res['data']['waybillCode'],
                     'originData'  => $res['data']
                 ]
             ];
        } 
        // 成功情况 3: { "code": 0, "data": { ... } } (Standard LOP common)
        if (isset($res['code']) && ($res['code'] === 0 || $res['code'] === '0') && isset($res['data']['waybillCode'])) {
            $this->writeDebugLog("命中判定 3 (code=0)");
            return [
                 'code' => 1,
                 'data' => [
                     'waybillCode' => $res['data']['waybillCode']
                 ]
             ];
        }
        
        // 成功情况 4: 轨迹查询 { "code": 0, "data": { "traceDetails": ... } }
        if (isset($res['code']) && ($res['code'] === 0 || $res['code'] === '0') && isset($res['data']['traceDetails'])) {
            $this->writeDebugLog("命中判定 4 (轨迹详情)");
            return [
                 'code' => 1,
                 'data' => $res['data']['traceDetails'] // 直接返回轨迹列表
            ];
        }
        
        $this->writeDebugLog("判定失败, 转入错误处理分支");

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

    /**
     * 京东云打获取打印数据
     * SDK编码: jdcloudprint
     * 调用路径: /PullDataService/pullData
     * @param int $orderId 集运订单ID (inpack_id)
     * @param string $waybillCode 京东运单号
     * @return array
     */
    public function jdcloudprint($orderId, $waybillCode)
    {
        $payload = [
            [
                "cpCode" => "JD",
                "wayBillInfos" => [
                    [
                        "popFlag" => 0,
                        "orderNo" => (string)$orderId, // 映射集运订单ID
                        "jdWayBillCode" => $waybillCode
                    ]
                ],
                "parameters" => [
                    "ewCustomerCode" => isset($this->config['customer_code']) ? $this->config['customer_code'] : ''
                ],
                "objectId" => $this->generateUuid()
            ]
        ];

        $this->writeDebugLog(">>> 发起京东云打印数据请求: /PullDataService/pullData");
        $this->writeDebugLog("Payload: " . json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $this->sendRequest('/PullDataService/pullData', $payload, 'jdcloudprint');
    }

    /**
     * 生成 UUID
     * @return string
     */
    private function generateUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
