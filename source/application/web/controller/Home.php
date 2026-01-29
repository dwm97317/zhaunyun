<?php
namespace app\web\controller;

use app\web\model\Setting as SettingModel;
use think\Db;
use think\Session;
use app\web\model\UploadFile;
use app\web\model\Banner as BannerModel;
use app\web\model\WebMenu as WebMenuModel;
use app\web\model\WebLink as WebLinkModel;
use app\web\model\Wxapp as WxappModel;
use app\web\model\Article as ArticleModel;
use app\web\model\article\Category as CategoryModel;
use app\common\library\Ditch\BaiShunDa\bsdexp;
use app\common\library\Ditch\Jlfba\jlfba;
use app\common\library\Ditch\kingtrans;
use app\common\library\Ditch\Hualei;
use app\common\library\Ditch\Xzhcms5;
use app\common\library\Ditch\Aolian;
use app\common\library\Ditch\Yidida;
use app\common\library\Ditch\Zto;

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
       return $this->fetch('template/cargo/index',compact('setting'));
    }
    
    // 站点首页
    public function home()
    {
  
        $setting = SettingModel::getItem('store', $this->wxapp_id);
        return $this->renderSuccessWeb([
            'setting' => $setting,
            'wxapp' =>(new WxappModel())->with(['logos','wechatimgs'])
                ->field('app_wxname,copyright_des,copyrighttext,filing_number,version,logo,wechatimg')
                ->where('wxapp_id',$this->wxapp_id)
                ->find()
        ]);
    }
    
    // 隐私协议
     public function protocol(){
        $category_id = (new CategoryModel())->where(['belong'=>4])->value("category_id");
        $detail = ArticleModel::where(['category_id'=>$category_id])->find();
        return $this->renderSuccessWeb(compact('detail'));
     }
    
    //网站友情链接
    public function weblinklist()
    {
        $WebLinkModel = new WebLinkModel();
        $param = $this->request->param();
        $data = $WebLinkModel->getList($param);
        return $this->renderSuccessWeb([
            'links' => $data,
        ]);
    }
    
    //网站轮播图
    public function bannerlist()
    {
        $BannerModel = new BannerModel();
        $data = $BannerModel->getList();
        return $this->renderSuccessWeb([
            'banner' => $data,
        ]);
    }
    
    //公告
    public function noticelist(){
        $bannerModel = (new BannerModel());
        $data = $bannerModel->noticeBanner();
        if(empty($data)){
            return $this->renderError($data = []);
        }
        return $this->renderSuccessWeb(['notices'=>$data]);
    }
    
    //菜单
    public function menulist()
    {
        $model = new WebMenuModel;
        $list = $model->getTree($this->wxapp_id);
        return $this->renderSuccessWeb([
            'menus' => $list,
        ]);
    }
    
    // 联系我们
    public function contact(){
        $setting = SettingModel::getItem('store',$this->wxapp_id);
        $this->view->engine->layout(false);
       return $this->fetch('template/cargo/contact',compact('setting'));
    }
    
    // 关于我们
    public function aboutus(){
        $setting = SettingModel::getItem('store',$this->wxapp_id);
        $this->view->engine->layout(false);
       return $this->fetch('template/cargo/about',compact('setting'));
    }
    
    // 服务路线
    public function line(){
        $setting = SettingModel::getItem('store',$this->wxapp_id);
        $this->view->engine->layout(false);
       return $this->fetch('template/cargo/line',compact('setting'));
    }
    
       //获取更新日志
    public function track(){
        $param = $this->request->param();
        if (!$this->request->isAjax()) {
            $setting = SettingModel::getItem('store',$this->wxapp_id);
            $this->view->engine->layout(false);
            return $this->fetch('template/cargo/track',compact('setting'));
        }
        // 执行查询
        $Logistics = new Logistics;
        $express = $param['express_num'];
        $logic = $logic4 = $logictik =[];
        $result=[];
        $logib = [];
        $logicdd = [];
        $logicddd = [];
        $logia = [];
        $logicv = [];
        $logguoji=[];
        $logzd = [];
        $logici = [];
        $logicti = [];
        $Logistics = new Logistics();
        $PackageModel = new PackageModel();
        $DitchModel= new DitchModel();
        $Inpack = new Inpack();
        $Express = new Express();
        $setting = SettingModel::detail("notice")['values'];
        //查询出来这个单号是包裹单号、国际单号、转单号|
        $packData = $PackageModel->where(['express_num'=>$express,'is_delete' => 0])->find();
         
        $inpackData = $Inpack->where('t_order_sn|order_sn|t2_order_sn',$express)->where(['is_delete' => 0])->find(); //国际单号
        // $inpackData2 = $Inpack->where(['t2_order_sn'=>$express,'is_delete' => 0])->find();  //转单号
        // $inpackData4 = $Inpack->where(['order_sn'=>$express,'is_delete' => 0])->find();
        //如果是包裹单号，可以反查下处于哪个集运单；
        //   dump($inpackData);die;
        
        if(!empty($packData)){
            //查出的系统内部物流信息
            $logzd = $Logistics->getList($express);
                // dump($logzd);die;
            if(count($logic)>0){
                // dump($inpackData);die;
                $logia = $Logistics->getorderno($logzd[0]['order_sn']);
            }
            $express_code = $Express->getValueById($packData['express_id'],'express_code');
            
            if($setting['is_track_yubao']['is_enable']==1){//如果预报推送物流，则查询出来
                $logib = $Logistics->getZdList($packData['express_num'],$express_code,$packData['wxapp_id']);
            }
            $logicv = array_merge($logia,$logib,$logzd);
            // dump($logicv);die;
            if(!empty($packData['inpack_id'])){
                $inpackData = $Inpack->where('id',$packData['inpack_id'])->where(['is_delete' => 0])->find(); //国际单号
            }
        }
        //   dump($inpackData);die;

        if(!empty($inpackData) ){
          
            if($inpackData['transfer']==0){
                $ditchdatas = $DitchModel->where('ditch_id','=',$inpackData['t_number'])->find();
                
                 //锦联
                if($ditchdatas['ditch_no']==10001){
                    $jlfba =  new jlfba(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token']]);
                    $result = $jlfba->query($express);
                }
                //百顺达
                if($ditchdatas['ditch_no']==10002){
                    $bsdexp =  new bsdexp(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token']]);
                    //   dump($bsdexp);die;
                    $result = $bsdexp->query($express);
                }
                //K5
                if($ditchdatas['ditch_no']==10003){
                    $kingtrans =  new kingtrans(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $kingtrans->query($express);
                }
                //华磊api
                if($ditchdatas['ditch_no']==10004){
                    $Hualei =  new Hualei(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Hualei->query($express);
                    //  dump($result);die;
                }
                
                //星泰api
                if($ditchdatas['ditch_no']==10005){
                    $Xzhcms5 =  new Xzhcms5(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Xzhcms5->query($express);
                }
                
                //澳联
                if($ditchdatas['ditch_no']==10006){
                    $Aolian =  new Aolian(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Aolian->query($express);
                    //   dump($result);die;
                }
                
                //易抵达
                if($ditchdatas['ditch_no']==10007){
                    $Yidida =  new Yidida(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Yidida->query($express);
                }
                //中通
                if($ditchdatas['ditch_no']==10009){
                    $Zto = new Zto(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>isset($ditchdatas['api_url'])?$ditchdatas['api_url']:'']);
                    $result = $Zto->query($express);
                }
                $logic = $result;
            }else{
                if(!empty($inpackData['t_order_sn'])){
                     $logicddd = $Logistics->getZdList($inpackData['t_order_sn'],$inpackData['t_number'],$inpackData['wxapp_id']);
                }
                if(!empty($inpackData['t2_order_sn'])){
                     $logicdd = $Logistics->getZdList($inpackData['t2_order_sn'],$inpackData['t2_number'],$inpackData['wxapp_id']);
                }
                $logic = array_merge($logicddd,$logicdd);
            }
           
            $packinck = $PackageModel->where(['inpack_id'=>$inpackData['id'],'is_delete' => 0])->find();
            if(!empty($packinck)){
                $logictik = $Logistics->getorderno($inpackData['order_sn']);
                // dump($logictik);die;
            }else{
                if(!empty($packinck['express_num'])){
                    $logictik = $Logistics->getList($packinck['express_num']);
                }
                
            }
            $logici = array_merge($logictik);
             
        }
        
        $logic = array_merge($logic,$logici,$logicv);
        return $this->renderSuccess(['data' => $logic], '查询成功');
    }
    
}
