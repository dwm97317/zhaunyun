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
    // $param = json_decode($param,true);
    $resJson = $param['data'];
    log_write($resJson[0]);
     if($resJson[0]['event']=="TRACKING_UPDATED"){
            $data = [
                'express_num' => $resJson['data']['number'],
                'status_cn' => $resJson['data']['track_info']['latest_status']['status'],
                'logistics_describe' =>$resJson['data']['track_info']['latest_event']['description'],
                'created_time' => date("Y-m-d H:i:s",time()),
                'wxapp_id'=>$param['wxapp_id'],
            ];
     }
    return $model->insert($data);
}
   

}