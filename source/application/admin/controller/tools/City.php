<?php

namespace app\admin\controller\tools;

use app\admin\controller\Controller;
use app\admin\model\city\Region;

/**
 * 城市
 * Class User
 * @package app\store\controller
 */
class City extends Controller
{
    /**
     * 更新日志列表
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function citylist()
    {
        $param = $this->request->param();
        $Region = new Region;
        $list = $Region->getList($param);
        return $this->fetch('city/index', compact('list'));
    }

    /**
     * 查看下级城市
     * @return array|mixed
     */   
    public function childcity($id){
        $Region = new Region;
        $param['id'] = $id;
        $list = $Region->getChildList($param);
        return $this->fetch('city/index', compact('list','id'));
    }
    
    /**
     * 生成json格式
     * @return array|mixed
     */ 
    public function getjsoncity(){
        $Region = new Region;
        $param = $this->request->param();
        $list = $Region->getList($param);
        $li = json_encode($list);
        // $file_path = "assets/store/js";
        // if(!file_exists($file_path)){
        //         mkdir($file_path,0755,true);
        //     }
        $res = file_put_contents('assets/store/js/region_data.json', $li);
        return $this->renderSuccess('生成成功', url('tools.city/citylist'));
    }

    /**
     * 添加商品分类
     * @return array|mixed
     */
    public function add($id)
    {
        // dump($id);die;
        $model = new Region;
        if (!$this->request->isAjax()) {
            // 获取所有地区
            $list = $model->where('id',$id)->select();
            // dump($list);die;
            return $this->fetch('city/add', compact('list'));
        }
        // 新增记录
        if ($model->add($this->postData('category'))) {
            return $this->renderSuccess('添加成功', url('tools.city/citylist'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
        /**
     * 添加商品分类
     * @return array|mixed
     */
    public function addtop()
    {
        // dump($id);die;
        $model = new Region;
        if (!$this->request->isAjax()) {
            // 获取所有地区
            // $list = $model->where('id',$id)->select();
            // dump($list);die;
            return $this->fetch('city/add');
        }
        // 新增记录
        if ($model->add($this->postData('category'))) {
            return $this->renderSuccess('添加成功', url('tools.city/citylist'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
    
    /**
     * 编辑商品分类
     * @param $category_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        // 模板详情
        $model = Region::get($id);
        if (!$this->request->isAjax()) {
            // 获取所有地区
            $list = $model->getCacheTree();
            return $this->fetch('city/edit', compact('model', 'list'));
        }
        // 更新记录
        if ($model->edit($this->postData('category'))) {
            return $this->renderSuccess('更新成功', url('tools.city/citylist'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
}