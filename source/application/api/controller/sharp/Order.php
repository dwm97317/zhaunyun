<?php
namespace app\api\controller\sharp;
use app\api\controller\Controller;
use app\api\model\sharing\SharingOrder;
use app\api\model\sharing\SharingOrderItem;
use app\api\model\sharing\SharingOrderAddress;
use app\api\model\Line;
use app\api\model\Package;
use app\api\model\PackageItem;
use app\common\model\Country;
use app\api\model\User as UserModel;
use app\api\service\sharing\SharingOrder as SharingOrderService;
use app\store\model\Inpack;
use app\api\model\sharing\Setting;
use app\api\model\sharing\SharingTrUser;
use app\api\model\Category;
use app\api\model\UserAddress;
use app\common\model\Setting as SettingModel;
/**
 * 拼团控制器
 * Class Article
 * @package app\api\controller
 */
class Order extends Controller
{
   
     // 创建拼团订单
     public function create(){
        $form = $this->request->param();
        // 当前用户信息
        $userInfo = $this->getUser();
        $model = (new SharingOrder());
        $form['member_id'] = $userInfo['user_id'];
        if ($model->created($form)){
              return $this->renderSuccess('拼团已成功发起');
        }
        return $this->renderError($model->getError()??'操作失败');
     }
     
     // 拼团订单管理列表
     public function managelist(){
        $query = $this->request->param();
        // 当前用户信息
        $userInfo = $this->getUser();
        $query['member_id'] = $userInfo['user_id'];
        $model = (new SharingOrder());
        $query['status'] = $this->mapStatus($query['status']);
        $list = $model->getList($query);

        $shareService = (new SharingOrderService());
        // 判断是否可打包
        $list = $shareService->checkIsInPack($list);
        return $this->renderSuccess(compact('list'));
     }
     
     // 解散拼团活动
     public function dissolution(){
        $id = $this->request->param('id');
        $userInfo = $this->getUser();
        $model = (new SharingOrder())->detail($id);
        if ($model->dissolution($userInfo)){
            return $this->renderSuccess('拼团成功取消');
        }
        return $this->renderError($model->getError()??'操作失败');
     }
     
     public function list(){
        $query = $this->request->param();
        // 当前用户信息
        $userInfo = $this->getUser();
        $query['member_id'] = $userInfo['user_id'];
        // 【1 开团中 2 待开团 3 待打包 4 待付款 5 待发货 6 已发货 7 已完成 8 已取消】
        if ($query['type']=='sharing'){
            $query['status'] = 1;
        }
        if ($query['type']=='received'){
            $query['status'] = 6;
        }
        if ($query['type']=='no_pay'){
            $query['status'] = 4;
        }
        if ($query['type']=='no_send'){
            $query['status'] = 5;
        }
        if ($query['type']=='complete'){
            $query['status'] = 7;
        }
        $model = (new SharingOrderItem());
        $list = $model->getList($query);
        foreach ($list as $key => $v){
            $address = (new SharingOrderAddress())->where(['order_id'=>$v['order_item_id'],'is_head'=>0])->find();
            $list[$key]['address'] = $address['country'].$address['province'].$address['city'].$address['region'];
        }
        return $this->renderSuccess(compact('list'));
     }
     
     // 获取热门城市
     public function getHotCountry(){
        $model = (new SharingOrder());
        $list = $model->getHotCountryIds();
        
        $data = (new Country())->where('id','in',$list)->select();
        return $this->renderSuccess(compact('data'));
     }
     
     // 拼团广场数据
     public function center(){
        $query = $this->request->param();
        if ($query['type']=='recommend'){
            $query['is_recommend'] = 1;
        }
        // if ($query['type']=='hot'){
        //     $order_ids = (new SharingOrderItem())->getHotOrderId();
        //     $query['order_ids'] = $order_ids;
        // }
        // 当前用户信息
        $userInfo = $this->getUser();
        $query['user_id'] = $userInfo['user_id'];
        $model = (new SharingOrder());
        $list = $model->getList($query);
        $shareService = (new SharingOrderService());
         // 获取已拼团包裹重量
         $list = $shareService->getPackageWeight($list);
         $list = $shareService->getMainAddressInfo($list);
        return $this->renderSuccess(compact('list'));
     }
     
