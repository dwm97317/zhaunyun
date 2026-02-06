<?php

namespace app\api\controller;

use app\api\model\Shelf as ShelfModel;
use app\api\model\ShelfUnit as ShelfUnitModel;
use app\api\model\store\shop\Clerk;

/**
 * 文章控制器
 * Class Article
 * @package app\api\controller
 */
class Warehouse extends Controller
{
    private $user;
    
    /**
      * 构造方法
      * @throws \app\common\exception\BaseException
      * @throws \think\exception\DbException
      */
    public function _initialize()
    {
        parent::_initialize();
        $this->user = $this->getUser();
    }

    /**
     * 货架列表
     * @param int $category_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function shelflists()
    {
        $model = new ShelfModel;
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        $list = $model->getList($clerk['shop_id']);
        return $this->renderSuccess(compact('list'));
    }
    
    /**
     * 货位列表
     * @param int $category_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function shelfunitlists()
    {
        $model = new ShelfUnitModel;
        $param = $this->request->param();
        $list = $model->getList($param['shelf_id']);
        return $this->renderSuccess(compact('list'));
    }
    


}
