<?php

namespace app\task\behavior\user;

use think\Cache;
use app\task\model\User as UserModel;
use app\task\model\user\Birthday as BirthdayModel;
use app\common\model\UserCoupon as UserCouponModel;
use app\task\model\Setting;
use app\task\model\SiteSms;
use app\store\model\Coupon;

class Birthday
{
    /* @var GradeModel $model */
    private $model;

    /**
     * 执行函数
     * @param $model
     * @return bool
     * @throws \Exception
     */
    public function run($model)
    {
        if (!$model instanceof BirthdayModel) {
            return new BirthdayModel and false;
        }
        $this->model = $model;
        if (!$model::$wxapp_id) {
            return false;
        }
    
        $cacheKey = "__task_space__[user/Birthday]__{$model::$wxapp_id}";
        if (!Cache::has($cacheKey)) {
            // 设置用户的生日提醒
            $this->setUserBirthday();

            //对当天生日的会员赠送优惠券
            $this->sendVipUserCoupon($model::$wxapp_id);
            Cache::set($cacheKey, time(), 60 * 10);
        }
        return true;
    }
    
    /**
     * 赠送优惠券
     * @return array|bool|false
     * @throws \Exception
     */
    private function sendVipUserCoupon($wxapp_id)
    {
        // 用户模型
        $UserModel = new UserModel;
        $SiteSms = new SiteSms;
        $userList = $UserModel->getVipBirthdayUserList();
    
        if ($userList->isEmpty()) {
            return false;
        }
        
        $coupon_id = Setting::getItem('grade',$wxapp_id)['birthdaycoupon'];
        $store = Setting::getItem('store',$wxapp_id);
        $data = [];
        foreach ($userList as $user) {
           $result =  (new BirthdayModel())->where('user_id',$user['user_id'])->find();
           if(!empty($result) && $result['is_send_coupon']==0){
               $this->receive($user,$coupon_id);
               $result->save(['is_send_coupon'=>1]);
           }
           if(!empty($result) && $result['is_send']==0){
               $result->save(['is_send'=>1]);
               $SiteSms->add(['user_id'=>$user['user_id'],'content'=>'今天是您的生日，'.$store['title'].'祝您生日快乐，健康生活每一天！','wxapp_id'=>$wxapp_id]);
           }
           
        }
        // 批量修改会员的等级
        return true;
    }
    
        /**
     * 领取优惠券
     * @param $user
     * @param $coupon_id
     * @return bool|false|int
     * @throws \think\exception\DbException
     */
    public function receive($user, $coupon_id)
    {
        // 获取优惠券信息
        $coupon = Coupon::detail($coupon_id);
        // 验证优惠券是否可领取
        if (!$this->checkReceive($user, $coupon)) {
            return false;
        }
        // 添加领取记录
        return $this->add($user, $coupon);
    }
    
    /**
     * 验证优惠券是否可领取
     * @param $user
     * @param Coupon $coupon
     * @return bool
     */
    public function checkReceive($user, $coupon)
    {
        if (!$coupon) {
            $this->error = '优惠券不存在';
            return false;
        }
        if (!$coupon->checkReceive()) {
            $this->error = $coupon->getError();
            return false;
        }
        // 验证是否已领取
        $userCouponIds = $this->getUserCouponIds($user);
        if (in_array($coupon['coupon_id'], $userCouponIds)) {
            $this->error = '该优惠券已领取';
            return false;
        }
        return true;
    }
    
        /**
     * 添加领取记录
     * @param $user
     * @param Coupon $coupon
     * @return bool
     */
    private function add($user, $coupon)
    {
        // 计算有效期
        if ($coupon['expire_type'] == 10) {
            $start_time = time();
            $end_time = $start_time + ($coupon['expire_day'] * 86400);
        } else {
            $start_time = $coupon['start_time']['value'];
            $end_time = $coupon['end_time']['value'];
        }
        // 整理领取记录
        $data = [
            'coupon_id' => $coupon['coupon_id'],
            'name' => $coupon['name'],
            'color' => $coupon['color']['value'],
            'coupon_type' => $coupon['coupon_type']['value'],
            'reduce_price' => $coupon['reduce_price'],
            'discount' => $coupon->getData('discount'),
            'min_price' => $coupon['min_price'],
            'expire_type' => $coupon['expire_type'],
            'expire_day' => $coupon['expire_day'],
            'start_time' => $start_time,
            'end_time' => $end_time,
            'apply_range' => $coupon['apply_range'],
            'user_id' => $user['user_id'],
            'wxapp_id' => $user['wxapp_id']
        ];
        $userCouponModel = new UserCouponModel();
        return $userCouponModel->transaction(function () use ($data, $coupon,$userCouponModel) {
            // 添加领取记录
            $status = $userCouponModel->save($data);
            if ($status) {
                // 更新优惠券领取数量
                $coupon->setIncReceiveNum();
            }
            return $status;
        });
    }
    
    /**
     * 获取用户优惠券ID集
     * @param $user_id
     * @return array
     */
    public function getUserCouponIds($user_id)
    {
        return (new UserCouponModel())->where('user_id', '=', $user_id)->column('coupon_id');
    }
    

    /**
     * 赠送优惠券
     * @return array|bool|false
     * @throws \Exception
     */
    private function setUserBirthday()
    {
        // 用户模型
        $UserModel = new UserModel;
        // 获取所有等级
        
        $list = $UserModel->getBirthdayList();
        //   dump($list->toArray());die;
        if ($list->isEmpty()) {
            return false;
        }
        
        //遍历生日在一个月内的所有会员
        $data = [];
        foreach ($list as $key => $value) {
            $result = (new BirthdayModel())->where('user_id',$value['user_id'])->where('is_send',0)->find();
            if(empty($result)){
                $data = [
                    'user_id' => $value['user_id'],
                    'birthday' => $value['birthday'],
                    'is_send' => 0,
                    'wxapp_id'=>$value['wxapp_id'],
                    'create_time'=>time()
                ];
                (new BirthdayModel())->insert($data);
            }
        }
        return true;
    }

}