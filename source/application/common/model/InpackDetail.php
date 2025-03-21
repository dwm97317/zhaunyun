<?php

namespace app\common\model;

/**
 * 订单申报模型
 * Class GoodsImage
 * @package app\common\model
 */
class InpackDetail extends BaseModel
{
    protected $name = 'inpack_details';
    
    public static function detail($id){
        return self::get($id);
    }
}
