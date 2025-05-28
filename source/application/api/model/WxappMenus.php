<?php

namespace app\api\model;
use app\common\model\wxapp\WxappMenus as WxappMenusModel;

/**
 * 小程序导航
 * Class Wxapp/nav
 * @package app\store\model
 */
class WxappMenus extends WxappMenusModel
{
    public function getList($query){
        return $this
        ->with(['image','select'])
        ->where('lang_type',$query['type'])
        ->where('status',0)
        ->order('sort','asc')
        ->select();
        ;
    }
}
