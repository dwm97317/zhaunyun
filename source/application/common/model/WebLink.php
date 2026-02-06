<?php
namespace app\common\model;

/**
 * 友情链接
 * Class WebLink
 * @package app\common\model
 */
class WebLink extends BaseModel
{
    protected $name = 'weblink';

    /**
     * 关联封面图
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('uploadFile', 'file_id', 'image_id');
    }
}