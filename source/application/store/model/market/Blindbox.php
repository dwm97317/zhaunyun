<?php

namespace app\store\model\market;

use app\common\model\market\Blindbox as BlindboxModel;

/**
 * 盲盒模型
 * Class Blindbox
 * @package app\store\model\market
 */
class Blindbox extends BlindboxModel
{    
    /**
     * 获取盲盒列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with(['coupon'])
            ->where('is_delete', '=', 0)
            ->where('wxapp_id', '=',self::$wxapp_id)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }
    
    /**
     * 添加盲盒
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }
    
    /**
     * 更新盲盒
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data) !== false;
    }
    
    /**
     * 删除记录 (软删除)
     * @return bool|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]) !== false;
    }
    
}
