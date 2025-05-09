<?php
namespace app\api\model;
use app\common\model\Logistics as LogisticsModel;
use app\common\library\express\Kuaidi100;
use app\api\service\trackApi\TrackApi;
use app\common\model\Setting;
use app\common\model\Inpack;
use app\api\model\Package;
use app\api\model\Express;

use app\api\model\store\shop\Clerk;
/**
 * 线路模型
 * Class Express
 * @package app\api\model
 */
class Logistics extends LogisticsModel
{ 
     public function setQuery($sn){
         return $this->where('order_sn',$sn);
     }
     
     public function getorderno($sn){
         $data =  $this->where('order_sn',$sn)
         ->where('express_num',null)
         ->group('logistics_describe')
         ->order('created_time desc')
         ->select()->toArray();
         return $data;
     }
     
     public function getlogisticssn($sn){
         $data =  $this->where('logistics_sn',$sn)
         ->where('express_num',null)
         ->order('created_time desc')
         ->group('logistics_describe')
         ->select()->toArray();
         return $data;
     }
     
     // 获取系统内部信息
     public function getList($sn){
        $data = $this->with(['clerk'])
        ->where('express_num',$sn)
        ->order('created_time desc')
        ->group('logistics_describe')
        ->select()->toArray();
        return $data;
     }
     
     //查询转单包裹的物流信息
     public function getZdList($sn,$number,$wxappId){
        $setting = Setting::getItem("store",$wxappId);
        $data = [];
       
        //查询17track物流信息
        if (!empty($setting['track17']['key'])){
        $track = (new TrackApi())->track(['track_sn'=>$sn,'t_number'=>$number,'wxapp_id'=> $wxappId]);
    //   dump($track);die;
        if(!empty($track)){
                foreach ($track['tracking']['providers'] as $k => $v){  //[0]['events'] as
                if(count($v['events'])>0){
                  
                    foreach ($v['events'] as $l){
                        //   dump($l);die;
                        $data[] = [
                          'logistics_describe' => $l['description'], 
                          'status_cn' => $l['location'],
                          'created_time' => str_replace(array('T','Z'),' ',$l['time_utc'])
                        ];
                    }
                    // dump($v['events']);die;
                    
                }
                    
               }
           }
        }
          
        return $data;
     }
 
        
    public function clerk(){
        return $this->belongsTo('app\api\model\store\shop\Clerk','operate_id','clerk_id');
    }
}