<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Currency as CurrencyModel;


/**
 * 货币设置
 * Class Currency
 * @package app\store\controller\setting
 */
class Currency extends Controller
{


    /**
     * 用户列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){
        $list = (new CurrencyModel())->getListAll();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 新增价格
     */
    public function add(){
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        $model = new CurrencyModel();
        if ($model->add($this->postData('currency'))) {
            return $this->renderSuccess('添加成功', url('setting.currency/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }


    /**
     * 编辑货架
     */
    public function edit($id){
        // 模板详情
        $model = (new CurrencyModel())->details($id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('currency'))) {
            return $this->renderSuccess('更新成功', url('setting.currency/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 复用货币
     */
    public function copy(){
        $model = (new CurrencyModel());
        $data = $model->select();
        if(count($data)>0){
           return $this->renderError($model->getError() ?: '请先删除现有的国家再复用'); 
        }
        if ($model->copy()) {
            return $this->renderSuccess('复用成功');
        }
        return $this->renderError($model->getError() ?: '复用失败');
    }


   /**
     * 删除模板
     * @param $delivery_id
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete($id)
    {
        $model = (new CurrencyModel())->deletes($id);
        if (!$model) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }


}
