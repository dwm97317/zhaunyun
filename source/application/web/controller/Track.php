<?php
declare (strict_types=1);

namespace app\web\controller;

use app\api\model\Logistics;
use app\web\model\Setting as SettingModel;
use app\api\model\Package as PackageModel;
use app\api\service\trackApi\TrackApi;
use app\common\library\Ditch\config;
use app\common\model\Ditch as DitchModel;
use app\store\model\store\Shop as ShopModel;
use app\common\library\Ditch\BaiShunDa\bsdexp;
use app\common\library\Ditch\Jlfba\jlfba;
use app\common\library\Ditch\kingtrans;
use app\common\library\Ditch\Hualei;
use app\common\library\Ditch\Xzhcms5;
use app\common\library\Ditch\Aolian;
use app\common\library\Ditch\Yidida;
use app\api\model\Inpack;
use app\api\model\Express;
use app\common\library\express\Kuaidi100;
use app\common\model\Setting;
use app\common\library\Ditch\Zto;

/**
 * 用户认证模块
 * Class Passport
 * @package app\web\controller
 */
class Track extends Controller
{
    //获取更新日志
    public function searchlog(){
        
        $param = $this->request->param();
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
                    // dump($logicdd);die;
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
        return $this->renderSuccessWeb(['data' => $logic], '查询成功');
    }
    
    
    //获取更新日志
    public function search(){
        
        $param = $this->request->param();
        if (!$this->request->isAjax()) {
            $setting = SettingModel::getItem('store',$this->wxapp_id);
            $this->view->engine->layout(false);
            return $this->fetch('track/search',compact('setting'));
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
        
        // 如果找不到包裹数据，尝试使用快递100智能识别并查询
        if(empty($packData) && empty($inpackData)){
            $storeSetting = Setting::getItem('store', $this->wxapp_id);
            if(!empty($storeSetting['kuaidi100']['customer']) && !empty($storeSetting['kuaidi100']['key'])){
                try {
                    $Kuaidi100 = new Kuaidi100($storeSetting['kuaidi100']);
                    // 先使用智能识别接口识别快递公司
                    $detectList = $Kuaidi100->autoDetect($express);
                    if($detectList !== false && !empty($detectList)){
                        // 使用第一个识别结果（最可能的快递公司）进行查询
                        $detectedCode = $detectList[0]['code'];
                        $kuaidi100Result = $Kuaidi100->query($detectedCode, $express);
                        if($kuaidi100Result !== false && !empty($kuaidi100Result)){
                            // 转换快递100返回格式为系统格式
                            $kuaidi100Data = [];
                            if(is_array($kuaidi100Result)){
                                foreach($kuaidi100Result as $item){
                                    $kuaidi100Data[] = [
                                        'logistics_describe' => isset($item['context']) ? $item['context'] : '',
                                        'created_time' => isset($item['time']) ? $item['time'] : (isset($item['ftime']) ? $item['ftime'] : ''),
                                    ];
                                }
                            }
                            if(!empty($kuaidi100Data)){
                                $logib = $kuaidi100Data;
                                $logicv = array_merge($logia,$logib,$logzd);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // 快递100查询失败，继续使用其他方式
                }
            }
        }
        
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
            
            // 如果17track查询不到数据，尝试使用快递100查询
            if(empty($logib)){
                $storeSetting = Setting::getItem('store', $packData['wxapp_id']);
                if(!empty($storeSetting['kuaidi100']['customer']) && !empty($storeSetting['kuaidi100']['key'])){
                    try {
                        $Kuaidi100 = new Kuaidi100($storeSetting['kuaidi100']);
                        $queryCode = $express_code; // 默认使用已有的快递公司代码
                        
                        // 如果没有快递公司代码，使用智能识别
                        if(empty($queryCode)){
                            $detectList = $Kuaidi100->autoDetect($express);
                            if($detectList !== false && !empty($detectList)){
                                // 使用第一个识别结果（最可能的快递公司）
                                $queryCode = $detectList[0]['code'];
                            }
                        }
                        
                        // 如果有快递公司代码（已有的或识别到的），进行查询
                        if(!empty($queryCode)){
                            $kuaidi100Result = $Kuaidi100->query($queryCode, $express);
                            if($kuaidi100Result !== false && !empty($kuaidi100Result)){
                                // 转换快递100返回格式为系统格式
                                $kuaidi100Data = [];
                                if(is_array($kuaidi100Result)){
                                    foreach($kuaidi100Result as $item){
                                        $kuaidi100Data[] = [
                                            'logistics_describe' => isset($item['context']) ? $item['context'] : '',
                                            'created_time' => isset($item['time']) ? $item['time'] : (isset($item['ftime']) ? $item['ftime'] : ''),
                                        ];
                                    }
                                }
                                if(!empty($kuaidi100Data)){
                                    $logib = $kuaidi100Data;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // 快递100查询失败，继续使用其他方式
                    }
                }
            }
            
            $logicv = array_merge($logia,$logib,$logzd);
            // dump($logicv);die;
            if(!empty($packData['inpack_id'])){
                $inpackData = $Inpack->where('id',$packData['inpack_id'])->where(['is_delete' => 0])->find(); //国际单号
            }
        }
        
        if(!empty($inpackData) ){
            // dump($inpackData['transfer']);die;

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
                        //   dump($logicdd);die;
                }
               
                $logic = array_merge($logicddd,$logicdd);
              
                // 如果没有查到数据，尝试使用快递100查询
                if(empty($logic)){
                    $storeSetting = Setting::getItem('store', $inpackData['wxapp_id']);
                  
                    if(!empty($storeSetting['kuaidi100']['customer']) && !empty($storeSetting['kuaidi100']['key'])){
                        try {
                            $Kuaidi100 = new Kuaidi100($storeSetting['kuaidi100']);
                            // 使用原始查询的单号进行智能识别
                            $queryExpress = $express;
                            // 如果没有快递公司代码，使用智能识别
                            $detectList = $Kuaidi100->autoDetect($queryExpress);
                                dump($detectList);die;
                            if($detectList !== false && !empty($detectList)){
                                // 使用第一个识别结果（最可能的快递公司）进行查询
                                $queryCode = $detectList[0]['code'];
                                $kuaidi100Result = $Kuaidi100->query($queryCode, $queryExpress);
                                if($kuaidi100Result !== false && !empty($kuaidi100Result)){
                                    // 转换快递100返回格式为系统格式
                                    $kuaidi100Data = [];
                                    if(is_array($kuaidi100Result)){
                                        foreach($kuaidi100Result as $item){
                                            $kuaidi100Data[] = [
                                                'logistics_describe' => isset($item['context']) ? $item['context'] : '',
                                                'created_time' => isset($item['time']) ? $item['time'] : (isset($item['ftime']) ? $item['ftime'] : ''),
                                            ];
                                        }
                                    }
                                    if(!empty($kuaidi100Data)){
                                        $logic = $kuaidi100Data;
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            // 快递100查询失败，继续使用其他方式
                        }
                    }
                }
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