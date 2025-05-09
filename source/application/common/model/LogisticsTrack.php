<?php
namespace app\common\model;
use app\store\model\Inpack;

/**
 * 包裹日志Hook模型
 * Class OrderAddress
 * @package app\common\model
 */
class LogisticsTrack extends BaseModel
{    
    protected $name = 'logistics_track';
    protected $updateTime = false;
    
    public static function addhookLog($param){
        $model = new static;
        if (is_string($param)) {
            $data = json_decode($param, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON解析错误: ' . json_last_error_msg());
        }
        } elseif (is_array($param)) {
            $data = $param;
        } else {
            throw new Exception('无效的Webhook数据格式');
        }
        if (!isset($data['event']) || !isset($data['data'])) {
            return false;
        }
       
         if($data['event'] == "TRACKING_UPDATED"){
            $datas = [
                'express_num' => $data['data']['number'],
                'status_cn' =>   $data['data']['track_info']['latest_status']['status'],
                'logistics_describe' =>$data['data']['track_info']['latest_event']['description'],
                'longitude'=>$data['data']['track_info']['shipping_info']['shipper_address']['coordinates']['longitude'],
                'latitude'=>$data['data']['track_info']['shipping_info']['shipper_address']['coordinates']['latitude'],
                'country'=>$data['data']['track_info']['shipping_info']['shipper_address']['country'],
                'state'=>$data['data']['track_info']['shipping_info']['shipper_address']['state'],
                'city'=>$data['data']['track_info']['shipping_info']['shipper_address']['city'],
                'created_time' => date("Y-m-d H:i:s",time()),
                'wxapp_id'=>$param['wxapp_id'],
            ];
            //如果包裹已到达，就修改包裹为已送达；
            if($datas['status_cn'] == 'Delivered'){
                (new Inpack())->where('t_order_sn',$datas['express_num'])->update(['status'=>7]);
                (new Inpack())->where('t2_order_sn',$datas['express_num'])->update(['status'=>7]);
            }
             // log_write($param['wxapp_id']);
            $result = $model->where('status_cn',$datas['status_cn'])->where('express_num',$datas['express_num'])->find();
            if(empty($result)){
                return $model->insert($datas);
            }
            return false;
         }
    }
   

}