<?php
namespace app\store\controller;

use app\store\model\store\User as StoreUser;
use think\Session;
use app\store\model\Countries;
use app\api\controller\Page;
use app\store\model\Line;
use app\common\model\UpdateLog;
use app\common\model\UpdateLogWxapp;
use app\common\model\ApiPost;
use app\common\model\Logistics;
use app\store\model\Setting as SettingModel;
use app\store\model\store\Shop as ShopModel;
use app\store\model\LineCategory as LineCategoryModel;
use app\store\model\Category as CategoryModel;
use app\store\model\PackageService;
use app\store\model\LineService;
use app\store\model\Express as ExpressModel;
use app\store\model\Ditch as DitchModel;
use app\store\model\Insure as InsureModel;

/**
 * 工具功能
 * Class Passport
 * @package app\store\controller
 */
class Tools extends Controller
{
    
    public function index()
    {
        return $this->fetch('index');
    }
    
    public function seachfree()
    {
        $country = (new Countries())->getListAllCountry();
        $data = input();
        if($data){
            $list = getsearchfree($data);
            $list=  json_encode($list);
            $list=  json_decode($list,true); 
          
        }else{
            $list = [];
            $list=  json_encode($list);
            $list=  json_decode($list,true); 
        }
        return $this->fetch('searchfree',compact('country','list'));
    }
    
    //获取更新日志
    public function updatelog(){
        $UpdateLog = new UpdateLog;
        $list = $UpdateLog->getList();
        return $this->fetch('updatelog',compact('list'));
    }
    
    //获取更新日志
    public function addwxapplog(){
        $UpdateLogWxapp = new UpdateLogWxapp;
        $param = $this->request->param();
        $UpdateLogWxapp->add($param);
        return $this->renderSuccess('添加成功');
    }
    
    
    public function apipost(){
        $ApiPost = new ApiPost;
        $list = $ApiPost->getList();
        return $this->fetch('apipost',compact('list'));
    }
    
    //使用指南
    public function guide(){
        $shoplist = (new ShopModel)->getList([]);
        $countrylist = (new Countries())->getListAllCountry();
        $linecategory = (new LineCategoryModel)->getList();
        $categorylist = (new CategoryModel())->getCacheTree();
        $linelist = (new Line())->getList([]);
        $packageservicelist = (new PackageService())->getList([]);
        $lineservicelist = (new LineService())->getList([]);
        $expresslist = (new ExpressModel)->getList();
        $ditchlist = (new DitchModel)->getList();
        $insurelist = (new InsureModel)->getList();
        return $this->fetch('guide',compact('shoplist','countrylist','linecategory','categorylist','linelist','packageservicelist','lineservicelist','expresslist','ditchlist','insurelist'));
    }
}
