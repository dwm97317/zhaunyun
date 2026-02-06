<?php

namespace app\store\controller\user;

use app\store\controller\Controller;
use app\store\model\user\Grade as GradeModel;
use app\store\model\Setting as SettingModel;
use app\store\model\Coupon as CouponModel;
use app\store\model\user\UserGradeOrder as UserGradeOrderModel;
/**
 * 会员等级
 * Class Grade
 * @package app\store\controller\user
 */
class Grade extends Controller
{
    
    
    
    /**
     * 会员等级列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new GradeModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }
    
    
    /**
     * 会员订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function order()
    {
        // 获取筛选参数
        $param = $this->request->param();
        $grade_id = $param['grade_id'] ?? '';
        $nickName = $param['nickName'] ?? '';
        $user_id = $param['user_id'] ?? '';
        $user_code = $param['user_code'] ?? '';
        $start_date = $param['start_date'] ?? '';
        $end_date = $param['end_date'] ?? '';
        $effect_start_date = $param['effect_start_date'] ?? '';
        $effect_end_date = $param['effect_end_date'] ?? '';
        $is_expired = $param['is_expired'] ?? '';
        
        $model = new UserGradeOrderModel;
        $list = $model->getList($grade_id, $nickName, $user_id, $user_code, $start_date, $end_date, $effect_start_date, $effect_end_date, $is_expired);
        // dump($list->toArray());die;
        $storesetting = SettingModel::getItem('store');
        
        // 获取会员等级列表用于下拉选择
        $gradeList = GradeModel::getUsableList();
        
        return $this->fetch('order', compact('list','storesetting','gradeList'));
    }
    
    
    /**
     * 会员等级设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function setting()
    {
        if (!$this->request->isAjax()) {
            $list = (new CouponModel())->getAllList();
            $vars['values'] = SettingModel::getItem("grade");
            $values = $vars['values'];
            return $this->fetch("setting",compact('values','list'));
        }
        $model = new SettingModel;
        if ($model->edit("grade", $this->postData("grade"))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }
    
   

    /**
     * 添加等级
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function add()
    {
        $model = new GradeModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($model->add($this->postData('grade'))) {
            return $this->renderSuccess('添加成功', url('user.grade/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑会员等级
     * @param $grade_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($grade_id)
    {
        // 会员等级详情
        $model = GradeModel::detail($grade_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 新增记录
        if ($model->edit($this->postData('grade'))) {
            return $this->renderSuccess('更新成功', url('user.grade/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除会员等级
     * @param $grade_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($grade_id)
    {
        // 会员等级详情
        $model = GradeModel::detail($grade_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}