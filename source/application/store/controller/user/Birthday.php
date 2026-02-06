<?php

namespace app\store\controller\user;

use app\store\controller\Controller;
use app\store\model\user\Birthday as BirthdayModel;
use app\store\model\Setting as SettingModel;
/**
 * 会员生日
 * Class Birthday
 * @package app\store\controller\user
 */
class Birthday extends Controller
{
    /**
     * 会员生日列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new BirthdayModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }
    
    /**
     * 添加等级
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function add()
    {
        $model = new BirthdayModel;
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
        $model = BirthdayModel::detail($grade_id);
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
    public function delete($id)
    {
        // 会员等级详情
        $model = BirthdayModel::detail($id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}