<?php
namespace app\common\model;

/**
 * 货币模型
 * Class Country
 * @package app\common\model
 */
class Currency extends BaseModel
{
    protected $name = 'currency';
    
    public function getListAllCountry(){
        return $this->select();
    }
    
}
