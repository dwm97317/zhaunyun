<?php

namespace app\common\model\market;

use think\Request;
use app\common\model\BaseModel;
/**
 * 盲盒计划
 * Class Blindbox
 * @package app\common\model\market
 */
class Blindbox extends BaseModel
{
    protected $name = 'blindbox';
  
    /**
     * 盲盒计划详情
     * @param $id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        return static::get($id);
    }
    
    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User');
    }
    
    /**
     * 关联优惠券表
     * @return \think\model\relation\BelongsTo
     */
    public function coupon()
    {
        return $this->belongsTo('app\store\model\Coupon');
    }


}
