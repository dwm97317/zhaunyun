<?php

namespace app\api\controller\user;

use app\api\controller\Controller;

use app\api\model\user\UserGradeOrder as UserGradeOrderModel;
use app\api\model\Setting as SettingModel;
use app\api\model\user\Grade;
use app\api\service\Payment as PaymentService;
use app\common\enum\OrderType as OrderTypeEnum;

/**
 * 用户订单管理
 * Class Order
 * @package app\api\controller\user
 */
class UserGradeOrder extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->user = $this->getUser();   // 用户信息
    }

    /**
     * 订单详情信息
     * @param $order_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail($order_id)
    {
        // 订单详情
        return UserGradeOrderModel::detail($order_id,[]);
    }

    
    /**
     * 确认充值新版本
     * @param null $gradeId
     * @param int $customMoney
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function createOrder($gradeId = null)
    {
        // 用户信息
        $userInfo = $this->getUser();
        $payType = $this->postData('paytype')[0];  //支付类型
        $client = $this->postData('client')[0];
        $gradeDetail = Grade::detail($gradeId);
        // dump($gradeDetail);die;
        // 生成充值订单
        $model = new UserGradeOrderModel;
        if (!$model->createOrder($userInfo, $gradeId, $payType)) {
            return $this->renderError($model->getError() ?: '充值失败');
        }
        switch ($payType) {
            case '20':
                // 构建微信支付
                $payment = PaymentService::wechat(
                    $userInfo,
                    $model['order_id'],
                    $model['order_no'],
                    $gradeDetail['price'],
                    OrderTypeEnum::GRADE
                );
                break;
                
            case '30':
                //构建汉特支付
                if($gradeDetail['price'] < 0.1){
                    return $this->renderError('VIP金额不能低于0.1');;
                }
                $payment = PaymentService::Hantepay(
                    $userInfo,
                    $model['order_id'],
                    $model['order_no'],
                    $gradeDetail['price'],
                    OrderTypeEnum::GRADE
                );
                break;
            
            default:
                // code...
                break;
        }
        // $data = [
        //     'order_no'=> $model['order_no'], 
        //     'pay_price'=> $model['pay_price'],   
        //     'pay_time'=> getTime(),
        //     'wxapp_id'=>$userInfo['wxapp_id'],
        //     'member_id'=>$userInfo['user_id']
        // ];
        // Message::send('package.balancepay',$data);   
        // 充值状态提醒
        $message = ['success' => '充值成功', 'error' => '订单未支付'];
        return $this->renderSuccess(compact('payment', 'message'), $message);
    }



}
