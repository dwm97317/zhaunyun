<?php

namespace app\common\service;

use app\store\model\Ditch as DitchModel;
use think\Cache;

/**
 * 渠道配置缓存服务
 * 用于优化推送到快递平台时的渠道配置查询性能
 */
class DitchCache
{
    // 缓存键前缀
    const CACHE_KEY_PREFIX = 'ditch_config_';
    
    // 缓存过期时间（秒）- 1小时
    const CACHE_EXPIRE = 3600;
    
    /**
     * 获取渠道配置（带缓存）
     * 
     * @param int $ditchId 渠道ID
     * @return array|null 渠道配置数组，不存在返回 null
     */
    public static function getConfig($ditchId)
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $ditchId;
        
        // 尝试从缓存读取
        $config = Cache::get($cacheKey);
        
        if ($config === false) {
            // 缓存未命中，查询数据库
            \app\common\service\PrintLogger::cacheMiss('渠道配置', $cacheKey, [
                'ditch_id' => $ditchId
            ]);
            
            $config = DitchModel::detail($ditchId);
            
            if ($config) {
                // 转换为数组（ThinkPHP 模型可能返回对象）
                $configArray = $config->toArray();
                
                // 写入缓存
                Cache::set($cacheKey, $configArray, self::CACHE_EXPIRE);
                
                \app\common\service\PrintLogger::success('渠道配置', '配置已缓存', [
                    'ditch_id' => $ditchId,
                    'ditch_name' => $configArray['ditch_name'] ?? '',
                    'ttl' => self::CACHE_EXPIRE . 's'
                ]);
                
                return $configArray;
            }
            
            \app\common\service\PrintLogger::error('渠道配置', '渠道不存在', [
                'ditch_id' => $ditchId
            ]);
            
            return null;
        }
        
        // 缓存命中
        \app\common\service\PrintLogger::cacheHit('渠道配置', $cacheKey, [
            'ditch_id' => $ditchId,
            'ditch_name' => $config['ditch_name'] ?? ''
        ]);
        
        return $config;
    }
    
    /**
     * 清除指定渠道配置缓存
     * 
     * @param int $ditchId 渠道ID
     * @return bool
     */
    public static function clearConfig($ditchId)
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $ditchId;
        $result = Cache::rm($cacheKey);
        
        if ($result) {
            PrintLogger::info('渠道配置', '缓存已清除', ['ditch_id' => $ditchId]);
        }
        
        return $result;
    }
    
    /**
     * 清除所有渠道配置缓存
     * 
     * @return bool
     */
    public static function clearAll()
    {
        // 获取所有渠道ID
        $ditchList = DitchModel::all();
        
        $success = true;
        $count = 0;
        foreach ($ditchList as $ditch) {
            if (self::clearConfig($ditch['ditch_id'])) {
                $count++;
            } else {
                $success = false;
            }
        }
        
        PrintLogger::info('渠道配置', '批量清除缓存', [
            'total' => count($ditchList),
            'cleared' => $count
        ]);
        
        return $success;
    }
    
    /**
     * 预热缓存 - 批量加载所有渠道配置到缓存
     * 
     * @return int 预热的渠道数量
     */
    public static function warmUp()
    {
        $ditchList = DitchModel::all();
        $count = 0;
        
        foreach ($ditchList as $ditch) {
            $ditchId = $ditch['ditch_id'];
            $cacheKey = self::CACHE_KEY_PREFIX . $ditchId;
            
            // 写入缓存
            Cache::set($cacheKey, $ditch->toArray(), self::CACHE_EXPIRE);
            $count++;
        }
        
        PrintLogger::success('渠道配置', '缓存预热完成', [
            'count' => $count,
            'ttl' => self::CACHE_EXPIRE . 's'
        ]);
        
        return $count;
    }
    
    /**
     * 获取缓存统计信息
     * 
     * @return array
     */
    public static function getStats()
    {
        $ditchList = DitchModel::all();
        $totalCount = count($ditchList);
        $cachedCount = 0;
        
        foreach ($ditchList as $ditch) {
            $cacheKey = self::CACHE_KEY_PREFIX . $ditch['ditch_id'];
            if (Cache::get($cacheKey) !== false) {
                $cachedCount++;
            }
        }
        
        $stats = [
            'total' => $totalCount,
            'cached' => $cachedCount,
            'hit_rate' => $totalCount > 0 ? round($cachedCount / $totalCount * 100, 2) : 0
        ];
        
        PrintLogger::info('渠道配置', '缓存统计', $stats);
        
        return $stats;
    }
}
