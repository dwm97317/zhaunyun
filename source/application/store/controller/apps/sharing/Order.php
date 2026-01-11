<?php
namespace app\store\controller\apps\sharing;
use app\store\controller\Controller;
use app\store\model\sharing\SharingOrder;
use app\store\model\store\Shop as ShopModel;
use app\store\model\sharing\SharingOrderAddress;
use app\common\model\Setting;
use app\store\model\Line;
use app\store\model\UserAddress;
use app\store\model\Inpack;
use app\common\model\User;
use app\common\service\Message;
use app\api\model\Logistics;
use app\store\model\Countries;
use app\store\model\sharing\SharingTrUser;
use app\api\model\Package as PackageModel;
use app\store\model\InpackService as InpackServiceModel;
/**
 * 拼单管理控制器
 * Class Active
 * @package app\store\controller\apps\sharing
 */
class Order extends Controller
{
    // 首页
    public function index(){
       $SharingOrder = (new SharingOrder());
       $shopList = ShopModel::getAllList();
       $Inpack = new Inpack();
       $param = $this->request->param();
       $lists = $SharingOrder->getList($param);
       foreach ($lists as $key => $item){
          // 使用 inpack 表的 share_id 字段统计拼团订单中的集运单数量（排除已删除的）
          $lists[$key]['count'] = $Inpack->where('share_id', $item['order_id'])
                                         ->where('is_delete', 0)
                                         ->count();
       }
       $list = $lists;
        // dump($list);die;
       $set = Setting::detail('store')['values']['address_setting'];
       $setcode = Setting::detail('store')['values']['usercode_mode'];
       return $this->fetch('index',compact('list','shopList','set','setcode'));
    }
    
    
    //查看集运单里边的集运单列表
    public function inpacklist(){
        $Inpack = new Inpack();
        $shopList = ShopModel::getAllList();
        $orderId = input('order_id');
        
        // 直接通过 inpack 表的 share_id 字段获取集运单列表
        $inpackList = $Inpack->where('share_id', $orderId)->where('is_delete',0)->select();
        $list = [];
        $set = Setting::detail('store')['values']['address_setting'];
        
        foreach ($inpackList as $key => $inpack){
           $list[$key] = $Inpack::details($inpack['id']);
           // 设置默认拼团状态
           $list[$key]['pin_status'] = ['value' => 1, 'text' => '已加入'];
        }
        
        return $this->fetch('inpacklist',compact('list','set','shopList'));
    }
    
