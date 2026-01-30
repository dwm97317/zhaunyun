<?php

namespace app\store\model;

use app\common\model\Ditch as DitchModel;
use think\Db;
class Ditch extends DitchModel
{
    
    

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        $data['create_time'] = time();
        $res = Db::table($this->getTable())->insert($data);
        return $res;
    }
    
    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        // Use Db directly to bypass any potential stale Model schema/cache issues
        $res = Db::table($this->getTable())
            ->where($this->getPk(), $this[$this->getPk()])
            ->update($data);
        
        // In ThinkPHP update() returns number of rows affected. 
        // 0 means nothing changed, which is success in this context.
        return $res !== false;
    }

    /**
     * 删除记录
     * @return bool|int
     */
    public function remove()
    {
        // 判断当前物流公司是否已被订单使用
        // $Order = new Order;
        // if ($orderCount = $Order->where(['ditch_id' => $this['ditch_id']])->count()) {
        //     $this->error = '当前物流公司已被' . $orderCount . '个订单使用，不允许删除';
        //     return false;
        // }
        return $this->delete();
    }

}