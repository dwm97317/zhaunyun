<?php

namespace app\common\service;

use app\common\service\PrintLogger;
use think\Db;
use think\Cache;

/**
 * 异步任务队列工具
 * 
 * 功能：为批量打印和批量推送提供异步处理能力
 * 
 * 特性：
 * - 任务队列管理（添加、执行、查询）
 * - 任务状态跟踪（pending、processing、completed、failed）
 * - 任务优先级支持
 * - 任务重试机制
 * - 任务结果存储
 * - 并发控制
 * - 统一日志记录
 * 
 * 使用场景：
 * - 批量打印订单（异步执行，不阻塞用户操作）
 * - 批量推送订单到渠道商（异步执行）
 * - 大批量操作（避免超时）
 * 
 * 实现方式：
 * - 使用数据库表存储任务队列
 * - 使用 Cache 实现分布式锁
 * - 支持后台定时任务执行
 */
class AsyncTaskQueue
{
    /**
     * 任务状态常量
     */
    const STATUS_PENDING = 'pending';       // 待处理
    const STATUS_PROCESSING = 'processing'; // 处理中
    const STATUS_COMPLETED = 'completed';   // 已完成
    const STATUS_FAILED = 'failed';         // 失败
    
    /**
     * 任务类型常量
     */
    const TYPE_ORDER_BATCH_PRINTER = 'order_batch_printer';   // 批量打印（OrderBatchPrinter）
    const TYPE_ORDER_BATCH_PUSHER = 'order_batch_pusher';     // 批量推送（OrderBatchPusher）
    
