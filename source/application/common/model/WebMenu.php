<?php
namespace app\common\model;

/**
 * 网站自定义菜单
 * Class WechatMenu
 * @package app\common\model
 */
class WebMenu extends BaseModel
{
    protected $name = 'webmenu';
    
    // 菜单类型
    const TYPE_PAGE = 10; // 单页
    const TYPE_LIST = 20; // 列表
    const TYPE_ABOUT = 30; // 关于我们
    const TYPE_WAREHOUSE = 40; // 仓库地址
    
    /**
     * 获取菜单树
     */
    public static function getTree($wxapp_id)
    {
        $menus = self::where('wxapp_id', $wxapp_id)
            ->order('sort', 'asc')
            ->select();
            
        return list_to_tree($menus->toArray(), 'id', 'parent_id');
    }
    
    /**
     * 获取菜单类型选项
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_PAGE => '单页',
            self::TYPE_LIST => '列表',
            self::TYPE_ABOUT => '关于我们',
            self::TYPE_WAREHOUSE => '仓库地址'
        ];
    }
}