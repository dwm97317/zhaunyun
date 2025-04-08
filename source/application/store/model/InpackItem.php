<?php
namespace app\store\model;

use app\common\model\InpackItem as InpackItemModel;

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
                    $result['cale_weight'] = $data['weight'][$key] > $data['volume_weight'][$key]?$data['weight'][$key]:$data['volume_weight'][$key];
                }
                $this->insert($result);
            }
        }
        return true;
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
