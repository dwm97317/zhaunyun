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
    public static function getAll()
    {
        $model = new static;
        return $model->order(['sort' => 'asc'])->select();
    }

    /**
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->order(['sort' => 'asc'])
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

}
