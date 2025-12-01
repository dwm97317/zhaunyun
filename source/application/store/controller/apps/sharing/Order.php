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
       
        $res = $SharingOrder->where('order_id',$id)->update(['inpack_id'=> $data['delivery']['t_order_sn'],'status'=>6]);
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
            $line = (new Line())->getListAll();
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
            $line = (new Line())->getListAll();
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
      
}