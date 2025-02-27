<?php

namespace app\api\controller;

use app\api\model\user\Grade as GradeModel;


/**
 * 会员VIP列表
 * Class nav
 * @package app\api\controller
 */
class Grade extends Controller
{
    /**
     * 导航列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function list()
    {
        $list = GradeModel::getUsableList();
        return $this->renderSuccess(compact('list'));
    }
    
     /**
     * 确认购买vip等级
     * @param null $planId
     * @param int $customMoney
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function buyGradesubmit($gradeId = null, $customMoney = 0)
    {
        // 用户信息
        $userInfo = $this->getUser();
        $paytype = $this->postData('paytype')[0];  //支付类型
        $client = $this->postData('client')[0];
        // 生成充值订单
        $model = new OrderModel;
        if (!$model->createOrder($userInfo, $gradeId, $customMoney)) {
            return $this->renderError($model->getError() ?: '充值失败');
        }
        switch ($paytype) {
            case '20':
                // 构建微信支付
                $payment = PaymentService::wechat(
                    $userInfo,
                    $model['order_id'],
                    $model['order_no'],
                    $model['pay_price'],
                    OrderTypeEnum::RECHARGE
                );
                break;
                
            case '30':
                //构建汉特支付
                if($model['pay_price'] < 0.1){
                    return $this->renderError('充值金额不能低于0.1');;
                }
                $payment = PaymentService::Hantepay(
                    $userInfo,
                    $model['order_id'],
                    $model['order_no'],
                    $model['pay_price'],
                    OrderTypeEnum::RECHARGE
                );
                break;
            
            default:
                // code...
                break;
        }
        $data = [
            'order_no'=> $model['order_no'], 
            'pay_price'=> $model['pay_price'],   
            'pay_time'=> getTime(),
            'wxapp_id'=>$userInfo['wxapp_id'],
            'member_id'=>$userInfo['user_id']
        ];
        Message::send('package.balancepay',$data);   
        // 充值状态提醒
        $message = ['success' => '充值成功', 'error' => '订单未支付'];
        return $this->renderSuccess(compact('payment', 'message'), $message);
    }
    

}