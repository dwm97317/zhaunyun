<?php

namespace app\common\model;

/**
 * 包裹认领任务模型
 * Class PackageClaim
 * @package app\common\model
 */
class PackageClaim extends BaseModel
{
    protected $name = 'package_claim';

    
    public function user(){
      return $this->belongsTo('app\common\model\User','user_id')->field('user_id,nickName,avatarUrl,user_code');
    }
    
    public function package(){
        return $this->belongsTo('app\common\model\Package');
    }
}
