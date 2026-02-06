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
    
        // 文件导入上传页面
    public function import(){
        return $this->fetch('import');
    }
    
     // 文件导入处理
    public function importdo(){
       $post = request()->param();
       //物流模板设置
        //   '商品序列号':'barcode',
        //   '品名':'goods_name',
        //   '品名（英文名称）':'goods_name_en',
        //   '品名（日文名称）':'goods_name_jp',
        //   '品牌':'brand',
        //   '规格型号':'spec',
        //   '原产国（地区）':'origin_region',
        //   '商品净重(kg)':'net_weight',
        //   '商品毛重(kg)':'gross_weight',
        //   '商品申报单价/人民币':'one_price',
        //   '商品申报单价/日元':'one_price_jp',

       $model = new BarcodeModel;
       $field = [
          'barcode','goods_name','goods_name_en','goods_name_jp','brand','spec','origin_region','net_weight','gross_weight','price','price_jp'
       ];
       $require_field = [
          'barcode','goods_name_jp'
       ];
       // 检查excel 是否符合格式
       $postKey = array_keys($post);
       if (!$this->checkRequireField($require_field,$post)){
           $post['error'] = "必填参数未填,请检查文档数据";
           return $this->renderError('导入错误','',$post);
       }
       
       $countries = [101 => '阿富汗',102 => '巴林',103 => '孟加拉国',  104 => '不丹',  105 => '文莱',  106 => '缅甸',  107 => '柬埔寨',  108 => '塞浦路斯',  109 => '朝鲜',  110 => '中国香港',  111 => '印度',  112 => '印度尼西亚',  113 => '伊朗',  114 => '伊拉克',  115 => '以色列',  116 => '日本',  117 => '约旦',  118 => '科威特',  119 => '老挝',  120 => '黎巴嫩',  121 => '中国澳门',  122 => '马来西亚',  123 => '马尔代夫',  124 => '蒙古',  125 => '尼泊尔',  126 => '阿曼',  127 => '巴基斯坦',  128 => '巴勒斯坦',  129 => '菲律宾',  130 => '卡塔尔',  131 => '沙特阿拉伯',  132 => '新加坡',  133 => '韩国',  134 => '斯里兰卡',  135 => '叙利亚',  136 => '泰国',  137 => '土耳其',  138 => '阿联酋',  139 => '也门共和国',  141 => '越南',  142 => '中国',  143 => '台澎金马关税区',  144 => '东帝汶'];  
     
       $postData = [
            'barcode' =>$post['barcode'],
            'goods_name' => isset($post['goods_name'])?$post['goods_name']:'',
            'goods_name_en' => isset($post['goods_name_en'])?$post['goods_name_en']:'',
            'goods_name_jp' => isset($post['goods_name_jp'])?$post['goods_name_jp']:'',
            'brand' =>isset($post['brand'])?$post['brand']:'',
            'spec'=>isset($post['spec'])?$post['spec']:'',
            'origin_region' => $post['origin_region']?$countries[$post['origin_region']]:'',
            'net_weight'=>isset($post['net_weight'])?$post['net_weight']:'',
            'gross_weight'=> isset($post['gross_weight'])?$post['gross_weight']:'',
            'price'=> isset($post['price'])?$post['price']:'',
            'price_jp'=>isset($post['price_jp'])?$post['price_jp']:'',
       ];
       if(!$model::useGlobalScope(false)->insert($postData)){
           $post['error'] = "导入失败";
           return $this->renderError('导入错误','',$post);
       }
       
       $post['success'] = '导入成功';
       return $this->renderSuccess('导入成功','',$post);
    }
    
        // 检查必要字段是否为空
    public function checkRequireField($field,$post){
        foreach ($field as $v){
            if (!isset($post[$v]) || empty($post[$v])){
                 return false;
            }
        }          
        return true;
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