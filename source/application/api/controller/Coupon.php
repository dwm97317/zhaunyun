<?php

namespace app\api\controller;

use app\api\model\Coupon as CouponModel;
use app\api\model\UserCoupon;

/**
 * 优惠券中心
 * Class Coupon
 * @package app\api\controller
 */
class Coupon extends Controller
{
    /**
     * 优惠券列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        $model = new CouponModel;
        $list = $model->getList($this->getUser(false));
        return $this->renderSuccess(compact('list'));
    }
    
    public function pointlists()
    {
        $model = new CouponModel;
        $list = $model->getpointList($this->getUser(false));
        return $this->renderSuccess(compact('list'));
    }
    
    
    public function couponDetail(){
       $model = new UserCoupon;
       $coupon_id = input('coupon_id');
       $couponData = $model->where('user_coupon_id',$coupon_id)->find();
       return $this->renderSuccess(compact('couponData'));
    }
    
    public function enablecoupon(){
        $free = input('total_free');
        $model = new UserCoupon;
        $list = $model->getUserCouponList($this->getUser(false)['user_id'],$free);
        return $this->renderSuccess(compact('list'));
    }

}