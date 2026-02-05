<?php

namespace app\common\service;

use app\common\service\PrintLogger;

/**
 * 智能重试工具
 * 
 * 用于处理推送到第三方渠道商时的网络抖动问题
 * 支持：
 * - 指数退避重试策略
 * - 可配置的重试次数和延迟
 * - 全局开关控制
 * - 详细的重试日志
 * - 预留批量推送和异步处理接口
 */
class RetryHelper
{
    // 默认配置
    const DEFAULT_MAX_ATTEMPTS = 3;        // 默认最大重试次数
    const DEFAULT_INITIAL_DELAY = 1000;    // 默认初始延迟（毫秒）
    const DEFAULT_MAX_DELAY = 10000;       // 默认最大延迟（毫秒）
    const DEFAULT_MULTIPLIER = 2;          // 默认延迟倍数（指数退避）
    
    /**
     * 执行带重试的操作
     * 
     * @param callable $operation 要执行的操作（返回 true 表示成功，false 表示失败）
     * @param array $options 配置选项
     *   - enabled: bool 是否启用重试（默认 false，只在批量推送时启用）
     *   - max_attempts: int 最大重试次数（默认 3）
     *   - initial_delay: int 初始延迟毫秒数（默认 1000）
     *   - max_delay: int 最大延迟毫秒数（默认 10000）
     *   - multiplier: float 延迟倍数（默认 2）
     *   - channel: string 渠道名称（用于日志）
     *   - operation_name: string 操作名称（用于日志）
     *   - should_retry: callable 自定义重试判断函数（可选）
     * @return array ['success' => bool, 'result' => mixed, 'attempts' => int, 'error' => string]
     */
    public static function execute(callable $operation, array $options = [])
    {
        // 合并默认配置
        // 注意：默认 enabled = false，只在批量推送时才启用重试
        $config = array_merge([
            'enabled' => false,  // 默认关闭，只在批量推送时启用
            'max_attempts' => self::DEFAULT_MAX_ATTEMPTS,
            'initial_delay' => self::DEFAULT_INITIAL_DELAY,
            'max_delay' => self::DEFAULT_MAX_DELAY,
            'multiplier' => self::DEFAULT_MULTIPLIER,
            'channel' => '未知渠道',
            'operation_name' => '操作',
            'should_retry' => null
        ], $options);
        
        // 如果未启用重试，直接执行一次
        if (!$config['enabled']) {
            try {
                $result = $operation();
                return [
                    'success' => (bool)$result,
                    'result' => $result,
                    'attempts' => 1,
                    'error' => ''
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'result' => null,
                    'attempts' => 1,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        $attempt = 0;
        $delay = $config['initial_delay'];
        $lastError = '';
        
        PrintLogger::info($config['channel'], '开始执行重试操作', [
            'operation' => $config['operation_name'],
            'max_attempts' => $config['max_attempts']
        ]);
        
        while ($attempt < $config['max_attempts']) {
            $attempt++;
            $startTime = microtime(true);
            
            try {
                PrintLogger::info($config['channel'], '尝试执行', [
                    'operation' => $config['operation_name'],
                    'attempt' => $attempt,
                    'max_attempts' => $config['max_attempts']
                ]);
                
                // 执行操作
                $result = $operation();
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                
                // 判断是否成功
                $isSuccess = false;
                if ($config['should_retry'] !== null && is_callable($config['should_retry'])) {
                    // 使用自定义判断函数
                    $isSuccess = !$config['should_retry']($result);
                } else {
                    // 默认判断：result 为 true 或非空数组表示成功
                    $isSuccess = ($result === true || (is_array($result) && !empty($result)));
                }
                
                if ($isSuccess) {
                    PrintLogger::success($config['channel'], '操作成功', [
                        'operation' => $config['operation_name'],
                        'attempt' => $attempt,
                        'elapsed_ms' => $elapsed
                    ]);
                    
                    return [
                        'success' => true,
                        'result' => $result,
                        'attempts' => $attempt,
                        'error' => ''
                    ];
                }
                
                // 操作失败，记录并准备重试
                $lastError = '操作返回失败结果';
                PrintLogger::warning($config['channel'], '操作失败，准备重试', [
                    'operation' => $config['operation_name'],
                    'attempt' => $attempt,
                    'max_attempts' => $config['max_attempts'],
                    'elapsed_ms' => $elapsed
                ]);
                
            } catch (\Exception $e) {
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                $lastError = $e->getMessage();
                
                PrintLogger::error($config['channel'], '操作异常', [
                    'operation' => $config['operation_name'],
                    'attempt' => $attempt,
                    'max_attempts' => $config['max_attempts'],
                    'error' => $lastError,
                    'elapsed_ms' => $elapsed
                ]);
            }
            
            // 如果还有重试机会，等待后重试
            if ($attempt < $config['max_attempts']) {
                $actualDelay = min($delay, $config['max_delay']);
                
                PrintLogger::info($config['channel'], '等待后重试', [
                    'operation' => $config['operation_name'],
                    'delay_ms' => $actualDelay,
                    'next_attempt' => $attempt + 1
                ]);
                
                usleep($actualDelay * 1000); // 转换为微秒
                
                // 指数退避：下次延迟时间翻倍
                $delay = (int)($delay * $config['multiplier']);
            }
        }
        
        // 所有重试都失败
        PrintLogger::error($config['channel'], '操作最终失败', [
            'operation' => $config['operation_name'],
            'total_attempts' => $attempt,
            'last_error' => $lastError
        ]);
        
        return [
            'success' => false,
            'result' => null,
            'attempts' => $attempt,
            'error' => $lastError
        ];
    }
    
    /**
     * 批量执行带重试的操作（预留接口）
     * 
     * @param array $operations 操作列表 [['callable' => callable, 'options' => array], ...]
     * @param array $batchOptions 批量配置
     *   - parallel: bool 是否并行执行（默认 false，顺序执行）
     *   - stop_on_error: bool 遇到错误是否停止（默认 false）
     * @return array ['success_count' => int, 'error_count' => int, 'results' => array]
     */
    public static function executeBatch(array $operations, array $batchOptions = [])
    {
        $config = array_merge([
            'parallel' => false,
            'stop_on_error' => false
        ], $batchOptions);
        
        $results = [];
        $successCount = 0;
        $errorCount = 0;
        
        PrintLogger::info('批量重试', '开始批量执行', [
            'total' => count($operations),
            'parallel' => $config['parallel']
        ]);
        
        foreach ($operations as $index => $op) {
            if (!isset($op['callable']) || !is_callable($op['callable'])) {
                PrintLogger::error('批量重试', '无效的操作', ['index' => $index]);
                $errorCount++;
                continue;
            }
            
            $options = isset($op['options']) ? $op['options'] : [];
            $result = self::execute($op['callable'], $options);
            
            $results[] = $result;
            
            if ($result['success']) {
                $successCount++;
            } else {
                $errorCount++;
                
                if ($config['stop_on_error']) {
                    PrintLogger::warning('批量重试', '遇到错误，停止执行', [
                        'completed' => $index + 1,
                        'total' => count($operations)
                    ]);
                    break;
                }
            }
        }
        
        PrintLogger::success('批量重试', '批量执行完成', [
            'total' => count($operations),
            'success' => $successCount,
            'error' => $errorCount
        ]);
        
        return [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'results' => $results
        ];
    }
    
    /**
     * 异步执行带重试的操作（预留接口）
     * 
     * 注意：当前为同步实现，未来可以集成队列系统（如 Redis Queue、RabbitMQ 等）
     * 
     * @param callable $operation 要执行的操作
     * @param array $options 配置选项
     * @return array ['queued' => bool, 'job_id' => string, 'message' => string]
     */
    public static function executeAsync(callable $operation, array $options = [])
    {
        // TODO: 集成队列系统
        // 当前为同步执行的占位实现
        
        PrintLogger::warning('异步重试', '异步功能未实现，使用同步执行', [
            'operation' => isset($options['operation_name']) ? $options['operation_name'] : '未知操作'
        ]);
        
        $result = self::execute($operation, $options);
        
        return [
            'queued' => false,
            'job_id' => '',
            'message' => '当前使用同步执行',
            'result' => $result
        ];
    }
    
    /**
     * 获取推荐的重试配置（根据渠道类型）
     * 
     * @param string $channel 渠道类型 (sf/zto/jd/yd 等)
     * @return array 推荐配置
     */
    public static function getRecommendedConfig($channel)
    {
        $configs = [
            'sf' => [
                'max_attempts' => 3,
                'initial_delay' => 1000,
                'max_delay' => 10000,
                'multiplier' => 2
            ],
            'zto' => [
                'max_attempts' => 3,
                'initial_delay' => 1000,
                'max_delay' => 10000,
                'multiplier' => 2
            ],
            'jd' => [
                'max_attempts' => 3,
                'initial_delay' => 1500,
                'max_delay' => 15000,
                'multiplier' => 2
            ],
            'yd' => [
                'max_attempts' => 3,
                'initial_delay' => 1000,
                'max_delay' => 10000,
                'multiplier' => 2
            ],
            'default' => [
                'max_attempts' => 3,
                'initial_delay' => 1000,
                'max_delay' => 10000,
                'multiplier' => 2
            ]
        ];
        
        return isset($configs[$channel]) ? $configs[$channel] : $configs['default'];
    }
}
