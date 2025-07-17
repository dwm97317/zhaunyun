<?php

namespace app\api\model;
use app\common\model\Insure as InsureModel;

/**
 * 保险
 * Class Insure
 * @package app\store\model
 */
class Insure extends InsureModel
{
    public function getList(){
        return $this
        ->with('image')
        ->where('status',0)
        ->order('sort','asc')
        ->select();
        ;
    }
}
