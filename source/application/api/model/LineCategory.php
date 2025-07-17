<?php
namespace app\api\model;

use app\common\model\LineCategory as LineCategoryModel;

/**
 * 运输方式模型
 * Class Category
 * @package app\common\model
 */
class LineCategory extends LineCategoryModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'update_time'
    ];

    public  function getList() {
        return $this->field('category_id,name,desc')->where('status',0)->select();
    }
    
    // 全部分类
    public function getCategoryAll(){
        return $this->field('category_id,name,desc')->where('status',0)->select();
    }
    
    // 全部分类
    public function getParentCategory(){
        return $this->field('category_id,name,desc')->where('status',0)->select();
    }
}