     // 取消该拼团单
     public function cancle(){
        $id = $this->request->param('id');
        $userInfo = $this->getUser();
        $model = (new SharingOrderItem())->detail($id);
        if ($model->cancle($userInfo)){
            return $this->renderSuccess('拼团成功取消');
        }
        return $this->renderError($model->getError()??'操作失败');
     }
     
     // 拼单详情
     public function detail(){
         $id = $this->request->param('id');
         $item_id = $this->request->param('item_id');
         $userInfo = $this->getUser();
         $model = (new SharingOrder())->detail($id);
         $item = (new SharingOrderItem())->find($item_id);
         $is_publiser = $userInfo['user_id']==$model['member_id']?true:false;
         $address_where['order_id'] = $item['order_item_id'];
         $is_publiser == true?$address_where['is_head'] = 1:$address_where['is_head'] = 0;
        //  dump($model);die;
         if ($model['address_id']){
             $model['address'] = (new SharingOrderAddress())->where($address_where)->find();
         }
         if ($model['line_id']){
             $model['line'] = (new Line())->find($model['line_id']);
         }
         return $this->renderSuccess(compact('model'));
     }
     
     // 拼单详情
     public function detail_join(){
         $id = $this->request->param('id');
         $userInfo = $this->getUser();
         $model = (new SharingOrder())->detail($id);
         $inpack = new Inpack();
         $Package = new Package();
         $userModel = new UserModel();
         $model['leader'] =$userModel->find($model['member_id']);

         $orderItem = (new SharingOrderItem())->where(['order_id'=> $id])->where('type',0)->select();
         $orderItem2 = (new SharingOrderItem())->where(['order_id'=> $id])->where('type',1)->select();
         
         // 从 sharing_tr_user 表获取用户列表
         $sharingTrUserList = (new SharingTrUser())->where(['order_id' => $model['order_id']])->with(['user'])->select();
         $userList = [];
         foreach($sharingTrUserList as $key => $val){
             if($val['user']){
                 $userList[$key] = $val['user'];
             }
         }
         if ($model['address_id']){
             $address_where['order_id'] = $model['order_id'];
             $address_where['is_head'] = 1;
             $model['address'] = (new SharingOrderAddress())->where($address_where)->find();
         }
         $SharingService = (new SharingOrderService());
         $setting = Setting::getItem('sharp');
         $model['setting'] =  htmlspecialchars_decode($setting['describe']);
         //根据设置来决定使用什么作为百分比
         if(isset($setting['sharepredict']) && $setting['sharepredict']==10){
            $countpackageid = (new SharingOrderItem())->where(['order_id' => $id])->where('type',0)->column('package_id');
            $countinpackid = (new SharingOrderItem())->where(['order_id' => $id])->where('type',1)->column('package_id');
            $countweight = $Package->whereIn('id', $countpackageid)->sum('weight');
            $countinpackweight = $inpack->whereIn('id', $countinpackid)->sum('cale_weight');
            $model['percent'] = (round(($countweight+$countinpackweight)/$model['predict_weight'],4))*100;   // 使用重量作为进度标准
         }else{
            $count = count($userList);
            $model['percent'] = (round($count/$model['max_people'],2))*100; // 使用人数作为进度标准
         }
         $model['join_user_list'] = $userList;
         
         // 判断当前用户是否已加入拼团
         $sharingTrUser = new SharingTrUser();
         $model['is_joined'] = $sharingTrUser->isJoined($userInfo['user_id'], $model['order_id']);
         
         if ($model['line_id']){
             $model['line'] = (new Line())->find($model['line_id']);
         }
         return $this->renderSuccess(compact('model'));
     }
     
