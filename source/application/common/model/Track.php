<?php
namespace app\common\model;

/**
 * 常用轨迹模型
 * Class Shop
 * @package app\common\model\store
 */
class Track extends BaseModel
{
    protected $name = 'diy_track';

    /**
     * 轨迹详情
     * @param $forwarder
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($track_id)
    {
        return static::get($track_id);
    }

}