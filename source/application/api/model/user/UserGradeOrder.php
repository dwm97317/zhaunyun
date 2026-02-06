<?php

namespace app\api\model\user;

use app\common\model\user\UserGradeOrder as UserGradeOrderModel;

use app\common\service\Order as OrderService;
/**
 * 用户VIP
 * Class UserGradeOrder
 * @package app\api\model\user
 */
class UserGradeOrder extends UserGradeOrderModel
{
     /**
     * 创建vip订单
     * @param \app\api\model\User $user 当前用户信息
     * @param int $planId 套餐id
     * @param double $customMoney 自定义充值金额
     * @return bool|false|int
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public function createOrder($user, $gradeId = null,$payType)
    {
        // 验证用户输入
        if (!$this->validateForm($gradeId)) {
            $this->error = $this->error ?: '数据验证错误';
            return false;
        }
        // 获取订单数据
        $data = $this->getOrderData($user, $payType, $gradeId);
        return $this->saveOrder($data);
    }
    
    /**
     * 表单验证
     * @param $rechargeType
     * @param $planId
     * @param $customMoney
     * @return bool
     */
    private function validateForm($gradeId)
    {
        if (empty($gradeId)) {
            $this->error = '请选择VIP等级';
            return false;
        }
        return true;
    }


    /**
     * 保存订单记录
     * @param $data
     * @return bool|false|int
     */
    private function saveOrder($data)
    {
        // 写入订单记录
        $this->save($data);
        return true;
    }

    /**
     * 生成VIP订单
     * @param $user
     * @param $rechargeType
     * @param $gradeId
     * @return array
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function getOrderData($user, $payType, $gradeId)
    {
        // 订单信息
        $data = [
            'user_id' => $user['user_id'],
            'order_no' => 'UG' . OrderService::createOrderNo(),
            'grade_id' => $gradeId,
            'pay_type' => $payType,
            'is_pay' => 0,
            'wxapp_id' => self::$wxapp_id,
        ];
        return $data;
    }

    /**
     * 获取订单详情(待付款状态)
     * @param $orderNo
     * @return self|null
     * @throws \think\exception\DbException
     */
    public function getPayDetail($orderNo)
    {
        return $this->with(['grade'])->where(['order_no' => $orderNo, 'is_pay' => 0])->find();
    }
}