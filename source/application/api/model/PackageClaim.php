<?php
namespace app\api\model;

use app\common\model\PackageClaim as PackageClaimModel;

/**
 * 包裹认领模型
 * Class PackageClaim
 * @package app\store\model
 */
class PackageClaim extends PackageClaimModel
{
    public function saveData($data){
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->save($data);
    }
}
