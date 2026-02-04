<?php
namespace app\common\library\Jdl;

use think\Log;

/**
 * 京东物流SDK客户端封装类
 * 统一处理所有API调用，使用官方SDK
 */
class JdlClient
{
    private $appKey;
    private $appSecret;
    private $accessToken;
    private $baseUrl;
    private $sdkLoaded = false;
    
    /**
     * 构造函数
     * @param string $appKey
     * @param string $appSecret
     * @param string $accessToken
     * @param bool $isSandbox 是否沙箱环境
     */
    public function __construct($appKey, $appSecret, $accessToken, $isSandbox = false)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->accessToken = $accessToken;
        $this->baseUrl = $isSandbox ? JdlConfig::UAT_API_URL : JdlConfig::PROD_API_URL;
        
        // 加载SDK
        $this->loadSdk();
    }
    
    /**
     * 加载京东官方SDK
     */
    private function loadSdk()
    {
        if ($this->sdkLoaded) {
            return;
        }
        
        $sdkPath = __DIR__ . '/lop-opensdk-php/src';
        
        if (!class_exists('Lop\LopOpensdkPhp\Support\DefaultClient')) {
            // 手动加载SDK核心类
            $files = [
                '/Client.php',
                '/Request.php',
                '/Response.php',
                '/Filter.php',
                '/FilterChain.php',
                '/Executor.php',
                '/ExecutorFactory.php',
                '/Options.php',
                '/SdkException.php',
                '/Version.php',
                '/Support/DefaultClient.php',
                '/Support/GenericRequest.php',
                '/Support/DefaultExecutorFactory.php',
                '/Support/DefaultFilterChain.php',
                '/Filters/IsvFilter.php',
                '/Filters/ErrorResponseFilter.php',
                '/Filters/Utils.php',
            ];
            
            foreach ($files as $file) {
                $filePath = $sdkPath . $file;
                if (file_exists($filePath)) {
                    require_once $filePath;
                }
            }
        }
        
        $this->sdkLoaded = true;
    }
    
    /**
     * 执行API请求
     * @param string $action 操作名称（createOrder, queryTrace等）
     * @param array $params 请求参数
     * @return array|false 返回响应数据或false
     */
    public function execute($action, $params)
    {
        try {
            // 获取API配置
            $path = JdlConfig::getApiPath($action);
            
            if (!$path) {
                Log::error("京东API配置不存在: action={$action}");
                return false;
            }
            
            // 云打印接口不需要Domain参数
            $isCloudPrint = in_array($action, ['getPrintData', 'getTemplates']);
            
            // 创建SDK客户端
            $client = new \Lop\LopOpensdkPhp\Support\DefaultClient($this->baseUrl);
            
            // 创建ISV过滤器（自动处理签名）
            $isvFilter = new \Lop\LopOpensdkPhp\Filters\IsvFilter(
                $this->appKey,
                $this->appSecret,
                $this->accessToken
            );
            
            // 创建错误响应过滤器
            $errorResponseFilter = new \Lop\LopOpensdkPhp\Filters\ErrorResponseFilter();
            
            // 创建请求
            $request = new \Lop\LopOpensdkPhp\Support\GenericRequest();
            
            // 云打印接口不设置Domain
            if (!$isCloudPrint) {
                $domain = JdlConfig::getDomain($action);
                if (!$domain) {
                    Log::error("京东API Domain配置不存在: action={$action}");
                    return false;
                }
                $request->setDomain($domain);
            }
            
            $request->setPath($path);
            $request->setMethod('POST');
            
            // 设置请求体
            $requestBody = json_encode($params, JSON_UNESCAPED_UNICODE);
            $request->setBody($requestBody);
            
            // 添加过滤器
            $request->addFilter($isvFilter);
            $request->addFilter($errorResponseFilter);
            
            // 设置选项
            $options = new \Lop\LopOpensdkPhp\Options();
            $options->setAlgorithm(\Lop\LopOpensdkPhp\Options::MD5_SALT);
            
            Log::info("京东API请求: action={$action}, isCloudPrint=" . ($isCloudPrint ? 'true' : 'false') . ", params=" . $requestBody);
            
            // 执行请求
            $response = $client->execute($request, $options);
            
            // 检查响应
            if (!$response->isSucceed()) {
                $errorBody = $response->getBody();
                Log::error("京东API请求失败: " . $errorBody);
                
                // 尝试解析错误信息
                $errorData = json_decode($errorBody, true);
                if ($errorData && isset($errorData['error_response'])) {
                    $errorMsg = isset($errorData['error_response']['zh_desc']) 
                        ? $errorData['error_response']['zh_desc'] 
                        : (isset($errorData['error_response']['en_desc']) ? $errorData['error_response']['en_desc'] : '未知错误');
                    Log::error("京东API错误详情: " . $errorMsg);
                }
                
                return false;
            }
            
            // 解析响应
            $result = json_decode($response->getBody(), true);
            if ($result === null) {
                Log::error("京东API响应解析失败: " . $response->getBody());
                return false;
            }
            
            Log::info("京东API响应: " . json_encode($result, JSON_UNESCAPED_UNICODE));
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error("京东API调用异常: action={$action}, error=" . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 检查响应是否成功
     * @param array $result API响应
     * @return bool
     */
    public function isSuccess($result)
    {
        // 京东API成功响应：code=200 或 code=0 或 code="1"
        if (isset($result['code'])) {
            $code = $result['code'];
            return $code === 200 || $code === 0 || $code === '1' || $code === 1;
        }
        
        // 云打印接口特殊处理
        if (isset($result['data']['result']['code'])) {
            return $result['data']['result']['code'] === '1';
        }
        
        return false;
    }
    
    /**
     * 获取错误消息
     * @param array $result API响应
     * @return string
     */
    public function getErrorMessage($result)
    {
        // 标准错误消息
        if (isset($result['msg'])) {
            return $result['msg'];
        }
        
        if (isset($result['message'])) {
            return $result['message'];
        }
        
        // 云打印接口错误消息
        if (isset($result['data']['result']['message'])) {
            return $result['data']['result']['message'];
        }
        
        return '未知错误';
    }
}
