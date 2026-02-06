<?php

namespace app\store\controller\user;

use app\store\controller\Controller;
use app\store\model\recharge\Order as OrderModel;
use app\store\model\Setting;

/**
 * 余额记录
 * Class Recharge
 * @package app\store\controller\user
 */
class Recharge extends Controller
{
    /**
     * 充值记录
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function order()
    {
        $model = new OrderModel;
        return $this->fetch('order', [
            // 充值记录列表
            'list' => $model->getList($this->request->param()),
            // 属性集
            'attributes' => $model::getAttributes(),
             // 设置
            'set' =>  Setting::detail('store')['values']['usercode_mode']
        ]);
    }
    
        /**
     * 充值订单详情
     * @param $id 订单ID
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function detail($id)
    {
        // 获取订单详情，加载关联数据
        $detail = OrderModel::with(['user', 'orderPlan'])->find($id);
        if (empty($detail)) {
            $this->error('订单不存在');
        }
        
        // 获取设置
        $set = Setting::detail('store')['values']['usercode_mode'];
        
        return $this->fetch('detail', [
            'detail' => $detail,
            'set' => $set,
            'attributes' => OrderModel::getAttributes(),
        ]);
    }

}