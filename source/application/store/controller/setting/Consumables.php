<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Consumables as ConsumablesModel;
use app\store\model\ConsumablesLog as ConsumablesLogModel;

/**
 * 耗材管理
 * Class Consumables
 * @package app\store\controller\setting
 */
class Consumables extends Controller
{
    /**
     * 耗材管理列表
     * @return mixed
     */
    public function index()
    {
        $model = new ConsumablesModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }
    
    /**
     * 耗材管理列表
     * @return mixed
     */
    public function log($id)
    {
        $model = new ConsumablesLogModel;
        $list = $model->getList(['con_id'=>$id]);
        // dump($list->toARray());die;
        return $this->fetch('log', compact('list'));
    }

    /**
     * 耗材
     * @param $category_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        $model = ConsumablesModel::get($id);
        if (!$model->remove($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加耗材
     * @return array|mixed
     */
    public function add()
    {
        $model = new ConsumablesModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($model->add($this->postData('consumables'))) {
            return $this->renderSuccess('添加成功', url('setting.consumables/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑耗材
     * @param $category_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        // 模板详情
        $model = ConsumablesModel::get($id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('consumables'))) {
            return $this->renderSuccess('更新成功', url('setting.consumables/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
