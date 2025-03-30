<?php

namespace app\common\model\market;

use app\common\model\BaseModel;
/**
 * 分享墙图片模型
 * Class BlindboxWallImage
 * @package app\common\model\market
 */
class BlindboxWallImage extends BaseModel
{
    protected $name = 'blindbox_wall_image';

    /**
     * 关联文件库
     * @return \think\model\relation\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo('UploadFile', 'image_id', 'file_id')
            ->bind(['file_path', 'file_name', 'file_url']);
    }

}
