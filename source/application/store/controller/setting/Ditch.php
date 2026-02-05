<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Ditch as DitchModel;
use app\store\model\DitchNumber as DitchNumberModel;
use think\Db;
use think\Cache;
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
        
        $model = new DitchModel;
        $this->refreshSenderSchema($model);

        if (!$this->request->isAjax()) {
            return $this->fetch('add',compact('track'));
        }
        // 新增记录
        $data = $this->postData('ditch');
        if (!isset($data['ditch_type']) || !in_array((int)$data['ditch_type'], [1, 2, 3, 4, 5], true)) {
            $data['ditch_type'] = 1;
        }

        // Decode HTML entities for JSON fields (Fixing ThinkPHP global filter issue)
        foreach (['push_config_json', 'sender_json', 'product_json', 'jd_multibox_config', 'jd_print_config'] as $jsonField) {
            if (isset($data[$jsonField]) && is_string($data[$jsonField])) {
                // First decode
                $decoded = htmlspecialchars_decode($data[$jsonField], ENT_QUOTES);
                // Double check if it needs another decode (sometimes data is double encoded)
                if (strpos($decoded, '&quot;') !== false) {
                     $decoded = htmlspecialchars_decode($decoded, ENT_QUOTES);
                }
                $data[$jsonField] = $decoded;
            }
        }

        // Remove jd_multibox_enabled and jd_print_name fields (already handled by hidden fields)
        unset($data['jd_multibox_enabled'], $data['jd_print_order_type'], $data['jd_custom_temp_url'], $data['jd_print_name']);

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
        
        // Ensure schema is fresh even for GET requests to ensure form renders correctly
        $model = new DitchModel;
        $this->refreshSenderSchema($model);
        
        $model = DitchModel::detail($ditch_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model','track'));
        }
        // 更新记录
        $data = $this->postData('express');
        if (isset($data['ditch_type']) && !in_array((int)$data['ditch_type'], [1, 2, 3, 4, 5], true)) {
            $data['ditch_type'] = 1;
        }
        
        // Decode HTML entities for JSON fields (Fixing ThinkPHP global filter issue)
        foreach (['push_config_json', 'sender_json', 'product_json', 'sf_print_options', 'jd_multibox_config', 'jd_print_config'] as $jsonField) {
            if (isset($data[$jsonField]) && is_string($data[$jsonField])) {
                // First decode
                $decoded = htmlspecialchars_decode($data[$jsonField], ENT_QUOTES);
                // Double check if it needs another decode (sometimes data is double encoded)
                if (strpos($decoded, '&quot;') !== false) {
                     $decoded = htmlspecialchars_decode($decoded, ENT_QUOTES);
                }
                $data[$jsonField] = $decoded;
            }
        }

        // Remove jd_multibox_enabled and jd_print_name fields (already handled by hidden fields)
        unset($data['jd_multibox_enabled'], $data['jd_print_order_type'], $data['jd_custom_temp_url'], $data['jd_print_name']);

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
     * 若 ditch 表无 account_id / account_password / sf_express_type / product_json 列，则从 $data 中移除，避免保存报错
     * @param DitchModel $model
     * @param array      $data
     */
    protected function stripAccountFieldsIfMissing($model, &$data)
    {
        try {
            $table = $model->getTable();
            
            // 检查 account_id 和 account_password 字段
            $row = Db::query("SHOW COLUMNS FROM `{$table}` LIKE 'account_id'");
            if (empty($row)) {
                unset($data['account_id'], $data['account_password']);
            }
            
            // 检查 sf_express_type 字段（顺丰快递产品类型）
            $sfRow = Db::query("SHOW COLUMNS FROM `{$table}` LIKE 'sf_express_type'");
            if (empty($sfRow)) {
                unset($data['sf_express_type']);
            }
            
            // 检查 product_json 字段（渠道产品配置）
            $productRow = Db::query("SHOW COLUMNS FROM `{$table}` LIKE 'product_json'");
            if (empty($productRow)) {
                unset($data['product_json']);
            }

            // 检查 push_config_json 字段 (快递管家功能升级)
            // 字段已通过脚本确认存在，不再执行容易误判的自动检测逻辑，防止意外 unset
            // $pushRow = Db::query("SHOW COLUMNS FROM `{$table}` LIKE 'push_config_json'");
            // if (empty($pushRow)) { ... }
        } catch (\Throwable $e) {
            // 发生异常时，对每个字段进行按需确认，避免全部 unset
            $fields = ['account_id', 'account_password', 'sf_express_type', 'product_json', 'shop_key', 'print_url'];
            foreach ($fields as $field) {
                try {
                    Db::query("SELECT `{$field}` FROM `{$table}` LIMIT 1");
                } catch (\Exception $e3) {
                    unset($data[$field]);
                }
            }
        }
    }

    protected function refreshSenderSchema($model)
    {
        try {
            $table = $model->getTable();
            $row = Db::query("SHOW COLUMNS FROM `{$table}` LIKE 'sender_json'");
            $row2 = Db::query("SHOW COLUMNS FROM `{$table}` LIKE 'push_config_json'");
            if (!empty($row) || !empty($row2)) {
                $runtimePath = ROOT_PATH . 'runtime';
                $schemaPath = $runtimePath . '/schema';
                if (is_dir($schemaPath)) {
                    $files = glob($schemaPath . '/*');
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            @unlink($file);
                        }
                    }
                }
                try {
                    Cache::clear();
                } catch (\Exception $e) {}
            }
        } catch (\Throwable $e) {}
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
