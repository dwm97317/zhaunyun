<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Barcode as BarcodeModel;

/**
 * 物流公司
 * Class Express
 * @package app\store\controller\setting
 */
class Barcode extends Controller
{
    /**
     * 物流公司列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new BarcodeModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }
    
        
    /**
     * 复用国家
     */
    public function copy(){
        $model = (new BarcodeModel());
        $data = $model->select();
        if(count($data)>0){
           return $this->renderError($model->getError() ?: '请先删除现有的物流公司再复用'); 
        }
        if ($model->copy()) {
            return $this->renderSuccess('复用成功');
        }
        return $this->renderError($model->getError() ?: '复用失败');
    }


    /**
     * 删除物流公司
     * @param $express_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($express_id)
    {
        $model = BarcodeModel::detail($express_id);
        if (!$model->remove()) {
            $error = $model->getError() ?: '删除失败';
            return $this->renderError($error);
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加物流公司
     * @return array|mixed
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        $model = new BarcodeModel;
        if ($model->add($this->postData('barcode'))) {
            return $this->renderSuccess('添加成功', url('setting.barcode/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑物流公司
     * @param $express_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($sku_id)
    {
        $model = BarcodeModel::detail($sku_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('barcode'))) {
            return $this->renderSuccess('更新成功', url('setting.barcode/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}