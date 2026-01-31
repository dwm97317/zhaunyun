<?php
namespace app\common\service;

use think\Cache;

/**
 * 顺丰打印缓存服务
 */
class SfPrintCache
{
    // AccessToken 缓存时间（秒）- 顺丰 token 有效期 2 小时
    const TOKEN_TTL = 7000; // 1小时55分钟，留5分钟余量
    
    // ParsedData 缓存时间（秒）- 面单数据可以缓存更久
    const PARSED_DATA_TTL = 86400; // 24小时
    
    /**
     * 获取 AccessToken（带缓存）
     */
    public static function getAccessToken($partnerID, $callable)
    {
        $cacheKey = "sf_token_{$partnerID}";
        
        // 尝试从缓存获取
        $token = Cache::get($cacheKey);
        if ($token) {
            return $token;
        }
        
        // 缓存未命中，调用接口
        $token = call_user_func($callable);
        if ($token) {
            Cache::set($cacheKey, $token, self::TOKEN_TTL);
        }
        
        return $token;
    }
    
    /**
     * 获取 ParsedData（带缓存）
     */
    public static function getParsedData($waybillNo, $callable)
    {
        $cacheKey = "sf_parsed_{$waybillNo}";
        
        // 尝试从缓存获取
        $data = Cache::get($cacheKey);
        if ($data) {
            return $data;
        }
        
        // 缓存未命中，调用接口
        $data = call_user_func($callable);
        if ($data) {
            Cache::set($cacheKey, $data, self::PARSED_DATA_TTL);
        }
        
        return $data;
    }
    
    /**
     * 清除指定运单号的缓存
     */
    public static function clearParsedData($waybillNo)
    {
        $cacheKey = "sf_parsed_{$waybillNo}";
        Cache::rm($cacheKey);
    }
    
    /**
     * 清除 AccessToken 缓存
     */
    public static function clearToken($partnerID)
    {
        $cacheKey = "sf_token_{$partnerID}";
        Cache::rm($cacheKey);
    }
}
