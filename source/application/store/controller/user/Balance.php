<?php

namespace app\store\controller\user;

use app\store\controller\Controller;
use app\store\model\user\BalanceLog as BalanceLogModel;
use app\store\model\Setting;

/**
 * 余额明细
 * Class Balance
 * @package app\store\controller\user
 */
class Balance extends Controller
{
    /**
     * 余额明细
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function log()
    {
        $model = new BalanceLogModel;
        return $this->fetch('log', [
            // 充值记录列表
            'list' => $model->getList($this->request->param()),
            // 属性集
            'attributes' => $model::getAttributes(),
            // 设置
            'set' =>  Setting::detail('store')['values']['usercode_mode']
        ]);
    }

}