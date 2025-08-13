<?php

namespace app\store\model;

use app\common\model\ConsumablesLog as ConsumablesLogModel; 

/**
 * 耗材管理
 * Class Consumables
 * @package app\store\model
 */
class ConsumablesLog extends ConsumablesLogModel
{
    public function getList($query=[]){
        return $this->setListQueryWhere($query)
        ->alias('a')
        ->with(['inpack','consumables'])
        ->order('create_time','asc')
        ->paginate(10,false,[
            'query'=>\request()->request()
        ]);
    }
    
    public function setListQueryWhere($query){
        !empty($query['con_id']) && $this->where('con_id',$query['con_id']);
        return $this;
    }
    
    /**
     * 新增记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 删除记录
     * @return int
     */
    public function remove() {
        return $this->delete();
    }

}
