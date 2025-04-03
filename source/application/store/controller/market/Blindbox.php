<?php

namespace app\store\controller\market;

use app\store\controller\Controller;
use app\store\model\market\Blindbox as BlindboxModel;
use app\store\model\market\BlindboxWall as BlindboxWallModel;
use app\common\model\Setting;
use app\store\model\Setting as SettingModel;
/**
 * 盲盒计划管理
 * Class Blindbox
 * @package app\store\controller\market
 */
class Blindbox extends Controller
{
    /* @var BlindboxModel $model */
    private $model;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new BlindboxModel;
    }

    /**
     * 盲盒列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $list = $this->model->getList();
        return $this->fetch('index', compact('list'));
    }
        
    /**
     * 删除盲盒
     * @param $id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        // 优惠券详情
        $model = $this->model::detail($id);
        // 更新记录
        if ($model->setDelete()) {
            return $this->renderSuccess('删除成功', url('market.blindbox/index'));
        }
        return $this->renderError($model->getError() ?: '删除成功');
    }
    
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
        return $this->fetch('wall', compact('list'));
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