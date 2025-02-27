<?php

namespace app\common\model;
use app\common\model\store\shop\Capital;
use app\common\model\store\shop\ShopBonus;
use app\common\model\store\Shop;
/**
 * 包裹图片模型
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
}
