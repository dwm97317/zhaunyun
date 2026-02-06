<?php
namespace app\api\model;
use app\common\model\LogisticsTrack as LogisticsTrackModel;

/**
 * 地图模型
 * Class LogisticsTrack
 * @package app\api\model
 */
class LogisticsTrack extends LogisticsTrackModel
{ 
    public function getList($query){
      if(empty($query['express_num'])){
          $this->error = "快递单号不能为空";
          return false;
      }
      return $this
        ->where('express_num',$query['express_num'])
        ->order('created_time asc')
        ->select();
    }
    
}