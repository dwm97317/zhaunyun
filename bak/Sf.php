<?php

namespace app\common\library\Ditch;

use app\store\model\Inpack;

/**
 * 顺丰快递开放平台对接（ditch_no=10010）
 * 配置来源于渠道商：app_key、app_token、api_url、customer_code（月结卡号）
 * 下单接口：EXP_RECE_CREATE_ORDER
 * 路由查询：EXP_RECE_SEARCH_ROUTES
 * 面单图片：EXP_RECE_SEARCH_WAYBILL_PICTURE
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

        // 处理备注：合并买家留言和卖家备注
        $remarkParts = [];
        if (!empty($params['buyer_remark'])) {
            $remarkParts[] = '买家留言: ' . $params['buyer_remark'];
        }
        if (!empty($params['seller_remark'])) {
            $remarkParts[] = '卖家备注: ' . $params['seller_remark'];
        }
        
        // 如果外部直接传了 remark，也加进去 (或者覆盖，视需求而定，这里选择追加)
        if (!empty($params['remark'])) {
            $remarkParts[] = $params['remark'];
        }
        
        if (!empty($remarkParts)) {
            // 顺丰 remark 字段长度限制较短(通常几十个字符)，注意截断，这里暂不做强截断
            $msgData['remark'] = implode('; ', $remarkParts);
        }

        // 子母单支持
        if (isset($params['is_mother_child']) && in_array($params['is_mother_child'], [1, 2])) {
            $msgData['isMother'] = (string)$params['is_mother_child'];
            if ($params['is_mother_child'] == 2 && !empty($params['mother_waybill_no'])) {
                $msgData['motherWaybillNo'] = $params['mother_waybill_no'];
            }
        }

        $requestData = [
            'partnerID' => isset($this->config['key']) ? $this->config['key'] : '',
            'requestID' => $this->generateRequestId(),
            'serviceCode' => 'EXP_RECE_CREATE_ORDER', // 下单接口
            'timestamp' => time(),
            'msgData' => json_encode($msgData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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

    private function getUploadUrl($fileName)
    {
        // 假设 Web 根目录在 web/，且配置了域名
        // 由于这里是在 CLI 或 API 上下文，REQUEST_SCHEME 可能获取不到，需根据配置
        $request = \think\Request::instance();
        $domain = $request->domain();
        
        // 如果是命令行模式，domain() 可能返回空或 localhost，需兜底
        if (empty($domain) || $domain == 'localhost') {
            // 尝试读取配置中的 base_url，如果没有则使用本地测试地址
            // $domain = \think\Config::get('app_host') ?: 'http://127.0.0.1:8080';
            // 这里为了通用性，暂不强制硬编码，但如果是在本地测试，需要注意
        }
        
        return $domain . '/uploads/sf_label/' . $fileName;
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
        // 注意：msgData 必须是发送给顺丰的原始字符串 (如果是 json_encode 后的)
        $msgData = isset($data['msgData']) ? $data['msgData'] : '';
        $timestamp = isset($data['timestamp']) ? $data['timestamp'] : '';
        
        // 根据最新文档：去除 URLEncoder 过程
        // String toVerifyText = msgData + timestamp + checkWord;
        $signStr = $msgData . $timestamp . $appSecret;
        return base64_encode(md5($signStr, true));
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
            CURLOPT_TIMEOUT        => 180, // 增加到180秒（3分钟）- 适应批量打印
            CURLOPT_CONNECTTIMEOUT => 120,  // 连接超时30秒
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $headers,
            // 增强网络连接稳定性的选项
            CURLOPT_TCP_NODELAY    => true,  // 禁用 Nagle 算法，减少延迟
            CURLOPT_TCP_KEEPALIVE  => 1,     // 启用 TCP Keep-Alive
            CURLOPT_TCP_KEEPIDLE   => 120,   // Keep-Alive 空闲时间
            CURLOPT_TCP_KEEPINTVL  => 60,    // Keep-Alive 探测间隔
            CURLOPT_FOLLOWLOCATION => true,  // 跟随重定向
            CURLOPT_MAXREDIRS      => 5,     // 最多5次重定向
            CURLOPT_ENCODING       => '',    // 支持所有编码
            CURLOPT_FRESH_CONNECT  => false, // 复用连接
            CURLOPT_FORBID_REUSE   => false, // 允许连接复用
        ]);

        $maxRetries = 3; // 增加重试次数到3次
        $attempt = 0;
        $result = false;
        $err = '';
        $httpCode = 0;

        do {
            $attempt++;
            $startTime = microtime(true);
            $result = curl_exec($ch);
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2); // 毫秒
            
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // 记录日志
            $logData = [
                'time' => date('Y-m-d H:i:s'),
                'url' => $url,
                'attempt' => $attempt,
                'duration_ms' => $duration,
                'http_code' => $httpCode,
                'error' => $err,
                'response_length' => $result ? strlen($result) : 0,
                // 只在失败时记录完整请求体（避免日志过大）
                'request_body' => ($err || $httpCode != 200) ? substr($body, 0, 500) : '[success]',
                'response_preview' => ($err || $httpCode != 200) ? substr($result, 0, 500) : '[success]',
            ];
            
            // 简单写入日志文件
            $logDir = dirname(ROOT_PATH) . DS . 'runtime' . DS . 'log' . DS . 'sf_express';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            file_put_contents($logDir . DS . date('Ym') . '.log', json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);

            // 成功条件：无错误且有返回结果
            if (!$err && $result !== false && $httpCode == 200) {
                break;
            }
            
            // 如果还有重试机会，等待后重试
            if ($attempt < $maxRetries) {
                // 指数退避：第1次重试等1秒，第2次等2秒
                $waitTime = $attempt * 1000000; // 微秒
                usleep($waitTime);
                
                // 记录重试信息
                \think\Log::warning("顺丰API请求失败，正在重试 ({$attempt}/{$maxRetries}): {$err}");
            }

        } while ($attempt < $maxRetries);

        curl_close($ch);
        
        // 最终失败
        if ($err || $result === false || $httpCode != 200) {
            $this->error = $err ?: "HTTP错误: {$httpCode}";
            \think\Log::error("顺丰API请求最终失败: {$this->error}, 尝试次数: {$attempt}");
            return false;
        }
        
        return $result;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * 获取 OAuth2 AccessToken (通用鉴权)
     *使用了公共提取的方法 app\common\library\Sf\OAuth
     * @return string|false Token
     */
    public function getOAuth2AccessToken()
    {
        $partnerId = isset($this->config['key']) ? $this->config['key'] : '';
        $secret    = isset($this->config['token']) ? $this->config['token'] : '';
        $isSandbox = isset($this->config['apiurl']) && strpos($this->config['apiurl'], 'sbox') !== false;

        $token = \app\common\library\Sf\OAuth::getAccessToken($partnerId, $secret, $isSandbox);
        
        if ($token === false) {
             $this->error = '获取AccessToken失败，请查看日志';
             return false;
        }

        return $token;
    }

    /**
     * 获取云打印 AccessToken (用于前端 JS SDK)
     * 兼容旧方法名，实际调用通用的 getOAuth2AccessToken
     * @return string|false Token
     */
    public function getCloudPrintAccessToken()
    {
        return $this->getOAuth2AccessToken();
    }

    /**
     * 获取面单图片（云打印插件接口）
     * 对接 COM_RECE_CLOUD_PRINT_PARSEDDATA
     * @param int $order_id 订单ID
     * @param array $options 选项
     *   - print_mode: 'all'(全部) | 'mother'(仅母单) | 'child'(仅子单) 默认: 'mother'
     *   - child_ids: 子单ID数组(print_mode='child'时必填)
     * @return array|string|false 返回 ParsedData 数组或 URL 字符串
     */
    public function printlabelParsedData($order_id, $options = [])
    {
        // 1. 获取订单信息
        $order = Inpack::detail($order_id);
        if (!$order) {
            $this->error = '订单不存在';
            return false;
        }
        
        // 转换为数组（兼容模型对象）
        if (is_object($order)) {
            $orderArray = $order->toArray();
        } else {
            $orderArray = $order;
        }

        $waybillNo = isset($options['waybill_no']) ? $options['waybill_no'] : '';
        
        // 2. 解析打印模式
        $printMode = isset($options['print_mode']) ? $options['print_mode'] : 'mother';
        
        // 获取所有子单（用于计算 sum 和获取母单号）
        $allPackages = $this->getChildPackages($orderArray, []);
        
        // 调试日志
        \think\Log::info('getChildPackages 返回数量: ' . count($allPackages));
        \think\Log::info('allPackages 数据: ' . json_encode($allPackages, JSON_UNESCAPED_UNICODE));
        
        // 获取母单号
        $motherWaybillNo = isset($orderArray['t_order_sn']) ? $orderArray['t_order_sn'] : '';
        
        // 如果母单号为空，从第一个子单获取（第一个子单号就是母单号）
        if (empty($motherWaybillNo) && !empty($allPackages)) {
            $firstPackage = $allPackages[0];
            $motherWaybillNo = isset($firstPackage['t_order_sn']) ? $firstPackage['t_order_sn'] : '';
            
            // 更新订单的母单号（用于后续逻辑）
            if (!empty($motherWaybillNo)) {
                $orderArray['t_order_sn'] = $motherWaybillNo;
            }
        }
        
        // 计算实际的不同运单号数量（用于 sum）
        // 注意：母单本身就是第一个子单，所以 sum = 所有子单数量
        $uniqueWaybills = [];
        
        // 收集所有子单的运单号（包括和母单号相同的）
        foreach ($allPackages as $pkg) {
            $childWaybillNo = isset($pkg['t_order_sn']) ? $pkg['t_order_sn'] : '';
            
            // 兼容旧的 package 表结构（有 express_num 字段）
            if (empty($childWaybillNo) && !empty($pkg['express_num'])) {
                if (strpos($pkg['express_num'], 'SF') === 0) {
                    $childWaybillNo = $pkg['express_num'];
                }
            }
            
            if (!empty($childWaybillNo)) {
                $uniqueWaybills[$childWaybillNo] = true;
            }
        }
        
        // sum = 所有子单的数量（包括母单本身）
        // 根据 API 文档：sum 是子母件运单总数
        $sum = count($uniqueWaybills);
        
        // 如果没有传递 waybill_no，使用母单号
        if (empty($waybillNo)) {
            $waybillNo = $motherWaybillNo;
        }

        if (empty($waybillNo)) {
            $this->error = '运单号不存在';
            return false;
        }
        
        // 判断当前打印的是母单还是子单
        $isPrintingChild = ($waybillNo !== $motherWaybillNo);
        
        $documents = [];
        
        // 3. 构建 documents 数组
        if ($isPrintingChild) {
            // 打印子单：找到对应的包裹
            $childSeq = 1; // 默认从1开始（如果找不到对应包裹）
            foreach ($allPackages as $index => $pkg) {
                $childWaybillNo = isset($pkg['t_order_sn']) ? $pkg['t_order_sn'] : '';
                
                // 兼容旧的 package 表结构（有 express_num 字段）
                if (empty($childWaybillNo) && !empty($pkg['express_num'])) {
                    if (strpos($pkg['express_num'], 'SF') === 0) {
                        $childWaybillNo = $pkg['express_num'];
                    }
                }
                
                if ($childWaybillNo === $waybillNo) {
                    // 找到了对应的子单，seq = index + 2（因为母单是1）
                    $childSeq = $index + 2;
                    $documents[] = $this->buildDocument($pkg, $waybillNo, $motherWaybillNo, $childSeq, $sum);
                    break;
                }
            }
            
            if (empty($documents)) {
                // 记录调试信息
                \think\Log::error('未找到对应的子单包裹: ' . json_encode([
                    'waybill_no' => $waybillNo,
                    'mother_waybill_no' => $motherWaybillNo,
                    'total_packages' => count($allPackages),
                    'package_waybills' => array_map(function($pkg) {
                        return isset($pkg['t_order_sn']) ? $pkg['t_order_sn'] : 'N/A';
                    }, $allPackages)
                ], JSON_UNESCAPED_UNICODE));
                
                $this->error = '未找到对应的子单包裹';
                return false;
            }
        } elseif ($printMode === 'all') {
            // 打印全部：母单 + 所有子单
            // 根据 API 文档：
            // - 母单：seq = 1（母单本身就是子单1）
            // - 其他子单：seq 从 2 开始递增
            // - sum = 所有子单的总数
            
            // 添加母单（seq = 1）
            $documents[] = $this->buildDocument($order, $motherWaybillNo, null, 1, $sum);
            
            // 添加其他子单（seq 从 2 开始）
            $childSeq = 2;
            foreach ($allPackages as $index => $pkg) {
                $childWaybillNo = isset($pkg['t_order_sn']) ? $pkg['t_order_sn'] : '';
                
                // 兼容旧的 package 表结构
                if (empty($childWaybillNo) && !empty($pkg['express_num'])) {
                    if (strpos($pkg['express_num'], 'SF') === 0) {
                        $childWaybillNo = $pkg['express_num'];
                    }
                }
                
                if (empty($childWaybillNo)) {
                    \think\Log::warning("包裹 {$pkg['id']} 没有有效的顺丰子运单号,跳过打印");
                    continue;
                }
                
                // 跳过和母单号相同的子单（因为母单已经添加了，seq=1）
                if ($childWaybillNo === $motherWaybillNo) {
                    \think\Log::info("跳过与母单号相同的子单: {$childWaybillNo}（母单已添加，seq=1）");
                    continue;
                }
                
                // 添加子单，使用 branchWaybillNo 参数
                $documents[] = $this->buildDocument($pkg, $childWaybillNo, $motherWaybillNo, $childSeq, $sum);
                $childSeq++; // 递增子单序号
            }
            
            \think\Log::info('顺丰打印全部: ' . json_encode([
                'mother_waybill' => $motherWaybillNo,
                'total_documents' => count($documents),
                'sum' => $sum,
                'unique_waybills' => count($uniqueWaybills),
                'all_packages_count' => count($allPackages),
                'documents_waybills' => array_map(function($doc) {
                    return isset($doc['masterWaybillNo']) ? $doc['masterWaybillNo'] : 'N/A';
                }, $documents)
            ], JSON_UNESCAPED_UNICODE));
        } else {
            // 打印母单（默认）
            if ($sum > 0) {
                // 有子单的情况：seq = 1, sum = 总数
                $documents[] = $this->buildDocument($order, $motherWaybillNo, null, 1, $sum);
            } else {
                // 单票运单：不传 seq 和 sum
                $documents[] = $this->buildDocument($order, $motherWaybillNo, null, 0, 0);
            }
        }
        
        if (empty($documents)) {
            $this->error = '没有可打印的运单';
            return false;
        }
        
        // 5. 验证 documents 参数
        try {
            $this->validateDocuments($documents);
        } catch (\Exception $e) {
            $this->error = '参数验证失败: ' . $e->getMessage();
            return false;
        }

        // 6. 调用顺丰云打印插件接口
        $baseUrl = isset($this->config['apiurl']) && $this->config['apiurl'] !== ''
            ? rtrim($this->config['apiurl'], '/')
            : 'https://sfapi.sf-express.com/std/service';

        // 动态构建模板编码
        // 插件接口通常需要明确的模板编码，如果未配置则尝试通用模板
        if (isset($this->config['template_code']) && !empty($this->config['template_code'])) {
            $templateCode = $this->config['template_code'];
        } else {
            $partnerID = isset($this->config['key']) ? $this->config['key'] : '';
            $templateCode = 'fm_76130_standard_' . $partnerID;
        }
        
        $msgData = [
            'templateCode' => $templateCode, 
            'version' => '2.0',
            'fileType' => 'json', // 插件接口通常返回 json 格式的点阵/绘制指令
            'sync' => true,
            'documents' => $documents  // 支持多个运单
        ];
        
        // 调试日志：记录发送给 API 的 documents
        \think\Log::info('发送给顺丰 API 的 documents: ' . json_encode($documents, JSON_UNESCAPED_UNICODE));

        $requestData = [
            'partnerID' => isset($this->config['key']) ? $this->config['key'] : '',
            'requestID' => $this->generateRequestId(),
            'serviceCode' => 'COM_RECE_CLOUD_PRINT_PARSEDDATA', // 插件接口
            'timestamp' => time(),
            'msgData' => json_encode($msgData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        $requestData['msgDigest'] = $this->generateSignature($requestData);
        
        // 调试日志：记录完整的请求数据
        \think\Log::info('顺丰 API 请求 msgData: ' . $requestData['msgData']);

        $resp = $this->httpPost($baseUrl, http_build_query($requestData));
        if ($resp === false) {
            return false;
        }

        $data = json_decode($resp, true);
        if (!is_array($data) || !isset($data['apiResultCode']) || $data['apiResultCode'] !== 'A1000') {
            // 使用增强的错误处理
            return $this->handleApiError($data, [
                'requestID' => $requestData['requestID'],
                'serviceCode' => 'COM_RECE_CLOUD_PRINT_PARSEDDATA',
                'documents_count' => count($documents)
            ]);
        }

        $apiResultData = isset($data['apiResultData']) ? json_decode($data['apiResultData'], true) : [];
        
        // 7. 解析返回的数据
        $files = isset($apiResultData['obj']['files']) ? $apiResultData['obj']['files'] : [];

        if (empty($files)) {
             $this->error = '未返回面单数据: ' . json_encode($apiResultData, JSON_UNESCAPED_UNICODE);
             return false;
        }
        
        // 8. 构建符合 SDK 要求的完整数据结构
        // SDK print() 方法需要: requestID, accessToken, templateCode, documents, version
        
        // 获取 accessToken (通过 OAuth2 认证，带缓存)
        $isSandbox = (strpos($baseUrl, 'sbox') !== false);
        $partnerId = isset($this->config['key']) ? $this->config['key'] : '';
        $secret = isset($this->config['token']) ? $this->config['token'] : '';
        
        if (empty($partnerId) || empty($secret)) {
            $this->error = '缺少 OAuth 认证参数';
            return false;
        }
        
        // 调用 OAuth 类获取 accessToken（有缓存，第二次调用很快）
        $accessToken = \app\common\library\Sf\OAuth::getAccessToken($partnerId, $secret, $isSandbox);
        
        if ($accessToken === false) {
            $this->error = '获取 accessToken 失败';
            return false;
        }
        
        $sdkData = [
            'requestID' => $requestData['requestID'],
            'accessToken' => $accessToken,
            'templateCode' => $msgData['templateCode'],
            'documents' => $documents,
            'version' => $msgData['version'],
            'files' => $files
        ];
        
        // 4. 根据打印模式返回数据
        // 如果是打印全部模式，直接返回 SDK 数据结构（包含所有运单）
        if ($printMode === 'all') {
            // 打印全部：返回完整的 SDK 数据结构，包含所有运单
            \think\Log::info('顺丰打印全部模式: ' . json_encode([
                'documents_count' => count($documents),
                'files_count' => count($files),
                'waybill_nos' => array_map(function($doc) {
                    return isset($doc['masterWaybillNo']) ? $doc['masterWaybillNo'] : 'N/A';
                }, $documents)
            ], JSON_UNESCAPED_UNICODE));
            
            return $sdkData;
        }
        
        if (count($files) === 1) {
            // 单个运单: 返回 SDK 所需的完整数据结构
            $fileData = $files[0];
            
            // 场景A: 返回 contents (JSON 渲染数据) - 用于云打印插件
            if (isset($fileData['contents'])) {
                // 返回符合 SDK 要求的数据结构
                return $sdkData;
            }

            // 场景B: 返回 url / images / content (PDF/图片)
            $picData = isset($fileData['url']) ? $fileData['url'] : '';
            if (isset($fileData['images']) && is_array($fileData['images'])) {
                $picData = $fileData['images'][0];
            }
            
            if (!empty($picData)) {
                 return $this->downloadAndSave($waybillNo, $picData, isset($fileData['token']) ? $fileData['token'] : '');
            }

            if (isset($fileData['content']) && !empty($fileData['content'])) {
                 return $this->saveBase64Directly($waybillNo, $fileData['content']);
            }
            
            $this->error = '未识别的返回数据格式';
            return false;
        } else {
            // 多个运单: 返回完整的 SDK 数据结构
            return $sdkData;
        }
    }

    private function saveBase64Directly($waybillNo, $content)
    {
        $webPath = ROOT_PATH . 'web' . DS . 'uploads' . DS . 'sf_label';
        if (!file_exists($webPath)) {
            mkdir($webPath, 0755, true);
        }
        $fileName = $waybillNo . '_' . time() . '_parsed.pdf';
        $filePath = $webPath . DS . $fileName;
        
        if (file_put_contents($filePath, base64_decode($content))) {
            return $this->getUploadUrl($fileName);
        }
        $this->error = 'Base64文件保存失败';
        return false;
    }

    private function downloadAndSave($waybillNo, $picData, $fileToken)
    {
        $webPath = ROOT_PATH . 'web' . DS . 'uploads' . DS . 'sf_label';
        if (!file_exists($webPath)) {
            mkdir($webPath, 0755, true);
        }

        $fileName = $waybillNo . '_' . time() . '_sync.pdf'; 
        $filePath = $webPath . DS . $fileName;

        // 尝试保存
        if (filter_var($picData, FILTER_VALIDATE_URL)) {
             // 使用 curl 下载 PDF
             $ch = curl_init($picData);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
             curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
             curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
             curl_setopt($ch, CURLOPT_TCP_NODELAY, true); 
             curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
             curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
             
             if (!empty($fileToken)) {
                 curl_setopt($ch, CURLOPT_HTTPHEADER, [
                     'X-Auth-Token: ' . $fileToken
                 ]);
             }
             
             $content = curl_exec($ch);
             $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
             
             if ($httpCode == 200 && $content) {
                 $res = file_put_contents($filePath, $content);
                 curl_close($ch);
                 if ($res) {
                     return $this->getUploadUrl($fileName);
                 } else {
                    $this->error = '文件保存失败，请检查目录权限';
                    return false;
                 }
             } else {
                 $curlError = curl_error($ch);
                 $this->error = "云打印文件下载失败 HTTP: {$httpCode}, CURL: {$curlError}";
                 curl_close($ch);
                 \think\Log::error($this->error);
                 return false; 
             }
        } else {
             // Base64 解码保存
             $result = file_put_contents($filePath, base64_decode($picData));
             if ($result === false) {
                $this->error = '面单保存失败';
                return false;
             }
             return $this->getUploadUrl($fileName);
        }
    }

    /**
     * 下载PDF到本地并返回本地路径
     * @param string $waybillNo 运单号
     * @param string $picData PDF URL或Base64
     * @param string $fileToken 访问令牌
     * @return string|false 本地文件路径
     */
    private function downloadAndSaveLocal($waybillNo, $picData, $fileToken)
    {
        $webPath = ROOT_PATH . 'web' . DS . 'uploads' . DS . 'sf_label';
        if (!file_exists($webPath)) {
            mkdir($webPath, 0755, true);
        }

        $fileName = $waybillNo . '_' . time() . '_' . mt_rand(1000, 9999) . '.pdf'; 
        $filePath = $webPath . DS . $fileName;

        if (filter_var($picData, FILTER_VALIDATE_URL)) {
             $ch = curl_init($picData);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
             curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
             curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
             
             if (!empty($fileToken)) {
                 curl_setopt($ch, CURLOPT_HTTPHEADER, [
                     'X-Auth-Token: ' . $fileToken
                 ]);
             }
             
             $content = curl_exec($ch);
             $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
             curl_close($ch);
             
             if ($httpCode == 200 && $content) {
                 if (file_put_contents($filePath, $content)) {
                     return $filePath;
                 }
             }
             return false;
        } else {
             // Base64 解码保存
             if (file_put_contents($filePath, base64_decode($picData))) {
                 return $filePath;
             }
             return false;
        }
    }

    /**
     * 合并多个PDF文件为一个
     * @param array $pdfPaths PDF文件路径数组
     * @param string $waybillNo 母件运单号
     * @return string|false 合并后的PDF路径
     */
    private function mergePDFs($pdfPaths, $waybillNo)
    {
        try {
            // 检查FPDI库是否存在
            if (!class_exists('FPDI')) {
                require_once ROOT_PATH . 'vendor/setasign/fpdi/fpdi.php';
            }
            
            $pdf = new \FPDI();
            
            // 遍历所有PDF文件
            foreach ($pdfPaths as $path) {
                if (!file_exists($path)) {
                    continue;
                }
                
                // 获取页数
                $pageCount = $pdf->setSourceFile($path);
                
                // 导入所有页面
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);
                    
                    // 添加页面
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }
            }
            
            // 保存合并后的PDF
            $webPath = ROOT_PATH . 'web' . DS . 'uploads' . DS . 'sf_label';
            $mergedFileName = $waybillNo . '_merged_' . time() . '.pdf';
            $mergedPath = $webPath . DS . $mergedFileName;
            
            $pdf->Output('F', $mergedPath);
            
            return $mergedPath;
            
        } catch (\Exception $e) {
            \think\Log::error('PDF合并失败: ' . $e->getMessage());
            $this->error = 'PDF合并失败: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * 验证 documents 参数
     * @param array $documents 文档数组
     * @return bool
     * @throws \Exception
     */
    private function validateDocuments($documents)
    {
        if (!is_array($documents) || empty($documents)) {
            throw new \Exception('documents 不能为空');
        }
        
        foreach ($documents as $index => $doc) {
            if (!isset($doc['masterWaybillNo']) || empty($doc['masterWaybillNo'])) {
                throw new \Exception("documents[{$index}] 缺少 masterWaybillNo");
            }
            
            // 验证运单号格式(顺丰运单号通常以 SF 开头,12位数字)
            $waybillNo = $doc['masterWaybillNo'];
            if (!preg_match('/^SF\d{12}$/', $waybillNo)) {
                \think\Log::warning("运单号格式可能不正确: {$waybillNo}");
            }
        }
        
        return true;
    }
    
    /**
     * 验证 API 返回的 ParsedData
     * @param array $fileData 文件数据
     * @return bool
     * @throws \Exception
     */
    private function validateParsedData($fileData)
    {
        $errors = [];
        
        // 必需字段检查
        if (!isset($fileData['contents'])) {
            $errors[] = '缺少 contents 字段';
        }
        
        if (!isset($fileData['waybillNo'])) {
            $errors[] = '缺少 waybillNo 字段';
        }
        
        // contents 格式检查
        if (isset($fileData['contents'])) {
            $contents = is_string($fileData['contents']) 
                ? json_decode($fileData['contents'], true) 
                : $fileData['contents'];
            
            if (!is_array($contents) && !is_string($fileData['contents'])) {
                $errors[] = 'contents 格式错误(应为 JSON 对象或字符串)';
            }
        }
        
        if (!empty($errors)) {
            throw new \Exception('ParsedData 验证失败: ' . implode(', ', $errors));
        }
        
        return true;
    }
    
    /**
     * 增强的错误处理
     * @param array $data API 响应数据
     * @param array $context 上下文信息
     * @return bool
     */
    private function handleApiError($data, $context = [])
    {
        $errorCode = isset($data['apiResultCode']) ? $data['apiResultCode'] : 'UNKNOWN';
        $errorMsg = isset($data['apiErrorMsg']) ? $data['apiErrorMsg'] : '未知错误';
        
        // 错误码映射
        $errorMap = [
            'A1001' => '签名验证失败',
            'A1002' => '参数错误',
            'A1003' => '运单号不存在',
            'A1004' => '模板不存在',
            'A1005' => 'AccessToken 无效',
            'A1006' => '服务异常',
            'A1007' => '请求超时'
        ];
        
        $friendlyMsg = isset($errorMap[$errorCode]) ? $errorMap[$errorCode] : $errorMsg;
        
        // 记录详细日志
        \think\Log::error('顺丰 API 错误: ' . json_encode([
            'error_code' => $errorCode,
            'error_msg' => $errorMsg,
            'friendly_msg' => $friendlyMsg,
            'context' => $context,
            'request_id' => isset($context['requestID']) ? $context['requestID'] : '',
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE));
        
        $this->error = "[{$errorCode}] {$friendlyMsg}";
        
        return false;
    }

    /**
     * 获取子包裹列表
     * @param array|object $order 订单信息（支持数组或模型对象）
     * @param array $childIds 指定的子单ID数组(为空则获取全部)
     * @return array 包裹列表
     */
    private function getChildPackages($order, $childIds = [])
    {
        // 转换为数组（兼容模型对象）
        if (is_object($order)) {
            $order = $order->toArray();
        }
        
        $orderId = isset($order['id']) ? $order['id'] : 0;
        if (empty($orderId)) {
            return [];
        }
        
        // 优先从 yoshop_inpack_item 表获取（新的子母单系统）
        $query = \think\Db::table('yoshop_inpack_item')
            ->where('inpack_id', $orderId);
        
        // 如果指定了子单ID,只查询指定的
        if (!empty($childIds)) {
            $query->whereIn('id', $childIds);
        }
        
        $items = $query->select();
        
        // 如果找到了子单，直接返回
        if (!empty($items)) {
            \think\Log::info('从 yoshop_inpack_item 表获取到 ' . count($items) . ' 个子单');
            return $items;
        }
        
        // 如果没有找到，尝试从 yoshop_package 表获取（旧的包裹系统）
        $packageIds = [];
        
        if (!empty($order['pack_ids'])) {
            $packageIds = explode(',', $order['pack_ids']);
            $packageIds = array_filter($packageIds);
        }
        
        if (!empty($packageIds)) {
            $query = \think\Db::table('yoshop_package')
                ->whereIn('id', $packageIds);
            
            // 如果指定了子单ID,只查询指定的
            if (!empty($childIds)) {
                $query->whereIn('id', $childIds);
            }
            
            $packages = $query->select();
            \think\Log::info('从 yoshop_package 表获取到 ' . count($packages) . ' 个包裹');
            return $packages;
        }
        
        \think\Log::warning('未找到任何子单数据，订单ID: ' . $orderId);
        return [];
    }
    
    /**
     * 构建 document 对象（支持自定义字段映射）
     * @param array $data 订单或包裹数据
     * @param string $waybillNo 运单号
     * @param string $parentWaybillNo 母单号(子单时传入)
     * @return array document 对象
     */
    private function buildDocument($data, $waybillNo, $parentWaybillNo = null, $seq = 0, $sum = 0)
    {
        $document = [
            'masterWaybillNo' => $waybillNo
        ];
        
        // 添加子单号(子单时)
        if ($parentWaybillNo) {
            $document['branchWaybillNo'] = $waybillNo;
            $document['masterWaybillNo'] = $parentWaybillNo;
        }
        
        // 添加 seq 和 sum（子母单必填）
        if ($seq > 0 && $sum > 0) {
            $document['seq'] = (string)$seq;
            $document['sum'] = (string)$sum;
        }
        
        // 应用自定义字段映射
        // 注意: 配置可能在 sf_waybill_config 或 custom_fields 字段中
        $customFields = [];
        
        // 优先从 sf_waybill_config 中获取
        if (isset($this->config['sf_waybill_config'])) {
            $waybillConfig = $this->config['sf_waybill_config'];
            
            // 如果是字符串，解析为数组
            if (is_string($waybillConfig)) {
                $decoded = json_decode($waybillConfig, true);
                if (is_array($decoded) && isset($decoded['custom_fields'])) {
                    $customFields = $decoded['custom_fields'];
                }
            } elseif (is_array($waybillConfig) && isset($waybillConfig['custom_fields'])) {
                $customFields = $waybillConfig['custom_fields'];
            }
        }
        
        // 兼容旧的 custom_fields 字段
        if (empty($customFields) && isset($this->config['custom_fields'])) {
            $customFields = $this->config['custom_fields'];
            
            // 如果是字符串，解析为数组
            if (is_string($customFields)) {
                $decoded = json_decode($customFields, true);
                if (is_array($decoded)) {
                    $customFields = $decoded;
                } else {
                    $customFields = [];
                }
            }
        }
        
        // 应用字段映射
        if (!empty($customFields) && is_array($customFields)) {
            foreach ($customFields as $apiField => $dataField) {
                // 确保键和值都是字符串
                if (!is_string($apiField) || !is_string($dataField)) {
                    continue;
                }
                
                if (isset($data[$dataField]) && !empty($data[$dataField])) {
                    $document[$apiField] = $data[$dataField];
                }
            }
        }
        
        // 默认备注(如果没有通过映射配置)
        if (!isset($document['remark'])) {
            $document['remark'] = $this->buildRemark($data);
        }
        
        return $document;
    }

    /**
     * 构建备注信息
     * @param array $data 订单或包裹数据
     * @param string $prefix 前缀(如"母单"、"子单1")
     * @return string 备注文本
     */
    private function buildRemark($data, $prefix = '')
    {
        $parts = [];
        
        if (!empty($prefix)) {
            $parts[] = $prefix;
        }
        
        if (!empty($data['buyer_remark'])) {
            $parts[] = '买家: ' . $data['buyer_remark'];
        }
        
        if (!empty($data['seller_remark'])) {
            $parts[] = '卖家: ' . $data['seller_remark'];
        }
        
        if (!empty($data['remark'])) {
            $parts[] = $data['remark'];
        }
        
        return !empty($parts) ? implode(' | ', $parts) : '';
    }

    /**
     * 获取面单图片（云打印）
     * 对接 COM_RECE_CLOUD_PRINT_WAYBILLS
     * @param int $order_id 订单ID
     * @return string|false 图片/PDF URL
     */
    public function printlabel($order_id)
    {
        // 1. 获取订单信息
        $order = Inpack::detail($order_id);
        if (!$order) {
            $this->error = '订单不存在';
            return false;
        }

        $waybillNo = isset($order['t_order_sn']) ? $order['t_order_sn'] : '';
        if (empty($waybillNo)) {
            // 尝试取 order_sn 或者 partner_order_code
            $waybillNo = isset($order['order_sn']) ? $order['order_sn'] : '';
        }

        if (empty($waybillNo)) {
            $this->error = '运单号不存在';
            return false;
        }

        // 2. 调用顺丰云打印接口
        $baseUrl = isset($this->config['apiurl']) && $this->config['apiurl'] !== ''
            ? rtrim($this->config['apiurl'], '/')
            : 'https://sfapi.sf-express.com/std/service';

        // 动态构建模板编码
        // 优先使用配置中的 template_code，否则使用默认的 fm_76130_standard_{partnerID}
        if (isset($this->config['template_code']) && !empty($this->config['template_code'])) {
            $templateCode = $this->config['template_code'];
        } else {
            $partnerID = isset($this->config['key']) ? $this->config['key'] : '';
            $templateCode = 'fm_76130_standard_' . $partnerID;
        }
        
        // 获取同步/异步配置 (默认同步)
        // 1: 异步, 0/null: 同步 (根据通常习惯，或者反过来，需看具体配置定义。这里假设 config['sync_mode'] == 1 为异步)
        // 修正：用户通常习惯 "开启异步" -> sync_mode = 1.
        // SF 接口参数 sync: true (同步), false (异步)
        $isAsync = isset($this->config['sync_mode']) && $this->config['sync_mode'] == 1;
        $syncParam = !$isAsync;

        $msgData = [
            'templateCode' => $templateCode, 
            'version' => '2.0',
            'fileType' => 'pdf', // 请求返回 PDF 格式
            'sync' => $syncParam, // 根据配置设置
            'documents' => []
        ];
        
        // ⭐ 子母件PDF合并支持
        // 注意: 沙箱环境可能不支持mergePdfBoolean参数,需要在生产环境测试
        // 如果API不支持,会在后续使用本地FPDI库合并
        if (count($packageIds) > 1) {
            $msgData['mergePdfBoolean'] = true;
            $msgData['mergeType'] = 'all';
        }
        
        // ⭐ 修复：支持子母件打印
        // 检查是否有子件(从pack_ids字段)
        $packageIds = [];
        if (!empty($order['pack_ids'])) {
            $packageIds = explode(',', $order['pack_ids']);
            $packageIds = array_filter($packageIds);
        }
        
        // 添加母件
        $msgData['documents'][] = [
            'masterWaybillNo' => $waybillNo,
            'remark' => isset($order['remark']) ? $order['remark'] : '母件'
        ];
        
        // 如果有多个包裹,添加子件
        if (count($packageIds) > 1) {
            // 查询包裹信息获取子运单号
            $packages = \think\Db::table('yoshop_package')
                ->whereIn('id', $packageIds)
                ->select();
            
            foreach ($packages as $index => $pkg) {
                // 优先使用包裹表中存储的运单号
                $childWaybillNo = '';
                
                // 检查 express_num 字段是否包含顺丰运单号(SF开头)
                if (!empty($pkg['express_num']) && strpos($pkg['express_num'], 'SF') === 0) {
                    $childWaybillNo = $pkg['express_num'];
                }
                
                // 如果没有有效的子运单号,跳过该包裹
                // 注意: 不能随意构造子运单号,必须使用顺丰API返回的真实运单号
                if (empty($childWaybillNo)) {
                    \think\Log::warning("包裹 {$pkg['id']} 没有有效的顺丰子运单号,跳过打印");
                    continue;
                }
                
                $msgData['documents'][] = [
                    'masterWaybillNo' => $childWaybillNo,
                    'parentWaybillNo' => $waybillNo,
                    'remark' => '子件' . ($index + 1)
                ];
            }
        }
        
        // 更新: 移除之前错误的 content 嵌套结构
        // $content = [ ... ];
        // $msgData['documents'][0]['content'] = $content;

        $requestData = [
            'partnerID' => isset($this->config['key']) ? $this->config['key'] : '',
            'requestID' => $this->generateRequestId(),
            'serviceCode' => 'COM_RECE_CLOUD_PRINT_WAYBILLS', // 云打印接口
            'timestamp' => time(),
            'msgData' => json_encode($msgData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        $requestData['msgDigest'] = $this->generateSignature($requestData);

        $resp = $this->httpPost($baseUrl, http_build_query($requestData));
        if ($resp === false) {
            return false;
        }

        $data = json_decode($resp, true);
        if (!is_array($data) || !isset($data['apiResultCode']) || $data['apiResultCode'] !== 'A1000') {
            $this->error = isset($data['apiErrorMsg']) ? $data['apiErrorMsg'] : '云打印接口调用失败';
            return false;
        }

        // 如果是异步模式，直接返回提示
        if ($isAsync) {
             // 返回符合规范的成功响应结构 (如果调用方需要解析 JSON)
             // 或者直接返回简单字符串，视上层业务逻辑而定
             // 这里保持原样返回字符串，但在日志或外层可以处理
             return '异步请求已发送，请等待回调推送';
        }

        $apiResultData = isset($data['apiResultData']) ? json_decode($data['apiResultData'], true) : [];
        
        // 3. 解析返回的文件数据
        // 云打印接口返回结构：obj -> files -> [ { "url": "...", "token": "..." } ]
        $files = isset($apiResultData['obj']['files']) ? $apiResultData['obj']['files'] : [];
        if (empty($files)) {
             $this->error = '未返回打印文件数据';
             return false;
        }
        
        // ⭐ 修复：处理文件(支持子母件PDF合并)
        $downloadedPaths = [];
        foreach ($files as $index => $fileData) {
            $picData = isset($fileData['url']) ? $fileData['url'] : '';
            $fileToken = isset($fileData['token']) ? $fileData['token'] : '';
            $fileWaybillNo = isset($fileData['waybillNo']) ? $fileData['waybillNo'] : $waybillNo;
            
            if (empty($picData)) {
                continue;
            }
            
            $localPath = $this->downloadAndSaveLocal($fileWaybillNo, $picData, $fileToken);
            if ($localPath) {
                $downloadedPaths[] = $localPath;
            }
        }
        
        if (empty($downloadedPaths)) {
            $this->error = '未获取到有效的 PDF 链接';
            return false;
        }
        
        // 如果有多个PDF文件(说明API的mergePdfBoolean没生效),使用本地合并
        if (count($downloadedPaths) > 1) {
            $mergedPath = $this->mergePDFs($downloadedPaths, $waybillNo);
            if ($mergedPath) {
                // 删除原始的单个PDF文件
                foreach ($downloadedPaths as $path) {
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
                // 返回合并后的PDF URL
                return $this->getUploadUrl(basename($mergedPath));
            }
        }
        
        // 返回第一个文件的URL
        return $this->getUploadUrl(basename($downloadedPaths[0]));
    }
}
