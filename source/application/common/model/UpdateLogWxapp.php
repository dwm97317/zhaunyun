<?php

namespace app\common\model;

use think\Request;
/**
 * 更新日志
 * Class UpdateLog
 * @package app\common\model
 */
class UpdateLogWxapp extends BaseModel
{
    protected $name = 'updatelog_wxapp';
  
     /**
     * 新增记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['create_time'] =time();
        $data['update_time'] =time();
        $data['wxapp_id'] = self::$wxapp_id;
        return self::useGlobalScope(false)->insert($data);
    }
    


}
