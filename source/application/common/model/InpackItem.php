<?php

namespace app\common\model;

/**
 * 订单子订单模型
 * Class GoodsImage
 * @package app\common\model
 */
class InpackItem extends BaseModel
{
    protected $name = 'inpack_item';
    protected $updateTime = false;
    
    public function details($id){
        return $this->find($id);
    }
    
    /**
     * 体积重
     * @param $value
     * @return mixed
     */
    public function getLineWeightAttr($value)
    {
        return number_format($value,2);
    }
}
