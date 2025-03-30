<?php

namespace app\common\model\market;

use think\Db;
use  app\common\model\BaseModel;
/**
 * 盲盒分享墙
 * Class BlindboxWall
 * @package app\common\model\market
 */
class BlindboxWall extends BaseModel
{
    protected $name = 'blindbox_wall';

    /**
     * 所属包裹
     * @return \think\model\relation\BelongsTo
     */
    public function packages()
    {
        return $this->belongsTo('Package','package_id');
    }

    /**
     * 订单商品
     * @return \think\model\relation\BelongsTo
     */
    public function blindbox()
    {
        return $this->belongsTo('Blindbox','blindbox_id');
    }

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * 关联分享墙图片表
     * @return \think\model\relation\HasMany
     */
    public function image()
    {
        return $this->hasMany('BlindboxWallImage')->order(['id' => 'asc']);
    }

    /**
     * 分享墙详情
     * @param $wall_id
     * @return Comment|null
     * @throws \think\exception\DbException
     */
    public static function detail($wall_id)
    {
        return self::get($wall_id, ['user', 'blindbox','packages','image.file']);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        return $this->transaction(function () use ($data) {
            // 删除分享墙图片
            $this->image()->delete();
            // 添加分享墙图片
            isset($data['images']) && $this->addCommentImages($data['images']);
            // 是否为图片分享
            $data['is_picture'] = !$this->image()->select()->isEmpty();
            // 更新分享墙记录
            return $this->allowField(true)->save($data);
        });
    }

    /**
     * 添加分享墙图片
     * @param $images
     * @return int
     */
    private function addCommentImages($images)
    {
        $data = array_map(function ($image_id) {
            return [
                'image_id' => $image_id,
                'wxapp_id' => self::$wxapp_id
            ];
        }, $images);
        return $this->image()->saveAll($data);
    }
}