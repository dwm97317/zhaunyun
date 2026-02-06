<?php

namespace app\common\model;

use think\Cache;

/**
 * 运输方式
 * Class LineCategory
 * @package app\common\model
 */
class LineCategory extends BaseModel
{
    protected $name = 'line_category';

    /**
     * 分类图片
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('uploadFile', 'file_id', 'image_id');
    }
    


    /**
     * 所有分类
     * @return mixed
     */
    public static function getALL()
    {
        $model = new static;
        return $model->where('status',0)->with(['image'])->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
    }

}