    /**
     * 添加批量打印任务
     * 
     * @param array $orderIds 订单ID数组
     * @param int $ditchId 渠道ID
     * @param array $printOptions 打印选项
     * @param int $priority 优先级（1-10，数字越大优先级越高）
     * @return int|false 任务ID，失败返回 false
     */
    public static function addBatchPrintTask(array $orderIds, $ditchId, array $printOptions = [], $priority = 5)
    {
        try {
            $taskData = [
                'task_type' => self::TYPE_ORDER_BATCH_PRINTER,
                'task_data' => json_encode([
                    'order_ids' => $orderIds,
                    'ditch_id' => $ditchId,
                    'print_options' => $printOptions
                ]),
                'status' => self::STATUS_PENDING,
                'priority' => $priority,
                'retry_count' => 0,
                'max_retries' => 3,
                'created_time' => date('Y-m-d H:i:s'),
                'updated_time' => date('Y-m-d H:i:s')
            ];
            
            $taskId = Db::name('async_task_queue')->insertGetId($taskData);
            
            PrintLogger::success('异步任务', '批量打印任务已添加', [
                'task_id' => $taskId,
                'order_count' => count($orderIds),
                'ditch_id' => $ditchId,
                'priority' => $priority
            ]);
            
            return $taskId;
            
        } catch (\Exception $e) {
            PrintLogger::error('异步任务', '添加任务失败', [
                'type' => self::TYPE_ORDER_BATCH_PRINTER,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 添加批量推送任务
     * 
     * @param array $orderIds 订单ID数组
     * @param int $ditchId 渠道ID
     * @param int $priority 优先级（1-10，数字越大优先级越高）
     * @return int|false 任务ID，失败返回 false
     */
    public static function addBatchPushTask(array $orderIds, $ditchId, $priority = 5)
    {
        try {
            $taskData = [
                'task_type' => self::TYPE_ORDER_BATCH_PUSHER,
                'task_data' => json_encode([
                    'order_ids' => $orderIds,
                    'ditch_id' => $ditchId
                ]),
                'status' => self::STATUS_PENDING,
                'priority' => $priority,
                'retry_count' => 0,
                'max_retries' => 3,
                'created_time' => date('Y-m-d H:i:s'),
                'updated_time' => date('Y-m-d H:i:s')
            ];
            
            $taskId = Db::name('async_task_queue')->insertGetId($taskData);
            
            PrintLogger::success('异步任务', '批量推送任务已添加', [
                'task_id' => $taskId,
                'order_count' => count($orderIds),
                'ditch_id' => $ditchId,
                'priority' => $priority
            ]);
            
            return $taskId;
            
        } catch (\Exception $e) {
            PrintLogger::error('异步任务', '添加任务失败', [
                'type' => self::TYPE_ORDER_BATCH_PUSHER,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 执行待处理的任务
     * 
     * @param int $limit 每次执行的任务数量
     * @param int $timeout 单个任务超时时间（秒）
     * @return array 执行结果统计
     */
    public static function processPendingTasks($limit = 10, $timeout = 300)
    {
        $startTime = microtime(true);
        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;
        
        PrintLogger::info('异步任务', '开始处理待处理任务', ['limit' => $limit]);
        
        try {
            // 获取待处理的任务（按优先级和创建时间排序）
            $tasks = Db::name('async_task_queue')
                ->where('status', self::STATUS_PENDING)
                ->whereOr('status', self::STATUS_FAILED)
                ->where('retry_count', '<', Db::raw('max_retries'))
                ->order('priority DESC, created_time ASC')
                ->limit($limit)
                ->select();
            
            if (empty($tasks)) {
                PrintLogger::info('异步任务', '没有待处理任务');
                return [
                    'processed' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'elapsed_time' => 0
                ];
            }
            
            foreach ($tasks as $task) {
                // 尝试获取任务锁（防止并发执行）
                $lockKey = 'async_task_lock_' . $task['id'];
                if (!self::acquireLock($lockKey, $timeout)) {
                    PrintLogger::warning('异步任务', '任务已被其他进程处理', ['task_id' => $task['id']]);
                    continue;
                }
                
                try {
                    // 更新任务状态为处理中
                    self::updateTaskStatus($task['id'], self::STATUS_PROCESSING);
                    
                    // 执行任务
                    $result = self::executeTask($task);
                    
                    if ($result['success']) {
                        // 任务成功
                        self::updateTaskStatus($task['id'], self::STATUS_COMPLETED, $result);
                        $successCount++;
                    } else {
                        // 任务失败，增加重试次数
                        $retryCount = $task['retry_count'] + 1;
                        if ($retryCount >= $task['max_retries']) {
                            // 达到最大重试次数，标记为失败
                            self::updateTaskStatus($task['id'], self::STATUS_FAILED, $result, $retryCount);
                        } else {
                            // 重新标记为待处理，等待重试
                            self::updateTaskStatus($task['id'], self::STATUS_PENDING, $result, $retryCount);
                        }
                        $failedCount++;
                    }
                    
                    $processedCount++;
                    
                } catch (\Exception $e) {
                    PrintLogger::error('异步任务', '任务执行异常', [
                        'task_id' => $task['id'],
                        'error' => $e->getMessage()
                    ]);
                    
                    $retryCount = $task['retry_count'] + 1;
                    self::updateTaskStatus(
                        $task['id'], 
                        $retryCount >= $task['max_retries'] ? self::STATUS_FAILED : self::STATUS_PENDING,
                        ['error' => $e->getMessage()],
                        $retryCount
                    );
                    $failedCount++;
                    $processedCount++;
                    
                } finally {
                    // 释放锁
                    self::releaseLock($lockKey);
                }
            }
            
            $elapsedTime = round(microtime(true) - $startTime, 2);
            
            PrintLogger::success('异步任务', '任务处理完成', [
                'processed' => $processedCount,
                'success' => $successCount,
                'failed' => $failedCount,
                'elapsed_time' => $elapsedTime . 's'
            ]);
            
            return [
                'processed' => $processedCount,
                'success' => $successCount,
                'failed' => $failedCount,
                'elapsed_time' => $elapsedTime
            ];
            
        } catch (\Exception $e) {
            PrintLogger::error('异步任务', '处理任务异常', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'processed' => $processedCount,
                'success' => $successCount,
                'failed' => $failedCount,
                'elapsed_time' => round(microtime(true) - $startTime, 2),
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 执行单个任务
     * 
     * @param array $task 任务数据
     * @return array 执行结果
     */
    private static function executeTask($task)
    {
        $taskData = json_decode($task['task_data'], true);
        
        PrintLogger::info('异步任务', '开始执行任务', [
            'task_id' => $task['id'],
            'task_type' => $task['task_type']
        ]);
        
        try {
            switch ($task['task_type']) {
                case self::TYPE_ORDER_BATCH_PRINTER:
                    // 执行批量打印
                    $result = \app\common\service\OrderBatchPrinter::print(
                        $taskData['order_ids'],
                        $taskData['ditch_id'],
                        isset($taskData['print_options']) ? $taskData['print_options'] : []
                    );
                    
                    return [
                        'success' => $result['success_count'] > 0,
                        'result' => $result
                    ];
                    
                case self::TYPE_ORDER_BATCH_PUSHER:
                    // 执行批量推送
                    $result = \app\common\service\OrderBatchPusher::push(
                        $taskData['order_ids'],
                        $taskData['ditch_id']
                    );
                    
                    return [
                        'success' => $result['success_count'] > 0,
                        'result' => $result
                    ];
                    
                default:
                    return [
                        'success' => false,
                        'error' => 'Unknown task type: ' . $task['task_type']
                    ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 更新任务状态
     * 
     * @param int $taskId 任务ID
     * @param string $status 状态
     * @param array $result 执行结果（可选）
     * @param int $retryCount 重试次数（可选）
     * @return bool
     */
    private static function updateTaskStatus($taskId, $status, $result = [], $retryCount = null)
    {
        $updateData = [
            'status' => $status,
            'updated_time' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($result)) {
            $updateData['result'] = json_encode($result);
        }
        
        if ($retryCount !== null) {
            $updateData['retry_count'] = $retryCount;
        }
        
        if ($status === self::STATUS_COMPLETED || $status === self::STATUS_FAILED) {
            $updateData['finished_time'] = date('Y-m-d H:i:s');
        }
        
        return Db::name('async_task_queue')
            ->where('id', $taskId)
            ->update($updateData);
    }
    
    /**
     * 获取任务状态
     * 
     * @param int $taskId 任务ID
     * @return array|null 任务信息
     */
    public static function getTaskStatus($taskId)
    {
        try {
            $task = Db::name('async_task_queue')
                ->where('id', $taskId)
                ->find();
            
            if (!$task) {
                return null;
            }
            
            return [
                'task_id' => $task['id'],
                'task_type' => $task['task_type'],
                'status' => $task['status'],
                'priority' => $task['priority'],
                'retry_count' => $task['retry_count'],
                'max_retries' => $task['max_retries'],
                'result' => !empty($task['result']) ? json_decode($task['result'], true) : null,
                'created_time' => $task['created_time'],
                'updated_time' => $task['updated_time'],
                'finished_time' => isset($task['finished_time']) ? $task['finished_time'] : null
            ];
            
        } catch (\Exception $e) {
            PrintLogger::error('异步任务', '查询任务状态失败', [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * 获取任务队列统计
     * 
     * @return array 统计信息
     */
    public static function getQueueStatistics()
    {
        try {
            $total = Db::name('async_task_queue')->count();
            $pending = Db::name('async_task_queue')->where('status', self::STATUS_PENDING)->count();
            $processing = Db::name('async_task_queue')->where('status', self::STATUS_PROCESSING)->count();
            $completed = Db::name('async_task_queue')->where('status', self::STATUS_COMPLETED)->count();
            $failed = Db::name('async_task_queue')->where('status', self::STATUS_FAILED)->count();
            
            return [
                'total' => $total,
                'pending' => $pending,
                'processing' => $processing,
                'completed' => $completed,
                'failed' => $failed
            ];
            
        } catch (\Exception $e) {
            PrintLogger::error('异步任务', '获取统计失败', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * 清理已完成的任务（保留最近N天）
     * 
     * @param int $days 保留天数
     * @return int 清理数量
     */
    public static function cleanupCompletedTasks($days = 7)
    {
        try {
            $beforeDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            $count = Db::name('async_task_queue')
                ->where('status', self::STATUS_COMPLETED)
                ->where('finished_time', '<', $beforeDate)
                ->delete();
            
            PrintLogger::info('异步任务', '清理已完成任务', [
                'days' => $days,
                'count' => $count
            ]);
            
            return $count;
            
        } catch (\Exception $e) {
            PrintLogger::error('异步任务', '清理任务失败', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
    
    /**
     * 获取分布式锁
     * 
     * @param string $key 锁键名
     * @param int $timeout 超时时间（秒）
     * @return bool
     */
    private static function acquireLock($key, $timeout = 300)
    {
        try {
            return Cache::set($key, time(), $timeout);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 释放分布式锁
     * 
     * @param string $key 锁键名
     * @return bool
     */
    private static function releaseLock($key)
    {
        try {
            return Cache::rm($key);
        } catch (\Exception $e) {
            return false;
        }
    }
}