     // 加入拼团
     public function join(){
         $id = $this->request->param('id');
         $userInfo = $this->getUser();
         
         // 获取拼团信息
         $order = (new SharingOrder())->detail($id);
         if (!$order) {
             return $this->renderError('拼团不存在');
         }
         
         // 检查拼团设置
         $setting = Setting::getItem('sharp');
         if($setting['is_open'] == 0) {
             return $this->renderError('暂时无法加入拼团');
         }
         
         // 检查拼团状态
         if ($order['status']['value'] != 1) {
             return $this->renderError('该拼团已结束或已取消，无法加入');
         }
         
         // 检查是否已加入
         $sharingTrUser = new SharingTrUser();
         if ($sharingTrUser->isJoined($userInfo['user_id'], $order['order_id'])) {
             return $this->renderError('您已经加入该拼团了');
         }
         
         // 检查拼团是否已满（如果按人数计算）
         if (!isset($setting['sharepredict']) || $setting['sharepredict'] != 10) {
             $currentCount = (new SharingOrderItem())->where(['order_id' => $order['order_id']])->where('status', '<', 9)->count();
             if ($currentCount >= $order['max_people']) {
                 return $this->renderError('拼团人数已满');
             }
         }
         
         // 加入拼团
         $data = [
             'user_id' => $userInfo['user_id'],
             'order_id' => $order['order_id'],
             'status' => 1,
         ];
         
         if ($sharingTrUser->join($data)) {
             return $this->renderSuccess('加入拼团成功');
         }
         
         return $this->renderError($sharingTrUser->getError() ?: '加入拼团失败');
     }
     
     // 退出拼团
     public function quit(){
         $id = $this->request->param('id');
         $userInfo = $this->getUser();
         
         // 获取拼团信息
         $order = (new SharingOrder())->detail($id);
         if (!$order) {
             return $this->renderError('拼团不存在');
         }
         
         // 检查是否已加入
         $sharingTrUser = new SharingTrUser();
         if (!$sharingTrUser->isJoined($userInfo['user_id'], $order['order_id'])) {
             return $this->renderError('您尚未加入该拼团');
         }
         
         // 开启事务
         $sharingTrUser->startTrans();
         try {
             // 1. 从 sharing_tr_user 表删除用户记录
             if (!$sharingTrUser->quit($userInfo['user_id'], $order['order_id'])) {
                 throw new \Exception($sharingTrUser->getError() ?: '退出拼团失败');
             }
             
             // 2. 查找该用户在 SharingOrderItem 中的所有记录（通过包裹/订单的 member_id）
             $orderItemList = (new SharingOrderItem())->where(['order_id' => $order['order_id']])->select();
             $orderItemIds = []; // 需要删除的 SharingOrderItem ID
             $updatePackageIds = []; // 需要更新 share_id 的包裹ID
             $updateInpackIds = []; // 需要更新 share_id 的订单ID
             
             foreach ($orderItemList as $item) {
                 if ($item['type'] == 0) {
                     // Package 类型
                     $package = (new Package())->where('id', $item['package_id'])->find();
                     if ($package && $package['member_id'] == $userInfo['user_id']) {
                         $orderItemIds[] = $item['order_item_id'];
                         $updatePackageIds[] = $item['package_id'];
                     }
                 } else {
                     // Inpack 类型
                     $inpack = (new Inpack())->where('id', $item['package_id'])->find();
                     if ($inpack && $inpack['member_id'] == $userInfo['user_id']) {
                         $orderItemIds[] = $item['order_item_id'];
                         $updateInpackIds[] = $item['package_id'];
                     }
                 }
             }
             
             // 3. 删除该用户的 SharingOrderItem 记录
             if (!empty($orderItemIds)) {
                 (new SharingOrderItem())->where('order_item_id', 'in', $orderItemIds)->delete();
             }
             
             // 4. 更新包裹/订单的 share_id 字段（如果存在）
             
             // 更新包裹的 share_id 字段，清除拼团ID
             if (!empty($updatePackageIds)) {
                 (new Package())->where('id', 'in', $updatePackageIds)->update(['share_id' => null]);
             }
             
             // 更新订单的 share_id 字段，清除拼团ID
             if (!empty($updateInpackIds)) {
                 (new Inpack())->where('id', 'in', $updateInpackIds)->update(['share_id' => null]);
             }
             
             $sharingTrUser->commit();
             return $this->renderSuccess('退出拼团成功');
             
         } catch (\Exception $e) {
             $sharingTrUser->rollback();
             return $this->renderError($e->getMessage());
         }
     }
     
