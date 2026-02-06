<?php

namespace app\common\service;

use app\common\service\DitchCache;
use app\common\service\RetryHelper;
use app\common\service\PrintLogger;
use app\store\controller\TrOrder;
use think\Request;

/**
 * 批量推送到渠道商工具
 * 
 * 功能：将多个集运订单批量推送到同一个渠道商
 * 
 * 特性：
 * - 批量推送多个订单到同一个渠道商
 * - 自动启用重试机制（防止网络抖动）
 * - 使用渠道配置缓存（只查询一次，提升性能）
 * - 统一日志记录
 * - 详细的推送结果统计
 * - 复用现有推送逻辑（避免代码重复）
 * 
 * 使用场景：
 * - 批量将多个集运订单推送到顺丰
 * - 批量将多个集运订单推送到中通
 * - 批量将多个集运订单推送到京东
 */
class OrderBatchPusher
{
    /**
     * 批量推送订单到同一个渠道商
     * 
     * @param array $orderIds 订单ID数组（多个集运订单）
     * @param int $ditchId 渠道ID（同一个渠道商）
     * @param array $extraParams 额外参数
     *   - product_id: 产品ID
     *   - async: 是否异步执行（默认false）
     *   - priority: 异步任务优先级（默认5，仅在async=true时有效）
     * @param array $retryConfig 重试配置（可选）
     * @return array 推送结果
     *   - success_count: 成功数量
     *   - error_count: 失败数量
     *   - total: 总数量
     *   - results: 详细结果数组 [['order_id' => xx, 'success' => bool, 'tracking_number' => xx, 'error' => xx], ...]
     *   - elapsed_time: 总耗时（秒）
     *   - ditch_name: 渠道名称
     *   - async: 是否异步执行
     *   - task_id: 异步任务ID（仅在async=true时返回）
     */
    public static function push(array $orderIds, $ditchId, array $extraParams = [], array $retryConfig = [])
    {
        // 检查是否启用异步执行
        $async = isset($extraParams['async']) ? (bool)$extraParams['async'] : false;
        
        if ($async) {
            // 异步执行：添加到任务队列
            $priority = isset($extraParams['priority']) ? (int)$extraParams['priority'] : 5;
            
            // 移除 async 和 priority 参数，避免传递到实际执行时
            $asyncExtraParams = $extraParams;
            unset($asyncExtraParams['async']);
            unset($asyncExtraParams['priority']);
            
            $taskId = \app\common\service\AsyncTaskQueue::addBatchPushTask(
                $orderIds,
                $ditchId,
                $asyncExtraParams,
                $priority
            );
            
            if ($taskId) {
                PrintLogger::success('批量推送', '任务已添加到异步队列', [
                    'task_id' => $taskId,
                    'order_count' => count($orderIds),
                    'ditch_id' => $ditchId,
                    'priority' => $priority
                ]);
                
                return [
                    'success_count' => 0,
                    'error_count' => 0,
                    'total' => count($orderIds),
                    'results' => [],
                    'elapsed_time' => 0,
                    'ditch_name' => '',
                    'async' => true,
                    'task_id' => $taskId,
                    'message' => 'Task added to async queue successfully'
                ];
            } else {
                PrintLogger::error('批量推送', '添加异步任务失败', [
                    'order_count' => count($orderIds),
                    'ditch_id' => $ditchId
                ]);
                
                return [
                    'success_count' => 0,
                    'error_count' => count($orderIds),
                    'total' => count($orderIds),
                    'results' => [],
                    'elapsed_time' => 0,
                    'ditch_name' => '',
                    'async' => true,
                    'error' => 'Failed to add task to async queue'
                ];
            }
        }
        
        // 同步执行（默认）
        return self::pushSync($orderIds, $ditchId, $extraParams, $retryConfig);
    }
    
