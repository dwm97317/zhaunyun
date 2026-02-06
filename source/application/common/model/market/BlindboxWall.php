<?php

namespace app\common\model\market;

use think\Db;
use  app\common\model\BaseModel;
use app\common\model\market\BlindboxWallImage;
/**
 * 盲盒分享墙
 * Class BlindboxWall
 * @package app\common\model\market
 */
class BlindboxWall extends BaseModel
{
    protected $name = 'blindbox_wall';

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
        return $this->hasMany('BlindboxWallImage','wall_id','wall_id')->order(['id' => 'asc']);
    }

    /**
     * 分享墙详情
     * @param $wall_id
     * @return Comment|null
     * @throws \think\exception\DbException
     */
    public static function detail($wall_id)
    {
        return self::get($wall_id, ['user','image.file']);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool
     */
    public function addWall($data)
    {
        return $this->transaction(function () use ($data) {
            $iamge = $data['uploaded'];
            unset($data['uploaded']);
            unset($data['token']);
            $data['create_time'] = time(); 
            $data['is_picture'] = isset($iamge)?1:0;
            $wall_id = $this->insertGetId($data);
            // 删除分享墙图片
            // 添加分享墙图片
            isset($iamge) && $this->saveAllImages($wall_id,$iamge);
            // 是否为图片分享
            
            // 更新分享墙记录
            return true;
        });
    }
    
    /**
     * 更新记录
     * @param $data
     * @return bool
     */
    public function editWall($data)
    {
        return $this->transaction(function () use ($data) {
            $iamge = $data['images'];
            unset($data['images']);
            $data['update_time'] = time(); 
            $wall_id = $this->save($data);
            $model = new BlindboxWallImage;
            isset($iamge) && count($iamge)>0  && $model->where('wall_id',$wall_id)->delete();
            isset($iamge) && $this->saveAllImages($wall_id,$iamge);
            return true;
        });
    }


    /**
     * 记录分享图片
     * @param $commentList
     * @param $formData
     * @return bool
     * @throws \Exception
     */
    private function saveAllImages($commentId, $formData)
    {
        // 生成评价图片数据
        $imageData = [];
        
        foreach ($formData as $imageId) {
            $imageData[] = [
                'wall_id' => $commentId,
                'image_id' => $imageId,
                'wxapp_id' => self::$wxapp_id
            ];
        }
       
        $model = new BlindboxWallImage;
        return !empty($imageData) && $model->saveAll($imageData);
    }
}