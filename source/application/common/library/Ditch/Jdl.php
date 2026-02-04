<?php
namespace app\common\library\Ditch;

use app\common\library\Jdl\JdlAuth;
use app\common\library\Jdl\JdlConfig;
use app\common\library\Jdl\JdlClient;
use think\Db;
use think\Log;

/**
 * 京东物流适配器类
 * 完全基于京东官方文档和SDK重写
 * 
 * 数据库字段映射：
 * - app_key → AppKey (应用密钥)
 * - shop_key → AppSecret (应用秘钥，用于签名)
 * - app_token → AccessToken (访问令牌，从数据库读取)
 * - account_password → RefreshToken (刷新令牌)
 * - customer_code → 商家编码（月结账号）
 * 
 * @package app\common\library\Ditch
 */
class Jdl
{
    private $config;
    private $error = '';
    private $client;

    /**
     * 构造函数
     * @param array $config 数据库配置
     */
    public function __construct($config)
    {
        $this->config = $config;
        
        // 验证必要配置
        if (empty($config['app_key']) || empty($config['shop_key'])) {
            $this->error = '京东物流配置不完整：缺少AppKey或AppSecret';
            Log::error($this->error);
            return;
        }
        
        // 从数据库读取AccessToken
        $accessToken = isset($config['app_token']) ? $config['app_token'] : '';
        if (empty($accessToken)) {
            $this->error = '京东物流AccessToken为空，请先完成OAuth授权';
            Log::error($this->error);
            return;
        }
        
        // 判断环境
        $isSandbox = (isset($config['api_url']) && strpos($config['api_url'], 'uat') !== false);
        
        // 初始化SDK客户端
        $this->client = new JdlClient(
            $config['app_key'],
            $config['shop_key'],
            $accessToken,
            $isSandbox
        );
    }

    /**
     * 创建订单
     * @param array $data 订单数据
     * @return array
     */
    public function createOrder($data)
    {
        try {
            if (!$this->client) {
                return $this->buildErrorResponse('客户端未初始化');
            }
            
            // 构建请求参数（按照京东API文档）
            $params = [
                'orderId' => isset($data['order_sn']) ? $data['order_sn'] : 'JD' . time(),
                'customerCode' => isset($this->config['customer_code']) ? $this->config['customer_code'] : '',
                'orderOrigin' => JdlConfig::ORDER_ORIGIN['B2C'], // 默认月结
                'senderContact' => $this->buildSenderContact($data),
                'receiverContact' => $this->buildReceiverContact($data),
                'productsReq' => $this->buildProductsReq($data),
            ];
            
            // 如果有运单号，带单号下单
            if (isset($data['waybill_code']) && !empty($data['waybill_code'])) {
                $params['waybillCode'] = $data['waybill_code'];
            }
            
            // 执行API请求
            $result = $this->client->execute('createOrder', $params);
            
            if (!$result) {
                return $this->buildErrorResponse('API请求失败');
            }
            
            // 检查业务逻辑是否成功
            if ($this->client->isSuccess($result)) {
                $waybillCode = isset($result['data']['waybillCode']) ? $result['data']['waybillCode'] : '';
                Log::info("京东创建订单成功: waybillCode={$waybillCode}");
                
                return [
                    'ack' => 'true',
                    'tracking_number' => $waybillCode,
                    'message' => 'success',
                    'order_id' => $params['orderId'],
                ];
            } else {
                $errorMsg = $this->client->getErrorMessage($result);
                $this->error = $errorMsg;
                Log::error("京东创建订单失败: {$errorMsg}");
                
                return $this->buildErrorResponse($errorMsg);
            }
            
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Log::error('京东创建订单异常: ' . $this->error);
            return $this->buildErrorResponse($this->error);
        }
    }

    /**
     * 查询轨迹
     * @param string $waybillCode 运单号
     * @return array
     */
    public function queryTrace($waybillCode)
    {
        try {
            if (!$this->client) {
                $this->error = '客户端未初始化';
                return [];
            }
            
            // 构建请求参数
            $params = [
                'waybillCode' => $waybillCode,
                'orderOrigin' => JdlConfig::ORDER_ORIGIN['B2C'],
                'customerCode' => isset($this->config['customer_code']) ? $this->config['customer_code'] : '',
            ];
            
            // 执行API请求
            $result = $this->client->execute('queryTrace', $params);
            
            if (!$result) {
                $this->error = 'API请求失败';
                return [];
            }
            
            // 检查业务逻辑是否成功
            if ($this->client->isSuccess($result)) {
                // 返回轨迹数组
                $traces = isset($result['data']['traceDetails']) ? $result['data']['traceDetails'] : [];
                Log::info("京东查询轨迹成功: waybillCode={$waybillCode}, count=" . count($traces));
                return $traces;
            } else {
                $this->error = $this->client->getErrorMessage($result);
                Log::error("京东查询轨迹失败: {$this->error}");
                return [];
            }
            
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Log::error('京东查询轨迹异常: ' . $this->error);
            return [];
        }
    }

