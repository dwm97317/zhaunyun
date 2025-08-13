<?php

namespace app\api\model;
use app\common\model\Consumables as ConsumablesModel;

/**
 * 耗材管理
 * Class Consumables
 * @package app\store\model
 */
class Consumables extends ConsumablesModel
{
    public function getList(){
        return $this
        ->with('image')
        ->order('sort','asc')
        ->select();
    }
}
