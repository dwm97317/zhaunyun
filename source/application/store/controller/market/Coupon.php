<?php

namespace app\store\controller\market;

use app\store\controller\Controller;
use app\store\model\Coupon as CouponModel;
use app\store\model\UserCoupon as UserCouponModel;
use app\common\model\Setting;
use app\store\model\Setting as SettingModel;
/**
 * 优惠券管理
 * Class Coupon
 * @package app\store\controller\market
 */
class Coupon extends Controller
{
    /* @var CouponModel $model */
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
        $this->model = new CouponModel;
    }

    /**
     * 优惠券列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $list = $this->model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加优惠券
     * @return array|mixed
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($this->model->add($this->postData('coupon'))) {
            return $this->renderSuccess('添加成功', url('market.coupon/index'));
        }
        return $this->renderError($this->model->getError() ?: '添加失败');
    }
    
        
    /**
     * 删除用户的优惠券
     * @param $coupon_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function deleteusercoupon($coupon_id)
    {
        // 优惠券详情
        $model = UserCouponModel::detail($coupon_id);
        // 更新记录
        if ($model->setDelete()) {
            return $this->renderSuccess('删除成功', url('market.coupon/receive'));
        }
        return $this->renderError($model->getError() ?: '删除成功');
    }

    /**
     * 更新优惠券
     * @param $coupon_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($coupon_id)
    {
        // 优惠券详情
        $model = CouponModel::detail($coupon_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('coupon'))) {
            return $this->renderSuccess('更新成功', url('market.coupon/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除优惠券
     * @param $coupon_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function delete($coupon_id)
    {
        // 优惠券详情
        $model = CouponModel::detail($coupon_id);
        // 更新记录
        if ($model->setDelete()) {
            return $this->renderSuccess('删除成功', url('market.coupon/index'));
        }
        return $this->renderError($model->getError() ?: '删除成功');
    }

    /**
     * 领取记录
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function receive()
    {
        $query = $this->request->param();
        $model = new UserCouponModel;
        $set = Setting::detail('store')['values'];
        $list = $model->getList($query);
        return $this->fetch('receive', compact('list','set'));
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
            $values = SettingModel::getItem('coupon');
            //   dump($values);die;
            return $this->fetch('setting', ['values' => $values,'list'=>$list]);
        }
        $model = new SettingModel;
        if ($model->edit('coupon', $this->postData('coupon'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

}