<?php

namespace app\store\model;

use app\common\model\Barcode as BarcodeModel;

use think\Db;
use think\Request;
class Barcode extends BarcodeModel
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
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getsearchList($query)
    { 
        if(isset($query['search'])){
            return $this->useGlobalScope(false)
            ->where('goods_name|goods_name_en|goods_name_jp','like','%'.$query['search'].'%')
            ->paginate(15, false, [
                'query' => Request::instance()->request()
            ]);
        }
        return $this
        ->useGlobalScope(false)
        ->paginate(15, false, [
            'query' => Request::instance()->request()
        ]);
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