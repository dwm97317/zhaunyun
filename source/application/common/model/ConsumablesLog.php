<?php

namespace app\common\model;

use app\common\model\BaseModel;

/**
 * 耗材日志管理
 * Class Consumables
 * @package app\common\model
 */
class ConsumablesLog extends BaseModel
{
    protected $name = 'consumables_log';

    /**
     * 获取详情
     * @param $roomId
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        return static::get($id);
    }
    
    /**
     * 关联文章封面图
     * @return \think\model\relation\HasOne
     */
    public function inpack()
    {
        return $this->hasOne('Inpack', 'id', 'inpack_id');
    }
    
     /**
     * 关联文章封面图
     * @return \think\model\relation\HasOne
     */
    public function consumables()
    {
        return $this->hasOne('Consumables', 'id', 'con_id');
    }
}