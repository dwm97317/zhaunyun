<?php
namespace app\web\model;
use app\common\model\Banner as BannerModel;

/**
 * 订单收货地址模型
 * Class OrderAddress
 * @package app\common\model
 */
class Banner extends BannerModel
{
    protected $updateTime = false;

    public function queryPage(){
        return $this->where(['status'=>1])->order('sort DESC')->field('title,image_id,redirect_type,url')->limit(6)->select();
    }
    
    public function getList(){
        return $this->with('image')
        ->where('banner_site',50)
        ->select();
    }
    
    //弹窗公告图
    public function noticeBanner(){
        return $this
        ->with('image')
        ->where(['status'=>1,'banner_site'=> 40])
        ->field('id,title,image_id,redirect_type,url,banner_site')
        ->order('created_time','asc')
        ->limit(10)->select();
    }
}
