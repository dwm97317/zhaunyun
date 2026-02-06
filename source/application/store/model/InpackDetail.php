<?php
namespace app\store\model;

use app\common\model\InpackDetail as InpackDetailModel;

/**
 * 集运申报模型
 * Class GoodsImage
 * @package app\store\model
 */
class InpackDetail extends InpackDetailModel
{
    
     /**
     * 关联表
     * @return \think\model\relation\BelongsTo
     */
    public function inpackdetail()
    {
        return $this->belongsTo('InpackDetailModel','inpack_id','id');
    }
    
    
    //新增申报
    public function addItem($data){
        $data['wxapp_id'] = self::$wxapp_id;
        $data['create_time'] = time();
        if(!empty($data['width']) && !empty($data['length']) && !empty($data['height'])){
            $data['volume'] = $data['width']*$data['length']*$data['height']/1000000;
        }
        if(!empty($data['weight']) && !empty($data['volume_weight'])){
            $data['cale_weight'] = $data['weight'] > $data['volume_weight']?$data['weight']:$data['volume_weight'];
        }
        if(!$this->allowField(true)->save($data)){
            $this->error = "保存失败";
            return false;
        }
        return true;
    }
    
        
    //编辑申报
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
    
    
    //删除申报
    public function deletes($id){
        return $this->find($id)->delete();
    }
    
}
