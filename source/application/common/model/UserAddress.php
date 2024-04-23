<?php
namespace app\common\model;

/**
 * 用户收货地址模型
 * Class UserAddress
 * @package app\common\model
 */
class UserAddress extends BaseModel
{
    protected $name = 'user_address';

    /**
     * 追加字段
     * @var array
     */
    protected $append = ['region'];
    
    public function countrydata(){
        return $this->belongsTo('app\common\model\Country','country_id','id');
    }
}
