<?php

namespace app\store\model;

use app\common\model\Track as TrackModel;

/**
 * 商家门店模型
 * Class Shop
 * @package app\store\model\store
 */
class Track extends TrackModel
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
        return $this->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
    
    
    /**
     * 获取列表数据
     * @param array $param
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAllList()
    {
        // 查询列表数据
        return $this->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        return $this->allowField(true)->save($data) !== false;
    }


    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function validateForm($data)
    {
        if (!isset($data['track_name']) || empty($data['track_name'])) {
            return false;
        }
        if (!isset($data['track_content']) || empty($data['track_content'])) {
            return false;
        }
        return true;
    }

}