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
    
     /**
     * 处理包装服务
     * @var array
     */
    public function doservice($inpack,$pack_ids){
        $packArr = explode(',',$pack_ids);
        $this->where('inpack_id',$inpack)->delete();
        foreach ($packArr as $key => $value){
            $pack[$key]['inpack_id'] = $inpack;
            $pack[$key]['service_id'] = $value;
            $pack[$key]['wxapp_id'] = self::$wxapp_id;
            $pack[$key]['create_time'] = time();
        }
        $res = $this->saveAll($pack);
        if(!$res){
            return false;
        }
        return true;
    }
    
    
    //删除
    public function deletes($id){
        return $this->find($id)->delete();
    }
    
}
