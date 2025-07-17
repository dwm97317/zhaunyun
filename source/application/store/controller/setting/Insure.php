<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Insure as InsureModel;

/**
 * 保险
 * Class Insure
 * @package app\store\controller\setting
 */
class Insure extends Controller
{
    /**
     * 保险列表
     * @return mixed
     */
    public function index()
    {
        $model = new InsureModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除保险
     * @param $category_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        $model = InsureModel::get($id);
        if (!$model->remove($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加保险
     * @return array|mixed
     */
    public function add()
    {
        $model = new InsureModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add', compact('list'));
        }
        // 新增记录
        if ($model->add($this->postData('insure'))) {
            return $this->renderSuccess('添加成功', url('setting.insure/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑保险
     * @param $category_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        // 模板详情
        $model = InsureModel::get($id, ['image']);
        if (!$this->request->isAjax()) {
            $list = $model->getList();
            return $this->fetch('edit', compact('model', 'list'));
        }
        // 更新记录
        if ($model->edit($this->postData('insure'))) {
            return $this->renderSuccess('更新成功', url('setting.insure/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
