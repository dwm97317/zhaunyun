<?php
namespace app\api\model;

use app\common\model\Barcode as BarcodeModel;

/**
 * 物流公司模型
 * Class Barcode
 * @package app\api\model
 */
class Barcode extends BarcodeModel
{
    public function saveData($param){
        // dump($param);die;
        return $this->save($param);
    }

}