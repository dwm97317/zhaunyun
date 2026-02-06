<?php
namespace app\common\model;

/**
 * 增值服务模型
 * Class OrderAddress
 * @package app\common\model
 */
class LineService extends BaseModel
{
    protected $name = 'line_services';
    protected $updateTime = false;
    
    public static  function detail($id){
        return (new static()) ->find($id);
    }
    
    /**
     * 关联包裹图片表
     * @return \think\model\relation\HasMany
     */
    public function linecategory()
    {
        return $this->hasOne('LineCategory','category_id','line_category_id');
    }
    
    public function country(){
        return $this->belongsTo('Countries','country_id');
    }
    
    public function getserviceFree($weight,$country_id,$line_category_id,$post,$boxes,$services_require,$total_goods_value=0){
        $price = 0;
        if(empty($services_require)){
            return $price;
        }
        $services = explode(',',$services_require);
        if(count($services)==0){
            return $price;
        }
        //   dump($country_id);die;
        $list = $this->where('service_id','in',$services)->where('country_id',$country_id)->where('line_category_id',$line_category_id)->select();
        if(count($list)==0){
            return $price;
        }
     
        foreach ($list as $value){
            //重量模式
            if($value['type']==10){
               foreach (json_decode($value['rule'],true) as $v){
                    if($weight > $v['weight_start'] && $weight < $v['weight_max']){
                        $price += $v['weight_price'];
                    }
               }
            }
            //循环遍历箱子数据来判断是否超长
            if($value['type']==20 && count($boxes)>0){
                foreach ($boxes as $g){
                    $long = max($g['length'],$g['width'],$g['height']);
                    foreach (json_decode($value['rule'],true) as $v){
                        if($long > $v['weight_start'] && $long < $v['weight_max']){
                            $price += $v['weight_price'];
                        }
                   }
                }
            }
            
            //偏远模式
            if($value['type']==30){
               $postcodeArray = explode(',', $value['remote_areas']);
               if(in_array($post,$postcodeArray)){
                   foreach (json_decode($value['rule'],true) as $v){
                        if($weight > $v['weight_start'] && $weight < $v['weight_max']){
                            $price += $v['weight_price'];
                        }
                   }
               }
            }
            //税费模式
            if($value['type']==40){
               foreach (json_decode($value['rule'],true) as $v){
                    if($weight > $v['weight_start'] && $weight < $v['weight_max']){
                        $price += $v['weight_price']*$total_goods_value*0.01;
                    }
               }
            }
            //周长模式
            if($value['type']==50){
               
               foreach ($boxes as $g){
                    $long = $g['length'] + $g['width'] + $g['height'];
                    foreach (json_decode($value['rule'],true) as $v){
                        if($long > $v['weight_start'] && $long < $v['weight_max']){
                            $price += $v['weight_price'];
                        }
                   }
                }
            }
        }
     
        return $price;
    }
}