    //查看参与人员及其包裹列表
    public function participants(){
        $orderId = input('order_id'); // 拼团订单ID
        $Inpack = new Inpack();
        $Line = new Line();
        $Package = new \app\api\model\Package();
        
        // 获取拼团订单信息
        $sharingOrder = (new SharingOrder())->where('order_id', $orderId)->find();
        if (!$sharingOrder) {
            return $this->renderError('拼团订单不存在');
        }
        
        $shareId = $orderId;
        
        // 通过 SharingTrUser 表查询参与的用户（通过 order_id）
        $sharingTrUserList = (new SharingTrUser())->where('order_id', $orderId)
                                                  ->with(['user'])
                                                  ->select();
        
        if (empty($sharingTrUserList)) {
            return $this->renderError('该拼团暂无参与人员');
        }
        
        // 通过 share_id 查询该拼团的所有集运单
        $inpackList = $Inpack->where('share_id', $shareId)
                             ->where('is_delete', 0)
                             ->with(['user', 'address'])
                             ->select();
        
        // 通过 share_id 查询该拼团的所有包裹（不依赖 inpack_id）
        $packages = $Package->where('share_id', $shareId)
                           ->where('is_delete', 0)
                           ->with(['packageimage.file', 'country'])
                           ->select();
        
        // 组织参与人员数据（按用户ID分组）
        $participants = [];
        foreach ($sharingTrUserList as $sharingTrUser) {
            $userId = $sharingTrUser['user_id'];
            
            // 获取用户信息
            $user = $sharingTrUser['user'] ?? User::detail($userId);
            if (!$user) {
                continue;
            }
            
            // 初始化参与人员数据
            $participants[$userId] = [
                'user_id' => $userId,
                'user_code' => $user['user_code'] ?? '',
                'nickName' => $user['nickName'] ?? '',
                'mobile' => $user['mobile'] ?? '',
                'inpacks' => [],
                'packages' => []
            ];
        }
        
        // 遍历集运单，按用户分组
        foreach ($inpackList as $inpack) {
            $userId = $inpack['member_id'];
            
            // 如果该用户不在参与人员列表中，跳过（理论上不应该发生）
            if (!isset($participants[$userId])) {
                continue;
            }
            
            // 添加集运单信息
            $participants[$userId]['inpacks'][] = [
                'id' => $inpack['id'],
                'order_sn' => $inpack['order_sn'],
                'status' => $inpack['status'],
                'weight' => $inpack['weight'],
                'free' => $inpack['free'],
                'is_pay' => $inpack['is_pay'],
                'created_time' => $inpack['created_time']
            ];
        }
        
        // 遍历包裹，按用户分组（通过 member_id）
        foreach ($packages as $package) {
            $userId = $package['member_id'];
            
            // 如果该用户不在参与人员列表中，跳过（理论上不应该发生）
            if (!isset($participants[$userId])) {
                continue;
            }
            
            // 添加包裹信息
            $participants[$userId]['packages'][] = [
                'id' => $package['id'],
                'express_num' => $package['express_num'],
                'weight' => $package['weight'],
                'remark' => $package['remark'],
                'status' => $package['status'],
                'inpack_id' => $package['inpack_id']
            ];
        }
        
        // 将关联数组转换为索引数组，便于视图遍历
        $participantsList = array_values($participants);
        
        $set = Setting::detail('store')['values']['address_setting'];
        $setcode = Setting::detail('store')['values']['usercode_mode'];
        
        // 获取线路列表
        $lineList = $Line->getListAll();
        return $this->fetch('participants', compact('participantsList', 'sharingOrder', 'set', 'setcode', 'shareId', 'lineList'));
    }
    
