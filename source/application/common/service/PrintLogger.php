<?php

namespace app\common\service;

use think\Log;

/**
 * 打印系统统一日志服务
 * 
 * 提供统一的日志格式和级别管理
 */
class PrintLogger
{
    // 日志前缀
    const PREFIX_CACHE = '💾';
    const PREFIX_API = '🌐';
    const PREFIX_PRINT = '🖨️';
    const PREFIX_SUCCESS = '✅';
    const PREFIX_ERROR = '❌';
    const PREFIX_WARNING = '⚠️';
    const PREFIX_INFO = 'ℹ️';
    
    /**
     * 记录缓存命中日志
     */
    public static function cacheHit($channel, $key, $data = [])
    {
        $message = self::PREFIX_CACHE . " {$channel} - 缓存命中";
        $context = array_merge(['cache_key' => $key], $data);
        Log::info($message . ': ' . json_encode($context, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * 记录缓存未命中日志
     */
    public static function cacheMiss($channel, $key, $data = [])
    {
        $message = self::PREFIX_CACHE . " {$channel} - 缓存未命中";
        $context = array_merge(['cache_key' => $key], $data);
        Log::info($message . ': ' . json_encode($context, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * 记录 API 请求日志
     */
    public static function apiRequest($channel, $url, $data = [])
    {
        $message = self::PREFIX_API . " {$channel} - API请求";
        $context = array_merge(['url' => $url], $data);
        Log::info($message . ': ' . json_encode($context, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * 记录 API 响应日志
     */
    public static function apiResponse($channel, $success, $data = [])
    {
        $prefix = $success ? self::PREFIX_SUCCESS : self::PREFIX_ERROR;
        $message = "{$prefix} {$channel} - API响应";
        Log::info($message . ': ' . json_encode($data, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * 记录打印任务日志
     */
    public static function printTask($channel, $action, $data = [])
    {
        $message = self::PREFIX_PRINT . " {$channel} - {$action}";
        Log::info($message . ': ' . json_encode($data, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * 记录成功日志
     */
    public static function success($channel, $message, $data = [])
    {
        $logMessage = self::PREFIX_SUCCESS . " {$channel} - {$message}";
        if (!empty($data)) {
            $logMessage .= ': ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        Log::info($logMessage);
    }
    
    /**
     * 记录错误日志
     */
    public static function error($channel, $message, $data = [])
    {
        $logMessage = self::PREFIX_ERROR . " {$channel} - {$message}";
        if (!empty($data)) {
            $logMessage .= ': ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        Log::error($logMessage);
    }
    
    /**
     * 记录警告日志
     */
    public static function warning($channel, $message, $data = [])
    {
        $logMessage = self::PREFIX_WARNING . " {$channel} - {$message}";
        if (!empty($data)) {
            $logMessage .= ': ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        Log::info($logMessage);
    }
    
    /**
     * 记录信息日志
     */
    public static function info($channel, $message, $data = [])
    {
        $logMessage = self::PREFIX_INFO . " {$channel} - {$message}";
        if (!empty($data)) {
            $logMessage .= ': ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        Log::info($logMessage);
    }
}
