<?php
namespace app\common\service;

use think\Cache;
use think\Log;

/**
 * 顺丰打印缓存服务 - 企业级实现
 * 
 * 特性：
 * - L1 (内存) + L2 (文件) 双层缓存
 * - 缓存穿透防护（空值缓存）
 * - 缓存击穿防护（互斥锁）
 * - 缓存雪崩防护（随机TTL）
 * - 性能监控
 */
class SfCache
{
    // L1 缓存（进程内存）
    private static $memoryCache = [];
    
    // TTL 配置
    const TOKEN_TTL = 7000;           // AccessToken: 1小时55分钟
    const PARSED_DATA_TTL = 86400;    // ParsedData: 24小时
    const EMPTY_TTL = 300;            // 空值缓存: 5分钟（防穿透）
    
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
     * @param string $partnerID 合作方ID
     * @param callable $fetcher 数据获取函数
     * @return string|false
     */
    public static function getAccessToken($partnerID, callable $fetcher)
    {
        $key = "sf_token_{$partnerID}";
        $startTime = microtime(true);
        
        // L1: 内存缓存
        if (isset(self::$memoryCache[$key])) {
            self::$stats['l1_hits']++;
            self::logPerf('token', 'L1_HIT', microtime(true) - $startTime);
            return self::$memoryCache[$key];
        }
        
        // L2: 文件缓存
        $cached = Cache::get($key);
        if ($cached !== false && $cached !== null) {
            self::$memoryCache[$key] = $cached; // 回填 L1
            self::$stats['l2_hits']++;
            self::logPerf('token', 'L2_HIT', microtime(true) - $startTime);
            return $cached;
        }
        
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
                $ttl = self::TOKEN_TTL + rand(-300, 300); // 随机TTL防雪崩
                Cache::set($key, $value, $ttl);
                self::$memoryCache[$key] = $value;
                self::$stats['sets']++;
                self::logPerf('token', 'MISS_SET', microtime(true) - $startTime);
            } else {
                // 获取失败，缓存空值防穿透
                Cache::set($key, '__EMPTY__', self::EMPTY_TTL);
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
     * 获取 ParsedData（带双层缓存）
     * 
     * @param string $waybillNo 运单号
     * @param callable $fetcher 数据获取函数
     * @return array|false
     */
    public static function getParsedData($waybillNo, callable $fetcher)
    {
        $key = "sf_parsed_{$waybillNo}";
        $startTime = microtime(true);
        
        // L1: 内存缓存
        if (isset(self::$memoryCache[$key])) {
            self::$stats['l1_hits']++;
            self::logPerf('parsed', 'L1_HIT', microtime(true) - $startTime);
            return self::$memoryCache[$key];
        }
        
        // L2: 文件缓存
        $cached = Cache::get($key);
        if ($cached !== false && $cached !== null) {
            // 检查是否是空值标记
            if ($cached === '__EMPTY__') {
                self::logPerf('parsed', 'L2_EMPTY', microtime(true) - $startTime);
                return false;
            }
            
            self::$memoryCache[$key] = $cached;
            self::$stats['l2_hits']++;
            self::logPerf('parsed', 'L2_HIT', microtime(true) - $startTime);
            return $cached;
        }
        
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
                $ttl = self::PARSED_DATA_TTL + rand(-3600, 3600); // ±1小时随机
                Cache::set($key, $value, $ttl);
                self::$memoryCache[$key] = $value;
                self::$stats['sets']++;
                self::logPerf('parsed', 'MISS_SET', microtime(true) - $startTime);
            } else {
                Cache::set($key, '__EMPTY__', self::EMPTY_TTL);
                self::logPerf('parsed', 'MISS_EMPTY', microtime(true) - $startTime);
            }
            
            return $value;
            
        } finally {
            if ($lockAcquired) {
                self::releaseLock($lockKey);
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
            $key = "sf_token_{$identifier}";
        } else {
            $key = "sf_parsed_{$identifier}";
        }
        
        Cache::rm($key);
        unset(self::$memoryCache[$key]);
        
        Log::info("Cache cleared: {$key}");
    }
    
    /**
     * 预热缓存
     */
    public static function warmup($waybillNos, $tokenFetcher, $dataFetcher)
    {
        $startTime = microtime(true);
        $success = 0;
        $failed = 0;
        
        foreach ($waybillNos as $waybillNo) {
            try {
                $data = $dataFetcher($waybillNo);
                if ($data) {
                    $key = "sf_parsed_{$waybillNo}";
                    Cache::set($key, $data, self::PARSED_DATA_TTL);
                    $success++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error("Warmup failed for {$waybillNo}: " . $e->getMessage());
            }
        }
        
        $elapsed = round((microtime(true) - $startTime) * 1000, 2);
        
        Log::info("Cache warmup completed: {$success} success, {$failed} failed, {$elapsed}ms");
        
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
            Log::warning("Cache slow: {$type} {$event} took {$ms}ms");
        }
    }
}
