<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Ditch as DitchModel;
use app\store\model\DitchNumber as DitchNumberModel;
use think\Db;
/**
 * 渠道商
 * Class Express
 * @package app\store\controller\setting
 */
class Ditch extends Controller
{
    /**
     * 渠道商列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new DitchModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }
    
        
    /**
     * 复用
     */
    public function copy(){
        $model = (new DitchModel());
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
        $model = DitchModel::detail($express_id);
        if (!$model->remove()) {
            $error = $model->getError() ?: '删除失败';
            return $this->renderError($error);
        }
        return $this->renderSuccess('删除成功');
    }
    
    /**
     * 删除物流公司
     * @param $express_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function deleteditch($id)
    {
        $model = DitchNumberModel::detail($id);
        if (!$model->delete()) {
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
        $track = getFileData('assets/track.json');
        // $track = array_slice($track,0,20000,true);
        if (!$this->request->isAjax()) {
            return $this->fetch('add',compact('track'));
        }
        // 新增记录
        $model = new DitchModel;
        $data = $this->postData('ditch');
        if (!isset($data['ditch_type']) || !in_array((int)$data['ditch_type'], [1, 2], true)) {
            $data['ditch_type'] = 1;
        }
        $this->stripAccountFieldsIfMissing($model, $data);
        if ($model->add($data)) {
            return $this->renderSuccess('添加成功', url('setting.ditch/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑物流公司
     * @param $express_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($ditch_id)
    {
        // 模板详情
        $track = getFileData('assets/track.json');
        $model = DitchModel::detail($ditch_id);
        // dump($model);die;
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model','track'));
        }
        // 更新记录
        $data = $this->postData('express');
        if (isset($data['ditch_type']) && !in_array((int)$data['ditch_type'], [1, 2], true)) {
            $data['ditch_type'] = 1;
        }
        $this->stripAccountFieldsIfMissing($model, $data);
        if ($model->edit($data)) {
            return $this->renderSuccess('更新成功', url('setting.ditch/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    
     /**
     * 查看渠道商的可用单号
     * @param $express_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function ditchnumber($ditch_id)
    {
        // 模板详情
        $DitchNumberModel = new DitchNumberModel();
        $list = $DitchNumberModel->getList();
        return $this->fetch('ditchnumber',compact("list"));
    }
    
    public function getdicthNumberList(){
        $post = $this->request->param();
        $DitchNumberModel = new DitchNumberModel();
        $list = $DitchNumberModel->where('status',0)->where('ditch_id',$post['ditch_no'])->select();
        return $this->renderSuccess('更新成功','',$list);
    }
 
    public function import(){
        return $this->fetch('import');
    }

    /**
     * 物流公司编码表
     * @return mixed
     */
    public function company()
    {
        $track = getFileData('assets/track.json');
        return $this->fetch('company',compact('track'));
    }
    
    /**
     * 若 ditch 表无 account_id / account_password 列，则从 $data 中移除，避免保存报错
     * @param DitchModel $model
     * @param array      $data
     */
    protected function stripAccountFieldsIfMissing($model, &$data)
    {
        try {
            $table = $model->getTable();
            $row = Db::query("SHOW COLUMNS FROM `{$table}` LIKE 'account_id'");
            if (empty($row)) {
                unset($data['account_id'], $data['account_password']);
            }
        } catch (\Throwable $e) {
            unset($data['account_id'], $data['account_password']);
        }
    }

     // 文件导入处理
    public function importdo(){
       $post = request()->param();
       $DitchNumberModel = new DitchNumberModel();
       //物流模板设置
       $ditch = DitchModel::detail($post['ditch_id']);
       if(empty($ditch)){
           $post['err'] = '渠道商不存在';
           return $this->renderError('渠道商不存在','',$post);
       }
       //查询单号是否存在
       $ditno = $DitchNumberModel->where('ditch_number',$post['ditch_number'])->find();
       if (!empty($ditno)){
           $post['err'] = '单号'.$post['ditch_number'].'已存在';
           return $this->renderError('导入失败','',$post);
       }
       $DitchNumberModel->save([
              'ditch_number'=>$post['ditch_number'],
              'ditch_id'=>$post['ditch_id'],
              'status'=>0,
              'wxapp_id'=>$ditch['wxapp_id']
        ]);
       $post['success'] = '导入成功';
       return $this->renderSuccess('导入成功','',$post);
    }

}