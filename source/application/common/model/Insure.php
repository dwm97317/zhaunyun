<?php

namespace app\common\model;

use app\common\model\BaseModel;

/**
 * 保险
 * Class Insure
 * @package app\common\model\wxapp
 */
class Insure extends BaseModel
{
    protected $name = 'insure';

    /**
     * 详情
     * @param $roomId
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($navId)
    {
        return static::get($navId);
    }
    
    
    /**
     * 关联文章封面图
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('uploadFile', 'file_id', 'image_id');
    }

}