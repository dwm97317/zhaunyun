<?php

namespace app\store\model\user;

use app\common\model\user\UserGradeOrder as UserGradeOrderModel;

use app\store\model\User as UserModel;

/**
 * 用户会员等级模型
 * Class Grade
 * @package app\store\model\user
 */
class UserGradeOrder extends UserGradeOrderModel
{
    /**
     * 获取列表记录
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with(['user','grade'])
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }
}