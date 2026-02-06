<?php
namespace app\store\controller;

use think\Controller;
use think\Cache;
use app\common\service\SfCache;

/**
 * 缓存管理控制器
 */
class SfCacheAdmin extends Controller
{
    /**
     * 缓存统计仪表板
     */
    public function dashboard()
    {
        $stats = SfCache::getStats();
        
        $cacheInfo = [
            'file_cache_path' => CACHE_PATH,
            'file_cache_size' => $this->getDirSize(CACHE_PATH),
            'lock_path' => RUNTIME_PATH . 'lock',
            'lock_count' => $this->countFiles(RUNTIME_PATH . 'lock')
        ];
        
        return json([
            'cache_stats' => $stats,
            'cache_info' => $cacheInfo,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * 清除所有缓存
     */
    public function clearAll()
    {
        $before = $this->getDirSize(CACHE_PATH);
        
        Cache::clear();
        SfCache::resetStats();
        
        $after = $this->getDirSize(CACHE_PATH);
        
        return json([
            'success' => true,
            'freed_bytes' => $before - $after,
            'freed_mb' => round(($before - $after) / 1024 / 1024, 2),
            'message' => '所有缓存已清除'
        ]);
    }
    
    /**
     * 清除指定运单号的缓存
     */
    public function clearWaybill()
    {
        $waybillNo = input('waybill_no');
        
        if (empty($waybillNo)) {
            return json(['error' => '运单号不能为空']);
        }
        
        SfCache::clear('parsed', $waybillNo);
        
        return json([
            'success' => true,
            'waybill_no' => $waybillNo,
            'message' => '缓存已清除'
        ]);
    }
    
    /**
     * 清除 AccessToken 缓存
     */
    public function clearToken()
    {
        $partnerID = input('partner_id', 'THGJH89TNITE');
        
        SfCache::clear('token', $partnerID);
        
        return json([
            'success' => true,
            'partner_id' => $partnerID,
            'message' => 'Token 缓存已清除'
        ]);
    }
    
    /**
     * 缓存预热
     */
    public function warmup()
    {
        $waybillNos = input('waybill_nos', '');
        
        if (empty($waybillNos)) {
            return json(['error' => '运单号列表不能为空']);
        }
        
        $nos = explode(',', $waybillNos);
        
        // TODO: 实现预热逻辑
        
        return json([
            'success' => true,
            'count' => count($nos),
            'message' => '预热完成'
        ]);
    }
    
    /**
     * 获取目录大小
     */
    private function getDirSize($dir)
    {
        $size = 0;
        
        if (!is_dir($dir)) {
            return 0;
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
    
    /**
     * 统计文件数量
     */
    private function countFiles($dir)
    {
        if (!is_dir($dir)) {
            return 0;
        }
        
        $count = 0;
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $count++;
            }
        }
        
        return $count;
    }
}
