<?php

namespace app\store\model;

use think\Cache;
use app\common\model\LineCategory as LineCategoryModel;

/**
 * 运输方式
 * Class LineCategory
 * @package app\store\model
 */
class LineCategory extends LineCategoryModel
{
      public function getList(){
        return $this->with(['image'])
        ->paginate(300,false, [
            'query' => \request()->request()
        ]);
    }
    
    
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
        !array_key_exists('image_id', $data) && $data['image_id'] = 0;
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 删除商品分类
     * @param $categoryId
     * @return bool|int
     */
    public function remove($categoryId)
    {
        return $this->delete();
    }
}
