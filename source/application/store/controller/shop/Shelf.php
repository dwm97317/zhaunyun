<?php
namespace app\store\controller\shop;

use app\store\controller\Controller;

use app\store\model\User;
use app\store\model\store\Shop as ShopModel;
use app\store\model\store\shop\Clerk as ClerkModel;
use app\store\model\Shelf as ShelfModel;
use app\store\model\ShelfUnit as ShelfUnitModel;
use app\store\model\ShelfUnit;
use app\store\model\ShelfUnitItem;
use app\common\library\FileZip;
use app\common\model\setting;

/**
 * 门店店员控制器
 * Class Clerk
 * @package app\store\controller\shop
 */
class Shelf extends Controller
{
    public function index(){
        $map['ware_no'] = $this->store['user']['shop_id'];
        $list = (new ShelfModel())->getList($map);
        foreach ($list as &$value) {
            $value['num'] = (new ShelfUnit())->where(['shelf_id'=>$value['id']])->count();
        }
        return $this->fetch('index', compact('list'));
    }
    
    /**
     * 新增货架
     */
    public function add(){
      $shopList = ShopModel::getAllList();
      if (!$this->request->isAjax()) {
        return $this->fetch('add',compact('shopList'));
      }
   
      // 新增记录
      $model = new ShelfModel();
      if ($model->add($this->postData('shelf'))) {
          return $this->renderSuccess('添加成功',url('/store/shop.shelf/index'));
      }
      return $this->renderError($model->getError() ?: '添加失败');
    }
    
    /**
     * 删除货架 
     */
    public function shelfdelete($id){
         $model = (new ShelfModel())->details($id);
         if (!$model->setDelete($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功',url('/store/shop.shelf/index'));
    } 
    
    
    public function edit($id){
        // 模板详情
        $model = (new ShelfModel())->details($id);
        if (!$this->request->isAjax()) {
            $shopList = ShopModel::getAllList();
            return $this->fetch('edit', compact('model','shopList'));
        }
        // 更新记录
        if ($model->edit($this->postData('shelf'))) {
            return $this->renderSuccess('更新成功', url('/store/shop.shelf/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    
    public function editshelfunit($shelf_unit_id){
        // 模板详情
        $model = (new ShelfUnitModel())->details($shelf_unit_id);
        // dump($model->toArray());die;
        if (!$this->request->isAjax()) {
            $shelfList = (new ShelfModel)->getAllList([]);
            return $this->fetch('shelfunitedit', compact('model','shelfList'));
        }
        // 更新记录
        if ($model->edit($this->postData('shelf'))) {
            return $this->renderSuccess('更新成功', url('/store/shop.shelf/datashelfunit'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    

    // 货位数据
    public function dataShelfUnit(){
       $map['ware_no'] = $this->store['user']['shop_id'];
       $set = Setting::detail('store')['values'];
       $list = (new ShelfModel())->getAllList($map);
       $shelf = [];
       if(count($list)>0){
           foreach ($list as $key => $item){
               $shelf[$key] = $item['id'];
           }
       }
       $data=[];$_map=[];
       $postData = $this->request->param();
           
      if (empty($postData['express_num'])){
           empty($postData['shelf_id']) && $postData['shelf_ids'] = $shelf;
           $data = (new ShelfUnit())->getWithShelf($postData);
        //   dump($data->toArray());die;
           foreach($data as &$v){
               $_map['shelf_unit_id'] = $v['shelf_unit_id'];
               $shelfunititem = (new ShelfUnitItem())->getItemWithPackage($_map);
               if ($shelfunititem){
                   $v['shelfunititem'] = $shelfunititem;
               }
           }
      }else{
           !empty($postData['express_num']) && $_map['express_num'] = $postData['express_num'];
            $item = (new ShelfUnitItem())->getItemWithPackage($_map);
            foreach ($item as $k =>$v){
                $shelf_unit_id[] = $v['shelf_unit_id'];
            }
           
            if(!empty($item)){
                $sheft_map = ['shelf_unit_ids'=>$shelf_unit_id];
                $dataShelf = (new ShelfUnit())->getWithShelf($sheft_map);
                foreach($dataShelf as &$v){
                  if ($item){
                      $v['shelfunititem'] = $item;
                 }
               }  
            }
            $data = $dataShelf;
      }
       return $this->fetch('datashelfunit', compact('data','list','set'));
    }
    
    public function reset(){
         $ids = $this->postData('selectId');
         $model = (new ShelfUnit()); 
         $res = $model->resetCode($ids);
         if ($res){
             return $this->renderSuccess('生成成功');
         }
         return $this->renderError('生成失败');
    }
    
    public function download(){
         $ids = $this->postData('selectId');
         $model = (new ShelfUnit()); 
         $list = $model->where('shelf_unit_id','in',$ids)->select();
         $res = FileZip::init($list);
         return $this->renderSuccess($res);
    }
}