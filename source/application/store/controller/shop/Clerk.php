<?php
namespace app\store\controller\shop;

use app\store\controller\Controller;

use app\store\model\User;
use app\store\model\store\Shop as ShopModel;
use app\store\model\store\shop\Clerk as ClerkModel;
use app\store\model\Shelf;
use app\store\model\ShelfUnit;
use app\store\model\ShelfUnitItem;

/**
 * 门店店员控制器
 * Class Clerk
 * @package app\store\controller\shop
 */
class Clerk extends Controller
{
    /**
     * 店员列表
     * @param int $shop_id
     * @param string $search
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($shop_id = 0, $search = '')
    {
        // 店员列表
        $model = new ClerkModel;
        $shop_id = $this->store['user']['shop_id'];
        $list = $model->getList(-1, $shop_id, $search);
        // 门店列表
        $shopList = ShopModel::getAllList();
        return $this->fetch('index', compact('list', 'shopList'));
    }
    
        // 货位数据
    public function dataShelfUnit(){
       $list = (new Shelf())->getList([]);
       $query = [];
       $postData = $this->request->param();
       if (!empty($postData['shelf_id'])){
           $query['shelf_id'] = $postData['shelf_id'];
       }
       if (!empty($postData['express_num'])){
           $query['express_num'] = $postData['express_num'];
       }
       if (!empty($postData['search'])){
           $query['shelf_unit_id'] = $postData['search'];
       }
       if (!isset($query['express_num'])){
           $data = (new ShelfUnit())->getWithShelf($query);
           foreach($data as &$v){
               $_map['shelf_unit_id'] = $v['shelf_unit_id'];
               $shelfunititem = (new ShelfUnitItem())->getItemWithPackage($_map);
               if ($shelfunititem){
                   $v['shelfunititem'] = $shelfunititem;
               }
           }
       }else{
            $data = $_map['express_num'] = $query['express_num'];
            $item = (new ShelfUnitItem())->getItemWithPackage($_map);
            $sheft_map = ['shelf_unit_id'=>$item['0']['shelf_unit_id']];
            $dataShelf = (new ShelfUnit())->getWithShelf($sheft_map);
            foreach($dataShelf as &$v){
               if ($item){
                   $v['shelfunititem'] = $item;
               }
           }
           $data = $dataShelf;
       }
      
       return $this->fetch('datashelfunit', compact('data','list'));
    }

    /**
     * 添加店员
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function add()
    {
        $model = new ClerkModel;
        
        if (!$this->request->isAjax()) {
            // 门店列表
            $shopList = ShopModel::getAllList();
            return $this->fetch('add', compact('shopList'));
        }
        if (!isset($this->postData('clerk')['user_id'])){
            return $this->renderError($model->getError() ?: '请选择用户');
        }
        // $clerk_type = $this->postData('clerk')['clerk_type'];
            // dump($this->postData('clerk'));die;
        // 新增记录
        if ($model->add($this->postData('clerk'))) {
            $UserModel = new User();
            $UserModel->setUserType($this->postData('clerk')['user_id'],5);
            return $this->renderSuccess('添加成功', url('shop.clerk/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑店员
     * @param $clerk_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($clerk_id)
    {
        // 店员详情
        $model = ClerkModel::detail($clerk_id);
        if (!$this->request->isAjax()) {
            $model = $model->toArray();
            $model['clerk_authority'] = json_decode($model['clerk_authority'],true);
            
            // 门店列表
            $shopList = ShopModel::getAllList();
            return $this->fetch('edit', compact('model', 'shopList'));
        }
        // 新增记录
        if ($model->edit($this->postData('clerk'))) {
            $UserModel = new User();
            $UserModel->setUserType($model['user_id'],5);
            return $this->renderSuccess('更新成功', url('shop.clerk/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除店员
     * @param $clerk_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($clerk_id)
    {
        // 店员详情
        $UserModel = new User();
        $model = ClerkModel::detail($clerk_id);
        if ($model->setDelete()) {
            $UserModel->setUserType($model['user_id'],0);
            return $this->renderSuccess('删除成功');
        }
         return $this->renderError($model->getError() ?: '删除失败');
    }

}