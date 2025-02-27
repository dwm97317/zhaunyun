<?php

namespace app\api\controller\points;

use app\api\controller\Controller;
use app\api\model\user\PointsLog as PointsLogModel;

/**
 * 余额账单明细
 * Class Log
 * @package app\api\controller\balance
 */
class Log extends Controller
{
    /**
     * 积分明细列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $user = $this->getUser();
        $list = (new PointsLogModel)->getList($user['user_id']);
        return $this->renderSuccess(compact('list'));
    }
    
    public function lists()
    {
        $user = $this->getUser();
        $param = $this->request->param();
        $param['user_id'] = $user['user_id'];
        if($param['type']==0){
            unset($param['type']);
        }
        $list = (new PointsLogModel)->getPointsList($param);
        return $this->renderSuccess(compact('list'));
    }

}