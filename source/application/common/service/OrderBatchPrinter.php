<?php

namespace app\common\service;

use app\common\service\DitchCache;
use app\common\service\RetryHelper;
use app\common\service\PrintLogger;
use app\common\service\InpackPrintStatus;
use app\store\controller\TrOrder;
use think\Request;

/**
 * 批量打印工具
 * 
 * 功能：将多个集运订单批量打印（同一个渠道商）
 * 
 * 特性：
 * - 批量打印多个订单到同一个渠道商
 * - 自动启用重试机制（防止网络抖动）
 * - 使用渠道配置缓存（只查询一次，提升性能）
 * - 统一日志记录
 * - 详细的打印结果统计
 * - 复用现有打印逻辑（避免代码重复）
 * 
 * 使用场景：
 * - 批量打印多个订单的顺丰面单
 * - 批量打印多个订单的中通面单
 * - 批量打印多个订单的京东面单
 */
class OrderBatchPrinter
{
    /**
     * 批量打印订单（同一个渠道商）
     * 
     * @param array $orderIds 订单ID数组（多个集运订单）
     * @param int $ditchId 渠道ID（同一个渠道商）
     * @param array $printOptions 打印选项
     *   - label: 标签尺寸（默认60）
     *   - print_all: 是否打印全部包裹（默认0）
     *   - async: 是否异步执行（默认false）
     *   - priority: 异步任务优先级（默认5，仅在async=true时有效）
     * @param array $retryConfig 重试配置（可选）
     * @return array 打印结果
     *   - success_count: 成功数量
     *   - error_count: 失败数量
     *   - total: 总数量
     *   - results: 详细结果数组 [['order_id' => xx, 'success' => bool, 'print_data' => xx, 'error' => xx], ...]
     *   - elapsed_time: 总耗时（秒）
     *   - ditch_name: 渠道名称
     *   - async: 是否异步执行
     *   - task_id: 异步任务ID（仅在async=true时返回）
     */
    public static function print(array $orderIds, $ditchId, array $printOptions = [], array $retryConfig = [])
    {
        // 检查是否启用异步执行
        $async = isset($printOptions['async']) ? (bool)$printOptions['async'] : false;
        
        if ($async) {
            // 异步执行：添加到任务队列
            $priority = isset($printOptions['priority']) ? (int)$printOptions['priority'] : 5;
            
            // 移除 async 和 priority 参数，避免传递到实际执行时
            $asyncPrintOptions = $printOptions;
            unset($asyncPrintOptions['async']);
            unset($asyncPrintOptions['priority']);
            
            $taskId = \app\common\service\AsyncTaskQueue::addBatchPrintTask(
                $orderIds,
                $ditchId,
                $asyncPrintOptions,
                $priority
            );
            
            if ($taskId) {
                PrintLogger::success('批量打印', '任务已添加到异步队列', [
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
                PrintLogger::error('批量打印', '添加异步任务失败', [
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
        return self::printSync($orderIds, $ditchId, $printOptions, $retryConfig);
    }
    
    /**
     * 同步批量打印订单
     * 
     * @param array $orderIds 订单ID数组
     * @param int $ditchId 渠道ID
     * @param array $printOptions 打印选项
     * @param array $retryConfig 重试配置
     * @return array 打印结果
     */
    private static function printSync(array $orderIds, $ditchId, array $printOptions = [], array $retryConfig = [])
    {
        $startTime = microtime(true);
        
        PrintLogger::info('批量打印', '开始批量打印', [
            'total_orders' => count($orderIds),
            'ditch_id' => $ditchId
        ]);
        
        // 使用缓存获取渠道配置（只查询一次，所有订单共享）
        $ditchConfig = DitchCache::getConfig($ditchId);
        if (!$ditchConfig) {
            PrintLogger::error('批量打印', '渠道配置不存在', ['ditch_id' => $ditchId]);
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
        
        PrintLogger::info('批量打印', '渠道配置已加载', [
            'ditch_id' => $ditchId,
            'ditch_name' => $ditchName,
            'ditch_type' => $ditchConfig['ditch_type'] ?? 0
        ]);
        
        // 准备批量操作列表（所有订单打印到同一个渠道）
        $operations = [];
        foreach ($orderIds as $orderId) {
            $operations[] = [
                'callable' => function() use ($orderId, $ditchId, $printOptions) {
                    return self::printSingleOrder($orderId, $ditchId, $printOptions);
                },
                'options' => array_merge([
                    'enabled' => true,  // 批量打印时启用重试
                    'channel' => $ditchName,
                    'operation_name' => "打印订单 #{$orderId}",
                ], $retryConfig)
            ];
        }
        
        // 使用 RetryHelper 批量执行（启用重试）
        $batchResult = RetryHelper::executeBatch($operations, [
            'parallel' => false,      // 顺序执行（避免API限流）
            'stop_on_error' => false  // 遇到错误继续执行
        ]);
        
        // 整理详细结果
        $detailedResults = [];
        $successOrderIds = [];  // 收集成功的订单ID
        
        foreach ($batchResult['results'] as $index => $result) {
            $orderId = $orderIds[$index];
            $isSuccess = $result['success'];
            
            $detailedResults[] = [
                'order_id' => $orderId,
                'success' => $isSuccess,
                'print_data' => $isSuccess && isset($result['result']['print_data']) 
                    ? $result['result']['print_data'] 
                    : null,
                'error' => $isSuccess ? '' : $result['error'],
                'attempts' => $result['attempts']
            ];
            
            // 收集成功的订单ID
            if ($isSuccess) {
                $successOrderIds[] = $orderId;
            }
        }
        
        // 批量标记成功的订单为"已批量打印"
        if (!empty($successOrderIds)) {
            $statusResult = InpackPrintStatus::batchMarkAsPrinted($successOrderIds);
            PrintLogger::info('批量打印', '更新打印状态', [
                'marked_count' => $statusResult['success_count'],
                'failed_count' => $statusResult['error_count']
            ]);
        }
        
        $elapsedTime = round(microtime(true) - $startTime, 2);
        
        PrintLogger::success('批量打印', '批量打印完成', [
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
     * 打印单个订单
     * 
     * 复用 TrOrder::getPrintTask() 的逻辑，避免代码重复
     * 
     * @param int $orderId 订单ID
     * @param int $ditchId 渠道ID
     * @param array $printOptions 打印选项
     * @return bool|array 成功返回结果数组，失败返回 false
     */
    private static function printSingleOrder($orderId, $ditchId, $printOptions = [])
    {
        try {
            // 创建模拟请求对象
            $request = Request::instance();
            
            // TP5.1 中设置参数的正确方式
            $params = [
                'id' => $orderId,
                'label' => isset($printOptions['label']) ? $printOptions['label'] : 60,
                'print_all' => isset($printOptions['print_all']) ? $printOptions['print_all'] : 0,
                'waybill_no' => isset($printOptions['waybill_no']) ? $printOptions['waybill_no'] : ''
            ];
            
            // 使用 bind 设置参数，这是 TP5.1 中动态添加参数的推荐方式
            foreach ($params as $key => $val) {
                $request->bind($key, $val);
            }
            
            // 兼容性补充：同时设置到 get 和 post 中
            $request->get($params);
            $request->post($params);

            
            // 记录调试日志，确认参数已设置
            PrintLogger::info('批量打印', "准备执行打印请求 #{$orderId}", [
                'id_from_param' => $request->param('id'),
                'id_from_get' => $request->get('id'),
                'is_ajax' => $request->isAjax()
            ]);

            
            // 创建 TrOrder 控制器实例
            $trOrder = new TrOrder($request);
            
            // 调用现有的打印方法
            $response = $trOrder->getPrintTask();
            
            // 记录响应结果
            PrintLogger::info('批量打印', "打印请求完成 #{$orderId}", [
                'response_type' => gettype($response),
                'code' => (is_array($response) && isset($response['code'])) ? $response['code'] : 'N/A',
                'msg' => (is_array($response) && isset($response['msg'])) ? $response['msg'] : 'N/A'
            ]);
            
            // 解析响应
            if (is_object($response)) {
                // 如果返回的是 Response 对象
                $data = $response->getData();
                
                if (isset($data['code']) && $data['code'] == 1) {
                    // 成功 - 返回完整的打印数据（包含所有字段）
                    PrintLogger::success('批量打印', '订单打印成功', [
                        'order_id' => $orderId,
                        'has_data' => isset($data['data'])
                    ]);
                    
                    // 返回完整的 data 字段（包含 mode, data, partnerID, env, printOptions 等）
                    return [
                        'success' => true,
                        'print_data' => isset($data['data']) ? $data['data'] : null,
                        'message' => isset($data['msg']) ? $data['msg'] : '打印成功'
                    ];
                } else {
                    // 失败
                    $errorMsg = isset($data['msg']) ? $data['msg'] : '打印失败';
                    PrintLogger::error('批量打印', '订单打印失败', [
                        'order_id' => $orderId,
                        'error' => $errorMsg
                    ]);
                    return false;
                }
            } elseif (is_array($response)) {
                // 如果直接返回数组
                if (isset($response['code']) && $response['code'] == 1) {
                    PrintLogger::success('批量打印', '订单打印成功', [
                        'order_id' => $orderId,
                        'has_data' => isset($response['data'])
                    ]);
                    
                    // 返回完整的 data 字段（包含 mode, data, partnerID, env, printOptions 等）
                    return [
                        'success' => true,
                        'print_data' => isset($response['data']) ? $response['data'] : null,
                        'message' => isset($response['msg']) ? $response['msg'] : '打印成功'
                    ];
                } else {
                    $errorMsg = isset($response['msg']) ? $response['msg'] : '打印失败';
                    PrintLogger::error('批量打印', '订单打印失败', [
                        'order_id' => $orderId,
                        'error' => $errorMsg
                    ]);
                    return false;
                }
            } else {
                PrintLogger::error('批量打印', '未知响应格式', [
                    'order_id' => $orderId,
                    'response_type' => gettype($response)
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            PrintLogger::error('批量打印', '打印异常', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
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
