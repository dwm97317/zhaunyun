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
}