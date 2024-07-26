<?php

namespace app\store\model;

use app\common\model\DitchNumber as DitchNumberModel;
use think\Db;

class DitchNumber extends DitchNumberModel
{
    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }
    
    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data);
    }

    /**
     * 删除记录
     * @return bool|int
     */
    public function remove()
    {
        return $this->delete();
    }

}