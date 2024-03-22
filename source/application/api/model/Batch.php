<?php

namespace app\api\model;

use app\common\model\Batch as BatchModel;

/**
 * 商家门店模型
 * Class Shop
 * @package app\store\model\store
 */
class Batch extends BatchModel
{
    /**
     * 获取列表数据
     * @param array $param
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        // 查询列表数据
        return $this
            ->where('is_over',0)
            ->where('is_delete',0)
            ->select();
    }
}