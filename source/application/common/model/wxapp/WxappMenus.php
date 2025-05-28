<?php

namespace app\common\model\wxapp;

use app\common\model\BaseModel;

/**
 * 微信小程序导航菜单模型
 * Class LiveRoom
 * @package app\common\model\wxapp
 */
class WxappMenus extends BaseModel
{
    protected $name = 'wxapp_menus';

    /**
     * 获取直播间详情
     * @param $roomId
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($navId)
    {
        return static::get($navId);
    }
    
    
    /**
     * 关联文章封面图
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('uploadFile', 'file_id', 'icon');
    }

    /**
     * 关联文章封面图
     * @return \think\model\relation\HasOne
     */
    public function select()
    {
        return $this->hasOne('uploadFile', 'file_id', 'selectedIcon');
    }
}