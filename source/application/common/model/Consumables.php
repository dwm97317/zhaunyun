<?php

namespace app\common\model;

use app\common\model\BaseModel;

/**
 * 耗材管理
 * Class Consumables
 * @package app\common\model
 */
class Consumables extends BaseModel
{
    protected $name = 'consumables';

    /**
     * 获取直播间详情
     * @param $roomId
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        return static::get($id);
    }
    
        /**
     * 获取直播间详情
     * @param $roomId
     * @return static|null
     * @throws \think\exception\DbException
     */
    public function finddetail($code)
    {
        return $this->where("barcode",$code)->find();
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