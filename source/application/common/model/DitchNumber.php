<?php

namespace app\common\model;

use think\Request;

/**
 * 物流公司模型
 * Class Express
 * @package app\common\model
 */
class DitchNumber extends BaseModel
{
    protected $name = 'ditch_number';

    /**
     * 获取全部
     * @return mixed
     */
    public static function getAlllist()
    {
        $model = new static;
        return $model->where('status',0)->limit(100)->select();
    }

    /**
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with("ditch")
            ->paginate(15, false, [
                'query' => Request::instance()->request()
            ]);
    }
    
    /**
     * 物流公司详情
     * @param $express_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($ditch_id)
    {
        return self::get($ditch_id);
    }
    
    public function ditch(){
        return $this->belongsTo('app\common\model\Ditch','ditch_id','ditch_id');
    }

}
