<?php

namespace app\admin\model\city;

use app\common\model\Region as RegionModel;

/**
 * 地区模型
 * Class Region
 * @package app\store\model
 */
class Region extends RegionModel
{
    public function getList($param){
        return self::getCacheTree();
    }
    
    public function getChildList($param){
        return $this->where('pid',$param['id'])->select();
    }
    
    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        return $this->allowField(true)->save($data);
    }
    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data) !== false;
    }
}
