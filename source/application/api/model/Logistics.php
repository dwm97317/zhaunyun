<?php
namespace app\api\model;
use app\common\model\Logistics as LogisticsModel;
use app\common\library\express\Kuaidi100;
use app\api\service\trackApi\TrackApi;
use app\common\model\Setting;
use app\common\model\Inpack;
use app\api\model\Package;
use app\api\model\Express;
/**
 * 线路模型
 * Class Express
 * @package app\api\model
 */
class Logistics extends LogisticsModel
{ 
    
    //  public function getPartyList($sn){
    //      //物流设置项目
    //     $setting = Setting::detail("store")['values'];
    //     $noticesetting = Setting::getItem('notice');
    //     $pstatus = [];

    //     $Package = new Package();
    //     $Inpack = new Inpack();
    //     $Express = new Express();
    //     $packData = $Package->where(['express_num'=>$sn,'is_delete' => 0])->find();
    //     $inpackData = $Inpack->where(['t_order_sn'=>$sn,'is_delete' => 0])->find();
    //     $inpackData2 = $Inpack->where(['t2_order_sn'=>$sn,'is_delete' => 0])->find();
    //     // dump($packData);die;
    //     $express_code = $Express->getValueById($packData['express_id'],'express_code');
    //     $data0 = $data1= $data2 =  $data3 = $data4 =  [];
    //     //包裹情况下
    //     if(!empty($packData)){
    //         if (!empty($setting['track17']['key'])){
    //             $track = (new TrackApi())->track(['track_sn'=>$sn,'t_number'=> $express_code ,'wxapp_id'=> $packData['wxapp_id']]);
    //             // dump($track);die;
    //             if(!empty($track)){
    //                 foreach ($track['tracking']['providers'][0]['events'] as $v){
    //                     $data0[] = [
    //                       'logistics_describe' => $v['description'], 
    //                       'status_cn' => $v['location'],
    //                       'created_time' => str_replace(array('T','Z'),' ',$v['time_utc'])
    //                     ];
    //               }
    //             }
               
    //             $inpackData = $Inpack->where('FIND_IN_SET(:ids,pack_ids)', ['ids' => $packData['id']])->where('is_delete',0)->find();
           
    //             if(!empty($inpackData['t_order_sn'])){
    //                 $track = (new TrackApi())->track(['track_sn'=>$inpackData['t_order_sn'],'t_number'=> $inpackData['t_number'] ,'wxapp_id'=> $packData['wxapp_id']]);
    //                 if(!empty($track)){
    //                     foreach ($track['tracking']['providers'][0]['events'] as $v){
    //                         $data3[] = [
    //                           'logistics_describe' => $v['description'], 
    //                           'status_cn' => $v['location'],
    //                           'created_time' => str_replace(array('T','Z'),' ',$v['time_utc'])
    //                         ];
    //                   }
    //                 }
    //             }
                
    //             if(!empty($inpackData['t2_order_sn'])){
    //                 $track = (new TrackApi())->track(['track_sn'=>$inpackData['t2_order_sn'],'t_number'=> $inpackData['t2_number'] ,'wxapp_id'=> $packData['wxapp_id']]);
                    
    //                 if(!empty($track)){
    //                     foreach ($track['tracking']['providers'][0]['events'] as $v){
    //                         $data4[] = [
    //                           'logistics_describe' => $v['description'], 
    //                           'status_cn' => $v['location'],
    //                           'created_time' => str_replace(array('T','Z'),' ',$v['time_utc'])
    //                         ];
    //                   }
    //                 }
    //             }
             
    //         }
            
           
    //         $ordersn = $this->where('express_num',$sn)->find();
    //         if($noticesetting['is_inpack']['is_enable']==1){
    //           $data1 = $this->where('order_sn',$ordersn['order_sn'])->select()->toArray(); 
    //         }else{
    //           $data2 = $this->where('express_num',$sn)->select()->toArray();
    //         }
    //         return $list = array_merge($data4,$data3,$data2,$data1,$data0);
    //     }
        
        
    //     //集运单情况下
    //     if(!empty($inpackData)){
    //         $ordersn = $this->where('logistics_sn',$sn)->find();
    //         if($noticesetting['is_package']['is_enable']==1){
    //           $data1 = $this->where('order_sn',$ordersn['order_sn'])->group('status')->select()->toArray(); 
    //         }else{
    //           $data2 = $this->where('logistics_sn',$sn)->select()->toArray();
    //         }
    //         if(!empty($inpackData['t2_order_sn'])){
    //                 $track = (new TrackApi())->track(['track_sn'=>$inpackData['t2_order_sn'],'t_number'=> $inpackData['t2_number'] ,'wxapp_id'=> $packData['wxapp_id']]);
                    
    //                 if(!empty($track)){
    //                     foreach ($track['tracking']['providers'][0]['events'] as $v){
    //                         $data4[] = [
    //                           'logistics_describe' => $v['description'], 
    //                           'status_cn' => $v['location'],
    //                           'created_time' => str_replace(array('T','Z'),' ',$v['time_utc'])
    //                         ];
    //                   }
    //                 }
    //             }
            
    //         return $list = array_merge($data4,$data3,$data2,$data1,$data0);
    //     }
    //     //转单号不为空
    //     if(!empty($inpackData2)){
          
    //         $track = (new TrackApi())->track(['track_sn'=>$inpackData2['t2_order_sn'],'t_number'=> $inpackData2['t2_number'] ,'wxapp_id'=> $inpackData2['wxapp_id']]);

    //         if(!empty($track)){
    //             foreach ($track['tracking']['providers'][0]['events'] as $v){
    //                 $data4[] = [
    //                   'logistics_describe' => $v['description'], 
    //                   'status_cn' => $v['location'],
    //                   'created_time' => str_replace(array('T','Z'),' ',$v['time_utc'])
    //                 ];
    //           }
    //         }
    //         return $list = array_merge($data4,$data3,$data2,$data1,$data0);
    //     }
    //     return [];
    //  }
     
     public function setQuery($sn){
         return $this->where('order_sn',$sn);
     }
     
     public function getorderno($sn){
         return $this->where('order_sn',$sn)->where('express_num',null)->order('created_time desc')->select()->toArray();
     }
     
     // 获取系统内部信息
     public function getList($sn){
        $data = $this->where('express_num',$sn)->order('created_time desc')->select()->toArray();
        return $data;
     }
     
     //查询转单包裹的物流信息
     public function getZdList($sn,$number,$wxappId){
        $setting = Setting::detail("store")['values'];
        $data = [];

        //查询17track物流信息
        if (!empty($setting['track17']['key'])){
        $track = (new TrackApi())->track(['track_sn'=>$sn,'t_number'=>$number,'wxapp_id'=> $wxappId]);
  
        if(!empty($track)){
                foreach ($track['tracking']['providers'][0]['events'] as $v){
                    $data[] = [
                      'logistics_describe' => $v['description'], 
                      'status_cn' => $v['location'],
                      'created_time' => str_replace(array('T','Z'),' ',$v['time_utc'])
                    ];
               }
           }
        }
        return $data;
     }
 
}