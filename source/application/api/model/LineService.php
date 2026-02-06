<?php
namespace app\api\model;

use app\common\model\LineService as LineServiceModel;

/**
 * 增值服务模型
 * Class LineService
 * @package app\common\model
 */
class LineService extends LineServiceModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'update_time'
    ];


}
