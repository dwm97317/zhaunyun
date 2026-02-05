<?php
namespace app\common\service;

use think\Cache;
use think\Log;

/**
 * 京东快递云打印缓存服务 - 企业级实现
 * 
 * 特性：
 * - L1 (内存) + L2 (文件) 双层缓存
 * - 缓存穿透防护（空值缓存）
 * - 缓存击穿防护（互斥锁）
 * - 缓存雪崩防护（随机TTL）
 * - 性能监控
 */
class JdCache
{
    // L1 缓存（进程内存）
    private static $memoryCache = [];
    
    // TTL 配置
    const ACCESS_TOKEN_TTL = 7000;      // AccessToken: 1小时55分钟
    const PRINT_DATA_TTL = 86400;       // PrintData: 24小时
    const PRINTER_LIST_TTL = 3600;      // PrinterList: 1小时
    const EMPTY_TTL = 300;              // 空值缓存: 5分钟（防穿透）
    
    // 性能统计
    private static $stats = [
        'l1_hits' => 0,
        'l2_hits' => 0,
        'misses' => 0,
        'sets' => 0
    ];
    
    /**
     * 获取 AccessToken（带双层缓存）
     * 
     * @param string $appKey 应用密钥
     * @param callable $fetcher 数据获取函数
     * @return string|false
     */
    public static function getAccessToken($appKey, callable $fetcher)
    {
        $key = "jd_token_" . md5($appKey);
        $startTime = microtime(true);
        
        // L1: 内存缓存
        if (isset(self::$memoryCache[$key])) {
            self::$stats['l1_hits']++;
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);
            \app\common\service\PrintLogger::cacheHit('京东Token', $key, ['elapsed_ms' => $elapsed]);
            self::logPerf('token', 'L1_HIT', microtime(true) - $startTime);
            return self::$memoryCache[$key];
        }
        
