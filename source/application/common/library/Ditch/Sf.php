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
            CURLOPT_TIMEOUT        => 30, // 恢复超时时间
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => $headers,
        ]);



        $maxRetries = 1; // 减少重试次数
        $attempt = 0;
        $result = false;
        $err = '';

        do {
            $attempt++;
            $result = curl_exec($ch);
            $err = curl_error($ch);
            
            // 记录日志
            $logData = [
                'time' => date('Y-m-d H:i:s'),
                'url' => $url,
                'attempt' => $attempt,
                'request_body' => $body,
                'response' => $result,
                'error' => $err,
                'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE)
            ];
            // 简单写入日志文件
            $logDir = dirname(ROOT_PATH) . DS . 'runtime' . DS . 'log' . DS . 'sf_express';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            file_put_contents($logDir . DS . date('Ym') . '.log', json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);

            if (!$err && $result !== false) {
                break;
            }
            
            // 重试间隔
            if ($attempt <= $maxRetries) {
                usleep(500000); // 500ms
            }

        } while ($attempt <= $maxRetries);

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

        // 动态构建模板编码: fm_76130_standard_{partnerID}
        $partnerID = isset($this->config['key']) ? $this->config['key'] : '';
        $templateCode = 'fm_76130_standard_' . $partnerID;
        
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
            'documents' => [
                [
                    'masterWaybillNo' => $waybillNo,
                    // 云打印 2.0 接口说明：
                    // 用户只需要提供运单号等关键字段即可，云打印会查询订单系统的订单数据。
                    // 因此不需要传递详细的 sender/consignee 等 content 信息。
                    // 仅当需要自定义区域备注时传 remark
                    // 'remark' => '...' 
                ]
            ]
        ];
        
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
        
        $fileData = $files[0];
        $picData = isset($fileData['url']) ? $fileData['url'] : '';
        // 获取 Token
        $fileToken = isset($fileData['token']) ? $fileData['token'] : '';

        if (empty($picData)) {
            $this->error = '未获取到有效的 PDF 链接';
            return false;
        }

        $webPath = ROOT_PATH . 'web' . DS . 'uploads' . DS . 'sf_label';
        // 如果目录不存在则创建
        if (!file_exists($webPath)) {
            mkdir($webPath, 0755, true);
        }

        $fileName = $waybillNo . '_' . time() . '_sync.pdf'; // 默认 PDF
        $filePath = $webPath . DS . $fileName;

        // 尝试保存
        if (filter_var($picData, FILTER_VALIDATE_URL)) {
             // 使用 curl 下载 PDF
             $ch = curl_init($picData);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
             // 优化下载速度：
             // 1. 减少超时时间 (默认30s可能过长，如果服务器响应慢)
             // 2. 启用 TCP_FASTOPEN (如果系统支持)
             // 3. 禁用不必要的检查
             curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 恢复并增加超时时间，确保下载完成
             curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // 连接超时 30s
             curl_setopt($ch, CURLOPT_TCP_NODELAY, true); // 禁用 Nagle 算法
             curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 不校验 Host
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 不校验 Peer
             curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 跟随重定向
             
             // 如果有 Token，添加到 Header
             if (!empty($fileToken)) {
                 curl_setopt($ch, CURLOPT_HTTPHEADER, [
                     'X-Auth-Token: ' . $fileToken
                 ]);
             }
             
             $content = curl_exec($ch);
             $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
             
             if ($httpCode == 200 && $content) {
                 $res = file_put_contents($filePath, $content);
                 curl_close($ch); // 关闭要在使用完后
                 if ($res) {
                     return $this->getUploadUrl($fileName);
                 } else {
                    $this->error = '文件保存失败，请检查目录权限';
                    return false;
                 }
             } else {
                 // 下载失败，记录日志
                 $curlError = curl_error($ch);
                 $this->error = "云打印文件下载失败 HTTP: {$httpCode}, CURL: {$curlError}";
                 curl_close($ch); // 关闭
                 \think\Log::error($this->error);
                 return false; // 强制失败，不降级
             }
        } else {
             // Base64 解码保存
             $result = file_put_contents($filePath, base64_decode($picData));
             if ($result === false) {
                $this->error = '面单保存失败';
                return false;
             }
        }

        // 返回 URL
        // 假设 public URL 映射
        $request = \think\Request::instance();
        $domain = $request->domain();
        $url = $domain . '/uploads/sf_label/' . $fileName;

        return $url;
    }
}
