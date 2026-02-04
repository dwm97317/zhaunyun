<?php

namespace app\common\library\Jdl;

use think\Log;

/**
 * 京东物流HTTP客户端类
 * 封装HTTP请求，处理响应解析和错误处理
 */
class JdlClients
{
    /** @var string 错误信息 */
    private $error = '';
    
    /** @var int 超时时间（秒） */
    private $timeout = 30;
    
    /**
     * 发送POST请求
     * 
     * @param string $url 请求URL
     * @param string $body 请求体
     * @param array $headers 请求头
     * @return string|false 响应内容或失败返回false
     */
    public function post($url, $body, array $headers = [])
    {
        $startTime = microtime(true);
        
        // 初始化CURL
        $ch = curl_init();
        
        // 设置CURL选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        // 设置请求头
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        // 执行请求
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        
        curl_close($ch);
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        // 记录请求日志
        Log::info("京东API请求: {$url}, 响应码: {$httpCode}, 耗时: {$duration}ms");
        
        // 检查CURL错误
        if ($curlErrno !== 0) {
            $this->error = "CURL错误 [{$curlErrno}]: {$curlError}";
            Log::error("京东API请求CURL错误: {$curlError}, URL: {$url}");
            return false;
        }
        
        // 检查HTTP状态码
        if ($httpCode !== 200) {
            // 尝试解析错误响应
            $errorMsg = "HTTP错误: {$httpCode}";
            if (!empty($response)) {
                $errorData = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($errorData['error_response'])) {
                    $zhDesc = isset($errorData['error_response']['zh_desc']) ? $errorData['error_response']['zh_desc'] : '';
                    $enDesc = isset($errorData['error_response']['en_desc']) ? $errorData['error_response']['en_desc'] : '';
                    $code = isset($errorData['error_response']['code']) ? $errorData['error_response']['code'] : '';
                    
                    if (!empty($zhDesc)) {
                        $errorMsg = "{$zhDesc} (错误码: {$code})";
                    } elseif (!empty($enDesc)) {
                        $errorMsg = "{$enDesc} (错误码: {$code})";
                    }
                }
            }
            
            $this->error = $errorMsg;
            Log::error("京东API请求HTTP错误: {$httpCode}, URL: {$url}, 响应: " . substr($response, 0, 200));
            return false;
        }
        
        // 检查响应内容
        if ($response === false || empty($response)) {
            $this->error = '响应内容为空';
            Log::error("京东API响应为空, URL: {$url}");
            return false;
        }
        
