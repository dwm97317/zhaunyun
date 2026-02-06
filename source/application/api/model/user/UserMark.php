<?php

namespace app\api\model\user;

use app\common\model\user\UserMark as UserMarkModel;
use app\common\model\Setting;

/**
 * 用户唛头模型
 * Class user_mark
 * @package app\common\model\user
 */
class UserMark extends UserMarkModel
{
    public function getList($user_id)
    {
        return $this->where('user_id',$user_id)->order("create_time desc")->select();
    }
    
    public function getUsermarkList($param)
    {
        $setting = Setting::getItem('store',self::$wxapp_id);
        if($setting['usercode_mode']['is_show']==0){
             isset($param['keyword']) && $this->where('m.user_id|m.mark','like','%'.$param['keyword'].'%');
        }
        if($setting['usercode_mode']['is_show']==1){
             isset($param['keyword']) && $this->where('u.user_code|m.mark','like','%'.$param['keyword'].'%');
        }
       
        return $this->alias('m')
        ->field('u.user_id,u.user_code,u.nickName,m.*')
        ->join('user u','u.user_id = m.user_id','left')
        ->order("m.create_time desc")
        ->paginate(30,false,[
            'query'=>\request()->request()
        ]);
    }
}