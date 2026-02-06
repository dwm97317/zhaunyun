<?php
namespace app\store\controller;

use app\store\model\WebMenu as WebMenuModel;
use think\Session;
use think\View;

/**
 * 微信公众号自定义菜单
 * Class WebMenu
 * @package app\store\controller
 */
class WebMenu extends Controller
{
    // 定义菜单类型映射
    protected $typeMap = [
        10 => '单页',
        20 => '列表', 
        30 => '关于我们',
        40 => '仓库地址',
        50 => '自定义链接'
    ];
    
    /**
     * 菜单列表
     */
    public function index()
    {
        $model = new WebMenuModel;
        $list = $model->getTree($this->getWxappId());
        return $this->fetch('web_menu/index', [
            'list' => $list,
            'typeMap' => $this->typeMap
        ]);
    }
    
    /**
     * 显示编辑表单 (GET请求)
     */
    public function edit($id)
    {
        $model = WebMenuModel::get($id);
        if (empty($model)) {
            return $this->renderError('菜单不存在');
        }
    
        // 返回JSON数据供模态框使用
        return $this->renderSuccess('','', [
            'id' => $model->id,
            'parent_id' => $model->parent_id,
            'name' => $model->name,
            'type' => $model->type,
            'link_id' => $model->link_id,
            'sort' => $model->sort,
            'typeMap' => $this->typeMap
        ]);
    }
    
    /**
     * 处理菜单更新 (POST请求)
     */
    public function update()
    {
        $data = $this->postData();
        $model = WebMenuModel::get($data['id']);
        if (empty($model)) {
            return $this->renderError('菜单不存在');
        }
    
        if ($model->edit($data)) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    
    /**
     * 删除菜单
     */
    public function delete($id)
    {
        $model = WebMenuModel::get($id);
        if (empty($model)) {
            return $this->renderError('菜单不存在');
        }
        
        if ($model->remove()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

    /**
     * 添加菜单
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $data = $this->postData();
            $model = new WebMenuModel;
            if ($model->add($data)) {
                return $this->renderSuccess('添加成功');
            }
            return $this->renderError($model->getError() ?: '添加失败');
        }
        
        // 获取父级菜单选项
        $parentOptions = WebMenuModel::getParentOptions($this->wxapp_id);
        return $this->fetch('add', [
            'parentOptions' => $parentOptions,
            'typeMap' => $this->typeMap
        ]);
    }
    
    /**
     * 更新菜单排序
     */
    public function sort()
    {
        $data = $this->postData();
    
        if (!isset($data['sort'])) {
            return $this->renderError('参数错误');
        }
        $model = new WebMenuModel;
        if ($model->updateSort($data['sort'])) {
            return $this->renderSuccess('排序更新成功');
        }
        return $this->renderError('排序更新失败');
    }
}