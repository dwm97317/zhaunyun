<?php

namespace app\api\controller;

use app\api\controller\Controller;
use app\api\model\Blindbox as BlindboxModel;
use app\api\model\BlindboxWall as BlindboxWallModel;
use app\common\model\Setting;
use app\api\model\Setting as SettingModel;
/**
 * 盲盒计划管理
 * Class Blindbox
 * @package app\store\controller\market
 */
class Blindbox extends Controller
{
    /**
     * 盲盒分享墙
     * @param $coupon_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function blindboxwall()
    {
        $BlindboxWallModel = new BlindboxWallModel();
        $list = $BlindboxWallModel->getList();
        return $this->renderSuccess(compact('list'));
    }
    

    /**
     * 发放设置
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function setting()
    {
        if (!$this->request->isAjax()) {
            $list = $this->model->getList();
            $values = SettingModel::getItem('blindbox');
            return $this->fetch('setting', ['values' => $values,'list'=>$list]);
        }
        $model = new SettingModel;
        if ($model->edit('blindbox', $this->postData('blindbox'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

}