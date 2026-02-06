<?php

namespace app\api\model;

use app\common\model\market\BlindboxLog as BlindboxLogModel;
/**
 * 盲盒计划管理
 * Class Blindbox
 * @package app\store\controller\market
 */
class BlindboxLog extends BlindboxLogModel
{
    /**
     * 获取盲盒列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this
            ->where('wxapp_id', '=',self::$wxapp_id)
            ->order(['create_time' => 'desc'])
            ->select();
    }
    
        /**
     * 添加领取记录
     * @param $user
     * @param Coupon $coupon
     * @return bool
     */
    public function add($data)
    {
        return $this->transaction(function () use ($data) {
            // 整理领取记录
            $data['wxapp_id'] = self::$wxapp_id;
            return $this->allowField(true)->save($data);
        });
    } 
    

}