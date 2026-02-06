<?php

namespace app\common\model;

use think\Request;
use app\common\library\express\Kuaidi100;

/**
 * 物流公司模型
 * Class Express
 * @package app\common\model
 */
class Barcode extends BaseModel
{
    protected $name = 'barcode';

    /**
     * 获取全部
     * @return mixed
     */
    public static function getAll()
    {
        $model = new static;
        return $model::useGlobalScope(false)->order(['sort' => 'asc'])->select();
    }

    /**
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->useGlobalScope(false)
            ->paginate(15, false, [
                'query' => Request::instance()->request()
            ]);
    }
    
    /**
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getTypeList($type)
    {
        return $this
            ->where('type','<>',$type)
            ->order(['sort' => 'asc'])
            ->select();
    }

    /**
     * 物流公司详情
     * @param $express_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($express_id)
    {
        $model = static::useGlobalScope(false);
        return $model->find($express_id);
    }

}