     // 加入包裹到拼团
     public function joinPackage(){
         $id = $this->request->param('id'); // 拼团ID
         $packageIds = $this->request->param('package_ids'); // 包裹ID，逗号分隔
         $userInfo = $this->getUser();
         
         // 验证参数
         if (!$id) {
             return $this->renderError('拼团ID不能为空');
         }
         if (!$packageIds) {
             return $this->renderError('请选择要加入的包裹');
         }
         
         // 获取拼团信息
         $order = (new SharingOrder())->detail($id);
         if (!$order) {
             return $this->renderError('拼团不存在');
         }
         
         // 检查拼团设置
         $setting = Setting::getItem('sharp');
         if($setting['is_open'] == 0) {
             return $this->renderError('暂时无法加入拼团');
         }
         
         // 检查拼团状态（只有开团中的拼团才能加入）
         if ($order['status']['value'] != 1) {
             return $this->renderError('该拼团已结束或已取消，无法加入');
         }
         
         // 解析包裹ID
         $packageIdArray = explode(',', $packageIds);
         $packageIdArray = array_filter(array_map('intval', $packageIdArray));
         if (empty($packageIdArray)) {
             return $this->renderError('请选择有效的包裹');
         }
         
         // 开启事务
         $SharingOrderItem = new SharingOrderItem();
         $SharingOrderItem->startTrans();
         try {
             $Package = new Package();
             $insertData = [];
             
             // 验证每个包裹
             foreach ($packageIdArray as $packageId) {
                 // 验证包裹是否存在且属于当前用户
                 $package = $Package->where('id', $packageId)->find();
                 if (!$package) {
                     throw new \Exception('包裹不存在');
                 }
                 if ($package['member_id'] != $userInfo['user_id']) {
                     throw new \Exception('包裹不属于您，无法加入拼团');
                 }
                 
                 // 检查包裹是否已经加入拼团（包括当前拼团）
                 $existingItem = $SharingOrderItem->where('package_id', $packageId)
                     ->where('type', 0)
                     ->where('status', '<', 9)
                     ->find();
                 if ($existingItem) {
                     if ($existingItem['order_id'] == $order['order_id']) {
                         throw new \Exception('包裹已加入该拼团，请勿重复添加');
                     } else {
                         throw new \Exception('包裹已加入其他拼团，请勿重复添加');
                     }
                 }
                 
                 // 准备插入数据
                 $insertData[] = [
                     'order_id' => $order['order_id'],
                     'package_id' => $packageId,
                     'status' => $setting['is_shenhe'] == 1 ? 2 : 1, // 如果需要审核则为2，否则为1
                     'type' => 0, // 0表示Package，1表示Inpack
                     'wxapp_id' => $this->request->param('wxapp_id'),
                     'create_time' => time(),
                     'update_time' => time(),
                 ];
             }
             
             // 批量插入
             if (!empty($insertData)) {
                 $SharingOrderItem->insertAll($insertData);
                 
                 // 更新包裹的 share_id 字段，存储拼团ID
                 $Package->where('id', 'in', $packageIdArray)->update(['share_id' => $order['order_id']]);
             }
             
             $SharingOrderItem->commit();
             return $this->renderSuccess('加入拼团成功');
             
         } catch (\Exception $e) {
             $SharingOrderItem->rollback();
             return $this->renderError($e->getMessage());
         }
     }
     
     // 申请加入拼团
     public function applytopintuan(){
         $param = $this->request->param();
         $SharingOrderItem = new SharingOrderItem();
         $inpack = new Inpack();
         $setting = Setting::getItem('sharp');
         if($setting['is_open'] == 0 ){
             return $this->renderError('暂时无法加入拼团');  
         }
         $shareoder = $SharingOrderItem->where('package_id',$param['id'])->where('status','<',9)->find();
         if(!empty($shareoder)){
             return $this->renderError('请勿重复加入该拼团');  
         }
         $data = [
           'order_id' => $param['pin_id'],
           'package_id' => $param['id'],
           'create_time' => time(),
           'update_time' => time(),
           'status' => $setting['is_shenhe']==1?2:1,
           'type'=>1,
           'wxapp_id' => $param['wxapp_id'],
         ];
         if($SharingOrderItem->insert($data)){
              $inpack->where('id',$param['id'])->update(['inpack_type' => 1 ]);
              return $this->renderSuccess('加入拼团成功');
         }
         return $this->renderError('暂时无法加入拼团');  
     }
     
