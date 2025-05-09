<?php

namespace app\store\model;

use app\common\model\WebMenu as WebMenuModel;

/**
 * 网站自定义菜单
 * Class WebMenu
 * @package app\store\model
 */
class WebMenu extends WebMenuModel
{
   /**
     * 添加菜单
     */
    public function add($data)
    {
        // 设置默认值
        // dump();die;
        $data['wxapp_id'] = self::$wxapp_id;
        $data['create_time'] = time();
        $data['update_time'] = time();
        
        return $this->allowField(true)->save($data);
    }
    
    /**
     * 编辑菜单
     */
    public function edit($data)
    {
        // 不能设置自己为父级
        if ($data['parent_id'] == $this->id) {
            $this->error = '不能设置自己为父级菜单';
            return false;
        }
        
        $data['update_time'] = time();
        return $this->save($data);
    }
    
    /**
     * 删除菜单
     */
    public function remove()
    {
        // 检查是否有子菜单
        $hasChildren = self::where('parent_id', $this->id)->count();
        if ($hasChildren) {
            $this->error = '请先删除子菜单';
            return false;
        }
        
        return $this->delete();
    }
    
    
    /**
     * 获取父级菜单选项
     */
    public static function getParentOptions($wxapp_id)
    {
        $options = [0 => '作为一级菜单'];
        $menus = self::where('wxapp_id', $wxapp_id)
            ->where('parent_id', 0)
            ->order('sort', 'asc')
            ->column('name', 'id');
            
        return $options + $menus;
    }
    
    
    /**
     * 更新排序
     */
    public static function updateSort($sort)
    {
        foreach ($sort as $id => $value) {
            self::where('id', $value['id'])->update(['sort' => $value['sort']]);
        }
        return true;
    }

}