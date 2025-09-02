<?php
namespace app\api\model;

use app\common\model\Currency as CurrencyModel;

/**
 * 货币管理
 * Class Currency
 * @package app\api\model
 */
class Currency extends CurrencyModel
{
    // 查询快递列表
    public function queryTopCountry(){
        return $this->where('is_default',1)->where('status',0)->find();
    }
}