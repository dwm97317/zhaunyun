<?php
namespace app\api\model;
use app\common\model\ShelfUnit as ShelfUnitModel;
use think\Db;
use traits\model\SoftDelete;

/**
 * 货位管理
 * Class Order
 * @package app\store\model
 */
class ShelfUnit extends ShelfUnitModel
{

    protected $createTime = null;
    protected $updateTime = null;
    
    // 关联货架
    public function Shelf(){
      return $this->belongsTo('app\api\model\Shelf','shelf_id')->field('id,shelf_no,shelf_name'); 
    }
    
    public function getList($shelf_id){
      return $this
        ->where('shelf_id',$shelf_id)
        ->select();
    }
    
    
    public function getShelfUnit($id){
        $shelf = (new ShelfUnit())->with('shelf')->where(['shelf_unit_id'=>$id])->find();
        if (!$shelf){
            return '';
        }
        if ($shelf['shelf']){
            return $shelf['shelf']['shelf_name'].$shelf['shelf']['shelf_no'].$shelf['shelf_unit_floor'].'层'.$shelf['shelf_unit_no']."号";
        }else{
            return '未知货架'.$shelf['shelf']['shelf_unit_floor'].'层'.$shelf['shelf_unit_no']."号";
        }
    }
}