    /**
     * 查询轨迹（别名方法，兼容旧接口）
     * @param string $waybillCode 运单号
     * @return array
     */
    public function query($waybillCode)
    {
        return $this->queryTrace($waybillCode);
    }

    /**
     * 云打印 - 获取解析后的打印数据
     * @param int $orderId 包裹ID
     * @param array $options 打印选项
     * @return array|false
     */
    public function printlabelParsedData($orderId, $options = [])
    {
        try {
            if (!$this->client) {
                $this->error = '客户端未初始化';
                return false;
            }
            
            // 1. 获取订单信息
            $order = \app\store\model\Inpack::detail($orderId);
            if (!$order) {
                $this->error = '订单不存在';
                return false;
            }
            
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
            
            // 3. 确定承运商编码
            $cpCode = isset($this->config['cp_code']) ? $this->config['cp_code'] : 'JD';
            
            // 4. 获取打印数据（密文）
            $printData = $this->getPrintData($waybillNo, $cpCode);
            if (!$printData) {
                return false;
            }
            
            // 5. 获取模板编码
            $templateCode = $this->getTemplateCode($cpCode);
            
            // 6. 构建云打印组件所需的数据结构
            $result = [
                'orderType' => 'PRINT',
                'key' => 'jd_' . $waybillNo . '_' . time(),
                'parameters' => [
                    'tempUrl' => JdlConfig::getTemplateUrl($templateCode),
                    'printData' => [$printData],
                ]
            ];
            
            // 7. 处理自定义区数据
            $customData = $this->buildCustomData($orderArray);
            if (!empty($customData)) {
                $result['parameters']['customData'] = [$customData];
            }
            
            // 8. 添加打印机名称
            if (isset($this->config['printer_name']) && !empty($this->config['printer_name'])) {
                $result['parameters']['printName'] = $this->config['printer_name'];
            }
            
            // 9. 添加偏移量
            if (isset($this->config['offset_top']) && $this->config['offset_top'] != 0) {
                $result['parameters']['offsetTop'] = $this->config['offset_top'] . 'mm';
            }
            if (isset($this->config['offset_left']) && $this->config['offset_left'] != 0) {
                $result['parameters']['offsetLeft'] = $this->config['offset_left'] . 'mm';
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Log::error('京东云打印异常: ' . $this->error);
            return false;
        }
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 构建发件人信息
     * @param array $data
     * @return array
     */
    private function buildSenderContact($data)
    {
        // 优先使用配置中的发件人信息
        $senderName = isset($this->config['sender_name']) ? $this->config['sender_name'] : '';
        $senderMobile = isset($this->config['sender_phone']) ? $this->config['sender_phone'] : '';
        $senderAddress = $this->buildFullAddress(
            isset($this->config['sender_province']) ? $this->config['sender_province'] : '',
            isset($this->config['sender_city']) ? $this->config['sender_city'] : '',
            isset($this->config['sender_district']) ? $this->config['sender_district'] : '',
            isset($this->config['sender_address']) ? $this->config['sender_address'] : ''
        );
        
        // 如果配置为空，使用订单数据
        if (empty($senderName)) {
            $senderName = isset($data['sender_name']) ? $data['sender_name'] : '';
        }
        if (empty($senderMobile)) {
            $senderMobile = isset($data['sender_mobile']) ? $data['sender_mobile'] : '';
        }
        if (empty($senderAddress)) {
            $senderAddress = isset($data['sender_address']) ? $data['sender_address'] : '';
        }
        
        return [
            'name' => $senderName,
            'mobile' => $senderMobile,
            'fullAddress' => $senderAddress,
        ];
    }

    /**
     * 构建收件人信息
     * @param array $data
     * @return array
     */
    private function buildReceiverContact($data)
    {
        $fullAddress = $this->buildFullAddress(
            isset($data['province']) ? $data['province'] : '',
            isset($data['city']) ? $data['city'] : '',
            isset($data['region']) ? $data['region'] : '',
            isset($data['detail']) ? $data['detail'] : ''
        );
        
        return [
            'name' => isset($data['name']) ? $data['name'] : '',
            'mobile' => isset($data['phone']) ? $data['phone'] : '',
            'fullAddress' => $fullAddress,
        ];
    }

    /**
     * 构建产品信息
     * @param array $data
     * @return array
     */
    private function buildProductsReq($data)
    {
        // 从配置或订单数据获取产品信息
        $productCode = isset($this->config['product_code']) ? $this->config['product_code'] : 'ed-m-0001'; // 默认京东特快
        $weight = isset($data['weight']) ? floatval($data['weight']) : 1.0;
        $volume = isset($data['volume']) ? floatval($data['volume']) : 1000.0;
        $remark = isset($data['remark']) ? $data['remark'] : '';
        
        return [
            'productCode' => $productCode,
            'weight' => $weight,
            'volume' => $volume,
            'remark' => $remark,
        ];
    }

    /**
     * 构建完整地址
     * @param string $province
     * @param string $city
     * @param string $district
     * @param string $detail
     * @return string
     */
    private function buildFullAddress($province, $city, $district, $detail)
    {
        return trim($province . $city . $district . $detail);
    }

    /**
     * 获取打印数据（密文）
     * @param string $waybillNo 运单号
     * @param string $cpCode 承运商编码
     * @return string|false
     */
    private function getPrintData($waybillNo, $cpCode)
    {
        try {
            // 构建请求参数（按照获取打印数据接口文档）
            $params = [
                'cpCode' => $cpCode,
                'objectId' => 'REQ_' . time() . '_' . mt_rand(1000, 9999),
                'parameters' => [
                    'ewCustomerCode' => isset($this->config['customer_code']) ? $this->config['customer_code'] : '',
                ],
                'wayBillInfos' => [
                    [
                        'jdWayBillCode' => $waybillNo,
                        'popFlag' => 0,
                    ]
                ],
            ];
            
            // 执行API请求
            $result = $this->client->execute('getPrintData', $params);
            
            if (!$result) {
                $this->error = 'API请求失败';
                return false;
            }
            
            // 检查业务逻辑是否成功
            if (isset($result['data']['result']['code']) && $result['data']['result']['code'] === '1') {
                $prePrintDatas = isset($result['data']['result']['prePrintDatas']) ? $result['data']['result']['prePrintDatas'] : [];
                
                if (empty($prePrintDatas)) {
                    $this->error = '未获取到打印数据';
                    return false;
                }
                
                $printData = isset($prePrintDatas[0]['perPrintData']) ? $prePrintDatas[0]['perPrintData'] : '';
                
                if (empty($printData)) {
                    $this->error = '打印数据为空';
                    return false;
                }
                
                Log::info("京东获取打印数据成功: waybillNo={$waybillNo}");
                return $printData;
            } else {
                $errorMsg = isset($result['data']['result']['message']) ? $result['data']['result']['message'] : '未知错误';
                $this->error = $errorMsg;
                Log::error("京东获取打印数据失败: {$errorMsg}");
                return false;
            }
            
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Log::error('京东获取打印数据异常: ' . $this->error);
            return false;
        }
    }

    /**
     * 获取打印模板编码
     * @param string $cpCode 承运商编码
     * @return string
     */
    private function getTemplateCode($cpCode)
    {
        // 优先使用配置中的模板编码
        if (isset($this->config['template_code']) && !empty($this->config['template_code'])) {
            return $this->config['template_code'];
        }
        
        // 使用默认模板
        return JdlConfig::getDefaultTemplateCode($cpCode);
    }

    /**
     * 构建自定义区数据
     * @param array $orderData 订单数据
     * @return array
     */
    private function buildCustomData($orderData)
    {
        $customData = [];
        
        // 添加发件人信息（如果需要替换）
        if (isset($this->config['custom_sender']) && $this->config['custom_sender']) {
            $customData['addData'] = [
                'sender' => [
                    'name' => isset($this->config['sender_name']) ? $this->config['sender_name'] : '',
                    'mobile' => isset($this->config['sender_phone']) ? $this->config['sender_phone'] : '',
                    'address' => isset($this->config['sender_address']) ? $this->config['sender_address'] : '',
                ]
            ];
        }
        
        return $customData;
    }

    /**
     * 构建错误响应
     * @param string $message
     * @return array
     */
    private function buildErrorResponse($message)
    {
        return [
            'ack' => 'false',
            'tracking_number' => '',
            'message' => $message,
            'order_id' => '',
        ];
    }
}
