<?php

namespace app\common\model\market;

use think\Request;
use app\common\model\BaseModel;
/**
 * 盲盒计划抽奖日志
 * Class Blindbox
 * @package app\common\model\market
 */
class BlindboxLog extends BaseModel
{
    protected $name = 'blindbox_log';
  
    /**
     * 盲盒计划日志详情
     * @param $id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        return static::get($id);
    }
    
    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User');
    }
}
