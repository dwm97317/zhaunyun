<?php

namespace app\api\model;
use app\common\model\ConsumablesLog as ConsumablesLogModel;

/**
 * 耗材管理
 * Class ConsumablesLog
 * @package app\store\model
 */
class ConsumablesLog extends ConsumablesLogModel
{
    
    /**
     * 新增记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }
}
