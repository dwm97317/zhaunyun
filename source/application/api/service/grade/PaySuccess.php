<?php

namespace app\api\service\grade;

use app\api\service\Basics;
use app\api\model\User as UserModel;
use app\api\model\user\UserGradeOrder as UserGradeOrderModel;
use app\api\model\user\GradeLog as GradeLogModel;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use app\common\enum\recharge\order\PayStatus as PayStatusEnum;
use app\common\enum\user\grade\log\ChangeType as ChangeTypeEnum;


class PaySuccess extends Basics
{
    // 订单模型
    public $model;

    // 当前用户信息
    private $user;

    /**
     * 构造函数
     * PaySuccess constructor.
     * @param $orderNo
     * @throws \think\exception\DbException
     */
    public function __construct($orderNo)
    {
        // 实例化订单模型
        $this->model = (new UserGradeOrderModel())->getPayDetail($orderNo);
        $this->wxappId = $this->model['wxapp_id'];
        // 获取用户信息
        $this->user = UserModel::detail($this->model['user_id']);
    }

    /**
     * 获取订单详情
     * @return UserGradeOrderModel|null
     */
    public function getOrderInfo()
    {
        return $this->model;
    }

    /**
     * 订单支付成功业务处理
     * @param int $payType 支付类型
     * @param array $payData 支付回调数据
     * @return bool
     */
    public function onPaySuccess($payType, $payData)
    {
        return $this->model->transaction(function () use ($payType, $payData) {
            // 更新订单状态
            if ($payType == PayTypeEnum::WECHAT) {
                $this->model->save([
                        'is_pay' => 1,
                        'pay_time' => time(),
                        'transaction_id' => $payData['out_trade_no']
                ]);
            }
            
            if ($payType == PayTypeEnum::OMIPAY) {
                $this->model->save([
                        'is_pay' => 1,
                        'pay_time' => time(),
                        'transaction_id' => $payData['out_order_no']
                ]);
            }
            
            // 修改用户VIP等级，累计时长
            if($this->user['grade_id']==0 || $this->user['grade_time']==0){
                $this->user->setInc('grade_time', (time() + $this->model['grade']['effective_time']*86400));
            }else{
                $this->user->setInc('grade_time',$this->model['grade']['effective_time']*86400);
            }
            $this->user->save(['grade_id'=>$this->model['grade_id']]);
             // 记录会员等级变更记录
            $GradeLogModel = new GradeLogModel;
            $grade = [
                'user_id'=>$this->user['user_id'],  
                'old_grade_id'=>$this->user['grade_id'], 
                'new_grade_id'=>$this->model['grade_id'],
                'wxapp_id' => $this->wxappId,
                'change_type' => ChangeTypeEnum::PAY_UPGRADE,
            ];
            $GradeLogModel->save($grade);
            return true;
        });
    }

}