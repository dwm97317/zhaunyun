<?php

namespace app\api\model;

use app\common\model\InpackItem as InpackItemModel;
use app\api\model\Inpack;
use app\api\model\Setting as SettingModel;

/**
 * 集运单服务项目模型
 * Class GoodsImage
 * @package app\api\model
 */
class InpackItem extends InpackItemModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
        'update_time',
    ];
    

    /**
     * 处理包装服务
     * @var array
     */
    public function add($data){
        $data['wxapp_id'] = self::$wxapp_id;
        $data['create_time'] = time();
        $Inpackdetail = (new Inpack())->getDetails($data['inpack_id'],[]);
        $settingdata  = SettingModel::getItem('store');
        if(!empty($data['width']) && !empty($data['length']) && !empty($data['height'])){
            $data['volume'] = $data['width']*$data['length']*$data['height']/1000000;
            $volume_weight = $data['width']*$data['length']*$data['height']/$Inpackdetail['line']['volumeweight'];
            if($Inpackdetail['line']['weightvol_integer']==1){
                $volume_weight = ceil($volume_weight);
            }
            if(!empty($data['weight'])){
                $data['cale_weight'] = $data['weight']*$Inpackdetail['line']['volumeweight_weight'] > $volume_weight?$data['weight']:$volume_weight;
            }
            $data['volume_weight'] = $volume_weight;
            $data['line_weight'] = $this->turnweight($settingdata['weight_mode']['mode'], $data['cale_weight'],$Inpackdetail['line']['line_type_unit']);
        }
        return $this->allowField(true)->insert($data);
    }
    
    public function turnweight($weight_mode,$oWeigth,$line_type_unit){
       
         switch ($weight_mode) {
           case '10':
                if($line_type_unit == 20){
                    $oWeigth = 0.001 * $oWeigth;
                }
                if($line_type_unit == 30){
                    $oWeigth = 0.00220462262185 * $oWeigth;
                }
               break;
           case '20':
                if($line_type_unit == 10){
                    $oWeigth = 1000 * $oWeigth;
                }
                if($line_type_unit == 30){
                    $oWeigth = 2.20462262185 * $oWeigth;
                }
               break;
           case '30':
               if($line_type_unit == 10){
                    $oWeigth = 453.59237 * $oWeigth;
                }
                if($line_type_unit == 20){
                    $oWeigth = 0.45359237 * $oWeigth;
                }
               break;
           default:
               if($line_type_unit == 10){
                    $oWeigth = 1000 * $oWeigth;
                }
                if($line_type_unit == 30){
                    $oWeigth = 2.20462262185 * $oWeigth;
                }
               break;
       }
        $oWeigth = round($oWeigth,2);
        return $oWeigth;
     }
     

}