     // 变更地址
     public function addressUpdate(){
        $id = $this->request->param('id');
        $userInfo = $this->getUser();
        $address = $this->request->param('address_id');
        $order = (new SharingOrder())->find($id);
        $order_item = (new SharingOrderItem())->where(['order_id'=>$order['order_id'],'user_id'=>$userInfo['user_id']])->find();
        $addressInfo = (new SharingOrderAddress())->where(['order_id'=>$order_item['order_item_id']])->find();
        if(!$order->updateAddress($addressInfo,$address)){
            return $this->renderError($order->getError()??'操作失败');
        };
        return $this->renderSuccess('地址更新成功');
     }
     
     // 审核列表
     public function verifylist(){
        $query = $this->request->param();
        $ShareOrderItem = (new SharingOrderItem());
        $where['status'] = [1,2,3,4,5,6];
        if (isset($query['type']) && $query['type']=='verify'){
            $where['status'] = [2];
        }
        if (isset($query['type']) && $query['type']=='reject'){
            $where['status'] = [9];
        }
        if (isset($query['type']) && $query['type']=='ok'){
            $where['status'] = [1,3,4,5,6,7];
        }
        $shareOrder = (new SharingOrder());
        $userInfo = $this->getUser();
        // 获取我发布的拼团ID
  
        $orderIds = $shareOrder->getMyOrderIds($userInfo['user_id'],$where);
            //  dump($orderIds);die;
        $orderIdsArr = array_column($orderIds->toArray(),'order_id');
        $list = $ShareOrderItem->getVeriftListWithPack($orderIdsArr,$where);
                        
        foreach ($list as $key => $v){
            $packageItem = (new PackageItem())->where(['order_id'=>$v['package']['id']])->select();
            $packageName = array_column($packageItem->toArray(),'class_name');
            $list[$key]['name'] = implode('-',$packageName);
        }
    
        return $this->renderSuccess(compact('list'));
     }
     
     // 修改审核状态
     public function verifyupdate(){
        $id = $this->request->param('id');
        $param = $this->request->param();
        $model = (new SharingOrderItem());
        $detail = $model->detail($id);
        // dump($detail);die;
        if ($detail->modifyUpdata($param)){
            return $this->renderSuccess('审核成功'); 
        }
        return $this->renderError($order->getError()??'操作失败');
     }
     
     // 获取拼团选择包裹列表
     public function getPackageList(){
         $this->user = $this->getUser();
         $field = 'id,country_id,order_sn,express_num,weight,storage_id,created_time,remark,source';
         
         $where[] = ['is_delete','=',0];
         $where[] = ['is_take','=',2];
         $where[] = ['member_id','=',$this->user['user_id']];
         $where[] = ['status','in',[2,3,4,7]];
         
         $param = $this->request->param();
         if(isset($param['usermark'])){
            $where[] = ['usermark','=',$param['usermark']];
         }
         
         $PackageModel = new Package();
         $Category = new Category();
         
         if(isset($param['category_id']) && $param['category_id']){
             $catelist = $Category->getSonCategoryAll($param['category_id']);
             $paramwhere['category_id'] = $catelist;
             $paramwhere['is_delete'] = 0;
             $paramwhere['is_take'] = 2;
             $paramwhere['member_id'] = $this->user['user_id'];
             $paramwhere['status'] = [2,3,4,7];
             
             // 使用 setSearchQuery 并添加 share_id 为 0 的条件
             $data = (new Package())->setSearchQuery($paramwhere)
                 ->where('a.share_id', 0)
                 ->alias('a')
                 ->with(['country','storage','packageimage.file'])
                 ->field('a.*')
                 ->join('package_item c', 'c.express_num = a.express_num',"LEFT")
                 ->Order('a.created_time DESC')
                 ->paginate(300);
             
             // 计算总重量（使用新的实例）
             $totalWeight = (new Package())->setSearchQuery($paramwhere)
                 ->where('a.share_id', 0)
                 ->alias('a')
                 ->join('package_item c', 'c.express_num = a.express_num',"LEFT")
                 ->sum('a.weight');
             
             return $this->renderSuccess([
                 'data' => $data,
                 'totalWeight' => $totalWeight
             ]);
         }
         
         // 排除已经加入拼团的包裹（share_id 为 0）
         $data = $PackageModel->setQuery($where)->where('share_id', 0)->with(['country','storage','packageimage.file'])->field($field)->Order('created_time DESC')->paginate(300);
         // 计算总重量
         $totalWeight = $PackageModel->setQuery($where)->where('share_id', 0)->sum('weight');
         
         return $this->renderSuccess([
             'data' => $data,
             'totalWeight' => $totalWeight
         ]);
     }
     
