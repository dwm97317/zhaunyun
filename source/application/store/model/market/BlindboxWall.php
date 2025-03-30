<?php

namespace app\store\model\market;

use app\common\model\market\BlindboxWall as BlindboxWallModel;

/**
 * 分享墙模型
 * Class BlindboxWall
 * @package app\store\model\market
 */
class BlindboxWall extends BlindboxWallModel
{
    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }
    
     /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\store\model\User')->field(['user_id', 'nickName', 'avatarUrl']);
    }
    
     /**
     * 关联包裹表
     * @return \think\model\relation\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo('app\store\model\Package','package_id')->field(['id', 'order_sn', 'express_num']);
    }
    

    
    /**
     * 获取分享墙总数量
     * @return int|string
     */
    public function getBlindboxWallTotal()
    {
        return $this->where(['is_delete' => 0])->count();
    }
    
    /**
     * 获取分享墙列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with(['user', 'blindbox','package','image.file'])
            ->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

}