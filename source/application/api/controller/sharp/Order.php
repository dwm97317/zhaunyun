<?php
namespace app\api\controller\sharp;
use app\api\controller\Controller;
use app\api\model\sharing\SharingOrder;
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
     
     /**
      * 获取我的拼团列表
      * 通过 share_id 从 Inpack 模型获取所有拼团订单
      * @return array
      */
     public function list(){
        $query = $this->request->param();
        // 当前用户信息
        $userInfo = $this->getUser();
        $userId = $userInfo['user_id'];
        
        // 通过 share_id 从 Inpack 模型获取所有拼团订单
        // 条件：member_id=当前用户, share_id>0(属于拼团), inpack_type=1(拼团包裹), is_delete=0(未删除)
        $Inpack = new Inpack();
        $Inpack->where('member_id', $userId)
               ->where('share_id', '>', 0)  // 只获取属于拼团的集运单
               ->where('inpack_type', 1)    // 拼团包裹申请打包
               ->where('is_delete', 0);
        
        // 根据 type 过滤状态
        // Inpack状态: 1-查验 2-待支付 3-已支付 4-下架 5-打包 6-转运中 7+-已完成
        // 前端状态类型: sharing(拼团中), no_pay(待付款), no_send(待发货), received(待收货), complete(已完成)
        if (isset($query['type']) && $query['type'] != '0' && $query['type'] != '') {
            if ($query['type'] == 'sharing') {
                // 拼团中：状态为1(查验)或5(打包)
                $Inpack->where('status', 'in', [1, 5]);
            } elseif ($query['type'] == 'no_pay') {
                // 待付款：状态为2(待支付)
                $Inpack->where('status', 2);
            } elseif ($query['type'] == 'no_send') {
                // 待发货：状态为3(已支付)或4(下架)
                $Inpack->where('status', 'in', [3, 4]);
            } elseif ($query['type'] == 'received') {
                // 待收货：状态为6(转运中)
                $Inpack->where('status', 6);
            } elseif ($query['type'] == 'complete') {
                // 已完成：状态大于等于7
                $Inpack->where('status', '>=', 7);
            }
        }
        
        // 关联拼团订单信息（SharingOrder）
        // sharingOrder 会自动格式化 start_time 为 'Y-m-d H:i:s' 格式
        $list = $Inpack->with([
                      'sharingOrder' => function($query) {
                          // 关联拼团订单的国家和地址信息
                          $query->with(['country', 'address']);
                      }, 
                      'address',  // 集运单的收件地址
                      'country'   // 集运单的国家信息
                  ])
                      ->order('created_time desc')
                      ->paginate(15, false, [
                          'query' => \request()->request()
                      ]);
        
        // 组装数据格式以匹配前端期望的结构
        // 前端需要: order(拼团订单), package(集运单), status(状态), order_item_id(用于操作)
        $resultList = [];
        
        foreach ($list as $inpack) {
            // 如果没有关联的拼团订单，跳过该记录
            if (!$inpack['sharingOrder']) {
                continue;
            }
            
            // 将 Inpack 状态映射为前端需要的状态格式
            // 状态: 1-已加入 2-待审核 3-待打包 4-待付款 5-待发货 6-已发货 7-已完成 8-已取消
            $statusMap = [
                1 => ['value' => 3, 'text' => '待打包'],  // 查验 -> 待打包
                2 => ['value' => 4, 'text' => '待付款'],  // 待支付 -> 待付款
                3 => ['value' => 5, 'text' => '待发货'],  // 已支付 -> 待发货
                4 => ['value' => 5, 'text' => '待发货'],  // 下架 -> 待发货
                5 => ['value' => 3, 'text' => '待打包'],  // 打包 -> 待打包
                6 => ['value' => 6, 'text' => '已发货'],  // 转运中 -> 已发货
            ];
            
            // 如果状态 >= 7，则认为是已完成
            if ($inpack['status'] >= 7) {
                $status = ['value' => 7, 'text' => '已完成'];
            } else {
                $status = isset($statusMap[$inpack['status']]) ? $statusMap[$inpack['status']] : ['value' => 1, 'text' => '已加入'];
            }
            
            // 将 inpack 转换为数组，并移除 sharingOrder 和 sharing_order 字段（避免混淆）
            $packageData = $inpack->toArray();
            // 移除关联字段，因为已经单独提取为 order
            unset($packageData['sharingOrder']);
            unset($packageData['sharing_order']);
            
            // 确保 sharingOrder 存在且可以转换为数组
            $sharingOrderData = [];
            if ($inpack['sharingOrder']) {
                $sharingOrderData = $inpack['sharingOrder']->toArray();
            }
            
            // 组装返回数据
            // order: 拼团订单信息（包含 order_sn, max_people, min_weight, start_time, order_id 等）
            // package: 集运单信息（包含 order_sn, address 等）
            // status: 状态信息（value 和 text）
            // order_item_id: 集运单ID（用于取消订单等操作）
            $resultList[] = [
                'order_item_id' => $inpack['id'], // 使用 inpack 的 id 作为 order_item_id
                'order' => $sharingOrderData, // 拼团订单信息（通过 share_id 关联），转换为数组
                'package' => $packageData, // 集运单信息（已移除 sharingOrder 字段）
                'status' => $status, // 状态信息
            ];
        }
        
        // 重新组装分页数据结构
        // 创建新的分页对象，使用组装后的数据
        $newList = [
            'total' => $list->total(),
            'per_page' => $list->listRows(),
            'current_page' => $list->currentPage(),
            'last_page' => $list->lastPage(),
            'data' => $resultList
        ];
        
        return $this->renderSuccess(['list' => $newList]);
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
        // 当前用户信息
        $userInfo = $this->getUser();
        $query['user_id'] = $userInfo['user_id'];
        $model = (new SharingOrder());
        $list = $model->getList($query);
        $shareService = (new SharingOrderService());
         // 获取已拼团包裹重量
         $list = $shareService->getPackageWeight($list);
         $list = $shareService->getMainAddressInfo($list);
         // 获取参与用户信息
         $list = $shareService->getJoinUserInfo($list);
        return $this->renderSuccess(compact('list'));
     }
     
     // 取消该拼团单
     /**
      * 取消拼团订单
      * 取消订单后：
      * 1. 跟拼团订单关联的包裹单号 share_id 恢复为 0
      * 2. inpack_id 也恢复为 0
      * 3. inpack 订单则删除 is_delete=1
      */
     public function cancle(){
        $id = $this->request->param('id'); // 这是 inpack 的 id
        $userInfo = $this->getUser();
        
        // 获取 inpack 订单
        $inpack = (new Inpack())->where('id', $id)
                                ->where('member_id', $userInfo['user_id'])
                                ->where('is_delete', 0)
                                ->find();
        
        if (!$inpack) {
            return $this->renderError('订单不存在或已删除');
        }
        
        // 验证订单是否属于拼团（inpack_type=1 且 share_id>0）
        if ($inpack['inpack_type'] != 1 || $inpack['share_id'] <= 0) {
            return $this->renderError('该订单不是拼团订单');
        }
        
        // 开启事务
        $inpack->startTrans();
        try {
            // 1. 获取该 inpack 关联的所有包裹ID
            $packIds = [];
            if (!empty($inpack['pack_ids'])) {
                $packIds = explode(',', $inpack['pack_ids']);
                // 过滤空值
                $packIds = array_filter($packIds, function($v) {
                    return !empty($v) && $v > 0;
                });
            }
            
            // 2. 更新包裹的 share_id 和 inpack_id 为 0
            if (!empty($packIds)) {
                (new Package())->where('id', 'in', $packIds)
                              ->where('member_id', $userInfo['user_id']) // 确保是当前用户的包裹
                              ->update([
                                  'share_id' => 0,
                                  'inpack_id' => 0,
                                  'status' => 2,
                                  'updated_time' => getTime()
                              ]);
            }
            
            // 3. 删除 inpack 订单（is_delete=1）
            $inpack->save(['is_delete' => 1, 'updated_time' => getTime()]);
            
            // 提交事务
            $inpack->commit();
            return $this->renderSuccess('拼团订单已成功取消');
            
        } catch (\Exception $e) {
            // 回滚事务
            $inpack->rollback();
            return $this->renderError('取消订单失败：' . $e->getMessage());
        }
     }
     
     // 拼单详情
     public function detail(){
         $id = $this->request->param('id');
         $item_id = $this->request->param('item_id');
         $userInfo = $this->getUser();
         $model = (new SharingOrder())->detail($id);
         $is_publiser = $userInfo['user_id']==$model['member_id']?true:false;
         $address_where['order_id'] = $model['order_id'];
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
           // 通过 share_id 获取包裹ID和集运单ID
           $countpackageid = $Package->where('share_id', $id)->where('is_delete', 0)->column('id');
           $countinpackid = $inpack->where('share_id', $id)->where('is_delete', 0)->column('id');
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
            $currentCount = (new SharingTrUser())->where(['order_id' => $order['order_id']])->count();
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
             
            // 2. 更新该用户的包裹和集运单的 share_id 字段，清除拼团关联
            
            // 更新包裹的 share_id 字段，清除拼团ID
            (new Package())->where('share_id', $order['order_id'])
                          ->where('member_id', $userInfo['user_id'])
                          ->update(['share_id' => 0, 'updated_time' => getTime()]);
            
            // 更新集运单的 share_id 字段，清除拼团ID
            (new Inpack())->where('share_id', $order['order_id'])
                          ->where('member_id', $userInfo['user_id'])
                          ->update(['share_id' => 0, 'inpack_type' => 0, 'updated_time' => getTime()]);
             
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
        $Package = new Package();
        $Package->startTrans();
        try {
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
                
                // 检查包裹是否已经加入拼团
                if ($package['share_id'] > 0) {
                    if ($package['share_id'] == $order['order_id']) {
                        throw new \Exception('包裹已加入该拼团，请勿重复添加');
                    } else {
                        throw new \Exception('包裹已加入其他拼团，请勿重复添加');
                    }
                }
            }
            
            // 更新包裹的 share_id 和 status
            $Package->where('id', 'in', $packageIdArray)
                    ->where('member_id', $userInfo['user_id'])
                    ->update([
                        'share_id' => $order['order_id'],
                        'status' => 4,
                        'updated_time' => getTime()
                    ]);
            
            $Package->commit();
            return $this->renderSuccess('加入拼团成功');
            
        } catch (\Exception $e) {
            $Package->rollback();
            return $this->renderError($e->getMessage());
        }
    }
     
     // 申请加入拼团
     public function applytopintuan(){
         $param = $this->request->param();
         $inpack = new Inpack();
         $userInfo = $this->getUser();
         $setting = Setting::getItem('sharp');
         if($setting['is_open'] == 0 ){
             return $this->renderError('暂时无法加入拼团');  
         }
         
         // 检查集运单是否已经加入拼团
         $inpackOrder = $inpack->where('id', $param['id'])->find();
         if (!$inpackOrder) {
             return $this->renderError('集运单不存在');
         }
         if ($inpackOrder['share_id'] > 0) {
             return $this->renderError('请勿重复加入该拼团');  
         }
         
         // 验证集运单属于当前用户
         if ($inpackOrder['member_id'] != $userInfo['user_id']) {
             return $this->renderError('该集运单不属于您');
         }
         
         // 更新集运单的 share_id 和 inpack_type
         $result = $inpack->where('id', $param['id'])->update([
             'share_id' => $param['pin_id'],
             'inpack_type' => 1,
             'updated_time' => getTime()
         ]);
         
         if($result){
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
        
        // 验证用户是否为拼团团长
        if ($order['member_id'] != $userInfo['user_id']) {
            return $this->renderError('只有团长可以修改地址');
        }
        
        $addressInfo = (new SharingOrderAddress())->where(['order_id'=>$order['order_id'], 'is_head' => 1])->find();
        if(!$order->updateAddress($addressInfo,$address)){
            return $this->renderError($order->getError()??'操作失败');
        };
        return $this->renderSuccess('地址更新成功');
     }
     
     // 审核列表（已废弃，不再使用 SharingOrderItem）
     public function verifylist(){
        // 由于不再使用 SharingOrderItem，审核功能需要重新设计
        // 可以通过 Package 和 Inpack 的 share_id 来获取需要审核的包裹和集运单
        $query = $this->request->param();
        $userInfo = $this->getUser();
        $shareOrder = (new SharingOrder());
        
        // 获取我发布的拼团ID
        $orderIds = $shareOrder->where('member_id', $userInfo['user_id'])
                               ->where('is_delete', 0)
                               ->column('order_id');
        
        $list = [];
        $Package = new Package();
        $Inpack = new Inpack();
        
        // 获取需要审核的包裹（通过 share_id 关联）
        $packages = $Package->where('share_id', 'in', $orderIds)
                           ->where('is_delete', 0)
                           ->select();
        
        foreach ($packages as $package) {
            $packageItem = (new PackageItem())->where(['order_id'=>$package['id']])->select();
            $packageName = array_column($packageItem->toArray(),'class_name');
            $list[] = [
                'id' => $package['id'],
                'type' => 'package',
                'package' => $package,
                'name' => implode('-', $packageName)
            ];
        }
        
        // 获取需要审核的集运单（通过 share_id 关联）
        $inpacks = $Inpack->where('share_id', 'in', $orderIds)
                         ->where('is_delete', 0)
                         ->select();
        
        foreach ($inpacks as $inpack) {
            $list[] = [
                'id' => $inpack['id'],
                'type' => 'inpack',
                'package' => $inpack,
                'name' => '集运单：' . $inpack['order_sn']
            ];
        }
    
        return $this->renderSuccess(compact('list'));
     }
     
     // 修改审核状态（已废弃，不再使用 SharingOrderItem）
     public function verifyupdate(){
        // 由于不再使用 SharingOrderItem，审核功能需要重新设计
        // 可以直接更新 Package 或 Inpack 的状态
        return $this->renderError('审核功能已废弃，请使用新的审核接口');
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
         
         // 验证包裹是否属于当前用户且在该拼团中（通过 share_id 关联）
         $packages = (new Package())->where('share_id', $order['order_id'])
             ->where('id', 'in', $packageIdArray)
             ->where('member_id', $userInfo['user_id'])
             ->where('is_delete', 0)
             ->select();
         
         $validPackageIds = [];
         foreach ($packages as $package) {
             $validPackageIds[] = $package['id'];
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
             
             // 包裹状态已在上面更新为 5（打包），无需额外更新
             
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