        return $response;
    }
    
    /**
     * 发送POST请求（带重试）
     * 
     * @param string $url 请求URL
     * @param string $body 请求体
     * @param array $headers 请求头
     * @param int $maxRetries 最大重试次数
     * @return string|false 响应内容或失败返回false
     */
    public function postWithRetry($url, $body, array $headers = [], $maxRetries = 3)
    {
        $attempt = 0;
        
        do {
            $attempt++;
            $result = $this->post($url, $body, $headers);
            
            // 成功则返回
            if ($result !== false) {
                return $result;
            }
            
            // 失败则等待后重试
            if ($attempt < $maxRetries) {
                $waitTime = $attempt * 1000000; // 指数退避（微秒）
                usleep($waitTime);
                Log::warning("京东API请求失败，正在重试 ({$attempt}/{$maxRetries})");
            }
            
        } while ($attempt < $maxRetries);
        
        Log::error("京东API请求最终失败，尝试次数: {$attempt}");
        return false;
    }
    
    /**
     * 发送GET请求
     * 
     * @param string $url 请求URL
     * @param array $headers 请求头
     * @return string|false 响应内容或失败返回false
     */
    public function get($url, array $headers = [])
    {
        $startTime = microtime(true);
        
        // 初始化CURL
        $ch = curl_init();
        
        // 设置CURL选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        // 设置请求头
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        // 执行请求
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        
        curl_close($ch);
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        // 记录请求日志
        Log::info("京东API GET请求: {$url}, 响应码: {$httpCode}, 耗时: {$duration}ms");
        
        // 检查CURL错误
        if ($curlErrno !== 0) {
            $this->error = "CURL错误 [{$curlErrno}]: {$curlError}";
            Log::error("京东API GET请求CURL错误: {$curlError}, URL: {$url}");
            return false;
        }
        
        // 检查HTTP状态码
        if ($httpCode !== 200) {
            // 尝试解析错误响应
            $errorMsg = "HTTP错误: {$httpCode}";
            if (!empty($response)) {
                $errorData = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($errorData['error_response'])) {
                    $zhDesc = isset($errorData['error_response']['zh_desc']) ? $errorData['error_response']['zh_desc'] : '';
                    $enDesc = isset($errorData['error_response']['en_desc']) ? $errorData['error_response']['en_desc'] : '';
                    $code = isset($errorData['error_response']['code']) ? $errorData['error_response']['code'] : '';
                    
                    if (!empty($zhDesc)) {
                        $errorMsg = "{$zhDesc} (错误码: {$code})";
                    } elseif (!empty($enDesc)) {
                        $errorMsg = "{$enDesc} (错误码: {$code})";
                    }
                }
            }
            
            $this->error = $errorMsg;
            Log::error("京东API GET请求HTTP错误: {$httpCode}, URL: {$url}, 响应: " . substr($response, 0, 200));
            return false;
        }
        
        // 检查响应内容
        if ($response === false || empty($response)) {
            $this->error = '响应内容为空';
            Log::error("京东API GET响应为空, URL: {$url}");
            return false;
        }
        
        return $response;
    }
    
    /**
     * 发送GET请求（带重试）
     * 
     * @param string $url 请求URL
     * @param array $headers 请求头
     * @param int $maxRetries 最大重试次数
     * @return string|false 响应内容或失败返回false
     */
    public function getWithRetry($url, array $headers = [], $maxRetries = 3)
    {
        $attempt = 0;
        
        do {
            $attempt++;
            $result = $this->get($url, $headers);
            
            // 成功则返回
            if ($result !== false) {
                return $result;
            }
            
            // 失败则等待后重试
            if ($attempt < $maxRetries) {
                $waitTime = $attempt * 1000000; // 指数退避（微秒）
                usleep($waitTime);
                Log::warning("京东API GET请求失败，正在重试 ({$attempt}/{$maxRetries})");
            }
            
        } while ($attempt < $maxRetries);
        
        Log::error("京东API GET请求最终失败，尝试次数: {$attempt}");
        return false;
    }
    
    /**
     * 解析响应
     * 
     * @param string $response 响应内容
     * @return array|false 解析后的数组或失败返回false
     */
    public function parseResponse($response)
    {
        if (empty($response)) {
            $this->error = '响应内容为空';
            return false;
        }
        
        // 解析JSON
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error = 'JSON解析失败: ' . json_last_error_msg();
            Log::error('京东API响应JSON解析失败', [
                'error' => json_last_error_msg(),
                'response' => substr($response, 0, 500),
            ]);
            return false;
        }
        
        return $data;
    }
    
    /**
     * 判断响应是否成功
     * 
     * @param array $data 响应数据
     * @return bool
     */
    public function isSuccess(array $data)
    {
        // 京东API成功响应的code通常为1000
        if (isset($data['code'])) {
            return intval($data['code']) === 1000;
        }
        
        // 兼容其他成功标识
        if (isset($data['success'])) {
            return $data['success'] === true || $data['success'] === 'true';
        }
        
        return false;
    }
    
    /**
     * 获取响应消息
     * 
     * @param array $data 响应数据
     * @return string
     */
    public function getMessage(array $data)
    {
        if (isset($data['message'])) {
            return $data['message'];
        }
        
        if (isset($data['msg'])) {
            return $data['msg'];
        }
        
        if (isset($data['error'])) {
            return $data['error'];
        }
        
        return '未知错误';
    }
    
    /**
     * 获取错误信息
     * 
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * 设置超时时间
     * 
     * @param int $timeout 超时时间（秒）
     * @return void
     */
    public function setTimeout($timeout)
    {
        $this->timeout = intval($timeout);
    }
    
    /**
     * 构建统一响应格式
     * 
     * @param bool $success 是否成功
     * @param string $waybillNo 运单号
     * @param string $message 消息
     * @param string $orderId 订单ID
     * @return array
     */
    public static function buildResponse($success, $waybillNo, $message, $orderId = '')
    {
        return [
            'ack' => $success ? 'true' : 'false',
            'tracking_number' => $waybillNo,
            'message' => $message,
            'order_id' => $orderId,
        ];
    }
    
    /**
     * 构建错误响应
     * 
     * @param string $message 错误消息
     * @param string $orderId 订单ID
     * @return array
     */
    public static function buildErrorResponse($message, $orderId = '')
    {
        return self::buildResponse(false, '', $message, $orderId);
    }
    
    /**
     * 构建成功响应
     * 
     * @param string $waybillNo 运单号
     * @param string $message 消息
     * @param string $orderId 订单ID
     * @return array
     */
    public static function buildSuccessResponse($waybillNo, $message = '成功', $orderId = '')
    {
        return self::buildResponse(true, $waybillNo, $message, $orderId);
    }
}
