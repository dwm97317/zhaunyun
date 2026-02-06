<?php

namespace app\api\model;

use app\common\model\market\Blindbox as BlindboxModel;
/**
 * 盲盒计划管理
 * Class Blindbox
 * @package app\store\controller\market
 */
class Blindbox extends BlindboxModel
{
    /**
     * 获取盲盒列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with(['coupon'])
            ->where('is_delete', '=', 0)
            ->where('wxapp_id', '=',self::$wxapp_id)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
    }
    
    /**
     * 获取所有盲盒列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAllList()
    {
         return $this->with(['coupon'])
            ->where('is_delete', '=', 0)
            ->where('goods_inventory', '>', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])->select();
    }
}