    // 导出参与人员列表
    public function exportParticipants(){
        $orderId = input('share_id'); // 拼团订单ID
        $Inpack = new Inpack();
        $Package = new \app\api\model\Package();
        
        // 获取拼团订单信息
        $sharingOrder = (new SharingOrder())->where('order_id', $orderId)->find();
        if (!$sharingOrder) {
            return $this->renderError('拼团订单不存在');
        }
        
        $shareId = $orderId;
        
        // 通过 SharingTrUser 表查询参与的用户（通过 order_id）
        $sharingTrUserList = (new SharingTrUser())->where('order_id', $orderId)
                                                  ->with(['user'])
                                                  ->select();
        
        if (empty($sharingTrUserList)) {
            return $this->renderError('该拼团暂无参与人员');
        }
        
        // 通过 share_id 查询该拼团的所有集运单
        $inpackList = $Inpack->where('share_id', $shareId)
                             ->where('is_delete', 0)
                             ->with(['user', 'address'])
                             ->select();
        
        // 通过 share_id 查询该拼团的所有包裹（不依赖 inpack_id）
        $packages = $Package->where('share_id', $shareId)
                           ->where('is_delete', 0)
                           ->with(['packageimage.file', 'country'])
                           ->select();
        
        // 组织参与人员数据（按用户ID分组）
        $participants = [];
        foreach ($sharingTrUserList as $sharingTrUser) {
            $userId = $sharingTrUser['user_id'];
            
            // 获取用户信息
            $user = $sharingTrUser['user'] ?? User::detail($userId);
            if (!$user) {
                continue;
            }
            
            // 初始化参与人员数据
            $participants[$userId] = [
                'user_id' => $userId,
                'user_code' => $user['user_code'] ?? '',
                'nickName' => $user['nickName'] ?? '',
                'mobile' => $user['mobile'] ?? '',
                'inpacks' => [],
                'packages' => []
            ];
        }
        
        // 遍历集运单，按用户分组
        foreach ($inpackList as $inpack) {
            $userId = $inpack['member_id'];
            
            if (!isset($participants[$userId])) {
                continue;
            }
            
            // 添加集运单信息
            $participants[$userId]['inpacks'][] = [
                'id' => $inpack['id'],
                'order_sn' => $inpack['order_sn'],
                'status' => $inpack['status'],
                'weight' => $inpack['weight'],
                'free' => $inpack['free'],
                'is_pay' => $inpack['is_pay'],
                'created_time' => $inpack['created_time']
            ];
        }
        
        // 遍历包裹，按用户分组（通过 member_id）
        foreach ($packages as $package) {
            $userId = $package['member_id'];
            
            if (!isset($participants[$userId])) {
                continue;
            }
            
            // 添加包裹信息
            $participants[$userId]['packages'][] = [
                'id' => $package['id'],
                'express_num' => $package['express_num'],
                'weight' => $package['weight'],
                'remark' => $package['remark'],
                'status' => $package['status'],
                'inpack_id' => $package['inpack_id']
            ];
        }
        
        // 将关联数组转换为索引数组
        $participantsList = array_values($participants);
        
        // 获取系统设置
        $setcode = Setting::detail('store')['values']['usercode_mode'];
        $statusMap = [1=>'待查验',2=>'待支付',3=>'已支付',4=>'已拣货',5=>'已打包',6=>'已发货',7=>'已收货',8=>'已完成',-1=>'问题件'];
        $packageStatusMap = [1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成',-1=>'问题件'];
        
        // 准备导出数据
        $tileArray = [
            '序号',
            $setcode['is_show'] == 1 ? '用户编号' : '用户ID',
            '昵称',
            '手机号',
            '集运单号',
            '集运单状态',
            '集运单重量(Kg)',
            '集运单费用',
            '集运单支付状态',
            '集运单创建时间',
            '包裹快递单号',
            '包裹重量(Kg)',
            '包裹状态',
            '包裹备注',
            '包裹总数',
            '可打包数量'
        ];
        
        $dataArray = [];
        $index = 1;
        
        foreach ($participantsList as $participant) {
            $inpackCount = count($participant['inpacks']);
            $packageCount = count($participant['packages']);
            $packableCount = count(array_filter($participant['packages'], function($p) { 
                return in_array($p['status'], [2, 3, 4, 7]) && empty($p['inpack_id']); 
            }));
            
            // 如果没有集运单，至少输出一行用户信息
            if ($inpackCount == 0) {
                $row = [
                    $index++,
                    $setcode['is_show'] == 1 && !empty($participant['user_code']) ? $participant['user_code'] : $participant['user_id'],
                    $participant['nickName'],
                    $participant['mobile'],
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $packageCount,
                    $packableCount
                ];
                $dataArray[] = $row;
            } else {
                // 如果有集运单，为每个集运单输出一行
                foreach ($participant['inpacks'] as $inpackIndex => $inpack) {
                    // 获取该集运单的包裹
                    $inpackPackages = array_filter($participant['packages'], function($p) use ($inpack) {
                        return $p['inpack_id'] == $inpack['id'];
                    });
                    
                    $packageNums = [];
                    $packageWeights = [];
                    $packageStatuses = [];
                    $packageRemarks = [];
                    
                    foreach ($inpackPackages as $pkg) {
                        $packageNums[] = $pkg['express_num'];
                        $packageWeights[] = $pkg['weight'];
                        $packageStatuses[] = $packageStatusMap[$pkg['status']] ?? '未知';
                        $packageRemarks[] = $pkg['remark'] ?: '-';
                    }
                    
                    $row = [
                        $index++,
                        $setcode['is_show'] == 1 && !empty($participant['user_code']) ? $participant['user_code'] : $participant['user_id'],
                        $participant['nickName'],
                        $participant['mobile'],
                        $inpack['order_sn'],
                        $statusMap[$inpack['status']] ?? '未知',
                        $inpack['weight'],
                        $inpack['free'],
                        $inpack['is_pay'] == 1 ? '已支付' : '未支付',
                        $inpack['created_time'],
                        implode('; ', $packageNums),
                        implode('; ', $packageWeights),
                        implode('; ', $packageStatuses),
                        implode('; ', $packageRemarks),
                        $packageCount,
                        $packableCount
                    ];
                    
                    $dataArray[] = $row;
                }
            }
        }
        
        // 导出CSV文件
        $filename = '参与人员列表_' . ($sharingOrder['title'] ?? $orderId) . '_' . date('YmdHis') . '.csv';
        export_excel($filename, $tileArray, $dataArray);
        exit;
    }
    
    
    //从拼团中移出订单
    public function yichu(){
        $Inpack = new Inpack();
        $inpackId = input('id');
        
        // 获取 inpack 信息
        $inpack = $Inpack->where('id', $inpackId)->find();
        if (!$inpack) {
            return $this->renderError('订单不存在');
        }
        
        // 更新 inpack 的 inpack_type 为 0，share_id 为 0
        $result = $Inpack->where('id', $inpackId)->update([
            'inpack_type' => 0,
            'share_id' => 0
        ]);
        if(!$result){
            return $this->renderError('移出失败');
        }
        
        // 如果 inpack 有关联的包裹，更新包裹的 share_id 为 0
        if (!empty($inpack['pack_ids'])) {
            $packIds = explode(',', $inpack['pack_ids']);
            $packIds = array_filter(array_map('intval', $packIds));
            if (!empty($packIds)) {
                (new \app\api\model\Package())->where('id', 'in', $packIds)
                    ->update(['share_id' => 0, 'updated_time' => getTime()]);
            }
        }
        
        return $this->renderSuccess('移出成功');
    }
    
    //对拼团订单发货
    public function delivery($id){
        $SharingOrder = (new SharingOrder());
        $Inpack = new Inpack();
           //更新拼团订单的inpack_id 以及 所有的用户集运单中的t_order_sn
        $detail = $SharingOrder->where('order_id',$id)->find();
        $track = getFileData('assets/track.json');
        if (!$this->request->isAjax()){
            return $this->fetch('delivery', compact(
                'detail','track'
            ));
        }
        $data = input();
       
        $res = $SharingOrder->where('order_id',$id)->update(['inpack_id'=> $data['delivery']['t_order_sn'],'status'=>4]);
        //TODO发货记录log and message send 
        if(!$res){
             return $this->renderError('发货失败');
        }
        // 通过 inpack 表的 share_id 获取所有关联的集运单
        $pack = $Inpack->where('share_id', $id)->where('is_delete', 0)->select();
        foreach ($pack as $val){
            $Inpack->where('id',$val['id'])->update(['t_order_sn'=>$data['delivery']['t_order_sn'],'status'=>6]);
        }
        //更新物流信息
        $this->AddPinLog($datas = ['selectIds' => $id,'logistics_describe' => '拼团订单已发货,国际单号：'.$data['delivery']['t_order_sn']]);
        
        return  $this->renderSuccess('发货成功','javascript:history.back(1)');
    }
    
    //更新拼团订单的物流信息
    public function AddPinLog($data){
        $Inpack = new Inpack();
        // 通过 inpack 表的 share_id 获取所有关联的集运单
        $res = $Inpack->where('share_id', $data['selectIds'])->where('is_delete', 0)->select();
        foreach ($res as $key =>$val){
            $sendOrder = (new Inpack())->details($val['id']);
            //发送用户以及用户信息
            $userId = $sendOrder['member_id'];
            $data['code'] = $val;
            $user = User::detail($userId);
            
            //发送订阅消息，模板消息
            $data['order_sn'] = isset($sendOrder['t_order_sn'])?$sendOrder['t_order_sn']:$sendOrder['order_sn'];
            $data['order'] = $sendOrder;
            $data['order']['total_free'] = $sendOrder['free'];
            $data['order']['userName'] = $user['nickName'];
            $data['order_type'] = 10;
            $data['order']['remark'] =$data['logistics_describe'] ;
            Message::send('order.payment',$data);
            
            //邮件通知
            if($user['email']){
                $this->sendemail($user,$data,$type=1);
            }
             $res = Logistics::addLog($sendOrder['order_sn'],$data['logistics_describe'],date("Y-m-d H:i:s",time()));
             if (!$res){
                return $this->renderError('物流更新失败');
             }
        }
        return $this->renderSuccess('物流更新成功');
    }
    
    
    //更新拼团订单的物流信息
    public function alllogistics(){
        $data = input();
        if(!$data['logistics_describe']){
             return $this->renderError('请输入订单物流信息');
        }
        $Inpack = new Inpack();
        // 通过 inpack 表的 share_id 获取所有关联的集运单
        $res = $Inpack->where('share_id', $data['selectIds'])->where('is_delete', 0)->select();
        foreach ($res as $key =>$val){
            $sendOrder = (new Inpack())->details($val['id']);
            //发送用户以及用户信息
            $userId = $sendOrder['member_id'];
            $data['code'] = $val;
            $user = User::detail($userId);
            
            //发送订阅消息，模板消息
            $data['order_sn'] = isset($sendOrder['t_order_sn'])?$sendOrder['t_order_sn']:$sendOrder['order_sn'];
            $data['order'] = $sendOrder;
            $data['order']['total_free'] = $sendOrder['free'];
            $data['order']['userName'] = $user['nickName'];
            $data['order_type'] = 10;
            $data['order']['remark'] =$data['logistics_describe'] ;
            Message::send('order.payment',$data);
            
            //邮件通知
            if($user['email']){
                $this->sendemail($user,$data,$type=1);
            }
             $res = Logistics::addLog($sendOrder['order_sn'],$data['logistics_describe'],$data['created_time']);
             if (!$res){
                return $this->renderError('物流更新失败');
             }
        }
        return $this->renderSuccess('物流更新成功');
    }
    
    // 修改用户地址
    public function updateAddress(){
        $selectIds = $this->postData();
        $SharingOrder = (new SharingOrder()); 
        if(!$selectIds['id'] || !$selectIds['address_id']){
            return $this->renderError('修改失败');
            
        }
        $result = $SharingOrder->where('order_id',$selectIds['id'])->update(['address_id'=>$selectIds['address_id']]);
        return $this->renderSuccess('修改成功');
    }
    
    //拼团新增
    public function add(){
        $model = new SharingOrder;
        if (!$this->request->isAjax()) {
            $shopList = ShopModel::getListName();
            $line = (new Line())->getListForPintuan();
            $countryList = (new Countries())->getListAll();
            return $this->fetch('add',compact('line','shopList','useraddress','countryList'));
        }
        // 新增记录
        if ($model->add($this->postData('data'))) {
            return $this->renderSuccess('添加成功', url('store/apps.sharing.order/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
    //删除拼团订单
    public function orderdelete($id){
        $model = new SharingOrder;
        $detail = $model::detail($id);
        if($detail){
            $detail->save(['is_delete'=>1]);
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError('删除失败');
    }
    
    
    
    // 拼团编辑
    public function edit(){
        $model = new SharingOrder;
        if (!$this->request->isAjax()) {
            $id= input('order_id');
            $detail = $model::detail($id);
            $shopList = ShopModel::getListName();
            $line = (new Line())->getListForPintuan();
            $countryList = (new Countries())->getListAll();
            return $this->fetch('edit',compact('detail','shopList','line','countryList'));
        }
                      
        // 新增记录
        if ($model->edit($this->postData('data'))) {
            return $this->renderSuccess('添加成功', url('store/apps.sharing.order/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    

    
    //拼团审核
    public function verify(){

        $param = $this->request->param();
        $Inpack = new Inpack();
        $data = [
            'status'=> $param['verify']['status'],
            'reject_reason' =>$param['verify']['reason']
        ];
 
        if($data['status']==9){
            // 拒绝审核：更新 inpack_type 为 0，share_id 为 0，并更新关联包裹的 share_id 为 0
            $inpack = $Inpack->where('id', $param['verify']['id'])->find();
            if (!$inpack) {
                return $this->renderError('订单不存在');
            }
            
            // 更新 inpack 的 inpack_type 和 share_id 为 0
            $result = $Inpack->where('id', $param['verify']['id'])->update([
                'inpack_type' => 0,
                'share_id' => 0,
                'updated_time' => getTime()
            ]);
            
            // 如果 inpack 有关联的包裹，更新包裹的 share_id 为 0
            if (!empty($inpack['pack_ids'])) {
                $packIds = explode(',', $inpack['pack_ids']);
                $packIds = array_filter(array_map('intval', $packIds));
                if (!empty($packIds)) {
                    (new \app\api\model\Package())->where('id', 'in', $packIds)
                        ->update(['share_id' => 0, 'updated_time' => getTime()]);
                }
            }
            
            if($result){
                return $this->renderSuccess('操作成功');
            }
        } else {
            // 其他审核状态：直接更新 inpack 的状态
            $data['updated_time'] = getTime();
            $resf = $Inpack->where('id', $param['verify']['id'])->update($data);
            if($resf){
                return $this->renderSuccess('操作成功');
            }
        }
        return $this->renderError('操作失败');
    }
    
    //拼团订单审核
    public function verifyOrder(){
        $param = $this->request->param();
        $SharingOrder = new SharingOrder();
        
        // 获取拼团订单
        $order = $SharingOrder->where('order_id', $param['id'])->find();
        if (!$order) {
            return $this->renderError('订单不存在');
        }
        
        $data = [
            'is_verify' => $param['verify']['status'],
            'update_time' => time()
        ];
        
        // 如果审核通过（status = 1），将订单状态设置为1（开团中）
        if ($param['verify']['status'] == 1) {
            $data['status'] = 1;
        }
        
        // 如果审核不通过，可以添加拒绝原因（如果有 reject_reason 字段）
        if (isset($param['verify']['reason']) && !empty($param['verify']['reason'])) {
            // 如果有拒绝原因字段，可以在这里添加
            // $data['reject_reason'] = $param['verify']['reason'];
        }
        
        // 更新拼团订单审核状态
        $result = $SharingOrder->where('order_id', $param['id'])->update($data);
        
        if($result){
            return $this->renderSuccess('审核成功');
        }
        return $this->renderError('审核失败');
    }
    
    //结束拼团
    public function endOrder(){
        $id = $this->request->param('id');
        $SharingOrder = new SharingOrder();
        
        // 获取拼团订单
        $order = $SharingOrder->where('order_id', $id)->find();
        if (!$order) {
            return $this->renderError('订单不存在');
        }
        
        // 检查订单状态是否为开团中
        if ($order['status']['value'] != 1) {
            return $this->renderError('只有开团中的订单才能结束');
        }
        
        // 更新订单状态为已完成
        $result = $SharingOrder->where('order_id', $id)->update([
            'status' => 2,
            'update_time' => time()
        ]);
        
        if($result){
            return $this->renderSuccess('拼团已结束');
        }
        return $this->renderError('操作失败');
    }
    
    //解散拼团
    public function disbandOrder(){
        $id = $this->request->param('id');
        $SharingOrder = new SharingOrder();
        $Inpack = new Inpack();
        $Package = new \app\api\model\Package();
        
        // 获取拼团订单
        $order = $SharingOrder->where('order_id', $id)->find();
        if (!$order) {
            return $this->renderError('订单不存在');
        }
        
        // 检查订单状态是否为开团中
        if ($order['status']['value'] != 1) {
            return $this->renderError('只有开团中的订单才能解散');
        }
        
        // 开启事务
        $SharingOrder->startTrans();
        try {
            // 1. 更新拼团订单状态为已解散
            $SharingOrder->where('order_id', $id)->update([
                'status' => 3,
                'update_time' => time()
            ]);
            
            // 2. 处理关联的包裹：将 share_id 设为 0，status 恢复为 2，inpack_id 设为 0
            $Package->where('share_id', $id)
                    ->update([
                        'share_id' => 0,
                        'status' => 2,
                        'inpack_id' => 0,
                        'updated_time' => getTime()
                    ]);
            
            // 3. 处理关联的集运单：将 share_id 设为 0，is_delete 设为 1
            $Inpack->where('share_id', $id)
                   ->where('is_delete', 0)
                   ->update([
                       'share_id' => 0,
                       'is_delete' => 1,
                       'updated_time' => getTime()
                   ]);
            
            // 提交事务
            $SharingOrder->commit();
            return $this->renderSuccess('拼团已解散');
            
        } catch (\Exception $e) {
            // 回滚事务
            $SharingOrder->rollback();
            return $this->renderError('解散失败：' . $e->getMessage());
        }
    }
    
    //已完结拼团
    public function completeOrder(){
        $id = $this->request->param('id');
        $SharingOrder = new SharingOrder();
        
        // 获取拼团订单
        $order = $SharingOrder->where('order_id', $id)->find();
        if (!$order) {
            return $this->renderError('订单不存在');
        }
        
        // 检查订单状态是否为已发货（状态4）
        if ($order['status']['value'] != 4) {
            return $this->renderError('只有已发货的订单才能标记为已完结');
        }
        
        // 更新订单状态为已完结
        $result = $SharingOrder->where('order_id', $id)->update([
            'status' => 5,
            'update_time' => time()
        ]);
        
        if($result){
            return $this->renderSuccess('拼团已完结');
        }
        return $this->renderError('操作失败');
    }
    
    // 代客户打包
    public function packForCustomer(){
        $packageIds = $this->request->param('package_ids');
        $shareId = $this->request->param('share_id');
        $lineId = $this->request->param('line_id');
        $addressId = $this->request->param('address_id');
        $remark = $this->request->param('remark', '');
        
        // 验证参数
        if (!$packageIds) {
            return $this->renderError('请选择要打包的包裹');
        }
        if (!$shareId) {
            return $this->renderError('拼团订单ID不能为空');
        }
        if (!$lineId) {
            return $this->renderError('请选择线路');
        }
        if (!$addressId) {
            return $this->renderError('请选择地址');
        }
        
        // 获取拼团订单信息
        $sharingOrder = (new SharingOrder())->where('order_id', $shareId)->find();
        if (!$sharingOrder) {
            return $this->renderError('拼团订单不存在');
        }
        
        // 解析包裹ID
        $idsArr = explode(',', $packageIds);
        $idsArr = array_filter(array_map('intval', $idsArr));
        if (empty($idsArr)) {
            return $this->renderError('请选择有效的包裹');
        }
        
        // 获取包裹信息
        $pack = (new PackageModel())->whereIn('id', $idsArr)->select();
        if (!$pack || count($pack) !== count($idsArr)) {
            return $this->renderError('打包包裹数据错误');
        }
        
        // 验证包裹状态（可以打包的状态：2已入库、3已拣货上架、4待打包、7已分拣下架）
        $status = array_unique(array_column($pack->toArray(), 'status'));
        if (count($status) == 1 && in_array($status[0], [1, 5, 6, 8, 9, 10, 11, -1])) {
            return $this->renderError('请选择可以打包的包裹');
        }
        
        // 验证包裹是否属于同一用户
        $packMember = array_unique(array_column($pack->toArray(), 'member_id'));
        if (count($packMember) != 1) {
            return $this->renderError('请选择同一用户的包裹进行打包');
        }
        $memberId = $packMember[0];
        
        // 验证包裹是否属于该拼团
        foreach ($pack as $p) {
            if ($p['share_id'] != $shareId) {
                return $this->renderError('包裹不属于该拼团订单');
            }
        }
        
        // 验证包裹是否在同一仓库
        $storageIds = array_unique(array_column($pack->toArray(), 'storage_id'));
        if (count($storageIds) != 1) {
            return $this->renderError('请选择同一仓库的包裹进行打包');
        }
        
        // 获取线路信息
        $line = (new Line())->find($lineId);
        if (!$line) {
            return $this->renderError('线路不存在，请重新选择');
        }
        
        // 获取地址信息
        $address = (new UserAddress())->find($addressId);
        if (!$address) {
            return $this->renderError('地址信息错误');
        }
        
        // 验证地址是否属于该用户
        if ($address['user_id'] != $memberId) {
            return $this->renderError('地址不属于该用户');
        }
        
            // 获取设置
            $noticesetting = setting::getItem('notice');
            $storesetting = setting::getItem('store');
            
            // 获取审核设置
            $wxapp_id = (new PackageModel())->getWxappId();
            $adminstyle = setting::getItem('adminstyle', $wxapp_id);
            $is_verify_free = isset($adminstyle['is_verify_free']) ? $adminstyle['is_verify_free'] : 0;
            // 如果is_verify_free==1需要审核，则is_doublecheck=0（未审核）；否则is_doublecheck=1（已审核/无需审核）
            $is_doublecheck = $is_verify_free == 1 ? 0 : 1;
            
            // 计算重量和体积
            $weight = (new PackageModel())->whereIn('id', $idsArr)->sum('weight');
            $volumn = (new PackageModel())->whereIn('id', $idsArr)->sum('volume');
            
            // 计算体积重
            $volumnweight = $volumn / $line['volumeweight'] * 1000000;
            if ($line['volumeweight_type'] == 20) {
                $volumnweight = round(($weight + ($volumn * 1000000 / $line['volumeweight'] - $weight) * $line['bubble_weight'] / 100), 2);
            }
            
            // 获取用户信息
            $userinfo = (new User())->where('user_id', $memberId)->find();
            
            // 开启事务
            $Package = new PackageModel();
            $Package->startTrans();
            try {
                // 创建集运单订单号
                $user_id = $memberId;
                if ($storesetting['usercode_mode']['is_show'] == 1) {
                    $member = (new User())->where('user_id', $memberId)->find();
                    $user_id = $member['user_code'] ?? $memberId;
                }
                
                $createSnfistword = $storesetting['createSnfistword'] ?? 'XS';
                $xuhao = ((new Inpack())->where(['member_id' => $memberId, 'is_delete' => 0])->count()) + 1;
                $shopname = ShopModel::detail($storageIds[0]);
                $shopAliasName = $shopname['shop_alias_name'] ?? 'XS';
                
                $orderno = createNewOrderSn(
                    $storesetting['orderno']['default'] ?? [],
                    $xuhao,
                    $createSnfistword,
                    $user_id,
                    $shopAliasName,
                    $address['country_id']
                );
                
                // 创建集运单
                $inpackOrder = [
                'order_sn' => $orderno,
                'remark' => $remark,
                'pack_ids' => $packageIds,
                'pack_services_id' => '',
                'storage_id' => $storageIds[0],
                'address_id' => $addressId,
                'free' => 0,
                'weight' => $weight,
                'cale_weight' => $weight,
                'line_weight' => $weight,
                'pay_type' => !empty($userinfo) ? $userinfo['paytype'] : 0,
                'volume' => $volumnweight,
                'pack_free' => 0,
                'other_free' => 0,
                'member_id' => $memberId,
                'country_id' => $address['country_id'],
                'created_time' => getTime(),
                'updated_time' => getTime(),
                'status' => 1,
                'source' => 1,
                'wxapp_id' => $wxapp_id,
                'line_id' => $lineId,
                'share_id' => $shareId, // 关联拼团订单ID
                'inpack_type' => 1, // 拼团包裹
                'is_doublecheck' => $is_doublecheck,
            ];
            
            $inpack = (new Inpack())->insertGetId($inpackOrder);
            if (!$inpack) {
                throw new \Exception('创建集运单失败');
            }
            
            $inpackdate = (new Inpack())->where('id', $inpack)->find();
            
            // 更新包裹信息
            $res = (new PackageModel())->whereIn('id', $idsArr)->update([
                'inpack_id' => $inpack,
                'status' => 5, // 待支付
                'line_id' => $lineId,
                'pack_service' => '',
                'address_id' => $addressId,
                'updated_time' => getTime()
            ]);
            
            if (!$res) {
                throw new \Exception('更新包裹信息失败');
            }
            
            // 更新包裹的物流信息
            foreach ($idsArr as $val) {
                $packnum = (new PackageModel())->where('id', $val)->value('express_num');
                if ($packnum) {
                    Logistics::updateOrderSn($packnum, $inpackdate['order_sn']);
                }
            }
            
            // 添加物流日志
            if ($noticesetting['packageit']['is_enable'] == 1) {
                Logistics::addInpackLogs($inpackdate['order_sn'], $noticesetting['packageit']['describe']);
            }
            
            // 是否自动计算运费
            $settingdata = setting::getItem('adminstyle', $inpackOrder['wxapp_id']);
            if (isset($settingdata) && $settingdata['is_auto_free'] == 1) {
                getpackfree($inpack, []);
            }
            
            // 提交事务
            $Package->commit();
            
            return $this->renderSuccess('打包成功，集运单号：' . $orderno);
            
        } catch (\Exception $e) {
            // 回滚事务
            $Package->rollback();
            return $this->renderError('打包失败：' . $e->getMessage());
        }
    }
    
    // 获取用户地址列表（用于AJAX请求）
    // 获取用户地址列表（用于AJAX请求）
    public function getUserAddresses(){
        $userId = $this->request->param('user_id');
        if (!$userId) {
            return $this->renderError('用户ID不能为空');
        }
        
        $addressList = (new UserAddress())->where('user_id', $userId)
                                          ->where('address_type', 0)
                                          ->select();
        
        // 将 Collection 转换为数组
        $addressArray = [];
        foreach ($addressList as $address) {
            $addressArray[] = $address->toArray();
        }
        
        return $this->renderSuccess('获取成功', '', $addressArray);
    }
      
}