        // L2: 文件缓存
        $cached = Cache::get($key);
        if ($cached !== false && $cached !== null) {
            self::$memoryCache[$key] = $cached; // 回填 L1
            self::$stats['l2_hits']++;
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);
            \app\common\service\PrintLogger::cacheHit('京东Token', $key, ['source' => 'L2', 'elapsed_ms' => $elapsed]);
            self::logPerf('token', 'L2_HIT', microtime(true) - $startTime);
            return $cached;
        }
        
        \app\common\service\PrintLogger::cacheMiss('京东Token', $key);
        
        // 缓存未命中 - 使用互斥锁防止击穿
        $lockKey = "{$key}_lock";
        $lockAcquired = self::acquireLock($lockKey, 10);
        
        if (!$lockAcquired) {
            // 未获取到锁，等待并重试
            usleep(100000); // 100ms
            $cached = Cache::get($key);
            if ($cached !== false && $cached !== null) {
                return $cached;
            }
        }
        
        try {
            // 调用数据源
            self::$stats['misses']++;
            $value = $fetcher();
            
            if ($value) {
                // 成功获取，缓存数据
                $ttl = self::ACCESS_TOKEN_TTL + rand(-300, 300); // 随机TTL防雪崩
                Cache::set($key, $value, $ttl);
                self::$memoryCache[$key] = $value;
                self::$stats['sets']++;
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                \app\common\service\PrintLogger::success('京东Token', '获取并缓存成功', [
                    'ttl' => $ttl . 's',
                    'elapsed_ms' => $elapsed
                ]);
                self::logPerf('token', 'MISS_SET', microtime(true) - $startTime);
            } else {
                // 获取失败，缓存空值防穿透
                Cache::set($key, '__EMPTY__', self::EMPTY_TTL);
                \app\common\service\PrintLogger::warning('京东Token', '获取失败，缓存空值', ['ttl' => self::EMPTY_TTL . 's']);
                self::logPerf('token', 'MISS_EMPTY', microtime(true) - $startTime);
            }
            
            return $value;
            
        } finally {
            if ($lockAcquired) {
                self::releaseLock($lockKey);
            }
        }
    }
    
    /**
     * 获取打印数据（带双层缓存和地址变更检测）
     * 
     * @param string $waybillNo 运单号
     * @param callable $fetcher 数据获取函数
     * @param array $addressData 收货地址数据（用于检测地址变更）
     * @param bool &$cacheHit 缓存命中标记（引用参数，返回是否命中缓存）
     * @return array|false
     */
    public static function getPrintData($waybillNo, callable $fetcher, $addressData = null, &$cacheHit = null)
    {
        $key = "jd_print_" . md5($waybillNo);
        $startTime = microtime(true);
        
        // L1: 内存缓存
        if (isset(self::$memoryCache[$key])) {
            self::$stats['l1_hits']++;
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);
            
            // 检查地址是否变更
            if ($addressData !== null) {
                $cached = self::$memoryCache[$key];
                if (is_array($cached) && isset($cached['__address_hash__'])) {
                    $currentAddressHash = self::hashAddress($addressData);
                    if ($cached['__address_hash__'] !== $currentAddressHash) {
                        \app\common\service\PrintLogger::warning('京东打印', '地址已变更，清除缓存', [
                            'waybill_no' => $waybillNo,
                            'source' => 'L1'
                        ]);
                        self::logPerf('print_data', 'L1_ADDRESS_CHANGED', microtime(true) - $startTime);
                        // 地址变更，清除缓存并重新获取
                        unset(self::$memoryCache[$key]);
                        Cache::rm($key);
                        // 继续执行数据源获取
                    } else {
                        \app\common\service\PrintLogger::cacheHit('京东打印', $key, [
                            'waybill_no' => $waybillNo,
                            'elapsed_ms' => $elapsed
                        ]);
                        self::logPerf('print_data', 'L1_HIT', microtime(true) - $startTime);
                        $cacheHit = true;
                        return $cached;
                    }
                } else {
                    \app\common\service\PrintLogger::cacheHit('京东打印', $key, [
                        'waybill_no' => $waybillNo,
                        'elapsed_ms' => $elapsed
                    ]);
                    self::logPerf('print_data', 'L1_HIT', microtime(true) - $startTime);
                    $cacheHit = true;
                    return self::$memoryCache[$key];
                }
            } else {
                \app\common\service\PrintLogger::cacheHit('京东打印', $key, [
                    'waybill_no' => $waybillNo,
                    'elapsed_ms' => $elapsed
                ]);
                self::logPerf('print_data', 'L1_HIT', microtime(true) - $startTime);
                $cacheHit = true;
                return self::$memoryCache[$key];
            }
        }
        
        // L2: 文件缓存
        $cached = Cache::get($key);
        
        if ($cached !== false && $cached !== null) {
            // 检查是否是空值标记
            if ($cached === '__EMPTY__') {
                \app\common\service\PrintLogger::warning('京东打印', '缓存为空值标记', ['waybill_no' => $waybillNo]);
                self::logPerf('print_data', 'L2_EMPTY', microtime(true) - $startTime);
                return false;
            }
            
            // 检查地址是否变更
            if ($addressData !== null && is_array($cached) && isset($cached['__address_hash__'])) {
                $currentAddressHash = self::hashAddress($addressData);
                if ($cached['__address_hash__'] !== $currentAddressHash) {
                    \app\common\service\PrintLogger::warning('京东打印', '地址已变更，清除缓存', [
                        'waybill_no' => $waybillNo,
                        'source' => 'L2'
                    ]);
                    self::logPerf('print_data', 'L2_ADDRESS_CHANGED', microtime(true) - $startTime);
                    // 地址变更，清除缓存并重新获取
                    Cache::rm($key);
                    // 继续执行数据源获取
                } else {
                    self::$memoryCache[$key] = $cached;
                    self::$stats['l2_hits']++;
                    $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                    \app\common\service\PrintLogger::cacheHit('京东打印', $key, [
                        'waybill_no' => $waybillNo,
                        'source' => 'L2',
                        'elapsed_ms' => $elapsed
                    ]);
                    self::logPerf('print_data', 'L2_HIT', microtime(true) - $startTime);
                    $cacheHit = true;
                    return $cached;
                }
            } else {
                self::$memoryCache[$key] = $cached;
                self::$stats['l2_hits']++;
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                \app\common\service\PrintLogger::cacheHit('京东打印', $key, [
                    'waybill_no' => $waybillNo,
                    'source' => 'L2',
                    'elapsed_ms' => $elapsed
                ]);
                self::logPerf('print_data', 'L2_HIT', microtime(true) - $startTime);
                $cacheHit = true;
                return $cached;
            }
        }
        
        \app\common\service\PrintLogger::cacheMiss('京东打印', $key, ['waybill_no' => $waybillNo]);
        
        // 缓存未命中 - 使用互斥锁
        $lockKey = "{$key}_lock";
        $lockAcquired = self::acquireLock($lockKey, 10);
        
        if (!$lockAcquired) {
            usleep(100000);
            $cached = Cache::get($key);
            if ($cached !== false && $cached !== null) {
                return $cached === '__EMPTY__' ? false : $cached;
            }
        }
        
        try {
            self::$stats['misses']++;
            $value = $fetcher();
            
            if ($value) {
                // 添加地址哈希到缓存数据中
                if ($addressData !== null && is_array($value)) {
                    $value['__address_hash__'] = self::hashAddress($addressData);
                }
                
                $ttl = self::PRINT_DATA_TTL + rand(-3600, 3600); // ±1小时随机
                Cache::set($key, $value, $ttl);
                
                self::$memoryCache[$key] = $value;
                self::$stats['sets']++;
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                \app\common\service\PrintLogger::success('京东打印', '获取并缓存成功', [
                    'waybill_no' => $waybillNo,
                    'ttl' => $ttl . 's',
                    'elapsed_ms' => $elapsed
                ]);
                self::logPerf('print_data', 'MISS_SET', microtime(true) - $startTime);
                $cacheHit = false;
            } else {
                Cache::set($key, '__EMPTY__', self::EMPTY_TTL);
                \app\common\service\PrintLogger::warning('京东打印', '获取失败，缓存空值', [
                    'waybill_no' => $waybillNo,
                    'ttl' => self::EMPTY_TTL . 's'
                ]);
                self::logPerf('print_data', 'MISS_EMPTY', microtime(true) - $startTime);
                $cacheHit = false;
            }
            
            return $value;
            
        } finally {
            if ($lockAcquired) {
                self::releaseLock($lockKey);
            }
        }
    }
    
    /**
     * 获取打印机列表（带双层缓存）
     * 
     * @param string $appKey 应用密钥
     * @param callable $fetcher 数据获取函数
     * @return array|false
     */
    public static function getPrinterList($appKey, callable $fetcher)
    {
        $key = "jd_printers_" . md5($appKey);
        $startTime = microtime(true);
        
        self::writeDebugLog("获取打印机列表: appKey={$appKey}, key={$key}");
        
        // L1: 内存缓存
        if (isset(self::$memoryCache[$key])) {
            self::$stats['l1_hits']++;
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);
            self::writeDebugLog("✅ L1 缓存命中 ({$elapsed}ms)");
            self::logPerf('printer_list', 'L1_HIT', microtime(true) - $startTime);
            return self::$memoryCache[$key];
        }
        
        self::writeDebugLog("L1 缓存未命中，查询 L2...");
        
        // L2: 文件缓存
        $cached = Cache::get($key);
        if ($cached !== false && $cached !== null) {
            if ($cached === '__EMPTY__') {
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                self::writeDebugLog("⚠️ L2 缓存为空值标记 ({$elapsed}ms)");
                self::logPerf('printer_list', 'L2_EMPTY', microtime(true) - $startTime);
                return false;
            }
            
            self::$memoryCache[$key] = $cached;
            self::$stats['l2_hits']++;
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);
            self::writeDebugLog("✅ L2 缓存命中，回填 L1 ({$elapsed}ms)");
            self::logPerf('printer_list', 'L2_HIT', microtime(true) - $startTime);
            return $cached;
        }
        
        self::writeDebugLog("L2 缓存未命中，尝试获取互斥锁...");
        
        // 缓存未命中
        $lockKey = "{$key}_lock";
        $lockAcquired = self::acquireLock($lockKey, 10);
        
        if (!$lockAcquired) {
            self::writeDebugLog("⚠️ 未获取到锁，等待其他进程...");
            usleep(100000);
            $cached = Cache::get($key);
            if ($cached !== false && $cached !== null) {
                self::writeDebugLog("✅ 从其他进程获取到缓存");
                return $cached === '__EMPTY__' ? false : $cached;
            }
        }
        
        self::writeDebugLog("🔓 获取到互斥锁，调用数据源...");
        
        try {
            self::$stats['misses']++;
            $value = $fetcher();
            
            if ($value) {
                $ttl = self::PRINTER_LIST_TTL + rand(-300, 300);
                Cache::set($key, $value, $ttl);
                self::$memoryCache[$key] = $value;
                self::$stats['sets']++;
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                self::writeDebugLog("✅ 数据源获取成功，缓存 TTL={$ttl}s ({$elapsed}ms)");
                self::logPerf('printer_list', 'MISS_SET', microtime(true) - $startTime);
            } else {
                Cache::set($key, '__EMPTY__', self::EMPTY_TTL);
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                self::writeDebugLog("⚠️ 数据源返回空值，缓存空值防穿透 ({$elapsed}ms)");
                self::logPerf('printer_list', 'MISS_EMPTY', microtime(true) - $startTime);
            }
            
            return $value;
            
        } finally {
            if ($lockAcquired) {
                self::releaseLock($lockKey);
                self::writeDebugLog("🔒 释放互斥锁");
            }
        }
    }
    
    /**
     * 获取互斥锁
     */
    private static function acquireLock($key, $timeout = 10)
    {
        $lockFile = RUNTIME_PATH . 'lock' . DS . $key . '.lock';
        $lockDir = dirname($lockFile);
        
        if (!is_dir($lockDir)) {
            mkdir($lockDir, 0755, true);
        }
        
        $fp = fopen($lockFile, 'w+');
        if (!$fp) {
            return false;
        }
        
        $acquired = flock($fp, LOCK_EX | LOCK_NB);
        if ($acquired) {
            // 存储文件句柄供后续释放
            self::$memoryCache["_lock_{$key}"] = $fp;
            return true;
        }
        
        fclose($fp);
        return false;
    }
    
    /**
     * 释放互斥锁
     */
    private static function releaseLock($key)
    {
        $lockKey = "_lock_{$key}";
        if (isset(self::$memoryCache[$lockKey])) {
            $fp = self::$memoryCache[$lockKey];
            flock($fp, LOCK_UN);
            fclose($fp);
            unset(self::$memoryCache[$lockKey]);
            
            // 删除锁文件
            $lockFile = RUNTIME_PATH . 'lock' . DS . $key . '.lock';
            if (file_exists($lockFile)) {
                @unlink($lockFile);
            }
        }
    }
    
    /**
     * 清除缓存
     */
    public static function clear($type, $identifier)
    {
        if ($type === 'token') {
            $key = "jd_token_" . md5($identifier);
        } elseif ($type === 'print') {
            $key = "jd_print_" . md5($identifier);
        } else {
            $key = "jd_printers_" . md5($identifier);
        }
        
        Cache::rm($key);
        unset(self::$memoryCache[$key]);
        
        \app\common\service\PrintLogger::info('京东缓存', '缓存已清除', [
            'type' => $type,
            'identifier' => $identifier
        ]);
    }
    
    /**
     * 批量清除缓存
     */
    public static function clearBatch($type, $identifiers)
    {
        $count = 0;
        foreach ($identifiers as $identifier) {
            self::clear($type, $identifier);
            $count++;
        }
        
        \app\common\service\PrintLogger::success('京东缓存', '批量清除完成', [
            'type' => $type,
            'count' => $count
        ]);
        
        return $count;
    }
    
    /**
     * 预热缓存
     */
    public static function warmup($waybillNos, $dataFetcher)
    {
        $startTime = microtime(true);
        $success = 0;
        $failed = 0;
        
        foreach ($waybillNos as $waybillNo) {
            try {
                $data = $dataFetcher($waybillNo);
                if ($data) {
                    $key = "jd_print_" . md5($waybillNo);
                    Cache::set($key, $data, self::PRINT_DATA_TTL);
                    $success++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
                \app\common\service\PrintLogger::error('京东缓存', '预热失败', [
                    'waybill_no' => $waybillNo,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $elapsed = round((microtime(true) - $startTime) * 1000, 2);
        
        \app\common\service\PrintLogger::success('京东缓存', '预热完成', [
            'success' => $success,
            'failed' => $failed,
            'elapsed_ms' => $elapsed
        ]);
        
        return [
            'success' => $success,
            'failed' => $failed,
            'elapsed_ms' => $elapsed
        ];
    }
    
    /**
     * 获取性能统计
     */
    public static function getStats()
    {
        $total = self::$stats['l1_hits'] + self::$stats['l2_hits'] + self::$stats['misses'];
        
        return [
            'l1_hits' => self::$stats['l1_hits'],
            'l2_hits' => self::$stats['l2_hits'],
            'misses' => self::$stats['misses'],
            'sets' => self::$stats['sets'],
            'total_requests' => $total,
            'hit_rate' => $total > 0 ? round((self::$stats['l1_hits'] + self::$stats['l2_hits']) / $total * 100, 2) : 0
        ];
    }
    
    /**
     * 重置统计
     */
    public static function resetStats()
    {
        self::$stats = [
            'l1_hits' => 0,
            'l2_hits' => 0,
            'misses' => 0,
            'sets' => 0
        ];
    }
    
    /**
     * 记录性能日志
     */
    private static function logPerf($type, $event, $elapsed)
    {
        $ms = round($elapsed * 1000, 2);
        
        // 只记录慢查询
        if ($ms > 100) {
            Log::warning("JD Cache slow: {$type} {$event} took {$ms}ms");
        }
    }
    
    /**
     * 计算地址哈希值（用于检测地址变更）
     * 
     * @param array $addressData 地址数据
     * @return string 地址哈希值
     */
    private static function hashAddress($addressData)
    {
        if (!is_array($addressData)) {
            return '';
        }
        
        // 提取关键地址字段
        $addressKey = [
            isset($addressData['name']) ? $addressData['name'] : '',
            isset($addressData['phone']) ? $addressData['phone'] : '',
            isset($addressData['province']) ? $addressData['province'] : '',
            isset($addressData['city']) ? $addressData['city'] : '',
            isset($addressData['region']) ? $addressData['region'] : '',
            isset($addressData['detail']) ? $addressData['detail'] : '',
        ];
        
        return md5(json_encode($addressKey));
    }
    
    /**
     * 写入调试日志（保留用于详细调试，但主要日志使用 PrintLogger）
     */
    private static function writeDebugLog($message)
    {
        $logDir = LOG_PATH . 'jd' . DS;
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . date('Ymd') . '.log';
        $timestamp = date('Y-m-d H:i:s.') . substr(microtime(), 2, 3);
        $logMessage = "[{$timestamp}] [JdCache] {$message}\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}
