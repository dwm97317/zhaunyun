<?php

namespace app\store\controller;

use app\store\controller\Controller;
use app\common\service\DitchCache;

/**
 * 渠道配置缓存管理控制器
 * 提供缓存预热、清除、统计等管理功能
 */
class DitchCacheAdmin extends Controller
{
    /**
     * 缓存统计页面
     */
    public function index()
    {
        $stats = DitchCache::getStats();
        return $this->renderSuccess('获取成功', '', $stats);
    }
    
    /**
     * 预热所有渠道配置缓存
     */
    public function warmup()
    {
        $count = DitchCache::warmUp();
        return $this->renderSuccess("缓存预热成功，共缓存 {$count} 个渠道配置");
    }
    
    /**
     * 清除指定渠道配置缓存
     */
    public function clear()
    {
        $ditchId = $this->request->param('ditch_id');
        
        if (empty($ditchId)) {
            return $this->renderError('请指定渠道 ID');
        }
        
        $result = DitchCache::clearConfig($ditchId);
        
        if ($result) {
            return $this->renderSuccess('缓存清除成功');
        }
        
        return $this->renderError('缓存清除失败');
    }
    
    /**
     * 清除所有渠道配置缓存
     */
    public function clearAll()
    {
        $result = DitchCache::clearAll();
        
        if ($result) {
            return $this->renderSuccess('所有缓存清除成功');
        }
        
        return $this->renderError('缓存清除失败');
    }
}