     // 获取用户在拼团中的包裹列表（用于申请打包）
     public function getMyPackages(){
         $id = $this->request->param('id'); // 拼团ID
         $userInfo = $this->getUser();
         
         // 获取拼团信息
         $order = (new SharingOrder())->detail($id);
         if (!$order) {
             return $this->renderError('拼团不存在');
         }
         
         // 直接通过 Package 表的 share_id 字段获取用户在拼团中的包裹
         // 排除已经打包的包裹（inpack_id > 0）
         $packageList = (new Package())->where('share_id', $order['order_id'])
             ->where('member_id', $userInfo['user_id'])
             ->where('is_delete', 0)
             ->where('inpack_id', '<=', 0)
             ->with(['storage', 'country', 'packageimage'])
             ->select();
         
         // 计算总重量
         $totalWeight = 0;
         foreach ($packageList as $pkg) {
             $totalWeight += $pkg['weight'] ?: 0;
         }
         
         return $this->renderSuccess([
             'list' => $packageList,
             'totalWeight' => $totalWeight,
             'order' => $order
         ]);
     }
     
     // 申请打包（拼团包裹）
     public function applyPack(){
         $id = $this->request->param('id'); // 拼团ID
         $packageIds = $this->request->param('package_ids'); // 包裹ID，逗号分隔
         $userInfo = $this->getUser();
         
         // 验证参数
         if (!$id) {
             return $this->renderError('拼团ID不能为空');
         }
         if (!$packageIds) {
             return $this->renderError('请选择要打包的包裹');
         }
         
         // 获取拼团信息
         $order = (new SharingOrder())->detail($id);
         if (!$order) {
             return $this->renderError('拼团不存在');
         }
         
         // 解析包裹ID
         $packageIdArray = explode(',', $packageIds);
         $packageIdArray = array_filter(array_map('intval', $packageIdArray));
         if (empty($packageIdArray)) {
             return $this->renderError('请选择有效的包裹');
         }
         
         // 验证包裹是否属于当前用户且在该拼团中
         $orderItems = (new SharingOrderItem())->where(['order_id' => $order['order_id'], 'type' => 0])
             ->where('package_id', 'in', $packageIdArray)
             ->where('status', '<', 9)
             ->select();
         
         $validPackageIds = [];
         foreach ($orderItems as $item) {
             $package = (new Package())->where('id', $item['package_id'])->find();
             if ($package && $package['member_id'] == $userInfo['user_id']) {
                 $validPackageIds[] = $package['id'];
             }
         }
         
         if (empty($validPackageIds)) {
             return $this->renderError('没有可打包的包裹');
         }
         
         // 检查包裹是否在同一仓库
         $packages = (new Package())->whereIn('id', $validPackageIds)->select();
         $storageIds = array_unique(array_column($packages->toArray(), 'storage_id'));
         if (count($storageIds) != 1) {
             return $this->renderError('请选择同一仓库的包裹进行打包');
         }
         
         // 使用拼团的线路信息（如果拼团有设置）
         $lineId = $order['line_id'] ?: 0;
         $line = null;
         if ($lineId) {
             $line = (new Line())->find($lineId);
         }
         
         // 调用现有的打包接口逻辑
         // 这里需要调用 Package 控制器的 postPack 方法逻辑
         // 或者直接在这里实现打包逻辑
         
         // 开启事务
         $Package = new Package();
         $Package->startTrans();
         try {
             // 计算重量和体积
             $allWeight = $Package->whereIn('id', $validPackageIds)->sum('weight');
             $volumn = $Package->whereIn('id', $validPackageIds)->sum('volume');
             
             // 计算体积重（如果有线路信息）
             $volumnweight = 0;
             $cale_weight = $allWeight;
             if ($line && $line['volumeweight']) {
                 $volumnweight = $volumn * 1000000 / $line['volumeweight'];
                 if($line['volumeweight_type'] == 20){
                     $volumnweight = round(($allWeight + ($volumn*1000000/$line['volumeweight'] - $allWeight)*$line['bubble_weight']/100),2);
                 }
                 $cale_weight = $allWeight > $volumnweight ? $allWeight : $volumnweight;
             }
             
             // 获取设置
             $storesetting = SettingModel::getItem('store');
             
             // 获取拼团的地址信息（直接使用拼团订单中的地址）
             $countryId = 0;
             if ($order['address_id']) {
                 $address = (new UserAddress())->find($order['address_id']);
                 if ($address) {
                     $countryId = $address['country_id'];
                 }
             }
             // 如果没有拼团地址，使用线路的国家ID
             if (!$countryId && $line && $line['country_id']) {
                 $countryId = $line['country_id'];
             }
             
             // 创建集运订单（直接使用拼团订单中的地址和线路）
             $inpackOrder = [
                 'order_sn' => $storesetting['createSn']==10 ? createSn() : createSnByUserIdCid($userInfo['user_id'], $countryId),
                 'remark' => $this->request->param('remark', ''),
                 'pack_ids' => implode(',', $validPackageIds),
                 'storage_id' => $storageIds[0],
                 'address_id' => $order['address_id'] ?: 0, // 直接使用拼团的地址ID
                 'free' => 0,
                 'weight' => $allWeight,
                 'cale_weight' => $cale_weight,
                 'line_weight' => $line ? turnweight($storesetting['weight_mode']['mode'], $cale_weight, $line['line_type_unit']) : 0,
                 'volume' => $volumnweight,
                 'pack_free' => 0,
                 'member_id' => $userInfo['user_id'],
                 'country_id' => $countryId,
                 'unpack_time' => getTime(),
                 'created_time' => getTime(),
                 'updated_time' => getTime(),
                 'status' => 1,
                 'line_id' => $lineId, // 直接使用拼团的线路ID
                 'share_id' => $order['order_id'], // 保存拼团订单ID
                 'inpack_type' => 1, // 拼团包裹申请打包
                 'wxapp_id' => $this->request->param('wxapp_id'),
             ];
             
             // 生成订单号
             $user_id = $userInfo['user_id'];
             if($storesetting['usercode_mode']['is_show'] == 1){
                 $member = (new UserModel())->where('user_id', $userInfo['user_id'])->find();
                 $user_id = $member['user_code'];
             }
             
             $createSnfistword = $storesetting['createSnfistword'];
             $xuhao = ((new Inpack())->where(['member_id'=>$userInfo['user_id'],'is_delete'=>0])->count()) + 1;
             $shopname = \app\api\model\store\Shop::detail($storageIds[0]);
             $orderno = createNewOrderSn($storesetting['orderno']['default'], $xuhao, $createSnfistword, $user_id, $shopname['shop_alias_name'], $countryId, $countryId);
             $inpackOrder['order_sn'] = $orderno;
             
             $inpack = (new Inpack())->insertGetId($inpackOrder);
             if (!$inpack) {
                 throw new \Exception('创建打包订单失败');
             }
             
             // 更新包裹状态
             $Package->whereIn('id', $validPackageIds)->update([
                 'status' => 5,
                 'line_id' => $lineId,
                 'address_id' => $order['address_id'] ?: 0, // 使用拼团的地址ID
                 'inpack_id' => $inpack,
                 'updated_time' => getTime()
             ]);
             
             // 更新 SharingOrderItem 状态为待付款
             (new SharingOrderItem())->where(['order_id' => $order['order_id'], 'type' => 0])
                 ->where('package_id', 'in', $validPackageIds)
                 ->update(['status' => 4, 'update_time' => time()]);
             
             $Package->commit();
             return $this->renderSuccess('申请打包成功');
             
         } catch (\Exception $e) {
             $Package->rollback();
             return $this->renderError($e->getMessage());
         }
     }
     
     // 映射查询状态
     public function mapStatus($value){
         $query_status = [
            0 => [1,2,3,4,5,6,7,8],
            1 => [1,2,3,4,5,6],
            2 => [8],
            3 => [7]
         ];
         return $query_status[$value]??0; 
     }
    
}
