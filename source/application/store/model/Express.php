<?php

namespace app\store\model;

use app\common\model\Express as ExpressModel;
use think\Db;
use app\store\model\Inpack;

class Express extends ExpressModel
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
    
    public function copy(){
        $data = getFileData('ditch/china.all.json');
        $express=[];
        foreach ($data as $key =>$value) {
            $express[$key]['express_name'] = $value['_name_zh-cn'];
            $express[$key]['express_code'] = $value['key'];
            $express[$key]['type'] = 0;
            $express[$key]['create_time'] = time();
            $express[$key]['update_time'] = time();
            $express[$key]['wxapp_id'] =self::$wxapp_id;
        }
        if ($this->insertAll($express)) {
            return true;
        }
        return false;
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
        // 判断当前物流公司是否已被订单使用
        $Order = new Inpack;
        if ($orderCount = $Order->where(['t_number' => $this['express_id'],'is_delete'=>0])->count()) {
            $this->error = '当前物流公司已被' . $orderCount . '个订单使用，不允许删除';
            return false;
        }
        return $this->delete();
    }

}