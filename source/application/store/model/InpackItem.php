<?php
namespace app\store\model;

use app\common\model\InpackItem as InpackItemModel;
use app\store\model\Inpack; 
use app\store\model\Setting as SettingModel;

/**
 * 集运子订单模型
 * Class GoodsImage
 * @package app\store\model
 */
class InpackItem extends InpackItemModel
{
    
     /**
     * 关联表
     * @return \think\model\relation\BelongsTo
     */
    public function inpackitem()
    {
        return $this->belongsTo('InpackItemModel','inpack_id','id');
    }
    
    
    //新增子项目
    public function addItem($data){
        $result['wxapp_id'] = self::$wxapp_id;
        $result['create_time'] = time();
        $result['inpack_id'] = $data['inpack_id'];
        $inpackdetail = (new Inpack())->details($data['inpack_id']);
        $settingdata  = SettingModel::getItem('store');
        foreach ($data['width'] as $key =>$val){
            for ($i = 0; $i < $data['num'][$key]; $i++) {
                if(!empty($data['width'][$key]) && !empty($data['length'][$key]) && !empty($data['height'][$key])){
                    $result['width'] = $data['width'][$key];
                    $result['length'] = $data['length'][$key];
                    $result['height'] = $data['height'][$key];
                    $result['volume'] = $data['width'][$key]*$data['length'][$key]*$data['height'][$key]/1000000;
                }
                if(!empty($data['weight'][$key]) && !empty($data['volume_weight'][$key])){
                    $result['weight'] = $data['weight'][$key];
                    $result['volume_weight'] = $data['volume_weight'][$key];
                    
                    $result['cale_weight'] = $data['weight'][$key] > $data['volume_weight'][$key]*$inpackdetail['line']['volumeweight_weight'] ?$data['weight'][$key]:$data['volume_weight'][$key];
                    
                    $result['line_weight'] = $this->turnweight($settingdata['weight_mode']['mode'],$result['cale_weight'],$inpackdetail['line']['line_type_unit']);
                }
                if(!empty($data['id'][$key]) && $i==0){
                    $this->where('id',$data['id'][$key])->update($result);
                }else{
                    $this->insert($result);
                }
            }
        }
        return true;
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
     
    
        
    //编辑子项目
    public function editItem($data){
        $detail = $this->find($data['id']);
        if(!empty($data['width']) && !empty($data['length']) && !empty($data['height'])){
            $data['volume'] = $data['width']*$data['length']*$data['height']/1000000;
        }
        if(!empty($data['weight']) && !empty($data['volume_weight'])){
            $data['cale_weight'] = $data['weight'] > $data['volume_weight']?$data['weight']:$data['volume_weight'];
        }
        if(!$detail->allowField(true)->save($data)){
            $this->error = "保存失败";
            return false;
        }
        return true;
    }
    
    
    //删除
    public function deletes($id){
        return $this->find($id)->delete();
    }
    
}
