<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Countries as CountryModel;


/**
 * 线路设置
 * Class Delivery
 * @package app\store\controller\setting
 */
class Country extends Controller
{


    /**
     * 用户列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){
        $list = (new CountryModel())->getListAll();
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
        $model = new CountryModel();
        if ($model->add($this->postData('country'))) {
            return $this->renderSuccess('添加成功', url('setting.country/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }


    /**
     * 编辑货架
     */
    public function edit($id){
        // 模板详情
        $model = (new CountryModel())->details($id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('country'))) {
            return $this->renderSuccess('更新成功', url('setting.country/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    /**
     * 复用国家
     */
    public function copy(){
        $model = (new CountryModel());
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
        $model = (new CountryModel())->deletes($id);
        if (!$model) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 切换状态
     * @param $id
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function changeStatus($id)
    {
        $status = $this->request->param('status', 1);
        $model = (new CountryModel())->details($id);
        if (!$model) {
            return $this->renderError('记录不存在');
        }
        if ($model->save(['status' => $status])) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError('操作失败');
    }
    
    /**
     * 批量切换状态
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function batchChangeStatus()
    {
        $ids = $this->request->param('ids', '');
        $status = $this->request->param('status', 1);
        
        if (empty($ids)) {
            return $this->renderError('请选择要操作的国家');
        }
        
        $idsArr = explode(',', $ids);
        $idsArr = array_filter($idsArr);
        
        if (empty($idsArr)) {
            return $this->renderError('请选择要操作的国家');
        }
        
        $model = new CountryModel();
        $successCount = 0;
        foreach ($idsArr as $id) {
            $country = $model->details($id);
            if ($country && $country->save(['status' => $status])) {
                $successCount++;
            }
        }
        
        if ($successCount > 0) {
            return $this->renderSuccess('成功操作 ' . $successCount . ' 个国家');
        }
        
        return $this->renderError('操作失败');
    }

}
