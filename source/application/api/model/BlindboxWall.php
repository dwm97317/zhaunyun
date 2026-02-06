<?php

namespace app\api\model;

use app\common\model\market\BlindboxWall as BlindboxWallModel;
/**
 * 分享墙管理
 * Class Blindbox
 * @package app\store\controller\market
 */
class BlindboxWall extends BlindboxWallModel
{
    /**
     * 获取分享墙列表
     * @param int $category_id
     * @param int $limit
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with(['image.file','user'])
            ->where('status', '=', 1)
            ->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
    
    
}