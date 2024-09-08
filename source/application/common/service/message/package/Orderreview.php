<?php

namespace app\common\service\message\package;

use app\common\service\message\Basics;
use app\common\model\Setting as SettingModel;
use app\common\model\User;
use app\common\model\store\Shop;
use app\common\enum\OrderType as OrderTypeEnum;

/**
 * 消息通知服务 [订单入库成功]
 * Class Payment
 * @package app\common\service\message\order
 */
class Orderreview extends Basics
{
    /**
     * 参数列表
     * @var array
     */
    protected $param = [];


    /**
     * 发送消息通知
     * @param array $param
     * @return mixed|void
     * @throws \think\Exception
     */
    public function send($param)
    {
        // 记录参数
        $this->param = $param;
        // 微信模板消息通知用户
        $this->onSendWxTplMsg();
    }

    /**
     * 微信模板消息通知用户
     * @return bool|mixed
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    private function onSendWxTplMsg()
    {
        // 获取订阅消息配置
        $template = SettingModel::getItem('tplMsg', $this->param['wxapp_id'])['orderreview'];
        $noticesetting = SettingModel::getItem('notice');
        $storesetting = SettingModel::getItem('store');
        if ($template['is_enable']==0) {
            return false;
        }
        if (empty($template['template_id'])) {
            return false;
        }
        //当入库不存在用户id，则不发送提醒；
        if(!isset($this->param['member_id'])){
             return false;
        }
        
        if(empty($this->getGzhOpenidByUserId($this->param['member_id']))){
            return false;
        }
        //判断是否采用H5方式；H5+小程序；

        if ($storesetting['client']['mode']==10) {
            return  $this->sendWxTplMsgForH5($this->param['wxapp_id'], [
            'touser' => $this->getGzhOpenidByUserId($this->param['member_id']),
            'template_id' => $template['template_id'],
            'data' => [
                $template['keywords'][0] => ['value' => $this->param['order_no']],
                $template['keywords'][1] => ['value' => $this->param['member_name']],
                $template['keywords'][2] => ['value' => $this->param['pay_time']],
            ]
        ]);
        }else{
            return  $this->sendWxTplMsg($this->param['wxapp_id'], [
            'touser' => $this->getGzhOpenidByUserId($this->param['member_id']),
            'template_id' => $template['template_id'],
            'url' => "pages/index/index",
            'miniprogram'=>[
                'appid' => '',
                'pagepath'=> "pages/index/index"
            ],
            'data' => [
                $template['keywords'][0] => ['value' => $this->param['order_no']],
                $template['keywords'][1] => ['value' => $this->param['member_name']],
                $template['keywords'][2] => ['value' => $this->param['pay_time']],
            ]
            ]);
        }
    }
   
    
    public function getOpenidByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('open_id');
    }
    
    public function getNickNameByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('nickName');
    }
    
    public function getUnionidByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('union_id');
    }
    
    public function getGzhOpenidByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('gzh_openid');
    }
    
    public function getShopByShopId($shop_id){
        return (new Shop())->where(['shop_id'=>$shop_id])->value('shop_name');
    }
}