    /**
     * 同步批量推送订单
     * 
     * @param array $orderIds 订单ID数组
     * @param int $ditchId 渠道ID
     * @param array $extraParams 额外参数
     * @param array $retryConfig 重试配置
     * @return array 推送结果
     */
    private static function pushSync(array $orderIds, $ditchId, array $extraParams = [], array $retryConfig = [])
    {
        $startTime = microtime(true);
        
        PrintLogger::info('批量推送', '开始批量推送到渠道商', [
            'total_orders' => count($orderIds),
            'ditch_id' => $ditchId
        ]);
        
        // 使用缓存获取渠道配置（只查询一次，所有订单共享）
        $ditchConfig = DitchCache::getConfig($ditchId);
        if (!$ditchConfig) {
            PrintLogger::error('批量推送', '渠道配置不存在', ['ditch_id' => $ditchId]);
            return [
                'success_count' => 0,
                'error_count' => count($orderIds),
                'total' => count($orderIds),
                'results' => [],
                'elapsed_time' => 0,
                'error' => '渠道配置不存在',
                'ditch_name' => ''
            ];
        }
        
        $ditchName = $ditchConfig['ditch_name'] ?? '未知渠道';
        
        PrintLogger::info('批量推送', '渠道配置已加载', [
            'ditch_id' => $ditchId,
            'ditch_name' => $ditchName,
            'ditch_type' => $ditchConfig['ditch_type'] ?? 0
        ]);
        
        // 准备批量操作列表（所有订单推送到同一个渠道）
        $operations = [];
        foreach ($orderIds as $orderId) {
            $operations[] = [
                'callable' => function() use ($orderId, $ditchId, $extraParams) {
                    return self::pushSingleOrder($orderId, $ditchId, $extraParams);
                },
                'options' => array_merge([
                    'enabled' => true,  // 批量推送时启用重试
                    'channel' => $ditchName,
                    'operation_name' => "推送订单 #{$orderId}",
                ], $retryConfig)
            ];
        }
        
        // 使用 RetryHelper 批量执行（启用重试）
        $batchResult = RetryHelper::executeBatch($operations, [
            'parallel' => false,      // 顺序执行（避免API限流）
            'stop_on_error' => false  // 遇到错误继续执行
        ]);
        
        $elapsedTime = round(microtime(true) - $startTime, 2);
        
        // 整理详细结果
        $detailedResults = [];
        foreach ($batchResult['results'] as $index => $result) {
            $orderId = $orderIds[$index];
            $detailedResults[] = [
                'order_id' => $orderId,
                'success' => $result['success'],
                'tracking_number' => $result['success'] && isset($result['result']['tracking_number']) 
                    ? $result['result']['tracking_number'] 
                    : '',
                'error' => $result['success'] ? '' : $result['error'],
                'attempts' => $result['attempts']
            ];
        }
        
        PrintLogger::success('批量推送', '批量推送完成', [
            'ditch_name' => $ditchName,
            'total' => count($orderIds),
            'success' => $batchResult['success_count'],
            'error' => $batchResult['error_count'],
            'elapsed_time' => $elapsedTime . 's'
        ]);
        
        return [
            'success_count' => $batchResult['success_count'],
            'error_count' => $batchResult['error_count'],
            'total' => count($orderIds),
            'results' => $detailedResults,
            'elapsed_time' => $elapsedTime,
            'ditch_name' => $ditchName,
            'async' => false
        ];
    }
    
