<?php

namespace app\common\model\user;

use app\common\model\BaseModel;

/**
 * 用户会员等级变更记录模型
 * Class UserGradeOrder
 * @package app\common\model\user
 */
class UserGradeOrder extends BaseModel
{
    protected $name = 'user_grade_order';


    /**
     * 关联会员记录表
     * @return \think\model\relation\BelongsTo
     */
    public function grade()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\user\\Grade");
    }
    
    
    /**
     * 关联会员记录表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\User");
    }

    /**
     * 新增记录
     * @param $data
     */
    public static function add($data)
    {
        $static = new static;
        $static->save(array_merge(['wxapp_id' => $static::$wxapp_id], $data));
    }
    
    
    /**
     * 会员等级详情
     * @param $grade_id
     * @param array $with
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($grade_id, $with = [])
    {
        return static::get($grade_id, $with);
    }

}