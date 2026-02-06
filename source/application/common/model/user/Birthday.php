<?php

namespace app\common\model\user;

use think\Hook;
use app\common\model\BaseModel;

/**
 * 会员生日
 * Class Birthday
 * @package app\common\model\user
 */
class Birthday extends BaseModel
{
    protected $name = 'user_birthday';
    protected $updateTime = false;

    /**
     * 订单模型初始化
     */
    public static function init()
    {
        parent::init();
        // 监听订单处理事件
        $static = new static;
        Hook::listen('user_birthday', $static);
    }

    /**
     * 获取器：升级条件
     * @param $json
     * @return mixed
     */
    public function getUpgradeAttr($json)
    {
        return json_decode($json, true);
    }


    /**
     * 修改器：升级条件
     * @param $data
     * @return mixed
     */
    public function setUpgradeAttr($data)
    {
        return json_encode($data);
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
    



    /**
     * 验证等级权重是否存在
     * @param int $weight 验证的权重
     * @param int $gradeId 自身的等级ID
     * @return bool
     */
    public static function checkExistByWeight($weight, $gradeId = 0)
    {
        $model = new static;
        $gradeId > 0 && $model->where('grade_id', '<>', (int)$gradeId);
        return $model->where('weight', '=', (int)$weight)
            ->value('grade_id');
    }

}