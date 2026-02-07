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
            // 批量打印时默认使用 print_all=1（打印全部包裹，包括子母单）
            $params = [
                'id' => $orderId,
                'label' => isset($printOptions['label']) ? $printOptions['label'] : 60,
                'print_all' => isset($printOptions['print_all']) ? (int)$printOptions['print_all'] : 1,  // 默认打印全部，强制转换为整数
                'waybill_no' => isset($printOptions['waybill_no']) ? $printOptions['waybill_no'] : ''
            ];
            
            // 清除可能存在的旧参数（避免被覆盖）
            // TP5.1 的 Request 对象会缓存参数，需要先清除
            $request->get(['id' => null, 'label' => null, 'print_all' => null, 'waybill_no' => null]);
            $request->post(['id' => null, 'label' => null, 'print_all' => null, 'waybill_no' => null]);
            
            // 使用 bind 设置参数，这是 TP5.1 中动态添加参数的推荐方式
            foreach ($params as $key => $val) {
                $request->bind($key, $val);
            }
            
            // 兼容性补充：同时设置到 get 和 post 中（使用整数值）
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
                    $printData = isset($data['data']) ? $data['data'] : null;
                    
                    // 增强打印数据：添加打印机配置
                    if ($printData) {
                        $printData = self::enhancePrintDataWithPrinterConfig($printData, $ditchId);
                    }
                    
                    PrintLogger::success('批量打印', '订单打印成功', [
                        'order_id' => $orderId,
                        'has_data' => isset($data['data']),
                        'mode' => isset($printData['mode']) ? $printData['mode'] : 'unknown'
                    ]);
                    
                    // 返回完整的 data 字段（包含 mode, data, partnerID, env, printOptions, 打印机配置等）
                    return [
                        'success' => true,
                        'print_data' => $printData,
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
                    $printData = isset($response['data']) ? $response['data'] : null;
                    
                    // 增强打印数据：添加打印机配置
                    if ($printData) {
                        $printData = self::enhancePrintDataWithPrinterConfig($printData, $ditchId);
                    }
                    
                    PrintLogger::success('批量打印', '订单打印成功', [
                        'order_id' => $orderId,
                        'has_data' => isset($response['data']),
                        'mode' => isset($printData['mode']) ? $printData['mode'] : 'unknown'
                    ]);
                    
                    // 返回完整的 data 字段（包含 mode, data, partnerID, env, printOptions, 打印机配置等）
                    return [
                        'success' => true,
                        'print_data' => $printData,
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
     * 增强打印数据：添加打印机配置
     * 
     * @param array $printData 原始打印数据
     * @param int $ditchId 渠道ID
     * @return array 增强后的打印数据
     */
    private static function enhancePrintDataWithPrinterConfig($printData, $ditchId)
    {
        if (!is_array($printData) || !isset($printData['mode'])) {
            return $printData;
        }
        
        // 获取渠道配置
        $ditchConfig = DitchCache::getConfig($ditchId);
        if (!$ditchConfig) {
            return $printData;
        }
        
        $mode = $printData['mode'];
        
        // 顺丰快递：解析 sf_print_options
        if ($mode === 'sf_plugin') {
            $sfPrintOptions = [];
            if (!empty($ditchConfig['sf_print_options'])) {
                $decoded = json_decode($ditchConfig['sf_print_options'], true);
                if (is_array($decoded)) {
                    $sfPrintOptions = $decoded;
                    PrintLogger::info('批量打印', '顺丰打印机配置已加载', [
                        'ditch_id' => $ditchId,
                        'default_printer' => isset($decoded['default_printer']) ? $decoded['default_printer'] : 'N/A',
                        'enable_select_printer' => isset($decoded['enable_select_printer']) ? $decoded['enable_select_printer'] : false
                    ]);
                } else {
                    PrintLogger::warning('批量打印', '顺丰打印配置解析失败', [
                        'ditch_id' => $ditchId,
                        'raw_value' => $ditchConfig['sf_print_options']
                    ]);
                }
            }
            $printData['sfPrintOptions'] = $sfPrintOptions;
        }
        
        // 京东快递：解析 jd_print_config
        elseif ($mode === 'jd_cloud_print') {
            $jdPrintConfig = [];
            if (!empty($ditchConfig['jd_print_config'])) {
                $decoded = json_decode($ditchConfig['jd_print_config'], true);
                if (is_array($decoded)) {
                    $jdPrintConfig = $decoded;
                    PrintLogger::info('批量打印', '京东打印机配置已加载', [
                        'ditch_id' => $ditchId,
                        'printName' => isset($decoded['printName']) ? $decoded['printName'] : 'N/A',
                        'orderType' => isset($decoded['orderType']) ? $decoded['orderType'] : 'N/A'
                    ]);
                } else {
                    PrintLogger::warning('批量打印', '京东打印配置解析失败', [
                        'ditch_id' => $ditchId,
                        'raw_value' => $ditchConfig['jd_print_config']
                    ]);
                }
            }
            $printData['jdPrintConfig'] = $jdPrintConfig;
        }
        
        return $printData;
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
    
    /**
     * 批量打印多渠道订单（自动渠道检测）
     * 
     * 功能：选择多个订单，系统自动识别每个订单的快递渠道，并将打印请求发送到相应的云打印API
     * 
     * @param array $orderIds 订单ID数组
     * @return array 打印结果
     *   - success: 整体是否成功（至少一个订单成功）
     *   - total: 总订单数
     *   - successful: 成功数量
     *   - failed: 失败数量
     *   - results: 每个订单的详细结果 [
     *       {
     *         'order_id': 订单ID,
     *         'channel': 渠道名称 (sf/zto/jd/unknown),
     *         'status': 'success'|'failed',
     *         'message': 消息,
     *         'error': 错误信息（失败时）
     *       }
     *     ]
     *   - channel_breakdown: 各渠道统计 {
     *       'sf': {'total': int, 'success': int, 'failed': int},
     *       'zto': {'total': int, 'success': int, 'failed': int},
     *       'jd': {'total': int, 'success': int, 'failed': int}
     *     }
     */
    public static function batchPrintMultiChannel(array $orderIds)
    {
        $startTime = microtime(true);
        
        // 验证输入
        if (empty($orderIds)) {
            return [
                'success' => false,
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
                'results' => [],
                'channel_breakdown' => [],
                'error' => '未选择订单'
            ];
        }
        
        PrintLogger::info('批量多渠道打印', '开始批量多渠道打印', [
            'total_orders' => count($orderIds),
            'order_ids' => $orderIds
        ]);
        
        // 1. 从数据库查询订单数据
        $inpackModel = new \app\store\model\Inpack();
        $orders = $inpackModel->whereIn('id', $orderIds)->select();
        
        if (!$orders || count($orders) === 0) {
            PrintLogger::error('批量多渠道打印', '订单不存在', ['order_ids' => $orderIds]);
            return [
                'success' => false,
                'total' => count($orderIds),
                'successful' => 0,
                'failed' => count($orderIds),
                'results' => [],
                'channel_breakdown' => [],
                'error' => '订单不存在'
            ];
        }
        
        // 转换为数组
        $ordersArray = [];
        foreach ($orders as $order) {
            $ordersArray[] = is_object($order) ? $order->toArray() : $order;
        }
        
        // 2. 检测渠道
        $channelMap = self::detectChannels($ordersArray);
        
        PrintLogger::info('批量多渠道打印', '渠道检测完成', [
            'channel_map' => $channelMap
        ]);
        
        // 3. 按渠道分组
        $groups = self::groupOrdersByChannel($ordersArray, $channelMap);
        
        PrintLogger::info('批量多渠道打印', '订单分组完成', [
            'sf_count' => count($groups['sf']),
            'zto_count' => count($groups['zto']),
            'jd_count' => count($groups['jd']),
            'unknown_count' => count($groups['unknown'])
        ]);
        
        // 4. 处理每个渠道组
        $allResults = [];
        
        // 处理顺丰订单
        if (!empty($groups['sf'])) {
            $sfResults = self::processChannelGroup('sf', $groups['sf']);
            $allResults = array_merge($allResults, $sfResults);
        }
        
        // 处理中通订单
        if (!empty($groups['zto'])) {
            $ztoResults = self::processChannelGroup('zto', $groups['zto']);
            $allResults = array_merge($allResults, $ztoResults);
        }
        
        // 处理京东订单
        if (!empty($groups['jd'])) {
            $jdResults = self::processChannelGroup('jd', $groups['jd']);
            $allResults = array_merge($allResults, $jdResults);
        }
        
        // 处理未知渠道订单（标记为失败）
        if (!empty($groups['unknown'])) {
            foreach ($groups['unknown'] as $order) {
                $allResults[] = [
                    'order_id' => $order['id'],
                    'channel' => 'unknown',
                    'status' => 'failed',
                    'message' => '无法识别快递渠道',
                    'error' => '订单未配置有效的快递渠道'
                ];
            }
        }
        
        // 5. 聚合结果
        $finalResult = self::aggregateResults($allResults);
        
        $elapsedTime = round(microtime(true) - $startTime, 2);
        $finalResult['elapsed_time'] = $elapsedTime;
        
        PrintLogger::success('批量多渠道打印', '批量多渠道打印完成', [
            'total' => $finalResult['total'],
            'successful' => $finalResult['successful'],
            'failed' => $finalResult['failed'],
            'elapsed_time' => $elapsedTime . 's'
        ]);
        
        return $finalResult;
    }
    
    /**
     * 检测每个订单的渠道
     * 
     * @param array $orders 订单数组
     * @return array 订单ID => 渠道名称的映射 ['order_id' => 'sf'|'zto'|'jd'|'unknown']
     */
    private static function detectChannels(array $orders)
    {
        $channelMap = [];
        
        foreach ($orders as $order) {
            $orderId = $order['id'];
            $ditchId = isset($order['t_number']) ? (int)$order['t_number'] : 0;
            
            if ($ditchId <= 0) {
                // 没有配置渠道
                $channelMap[$orderId] = 'unknown';
                PrintLogger::warning('批量多渠道打印', '订单未配置渠道', [
                    'order_id' => $orderId
                ]);
                continue;
            }
            
            // 获取渠道配置
            $ditchConfig = DitchCache::getConfig($ditchId);
            if (!$ditchConfig) {
                $channelMap[$orderId] = 'unknown';
                PrintLogger::warning('批量多渠道打印', '渠道配置不存在', [
                    'order_id' => $orderId,
                    'ditch_id' => $ditchId
                ]);
                continue;
            }
            
            // 根据 ditch_type 判断渠道
            $ditchType = isset($ditchConfig['ditch_type']) ? (int)$ditchConfig['ditch_type'] : 0;
            
            if ($ditchType === 4) {
                $channelMap[$orderId] = 'sf';
            } elseif ($ditchType === 2 || $ditchType === 3) {
                $channelMap[$orderId] = 'zto';
            } elseif ($ditchType === 5) {
                $channelMap[$orderId] = 'jd';
            } else {
                $channelMap[$orderId] = 'unknown';
                PrintLogger::warning('批量多渠道打印', '未知的渠道类型', [
                    'order_id' => $orderId,
                    'ditch_id' => $ditchId,
                    'ditch_type' => $ditchType
                ]);
            }
        }
        
        return $channelMap;
    }
    
    /**
     * 按渠道分组订单
     * 
     * @param array $orders 订单数组
     * @param array $channelMap 渠道映射
     * @return array 分组结果 ['sf' => [...], 'zto' => [...], 'jd' => [...], 'unknown' => [...]]
     */
    private static function groupOrdersByChannel(array $orders, array $channelMap)
    {
        $groups = [
            'sf' => [],
            'zto' => [],
            'jd' => [],
            'unknown' => []
        ];
        
        foreach ($orders as $order) {
            $orderId = $order['id'];
            $channel = isset($channelMap[$orderId]) ? $channelMap[$orderId] : 'unknown';
            
            $groups[$channel][] = $order;
        }
        
        return $groups;
    }
    
    /**
     * 处理单个渠道组的订单
     * 
     * @param string $channel 渠道名称 (sf/zto/jd)
     * @param array $orders 该渠道的订单数组
     * @return array 处理结果数组
     */
    private static function processChannelGroup($channel, array $orders)
    {
        $results = [];
        $channelUnavailable = false;
        $channelUnavailableReason = '';
        
        PrintLogger::info('批量多渠道打印', "开始处理{$channel}渠道订单", [
            'channel' => $channel,
            'count' => count($orders)
        ]);
        
        // 先检查渠道是否可用（通过检查第一个订单）
        if (!empty($orders)) {
            $firstOrder = $orders[0];
            $ditchId = isset($firstOrder['t_number']) ? (int)$firstOrder['t_number'] : 0;
            
            try {
                $ditchConfig = DitchCache::getConfig($ditchId);
                if (!$ditchConfig) {
                    $channelUnavailable = true;
                    $channelUnavailableReason = '渠道配置不存在';
                }
            } catch (\Exception $e) {
                $channelUnavailable = true;
                $channelUnavailableReason = '渠道配置加载失败: ' . $e->getMessage();
            }
        }
        
        // 如果渠道完全不可用，将所有订单标记为失败
        if ($channelUnavailable) {
            PrintLogger::error('批量多渠道打印', "渠道{$channel}不可用", [
                'channel' => $channel,
                'reason' => $channelUnavailableReason,
                'order_count' => count($orders)
            ]);
            
            foreach ($orders as $order) {
                $results[] = [
                    'order_id' => $order['id'],
                    'channel' => $channel,
                    'status' => 'failed',
                    'message' => '渠道不可用',
                    'error' => $channelUnavailableReason
                ];
            }
            
            return $results;
        }
        
        // 渠道可用，处理每个订单
        foreach ($orders as $order) {
            $orderId = $order['id'];
            $ditchId = isset($order['t_number']) ? (int)$order['t_number'] : 0;
            
            try {
                // 实例化对应的 Ditch 类
                $ditchConfig = DitchCache::getConfig($ditchId);
                if (!$ditchConfig) {
                    throw new \Exception('渠道配置不存在');
                }
                
                $ditchClass = null;
                if ($channel === 'sf') {
                    $ditchClass = new \app\common\library\Ditch\Sf($ditchConfig);
                } elseif ($channel === 'zto') {
                    $ditchClass = new \app\common\library\Ditch\Zto($ditchConfig);
                } elseif ($channel === 'jd') {
                    $ditchClass = new \app\common\library\Ditch\Jd($ditchConfig);
                }
                
                if (!$ditchClass) {
                    throw new \Exception('无法实例化渠道类');
                }
                
                // 调用 cloudPrint 方法
                // 注意：Sf 和 Jd 的 cloudPrint 方法签名可能不同，需要适配
                $printResult = null;
                
                PrintLogger::info('批量多渠道打印', "准备调用{$channel}打印方法", [
                    'order_id' => $orderId,
                    'channel' => $channel,
                    'ditch_id' => $ditchId,
                    'waybill_no' => isset($order['t_order_sn']) ? $order['t_order_sn'] : ''
                ]);
                
                if ($channel === 'sf') {
                    // 顺丰：printlabelParsedData($order_id, $options)
                    $waybillNo = isset($order['t_order_sn']) ? $order['t_order_sn'] : '';
                    $printResult = $ditchClass->printlabelParsedData($orderId, [
                        'waybill_no' => $waybillNo,
                        'print_mode' => 'mother'
                    ]);
                    
                    PrintLogger::info('批量多渠道打印', "顺丰打印方法返回", [
                        'order_id' => $orderId,
                        'result_type' => gettype($printResult),
                        'result_is_array' => is_array($printResult),
                        'result_keys' => is_array($printResult) ? array_keys($printResult) : null
                    ]);
                } elseif ($channel === 'zto') {
                    // 中通：cloudPrint($order_id, $options)
                    $printResult = $ditchClass->cloudPrint($orderId, [
                        'print_mode' => 'mother'
                    ]);
                    
                    PrintLogger::info('批量多渠道打印', "中通打印方法返回", [
                        'order_id' => $orderId,
                        'result_type' => gettype($printResult),
                        'result_is_array' => is_array($printResult),
                        'result_keys' => is_array($printResult) ? array_keys($printResult) : null
                    ]);
                } elseif ($channel === 'jd') {
                    // 京东：jdcloudprint($orderId, $waybillCode)
                    $waybillCode = isset($order['t_order_sn']) ? $order['t_order_sn'] : '';
                    if (empty($waybillCode)) {
                        throw new \Exception('京东运单号不存在');
                    }
                    $printResult = $ditchClass->jdcloudprint($orderId, $waybillCode);
                    
                    PrintLogger::info('批量多渠道打印', "京东打印方法返回", [
                        'order_id' => $orderId,
                        'result_type' => gettype($printResult),
                        'result_is_array' => is_array($printResult),
                        'result_keys' => is_array($printResult) ? array_keys($printResult) : null
                    ]);
                }
                
                // 判断打印是否成功
                if ($printResult === false) {
                    // 失败
                    $errorMsg = method_exists($ditchClass, 'getError') ? $ditchClass->getError() : '打印失败';
                    
                    $results[] = [
                        'order_id' => $orderId,
                        'channel' => $channel,
                        'status' => 'failed',
                        'message' => '打印失败',
                        'error' => $errorMsg
                    ];
                    
                    PrintLogger::error('批量多渠道打印', '订单打印失败', [
                        'order_id' => $orderId,
                        'channel' => $channel,
                        'error' => $errorMsg
                    ]);
                } else {
                    // 成功
                    $results[] = [
                        'order_id' => $orderId,
                        'channel' => $channel,
                        'status' => 'success',
                        'message' => '打印任务已发送',
                        'error' => null
                    ];
                    
                    PrintLogger::success('批量多渠道打印', '订单打印成功', [
                        'order_id' => $orderId,
                        'channel' => $channel
                    ]);
                }
                
            } catch (\Exception $e) {
                // 捕获异常，区分不同类型的错误
                $errorMsg = $e->getMessage();
                $errorType = '打印异常';
                
                // 识别错误类型
                if (strpos($errorMsg, 'timeout') !== false || strpos($errorMsg, '超时') !== false) {
                    $errorType = '网络超时';
                } elseif (strpos($errorMsg, 'auth') !== false || strpos($errorMsg, '认证') !== false || 
                         strpos($errorMsg, 'token') !== false || strpos($errorMsg, 'permission') !== false) {
                    $errorType = 'API认证失败';
                } elseif (strpos($errorMsg, 'connect') !== false || strpos($errorMsg, '连接') !== false) {
                    $errorType = '网络连接失败';
                }
                
                $results[] = [
                    'order_id' => $orderId,
                    'channel' => $channel,
                    'status' => 'failed',
                    'message' => $errorType,
                    'error' => $errorMsg
                ];
                
                PrintLogger::error('批量多渠道打印', '订单打印异常', [
                    'order_id' => $orderId,
                    'channel' => $channel,
                    'error_type' => $errorType,
                    'error' => $errorMsg,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        return $results;
    }
    
    /**
     * 聚合所有渠道的打印结果
     * 
     * @param array $allResults 所有结果数组
     * @return array 聚合后的结果
     */
    private static function aggregateResults(array $allResults)
    {
        $total = count($allResults);
        $successful = 0;
        $failed = 0;
        
        // 渠道分解统计
        $channelBreakdown = [
            'sf' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'zto' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'jd' => ['total' => 0, 'success' => 0, 'failed' => 0]
        ];
        
        foreach ($allResults as $result) {
            $channel = $result['channel'];
            $status = $result['status'];
            
            if ($status === 'success') {
                $successful++;
            } else {
                $failed++;
            }
            
            // 更新渠道统计
            if (isset($channelBreakdown[$channel])) {
                $channelBreakdown[$channel]['total']++;
                if ($status === 'success') {
                    $channelBreakdown[$channel]['success']++;
                } else {
                    $channelBreakdown[$channel]['failed']++;
                }
            }
        }
        
        return [
            'success' => $successful > 0,
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'results' => $allResults,
            'channel_breakdown' => $channelBreakdown
        ];
    }
}