    /**
     * 推送单个订单到渠道商
     * 
     * 复用 TrOrder::sendtoqudaoshang() 的逻辑，避免代码重复
     * 
     * @param int $orderId 订单ID
     * @param int $ditchId 渠道ID
     * @param array $extraParams 额外参数
     * @return bool|array 成功返回结果数组，失败返回 false
     */
    private static function pushSingleOrder($orderId, $ditchId, $extraParams = [])
    {
        try {
            // 🔍 调试日志：开始推送单个订单
            PrintLogger::info('批量推送', '开始推送单个订单', [
                'order_id' => $orderId,
                'ditch_id' => $ditchId,
                'extra_params' => $extraParams
            ]);
            
            // 构造请求参数
            $requestParams = [
                'id' => $orderId,
                'ditch_id' => $ditchId,
                'product_id' => isset($extraParams['product_id']) ? $extraParams['product_id'] : ''
            ];
            
            // 🔍 调试日志：记录请求参数
            PrintLogger::info('批量推送', '请求参数', $requestParams);
            
            // 保存原始全局变量
            $originalGet = $_GET;
            $originalPost = $_POST;
            $originalRequest = $_REQUEST;
            
            // 设置全局参数
            $_GET = $requestParams;
            $_POST = $requestParams;
            $_REQUEST = $requestParams;
            
            PrintLogger::info('批量推送', '全局参数已设置', [
                'order_id' => $orderId,
                '_GET' => $_GET,
                '_POST' => $_POST
            ]);
            
            // 创建 TrOrder 控制器实例
            PrintLogger::info('批量推送', '准备创建 TrOrder 实例', [
                'order_id' => $orderId
            ]);
            
            try {
                // 🔧 优化：强制清除 ThinkPHP Request 单例缓存（确保读取最新的全局变量）
                if (class_exists('\think\Request')) {
                    $reflection = new \ReflectionClass('\think\Request');
                    if ($reflection->hasProperty('instance')) {
                        $instanceProperty = $reflection->getProperty('instance');
                        $instanceProperty->setAccessible(true);
                        $instanceProperty->setValue(null, null);
                        
                        PrintLogger::info('批量推送', 'Request 单例已清除', [
                            'order_id' => $orderId
                        ]);
                    }
                }
                
                $trOrder = new TrOrder();
                PrintLogger::info('批量推送', 'TrOrder 实例创建成功', [
                    'order_id' => $orderId,
                    'class' => get_class($trOrder)
                ]);
            } catch (\Exception $instanceException) {
                // 恢复全局变量
                $_GET = $originalGet;
                $_POST = $originalPost;
                $_REQUEST = $originalRequest;
                
                PrintLogger::error('批量推送', '创建 TrOrder 实例失败', [
                    'order_id' => $orderId,
                    'exception' => $instanceException->getMessage(),
                    'file' => $instanceException->getFile(),
                    'line' => $instanceException->getLine()
                ]);
                throw $instanceException;
            }
            
            // 🔍 调试日志：调用 sendtoqudaoshang 方法
            PrintLogger::info('批量推送', '调用 TrOrder::sendtoqudaoshang', [
                'order_id' => $orderId,
                'ditch_id' => $ditchId
            ]);
            
            // 调用现有的推送方法
            try {
                $response = $trOrder->sendtoqudaoshang();
                
                // 🔍 调试日志：调用成功，记录响应
                PrintLogger::info('批量推送', 'sendtoqudaoshang 调用成功', [
                    'order_id' => $orderId,
                    'response_exists' => isset($response),
                    'response_is_null' => is_null($response),
                    'response_type' => is_object($response) ? get_class($response) : gettype($response)
                ]);
                
            } catch (\Exception $callException) {
                // 恢复全局变量
                $_GET = $originalGet;
                $_POST = $originalPost;
                $_REQUEST = $originalRequest;
                
                // 🔍 调试日志：调用异常
                PrintLogger::error('批量推送', 'sendtoqudaoshang 调用异常', [
                    'order_id' => $orderId,
                    'exception' => $callException->getMessage(),
                    'file' => $callException->getFile(),
                    'line' => $callException->getLine()
                ]);
                throw $callException;
            }
            
            // 恢复全局变量
            $_GET = $originalGet;
            $_POST = $originalPost;
            $_REQUEST = $originalRequest;
            
            // 🔍 调试日志：记录响应类型
            PrintLogger::info('批量推送', '响应类型', [
                'order_id' => $orderId,
                'response_type' => is_object($response) ? get_class($response) : gettype($response)
            ]);
            
            // 解析响应
            if (is_object($response)) {
                // 如果返回的是 Response 对象
                $data = $response->getData();
                
                // 🔍 调试日志：记录响应数据
                PrintLogger::info('批量推送', '响应数据（Response对象）', [
                    'order_id' => $orderId,
                    'data' => $data
                ]);
                
                if (isset($data['code']) && $data['code'] == 1) {
                    // 成功：检查嵌套的 data.ack
                    $innerData = isset($data['data']) ? $data['data'] : [];
                    $ack = isset($innerData['ack']) ? $innerData['ack'] : false;
                    
                    if ($ack === true || $ack === 'true') {
                        $trackingNumber = isset($innerData['tracking_number']) ? $innerData['tracking_number'] : '';
                        
                        PrintLogger::success('批量推送', '订单推送成功', [
                            'order_id' => $orderId,
                            'tracking_number' => $trackingNumber
                        ]);
                        
                        return [
                            'ack' => true,
                            'tracking_number' => $trackingNumber,
                            'message' => isset($data['msg']) ? $data['msg'] : '推送成功'
                        ];
                    } else {
                        // code=1 但 ack 不是 true
                        $errorMsg = isset($innerData['message']) ? $innerData['message'] : '推送失败';
                        
                        PrintLogger::error('批量推送', '订单推送失败（ack=false）', [
                            'order_id' => $orderId,
                            'error' => $errorMsg,
                            'inner_data' => $innerData
                        ]);
                        return false;
                    }
                } else {
                    // 失败
                    $errorMsg = isset($data['msg']) ? $data['msg'] : '推送失败';
                    
                    // 🔍 调试日志：记录失败详情
                    PrintLogger::error('批量推送', '订单推送失败（Response对象）', [
                        'order_id' => $orderId,
                        'error' => $errorMsg,
                        'full_data' => $data
                    ]);
                    return false;
                }
            } elseif (is_array($response)) {
                // 如果直接返回数组
                
                // 🔍 调试日志：记录数组响应
                PrintLogger::info('批量推送', '收到数组响应', [
                    'order_id' => $orderId,
                    'response' => $response
                ]);
                
                // 检查是否是 ThinkPHP 格式 {code, msg, data}
                if (isset($response['code'])) {
                    if ($response['code'] == 1) {
                        // 成功：检查嵌套的 data.ack
                        $innerData = isset($response['data']) ? $response['data'] : [];
                        $ack = isset($innerData['ack']) ? $innerData['ack'] : false;
                        
                        if ($ack === true || $ack === 'true') {
                            $trackingNumber = isset($innerData['tracking_number']) ? $innerData['tracking_number'] : '';
                            
                            PrintLogger::success('批量推送', '订单推送成功', [
                                'order_id' => $orderId,
                                'tracking_number' => $trackingNumber
                            ]);
                            
                            return [
                                'ack' => true,
                                'tracking_number' => $trackingNumber,
                                'message' => isset($response['msg']) ? $response['msg'] : '推送成功'
                            ];
                        } else {
                            // code=1 但 ack 不是 true
                            $errorMsg = isset($innerData['message']) ? $innerData['message'] : '推送失败';
                            
                            PrintLogger::error('批量推送', '订单推送失败（ack=false）', [
                                'order_id' => $orderId,
                                'error' => $errorMsg,
                                'inner_data' => $innerData
                            ]);
                            return false;
                        }
                    } else {
                        // code != 1，失败
                        $errorMsg = isset($response['msg']) ? $response['msg'] : '推送失败';
                        
                        PrintLogger::error('批量推送', '订单推送失败（数组响应）', [
                            'order_id' => $orderId,
                            'error' => $errorMsg,
                            'full_response' => $response
                        ]);
                        return false;
                    }
                } else {
                    // 直接的 ack 格式（旧格式）
                    if (isset($response['ack']) && ($response['ack'] === true || $response['ack'] === 'true')) {
                        PrintLogger::success('批量推送', '订单推送成功', [
                            'order_id' => $orderId,
                            'tracking_number' => isset($response['tracking_number']) ? $response['tracking_number'] : ''
                        ]);
                        return $response;
                    } else {
                        $errorMsg = isset($response['message']) ? $response['message'] : '推送失败';
                        
                        PrintLogger::error('批量推送', '订单推送失败（直接格式）', [
                            'order_id' => $orderId,
                            'error' => $errorMsg,
                            'full_response' => $response
                        ]);
                        return false;
                    }
                }
            } else {
                // 🔍 调试日志：未知响应格式
                PrintLogger::error('批量推送', '未知响应格式', [
                    'order_id' => $orderId,
                    'response_type' => gettype($response),
                    'response' => $response
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            // 恢复全局变量（如果还没恢复）
            if (isset($originalGet)) $_GET = $originalGet;
            if (isset($originalPost)) $_POST = $originalPost;
            if (isset($originalRequest)) $_REQUEST = $originalRequest;
            
            // 🔍 调试日志：记录异常
            PrintLogger::error('批量推送', '推送订单异常', [
                'order_id' => $orderId,
                'ditch_id' => $ditchId,
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * 获取推荐的重试配置
     * 
     * @param int $ditchId 渠道ID
     * @return array 重试配置
     */
    public static function getRecommendedRetryConfig($ditchId)
    {
        $ditchConfig = DitchCache::getConfig($ditchId);
        if (!$ditchConfig) {
            return RetryHelper::getRecommendedConfig('default');
        }
        
        $ditchNo = $ditchConfig['ditch_no'];
        $ditchType = isset($ditchConfig['ditch_type']) ? (int)$ditchConfig['ditch_type'] : 0;
        
        // 根据渠道编号或类型返回推荐配置
        if ($ditchNo == 10004) {
            return RetryHelper::getRecommendedConfig('default');
        } elseif ($ditchNo == 10009 || in_array($ditchType, [2, 3], true)) {
            return RetryHelper::getRecommendedConfig('zto');
        } elseif ($ditchNo == 10010 || $ditchType === 4) {
            return RetryHelper::getRecommendedConfig('sf');
        } elseif ($ditchType === 5) {
            return RetryHelper::getRecommendedConfig('jd');
        }
        
        return RetryHelper::getRecommendedConfig('default');
    }
}
