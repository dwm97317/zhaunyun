<?php
namespace app\web\controller;

use app\web\model\Setting as SettingModel;
use think\Db;
use think\Session;
/**
 * web
 * Class Home
 * @package app\web\controller
 */
class Home extends Controller
{
    // 站点首页
    public function index(){
        $setting = SettingModel::getItem('store',$this->wxapp_id);
        $this->view->engine->layout(false);
       return $this->fetch('home/index',compact('setting'));
    }
}
