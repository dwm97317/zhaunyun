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
}
