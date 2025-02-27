<?php
namespace app\store\controller;
use app\api\model\Logistics;
use app\store\model\Inpack;
use app\store\model\InpackItem;
use app\store\model\Package;
use app\api\controller\Package as PackageModel;
use app\store\model\PackageItem;
use app\store\model\Line;
use app\store\model\ShelfUnitItem;
use app\store\model\Comment as CommentModel;
use app\store\model\Express as ExpressModel;
use app\store\model\Ditch as DitchModel;
use app\common\model\User;
use app\common\service\Message;
use app\store\model\User as UserModel;
use app\store\model\store\Shop as ShopModel;
use app\common\model\Setting;
use app\store\model\InpackService;
use app\api\model\Setting as SettingModel;
use app\store\model\user\UserLine;
use app\store\model\UserAddress;
use app\store\model\Batch;
use app\common\model\Setting as SettingModelPlus;
use app\store\model\sharing\SharingOrder;
use app\store\model\sharing\SharingOrderItem;
use app\store\model\PackageService;
use app\common\model\store\shop\Capital;
use app\store\model\store\shop\ShopBonus;
use app\store\model\store\shop\Clerk;
use app\api\model\dealer\Setting as SettingDealerModel;
use app\common\model\dealer\User as DealerUser;
use app\api\model\dealer\Referee as RefereeModel;
use app\common\model\dealer\Order as DealerOrder;
use app\common\service\qrcode;
use app\store\model\UploadFile;
use app\store\model\Track;
use app\common\library\Pinyin;
use app\common\library\AITool\BaiduTextTran;
use Dompdf\Dompdf;
use Dompdf\Options;
/**
 * 订单管理
 * Class Order
 * @package app\store\controller
 */
class TrOrder extends Controller
{
    /**
     * 待查验订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function verify_list()
    {
        return $this->getList('待查验订单列表', 'verify');
    }

    /**
     * 待支付订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function pay_list()
    {
        
        return $this->getNoPayList('待支付订单列表', 'pay');
    }

    /**
     * 待发货订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function payed_list()
    {
        return $this->getList('待发货订单列表', 'payed');
    }

    /**
     * 转运中订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function sending()
    {
        return $this->getList('已发货订单列表', 'sending');
    }
    
    /**
     * 转运中订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function sended()
    {
        return $this->getList('已到货订单列表', 'sended');
    }
    
    /**
     * 转运中订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function complete()
    {
        return $this->getList('已完成订单列表', 'complete');
    }
    
    /**
     * 转运中订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function quicklypack()
    {
        return $this->getQuicklypack('已完成订单列表', 'all');
    }
    
    /**
     * 超时件订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function exceedorder(){
        return $this->getExceedList('超时件订单列表', 'exceed');
    }
    
    /**
     * 添加子订单
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function addInpackItem(){
        $param = $this->request->param();
        $InpackItem = new InpackItem();
        if($InpackItem->addItem($param['inpack'])){
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($InpackItem->getError() ?: '添加失败');
    }
    
    
    /**
     * 订单列表
     * @param string $title
     * @param string $dataType
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getQuicklypack($title, $dataType)
    {
        // 订单列表
        $model = new Inpack;
        $set = Setting::detail('store')['values'];
        $list = $model->getQuicklypack($dataType, $this->request->param());
        foreach ($list as &$value) {
            $value['num'] = !empty($value['pack_ids'])?count(explode(',',$value['pack_ids'])):0;
            $value['sonnum'] =  (new InpackItem())->where(['inpack_id'=>$value['id']])->count();
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
           if ($dataType=='payed'){
                $value['down_shelf'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',7)->count();
                $value['inpack'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',8)->count();
           }
        }

        return $this->fetch('index', compact('list','dataType','set'));
    }
    
    
        
    //欠费用户列表
    public function arrearsuser(){
        $Inpack = new Inpack;
        $UserModel = new UserModel;
        //找到所有未结算的订单的用户id
        $packdata = $Inpack->where(['is_pay'=>2,'pay_type'=>2,'status'=>8,'is_delete'=>0])->field('member_id')->select()->toArray();
        $packdata = $this->uniquArr($packdata);
    
        foreach($packdata as $key =>$value){
            $list[$key] = $UserModel::detail($value['member_id']);
            $list[$key]['total'] = $Inpack->where(['is_pay'=>2,'pay_type'=>2,'status'=>8,'is_delete'=>0])->where('member_id',$value['member_id'])->count();
        }
        
        $set = Setting::detail('store')['values']['usercode_mode'];
        return $this->fetch('arrearsuser', compact('list','set'));
    }
    
        /**
     * 订单列表
     * @param string $title
     * @param string $dataType
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getExceedList($title, $dataType)
    {
        // 订单列表
        $model = new Inpack;
        $Line = new Line;
        $lineList = $Line->getListAll();
        $set = Setting::detail('store')['values'];
        $list = $model->getExceedList($dataType, $this->request->param());
        foreach ($list as &$value) {
            $value['num'] = !empty($value['pack_ids'])?count(explode(',',$value['pack_ids'])):0;
            $value['sonnum'] =  (new InpackItem())->where(['inpack_id'=>$value['id']])->count();
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
        }

        return $this->fetch('index', compact('list','dataType','set','lineList'));
    }
    
    /**
     * 获取用户每个月都出货量
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function getUserMouthWeight()
    {
        $param = $this->request->param();
        $Inpack =new Inpack;
        $mouthlist = [];
        $mouthlistt = [];
        $currentYear = date("Y");
        $lastYear = date("Y", strtotime("-1 year"));
        
        $setting = SettingModel::getItem('store',$this->getWxappId());
        $nowmouth = date('m');
        $nowmouth = ltrim($nowmouth, '0');
        for ($i = $nowmouth; $i <= 12; $i++) {
             $mouthlist[$i]['mouth'] = $lastYear.'-'.$i;
             $specifiedDate = date($currentYear.'-'.$i);
             $lastDayOfSpecifiedMonth = date($lastYear.'-m-t', strtotime($specifiedDate));
             
            //   dump($lastDayOfSpecifiedMonth);die;
             $mouthlist[$i]['sum'] = $Inpack->where('member_id',$param['user_id'])->where('is_delete',0)->where('created_time','between',[date('Y-'.$i.'-01'),$lastDayOfSpecifiedMonth])->SUM('weight') . $setting['weight_mode']['unit'];
             $mouthlist[$i]['total'] = $Inpack->where('member_id',$param['user_id'])->where('is_delete',0)->where('created_time','between',[date('Y-'.$i.'-01'),$lastDayOfSpecifiedMonth])->count();
        }
        
        
        for ($i = 1; $i <= $nowmouth; $i++) {
             $mouthlistt[$i]['mouth'] = $currentYear.'-'.$i;
             $specifiedDate = date($currentYear.'-'.$i);
             $lastDayOfSpecifiedMonth = date('Y-m-t', strtotime($specifiedDate));
             $mouthlistt[$i]['sum'] = $Inpack->where('member_id',$param['user_id'])->where('is_delete',0)->where('created_time','between',[date('Y-'.$i.'-01'),$lastDayOfSpecifiedMonth])->SUM('weight') . $setting['weight_mode']['unit'];
             $mouthlistt[$i]['total'] = $Inpack->where('member_id',$param['user_id'])->where('is_delete',0)->where('created_time','between',[date('Y-'.$i.'-01'),$lastDayOfSpecifiedMonth])->count();
        }
        
        $mouthlist = array_merge($mouthlist,$mouthlistt);
        return $this->renderSuccess('获取成功','',$mouthlist);
    }
    
    /**
     * 生成转运单号
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function createbatchname()
    {
        $param = $this->request->param();
        $Inpack =new Inpack;
        $settingDate = SettingModel::getItem('adminstyle',$this->getWxappId());
        $detail = Inpack::details($param['id']);
        $shopname = ShopModel::detail($detail['storage_id']);
        $address = (new UserAddress())->where(['address_id'=>$detail['address_id']])->find();
        // dump($address);die;
        $xuhao = ((new Inpack())->where(['member_id'=>$detail['member_id'],'is_delete'=>0])->count()) + 1;
        $batch = createNewOrderSn($settingDate['orderno']['default'],$xuhao,$settingDate['orderno']['first_title'],$detail['member_id'],$shopname['shop_alias_name'],$address['country_id']);
        return $this->renderSuccess('获取成功','',$batch);
    }
    
    public function package($id){
         // 订单详情
        $Package = new Package();
        $storesetting = SettingModel::getItem('store',$this->getWxappId());
        $list = $Package->with("packageimage.file")->where('inpack_id',$id)->select();
       
        foreach ($list as $k => $v){
            $list[$k]['shelf'] = (new ShelfUnitItem())->getShelfUnitByPackId($v['id']);
            $list[$k]['pakitem'] = (new PackageItem())->where('order_id',$v['id'])->select();
        }
        //   dump($list->toArray());die;
        return $this->fetch('package', compact('list','id','storesetting'));
    }
    
    //修改集运单所属用户id
    public function changeUser(){
        $ids = $this->postData('selectIds')[0];
        $user_id = $this->postData('user_id')[0];
        $Package = new Package();
        $idsArr = explode(',',$ids);
        $array = (new Inpack())->whereIn("id",$idsArr)->where('is_delete',0)->select();
        foreach ($array as $key => $val){
            $Package->where('inpack_id',$val['id'])->update(['member_id'=>$user_id,'updated_time'=>getTime(),'is_take'=>2]);
            $res = $val->save(['member_id'=>$user_id,'updated_time'=>getTime()]);
        }
        if (!$res){
            return $this->renderError('修改提交失败');
        }
        return $this->renderSuccess('修改提交成功');
    }
    
     /**
     * 获取集运路线的数据
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function getlinedata(){
        $param = $this->request->param();
        $model = new Inpack;
        $Line = new Line;
        $line = $model->field('line_id')->where('t_number',$param['ditch_id'])->where('is_delete',0)->group('line_id')->select();
        foreach ($line as $key =>$val){
            $data[$key] = $Line->details($val['line_id']);
            $data[$key]['total_order'] = $model->where('t_number',$param['ditch_id'])->where('line_id',$val['line_id'])->where('is_delete',0)->count();
            $data[$key]['exceed'] = $model->where('t_number',$param['ditch_id'])->where('line_id',$val['line_id'])->where('is_delete',0)->where('is_exceed',1)->count();
            if($data[$key]['total_order']==0){
                $data[$key]['exced_ratio'] = '0%';
            }else{
                $data[$key]['exced_ratio'] = number_format($data[$key]['exceed']/$data[$key]['total_order'],4)*100 .'%';
            }
            $data[$key]['total_free'] = $model->where('t_number',$param['ditch_id'])->where('line_id',$val['line_id'])->where('is_delete',0)->sum('real_payment');
        }
        return $this->renderSuccess('更新成功','',compact('data'));
    }
    
    /**
     * 集运单详情
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function orderdetail($id){
        $packageItem = [];
        $line = (new Line())->getList([]);
        // 订单详情
        $detail = Inpack::details($id);
        // dump($detail->toArray());die;
        if ($detail['status']>=2){
            $detail['total'] = $detail['free']+$detail['pack_free']+$detail['other_free'];
        }
        // $packages = explode(",",$detail['pack_ids']);
         $packagelist= (new Package())->where("inpack_id",$detail['id'])->select();
        //   dump($packagelist);die;
        foreach($packagelist as $key => $value){
          $packageItems[$key] = (new PackageItem())->where("order_id",$value['id'])->find();
          if(!empty($packageItems[$key])){
              $packageItem[$key] = $packageItems[$key];
          }
        }
        //  dump($packageItem->toArray());die;
        $packageService = (new PackageService())->getList([]);
        $detail['service'] = (new InpackService())->with('service')->where('inpack_id',$id)->select();
        // dump($PackageItem);die;
        //获取订单日志记录
        $detail['log'] = (new Logistics())->where('order_sn',$detail['order_sn'])->select();
        //获取到用户信息
        $detail['user'] = (new UserModel())->where('user_id',$detail['member_id'])->find();
        //获取到仓库信息
        $detail['storage'] = (new ShopModel())->where('shop_id',$detail['storage_id'])->find();
        //获取子订单记录
        $detail['sonitem'] = (new InpackItem())->where('inpack_id',$detail['id'])->select();
        $set = Setting::detail('store')['values'];
        return $this->fetch('orderdetail', compact(
            'detail','line','package','packageItem','packageService','set'
        ));
    }
    
     /**
     * 后台修改备注信息
     * @return false|int
     * @throws \think\exception\DbException
     */
    public function changeRemark(){
        $param = $this->request->param();
        $model = new Inpack();
        $detail = $model::details($param['id']);
        if($detail->save(['remark'=>$param['remark']])){
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError('更新失败');
    }

     /**
     * 后台批量修改集运单状态
     * @param $selectIds
     * @param $status
     * @return false|int
     * @throws \think\exception\DbException
     */
    public function upsatatus(){
       $ids = $this->postData('selectIds')[0];
       $status = $this->postData('pack')['status'];
       
       $noticesetting = SettingModelPlus::getItem('notice');
       $idsArr = explode(',',$ids);
       $model = new Inpack();
       //循环处理订单状态
       foreach ($idsArr as $v){
           $order =  $model->where(['id'=>$v])->find($v);
           $userData = (new User())->where('user_id',$order['member_id'])->find();
           $pack_ids = explode(',',$order['pack_ids']);
           $_up = [
             'status' => $status
           ];
           
           $status_map = [
               5 => '8',
               6 => '9',
               7 => '10',
               8 => '11',
           ];
           if($status==5){
               $_up['status'] = 3;
           }
           if($status==7){
               $_up['shoprk_time'] = getTime();
           }
           if($status==8){
               $_up['receipt_time'] = getTime();
           }
           $model->where(['id'=>$v])->update($_up);
           (new Package())->where('id','in',$pack_ids)->update(['status'=>$status_map[$status]]);
           if(strpos($noticesetting['dosend']['describe'],'code')){
                 $dosend = str_ireplace('{code}', $order['t_order_sn'], $noticesetting['dosend']['describe']);
            }else{
                 $dosend = $noticesetting['dosend']['describe'];
            }
           $status_remark = [
               5=> "退回到待发货状态，修改发货单号",
               6 => $dosend,
               7 => $noticesetting['reach']['describe'],
               8 => $noticesetting['take']['describe'],
           ];
           
           
           //处理模板消息
           $data['order_sn'] = $order['order_sn'];
           $data['order'] = $order;
           $data['order']['total_free'] = $order['free'];
           $data['order']['userName'] = $userData['nickName'];
           $data['order_type'] = 10;
           $data['order']['remark'] = $status_remark[$status];
           Logistics::addInpackLogs($order['order_sn'],$status_remark[$status]);
           Message::send('order.payment',$data);
       }    
       return $this->renderSuccess('更新成功');
    }
    
   /**
     * 已完成订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function complete_list()
    {
        return $this->getList('转运中订单列表', 'intransit');
    }

    /**
     * 已取消订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function cancel_list()
    {
        return $this->getList('已取消订单列表', 'cancel');
    }

    /**
     * 全部订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function all_list()
    {
        return $this->getList('全部订单列表', "all");
    }
    
    public function edit($id){
        $line = (new Line())->getListAll([]);
        // 订单详情
        $detail = Inpack::details($id);
        $detail['total'] = $detail['free']+$detail['pack_free']+$detail['other_free'];
        $set = Setting::detail('store')['values'];
        $is_auto_free = 0;
        if($set['is_auto_free']==1){
            $is_auto_free = 1;
        }
        return $this->fetch('detail', compact('detail','line','set','is_auto_free'));
    }
    
    
    /**
     * 点击编辑集运单，修改保存的函数
     * 2022年11月5日 增加图片增删功能
    */
    public function modify_save(){
       $model = (new Inpack());
       if ($model->edit($this->postData('data'))){
             return $this->renderSuccess('操作成功','javascript:history.back(1)');
       } 
       return $this->renderError($model->getError() ?: '操作失败');
    }
    
    //获取订单金额和用户余额
    public function balanceAndPrice(){
       $data = $this->request->param(); 
       $model = new Inpack();
       $user =  new UserModel;
       $inpackdata = $model::details($data['id']);
       $userdata = User::detail($data['user_id']);
       $payprice = $inpackdata['free'] + $inpackdata['pack_free'] + $inpackdata['other_free'] + $inpackdata['insure_free'];
       return  $this->renderSuccess('操作成功','',$result=['price' =>  $payprice ,'balance' =>$userdata['balance']]);
    }
    
     //使用现金支付支付集运单费用
    public function cashforprice(){
        $data = $this->request->param();
        $model = new Inpack();
        $user =  new UserModel;
        $inpackdata = $model::details($data['id']);
        $userdata = User::detail($data['user_id']);
 
        $payprice = $inpackdata['free'] + $inpackdata['pack_free'] + $inpackdata['other_free']  + $inpackdata['insure_free'];
        if($payprice==0){
            return $this->renderError('订单金额为0，请先设置订单金额');
        }
        //扣除余额，并产生一天用户的消费记录；减少用户余额；
        $res = $user->logUpdate(0,$data['user_id'],$payprice,date("Y-m-d H:i:s").',集运单'.$inpackdata['order_sn'].'使用现金支付'.$payprice.'（现金支付不改变用户余额）');
        if(!$res){
            return $this->renderError($user->getError() ?: '操作失败');
        }
              
        //累计消费金额
        $userdata->setIncPayMoney($payprice);
        $this->dealerData(['amount'=>$payprice,'order_id'=>$data['id']],$userdata);
        //修改集运单状态何支付状态
       
        if($inpackdata['status']==2){
            $inpackdata->where('id',$data['id'])->update(['real_payment'=>$payprice,'status'=>3,'is_pay'=>1,'is_pay_type'=>5,'pay_time'=>date('Y-m-d H:i:s',time())]);
        }else{
            $inpackdata->where('id',$data['id'])->update(['real_payment'=>$payprice,'is_pay'=>1,'is_pay_type'=>5,'pay_time'=>date('Y-m-d H:i:s',time())]);
        }
        
        return $this->renderSuccess('操作成功');
    }
    
    // 处理分销逻辑
     public function dealerData($data,$user){
        
        // 分销商基本设置
        $setting = SettingDealerModel::getItem('basic');
        $User = (new User());
        $dealeruser = new DealerUser();
        // 是否开启分销功能
        if (!$setting['is_open']) {
            return false;
        }
        $commission = SettingDealerModel::getItem('commission');
        // 判断用户 是否有上级
        $ReffeerModel = new RefereeModel;
        $dealerCapital = [];
        $dealerUpUser = $ReffeerModel->where(['user_id'=>$user['user_id']])->find();
        if (!$dealerUpUser){
            return false;
        }
        $firstMoney = $data['amount'] * ($commission['first_money']/100);
        $firstUserId = $dealerUpUser['dealer_id'];
        $remainMoney = $data['amount'] - $firstMoney;
    
        //给用户分配余额
        $dealeruser->grantMoney($firstUserId,$firstMoney);
        $dealerCapital[] = [
           'user_id' => $firstUserId,
           'flow_type' => 10,
           'money' => $firstMoney,
           'describe' => '分销收益',
           'create_time' => time(),
           'update_time' => time(),
           'wxapp_id' => $user['wxapp_id'],
        ];
        # 判断是否进行二级分销
        if ($setting['level'] >= 2) {
            // 查询一级分销用户 是否存在上级
            $dealerSencondUser = $ReffeerModel->where(['user_id'=>$dealerUpUser['dealer_id']])->find();
            if ($dealerSencondUser){
                $secondMoney = $remainMoney * ($commission['second_money']/100);
                $remainMoney = $remainMoney - $secondMoney;
                $secondUserId = $dealerSencondUser['dealer_id'];
                $dealerCapital[] = [
                   'user_id' => $secondUserId,
                   'flow_type' => 10,
                   'money' => $secondMoney,
                   'describe' => '分销收益',
                   'create_time' => time(),
                   'update_time' => time(),
                   'wxapp_id' => $user['wxapp_id'],
                ];
                $dealeruser->grantMoney($secondUserId,$secondMoney);
            }
        }
        # 判断是否进行三级分销
        if ($setting['level'] == 3) {
            // 查询二级分销用户 是否存在上级
            $dealerthirddUser = $ReffeerModel->where(['user_id'=>$dealerSencondUser['dealer_id']])->find();
            if ($dealerSencondUser){
                $thirdMoney = $remainMoney * ($commission['third_money']/100);
                $thirdUserId = $dealerthirddUser['dealer_id'];
                $dealerCapital[] = [
                   'user_id' => $thirdUserId,
                   'flow_type' => 10,
                   'money' => $thirdMoney,
                   'describe' => '分销收益',
                   'create_time' => time(),
                   'update_time' => time(),
                   'wxapp_id' => $user['wxapp_id'],
                ];
                $dealeruser->grantMoney($thirdUserId,$thirdMoney);
            }
        }
       
        // 生成分销订单
        $dealerOrder = [
            'user_id' => $user['user_id'],
            'order_id' => $data['order_id'],
            'order_price' => $data['amount'],
            'order_type' => 30,
            'first_user_id' => $firstUserId??0,
            'second_user_id' => $secondUserId??0,
            'third_user_id' => $thirdUserId??0,
            'first_money' => $firstMoney??0,
            'second_money' => $secondMoney??0,
            'third_money' => $thirdMoney??0,
            'is_invalid' => 0,
            'is_settled' => 1,
            'settle_time' => time(),
            'create_time' => time(),
            'update_time' => time(),
            'wxapp_id' => $user['wxapp_id']
        ];
             
        $resCapi = (new Capital())->allowField(true)->saveAll($dealerCapital);
        $resDeal = (new DealerOrder())->allowField(true)->save($dealerOrder);
        if(!$resCapi || !$resDeal){
            return false;
        }
        return true;
     }
    
    //使用余额抵扣集运单费用
    public function payyue(){
        $data = $this->request->param();
        $model = new Inpack();
        $user =  new UserModel;
        $inpackdata = $model::details($data['id']);
        $userdata = User::detail($data['user_id']);
        
        $payprice = $inpackdata['free'] + $inpackdata['pack_free'] + $inpackdata['other_free'];
      
        if(($userdata['balance'] < $payprice) || $payprice==0){
            return $this->renderError('用户余额不足');
        }
           
        //扣除余额，并产生一天用户的消费记录；减少用户余额；
        $res = $user->banlanceUpdate('remove',$data['user_id'],$payprice,date("Y-m-d H:i:s").',集运单'.$inpackdata['order_sn'].'消费余额'.$payprice);
        if(!$res){
            return $this->renderError($user->getError() ?: '操作失败');
        }
      
        //累计消费金额
        $userdata->setIncPayMoney($payprice);
        //修改集运单状态何支付状态
        $this->dealerData(['amount'=>$payprice,'order_id'=>$data['id']],$userdata);
        if($inpackdata['status']==2){
            $inpackdata->where('id',$data['id'])->update(['real_payment'=>$payprice,'status'=>3,'is_pay'=>1,'is_pay_type'=>0,'pay_time'=>date('Y-m-d H:i:s',time())]);
        }else{
            $inpackdata->where('id',$data['id'])->update(['real_payment'=>$payprice,'is_pay'=>1,'is_pay_type'=>0,'pay_time'=>date('Y-m-d H:i:s',time())]);
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 物流更新 
     * */
    public function logistics($id){
        $sendOrder = (new Inpack())->details($id);
        $Track = new Track;
        if (!$this->request->isAjax()){
            $tracklist = $Track->getAllList();
            return $this->fetch('send_order_logistics', compact('sendOrder','tracklist'));
        }
        // dump($this->postData('sendOrder'));die;
            $order_logic = $this->postData('sendOrder')['logistics'];
            if(empty($order_logic)){
                $trackData = $Track::detail($this->postData('sendOrder')['track_id']);
                $order_logic = $trackData['track_content'];
            }
            if(empty($order_logic) && empty($this->postData('sendOrder')['track_id'])){
                 return $this->renderError('请输入物流轨迹');
            }
        //发送用户以及用户信息
            $userId = $sendOrder['member_id'];
            $data['code'] = $id;
            $data['logistics_describe']= $order_logic;
            $user = User::detail($userId);
            if($user['email']){
                $this->sendemail($user,$data,$type=1);
            }
            //发送订阅消息，模板消息
            $data['order_sn'] = $sendOrder['order_sn'];
            $data['order'] = $sendOrder;
            $data['order']['total_free'] = $sendOrder['free'];
            $data['order']['userName'] = $user['nickName'];
            $data['order_type'] = 10;
            $data['order']['remark'] =$data['logistics_describe'] ;
            Message::send('order.payment',$data);
             $res = Logistics::addLog($sendOrder['order_sn'],$order_logic,$this->postData('sendOrder')['created_time']);
             if (!$res){
                return $this->renderError('物流更新失败');
            }
            
        return $this->renderSuccess('物流更新成功');
    }
    
    
    /**
     * 批量物流更新 
     * 2022年5月11日
     * */
    public function alllogistics(){
        $data = input();
        if(empty($data['logistics_describe'])){
            $trackData = Track::detail($data['track_id']);
            $data['logistics_describe'] = $trackData['track_content'];
        }
        if(empty($data['logistics_describe']) && empty($data['track_id'])){
             return $this->renderError('请输入订单物流信息');
        }
        
        $selectids = explode(',',$data['selectIds']);
        
        foreach ($selectids as $key =>$val){
            $sendOrder = (new Inpack())->details($val);
            //发送用户以及用户信息
            $userId = $sendOrder['member_id'];
            $data['code'] = $val;
            $user = User::detail($userId);
            
            //发送订阅消息，模板消息
            $data['order_sn'] = $sendOrder['order_sn'];
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
    



    /**
     * 订单详情
     * @param $order_id
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function detail($order_id)
    {
        // 订单详情
        $detail = OrderModel::detail($order_id);
        // 物流公司列表
        $expressList = ExpressModel::getAll();
        // 门店店员列表
        $shopClerkList = (new ShopClerkModel)->getList(true);
        return $this->fetch('detail', compact(
            'detail',
            'expressList',
            'shopClerkList'
        ));
    }
    
    // 发货物流
    public function delivery($id){
        $detail = Inpack::details($id);
        $ExpressModel = new ExpressModel();
        $DitchModel = new DitchModel();
        $track = $ExpressModel->getTypeList($type = 1);
        $ditchlist = $DitchModel->getAll();
        return $this->fetch('delivery', compact(
            'detail','track','ditchlist'
        ));
    }
    
    // 发货物流
    public function changesn($id){
        $detail = Inpack::details($id);
        $ExpressModel = new ExpressModel();
        $DitchModel = new DitchModel();
        $track = $ExpressModel->getTypeList($type = 1);
        $ditchlist = $DitchModel->getAll();
        return $this->fetch('changesn', compact(
            'detail','track','ditchlist'
        ));
    }
    
    // 转单物流
    public function zddeliverySave(){
       $model = (new Inpack());
       if ($model->zddeliverySave($this->postData('delivery'))){
           return $this->renderSuccess('操作成功');
       } 
       return $this->renderError($model->getError() ?: '操作失败');
    }
    
    // 打印面单
    public function printOrder($id){
        $detail = Inpack::details($id);
        return $this->fetch('orderPrint', compact(
            'detail'
        ));
    }
    
    // 发货物流
    public function deliverySave(){
       $model = (new Inpack());
       if ($model->modify($this->postData('delivery'))){
           return $this->renderSuccess('操作成功');
       } 
       return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 修改订单价格
     * @param $order_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function updatePrice($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->updatePrice($this->postData('order'))) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }
    
    
    //问题件删除
    public function orderdelete($id){
        $model = Inpack::details($id);
        $package_ids = $model['pack_ids'];
        $pack = explode(',',$package_ids);
        if(!empty($pack)){
          foreach ($pack as $key => $val){
            (new Package())->where('id',$val)->update(['status' => 2,'inpack_id'=>null]);
          }  
        }else{
            (new Package())->where('inpack_id',$model['id'])->update(['status' => 2,'inpack_id'=>null]);
        }
        if ($model->removedelete($id)) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }
    
     //集运单删除
    public function delete($id){
        $model = Inpack::details($id);
        //找到集运单所有的包裹单号，循环设置状态为2；
        $package_ids = $model['pack_ids'];
        $pack = explode(',',$package_ids);
        if(!empty($pack)){
          foreach ($pack as $key => $val){
            (new Package())->where('id',$val)->update(['status' => 2,'inpack_id'=>null]);
          }  
        }else{
            (new Package())->where('inpack_id',$model['id'])->update(['status' => 2,'inpack_id'=>null]);
        }
        if ($model->removedelete($id)) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

    /**
     * 订单列表
     * @param string $title
     * @param string $dataType
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getList($title, $dataType)
    {
        // 订单列表
        $model = new Inpack;
        $Line = new Line;
        $Clerk = new Clerk;
        $Track = new Track;
        $set = Setting::detail('store')['values'];
        $userclient =  Setting::detail('userclient')['values'];
        $adminstyle = Setting::detail('adminstyle')['values'];
        $params = $this->request->param();
        if(!isset($params['limitnum'])){
            $params['limitnum'] = isset($adminstyle['pageno'])?$adminstyle['pageno']['inpack']:15;
        }
        $list = $model->getList($dataType, $params);
        $tracklist = $Track->getAllList();
        $servicelist = $Clerk->where('clerk_authority','like','%is_myuser%')->where('clerk_authority','like','%is_myuserpackage%')->where('is_delete',0)->select();
        $pintuanlist = (new SharingOrder())->getAllList();
        $batchlist = (new Batch())->getAllwaitList([]);
        $shopList = ShopModel::getAllList();
        $lineList = $Line->getListAll();
        foreach ($list as &$value) {
            $value['num'] =  (new Package())->where(['inpack_id'=>$value['id'],'is_delete'=>0])->count();
            $value['sonnum'] =  (new InpackItem())->where(['inpack_id'=>$value['id']])->count();
        }
//   dump($pintuanlist->toArray());die;
        return $this->fetch('index', compact('adminstyle','list','dataType','set','pintuanlist','shopList','lineList','servicelist','userclient','batchlist','tracklist'));
    }
    
        //货到付款欠费用户列表
    public function nopayuser(){
        $Inpack = new Inpack;
        $UserModel = new UserModel;
        //找到所有未结算的订单的用户id
        $packdata = $Inpack->where(['is_pay'=>2,'pay_type'=>1,'is_delete'=>0])->where(in_array('status',[7,8]))->where('member_id','>',0)->field('member_id')->select()->toArray();
        $packdata = $this->uniquArr($packdata);
            // dump($packdata);die;
        foreach($packdata as $key =>$value){
            $list[$key] = $UserModel::detail($value['member_id']);
            $list[$key]['total'] = $Inpack->where(['is_pay'=>2,'pay_type'=>1,'is_delete'=>0])->where('status','in',[7,8])->where('member_id',$value['member_id'])->count();
        }
        
        $set = Setting::detail('store')['values']['usercode_mode'];
        return $this->fetch('nopayuser', compact('list','set'));
    }
    
        /**
     * 货到付款订单
     * @param $selectIds
     * @param $status
     * @return false|int
     * @throws \think\exception\DbException
     */
    public function nopayorder(){
        // 订单列表
        $model = new Inpack;
        $set = Setting::detail('store')['values'];
        $dataType = 'arrearsorder';
        $Line = new Line;
        $lineList = $Line->getListAll();
        $list = $model->getnopayorderList([7,8], $this->request->param());
        $pintuanlist = (new SharingOrder())->getList([]);
        $shopList = ShopModel::getAllList();
        foreach ($list as &$value) {
            $value['num'] = !empty($value['pack_ids'])?count(explode(',',$value['pack_ids'])):0;
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
           if ($dataType=='payed'){
                $value['down_shelf'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',7)->count();
                $value['inpack'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',8)->count();
           }
        }

        return $this->fetch('index', compact('list','dataType','set','pintuanlist','shopList','lineList'));
    }
    
        /**
     * 货到付款订单
     * @param $selectIds
     * @param $status
     * @return false|int
     * @throws \think\exception\DbException
     */
    public function arrearsorder(){
        // 订单列表
        $model = new Inpack;
        $set = Setting::detail('store')['values'];
        $dataType = 'arrearsorder';
        $Line = new Line;
        $lineList = $Line->getListAll();
        $list = $model->getArrearsList([8], $this->request->param());
        $pintuanlist = (new SharingOrder())->getList([]);
        $shopList = ShopModel::getAllList();
        foreach ($list as &$value) {
            $value['num'] = !empty($value['pack_ids'])?count(explode(',',$value['pack_ids'])):0;
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
           if ($dataType=='payed'){
                $value['down_shelf'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',7)->count();
                $value['inpack'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',8)->count();
           }
        }

        return $this->fetch('index', compact('list','dataType','set','pintuanlist','shopList','lineList'));
    }
    
    /**
     * 订单列表
     * @param string $title
     * @param string $dataType
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getNoPayList($title, $dataType)
    {
        // 订单列表
        $model = new Inpack;
        $set = Setting::detail('store')['values'];
        $list = $model->getNoPayList($dataType, $this->request->param());
        foreach ($list as &$value) {
            $value['num'] = !empty($value['pack_ids'])?count(explode(',',$value['pack_ids'])):0;
            $value['sonnum'] =  (new InpackItem())->where(['inpack_id'=>$value['id']])->count();
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
           if ($dataType=='payed'){
                $value['down_shelf'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',7)->count();
                $value['inpack'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',8)->count();
           }
        }

        return $this->fetch('index', compact('list','dataType','set'));
    }
    
    
    /***
     * 从集运单剔除包裹
     * 剔除的包裹恢复到待打包状态
     * 2022年5月8日  重构
     */
    public function delete_package(){
          $model = new Inpack();
          //单个移出集运单
          if(input('value') && input('id')){
              $detail = $model->find(input('value'));
              $package_ids = explode(',',$detail['pack_ids']);
              $key = array_search(input('id'),$package_ids);
              if ($key!==false){
                  unset($package_ids[$key]);
              }
              $update['status'] = 2;
              $update['inpack_id'] = null;
             (new Package())->where('id',input('id'))->update($update);
              $inpackUpdate['pack_ids'] = implode(',',$package_ids);
              $res = $model->where(['id'=>input('value')])->update($inpackUpdate);
          
              if ($res){
                   return $this->renderSuccess('修改成功');
              }
              return $this->renderError($model->getError() ?: '修改失败');
          }
          
          
          //批量移出集运单
          $ids= input("post.selectId/a");  //需要去除的包裹id；
          $item =input("post.selectItem"); // 集运单编号
          $detail = $model->find($item);
          $package_ids = explode(',',$detail['pack_ids']);
          //移除数组
 
          $package_ids = array_diff($package_ids,$ids);
          //循环更新包裹状态为已入库，可打包状态
          $update['status'] = 2;
          $update['inpack_id'] = null;
          foreach($ids as $key => $val){
             (new Package())->where('id',$val)->update($update);    
          } 
          //更新集运单的包裹

          $inpackUpdate['pack_ids'] = implode(',',$package_ids);
          $res = $model->where(['id'=>$item])->update($inpackUpdate);
          
          if ($res){
               return $this->renderSuccess('修改成功');
          }
          return $this->renderError($model->getError() ?: '修改失败');
    }
    
    // 添加快递进入集运单 
    public function add(){
        $order_id = $this->getData('id');
        $Inpack = new Inpack();
        $model = $Inpack::details($order_id);

        if (!$this->request->isAjax()){
            return $this->fetch('appendchild', compact('model'));
        }
    
        if ($Inpack->appendData($this->postData('delivery'))) {
            return $this->renderSuccess('修改成功','javascript:history.back(1)');
        }
        return $this->renderError($Inpack->getError() ?: '修改失败');
    }
    
        
    //二位数组去除重复的工具类
    private function uniquArr($array){
        $result = array();
        foreach($array as $k=>$val){
            $code = false;
            foreach($result as $_val){
                if($_val['member_id'] == $val['member_id']){
                    $code = true;
                    break;
                }
            }
            if(!$code){
                $result[]=$val;
            }
        }
        return $result;
    }
    
    // 计算价格
    public function caleamount(){
        $data = $this->postData();
        $line_id = $data['line_id'];
        $pakdata = Inpack::details($data['pid']);
        $line = (new Line())->find($line_id);
        if (!$line){
            return $this->renderError('线路不存在,请重新选择');
        }

        $free_rule = json_decode($line['free_rule'],true);
        $price = 0; // 总运费
        $allWeigth = 0;
        $caleWeigth = 0;
        $volumn = 0;
        $setting = SettingModel::getItem('store',$pakdata['wxapp_id']);
        
        if($setting['is_discount']==1){
            $UserLine =  (new UserLine());
            $linedata= $UserLine->where('user_id',$pakdata['member_id'])->where('line_id',$line['id'])->find();
           
                if($linedata){
                   $value['discount']  = $linedata['discount'];
                }else{
                   $value['discount'] =1;
                }
                //会员等级折扣
                $suer  = User::detail($pakdata['member_id']);
                
                if(!empty($suer['grade']) && $suer['grade']['status']==1 && $suer['grade']['equity']['discount']>0){
                    $value['discount'] = $suer['grade']['equity']['discount'] * 0.1;
                }
                //   dump($value['discount']);die;
        }else{
            $value['discount'] =1;
        }
          
        !isset($data['weight']) && $data['weight']=0;   
        // 计算体检重
        $weigthV = round(($data['length']*$data['width']*$data['height'])/$line['volumeweight'],2);
        if(!empty($data['length']) && !empty($data['width']) && !empty($data['height']) && $line['volumeweight_type']==20){
            $weigthV = round(($data['weight'] + (($data['length']*$data['width']*$data['height'])/$line['volumeweight'] - $data['weight'])*$line['bubble_weight']/100),2);
        }
        // 取两者中 较重者 
        $oWeigth = $weigthV>=$data['weight']*$line['volumeweight_weight'] ?$weigthV:$data['weight'];
        if($line['line_type']==1){
            $oWeigth = round(($data['length']*$data['width']*$data['height'])/$line['volumeweight'],2);
        }
        //关税和增值服务费用
        // $otherfree = $line['service_route'];
        $long = max($data['length'],$data['width'],$data['height']);
        $otherfree = getServiceFree($line['services_require'],$oWeigth,$long);
        // dump($otherfree);die;
        $insure_free = $pakdata['insure_free'];
        $reprice=0;
         //单位转化
           switch ($setting['weight_mode']['mode']) {
               case '10':
                    if($line['line_type_unit'] == 20){
                        $oWeigth = 0.001 * $oWeigth;
                    }
                    if($line['line_type_unit'] == 30){
                        $oWeigth = 0.00220462262185 * $oWeigth;
                    }
                   break;
               case '20':
                    if($line['line_type_unit'] == 10){
                        $oWeigth = 1000 * $oWeigth;
                    }
                    if($line['line_type_unit'] == 30){
                        $oWeigth = 2.20462262185 * $oWeigth;
                    }
                   break;
               case '30':
                   if($line['line_type_unit'] == 10){
                        $oWeigth = 453.59237 * $oWeigth;
                    }
                    if($line['line_type_unit'] == 20){
                        $oWeigth = 0.45359237 * $oWeigth;
                    }
                   break;
               default:
                   if($line['line_type_unit'] == 10){
                        $oWeigth = 1000 * $oWeigth;
                    }
                    if($line['line_type_unit'] == 30){
                        $oWeigth = 2.20462262185 * $oWeigth;
                    }
                   break;
           }
           $oWeigth = round($oWeigth,2);

        $lines['predict'] = [
              'weight' => $oWeigth,
              'price' => '包裹重量超限',
              'service'=>0,
           ]; 
        switch ($line['free_mode']) {
            case '1':
               $free_rule = json_decode($line['free_rule'],true);
               $size = sizeof($free_rule);    
               if(($oWeigth>= $free_rule[0]['weight'][0]) && ($oWeigth<= $free_rule[$size-1]['weight'][1])){
   
                  foreach ($free_rule as $k => $v) {
                      if ($oWeigth>$v['weight'][1]){
                            $reprice += ($v['weight'][1] - $v['weight'][0])*$v['weight_price'];
                            continue;
                      }else{
                           $reprice += ($oWeigth - $v['weight'][0])*$v['weight_price'];
                           break;
                      }
                  }
                  $lines['predict'] = [
                    'weight' => $oWeigth,
                    'price' => ($reprice+ $free_rule[0]['weight_price']*$free_rule[0]['weight'][0]+$otherfree)*$value['discount'],
                    'rule' => $free_rule,
                    'service' =>0,
                  ];         
               }else{
                    break;
               }
               break;
            case '2':
                //首重价格+续重价格*（总重-首重）
               $free_rule = json_decode($line['free_rule'],true);
               foreach ($free_rule as $k => $v) {
                    //判断时候需要取整
                    if($line['is_integer']==1){
                        if($oWeigth<$v['first_weight']){
                            $oWeigth = $v['first_weight'];
                        }
                        $ww = ceil((($oWeigth-$v['first_weight'])/$v['next_weight']));
                    }else{
                        $ww = ($oWeigth-$v['first_weight'])/$v['next_weight'];
                    }
                    $lines['predict'] = [
                      'weight' => $oWeigth,
                      'price' => ($v['first_price']+ $ww*$v['next_price'] + $otherfree)*$value['discount'],
                      'rule' => $v,
                      'service' =>0,
                    ];   
               }
          
                break;
            case '3':
                $free_rule = json_decode($line['free_rule'],true);
               foreach ($free_rule as $k => $v) {
                   if ($oWeigth >= $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<=$v['weight'][1]){
                          $lines['predict'] = [
                              'weight' => $oWeigth,
                              'price' => ($v['weight_price'] + $otherfree)*$value['discount'],
                              'rule' => $v,
                              'service' =>0,
                          ];   
                      }
                   }
                  
               }

               break;
               
            case '4':
                $free_rule = json_decode($line['free_rule'],true);
                
               foreach ($free_rule as $k => $v) {
                    //判断时候需要取整
                    if($line['is_integer']==1){
                        $ww = ceil(floatval($oWeigth)/floatval($v['weight_unit']));
                    }else{
                        $ww = floatval($oWeigth)/floatval($v['weight_unit']);
                    }
                   if ($oWeigth >= $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<=$v['weight'][1]){
                          !isset($v['weight_unit']) && $v['weight_unit']=1;
                          $lines['predict'] = [
                              'weight' => $oWeigth,
                              'price' => (floatval($v['weight_price']) * $ww + floatval($otherfree))*$value['discount'],
                              'rule' => $v,
                              'service' =>0,
                          ]; 
            
                      }
                   }
               }
               
               break;
               
               case '5':
                $free_rule = json_decode($line['free_rule'],true);
            
               foreach ($free_rule as $k => $vv) {
                   
                   //判断时候需要取整
                if($vv['type']=="1"){
                    if($line['is_integer']==1){
                        $ww = ceil((($oWeigth-$vv['first_weight'])/$vv['next_weight']));
                    }else{
                        $ww = ($oWeigth-$vv['first_weight'])/$vv['next_weight'];
                    }
                   
                    if ($oWeigth >= $vv['first_weight']){
                          $lines['sortprice'] =($vv['first_price']+ $ww*$vv['next_price'] + $otherfree)*$value['discount'];
                          $lines['predict'] = [
                              'weight' => $oWeigth,
                              'price' => number_format(($vv['first_price']+ $ww*$vv['next_price'] + $otherfree)*$value['discount'],2),
                              'rule' => $vv,
                              'service' =>0,
                          ]; 
                  }else{
                      $lines['sortprice'] = $vv['first_price'];
                      $lines['predict'] = [
                              'weight' => $oWeigth,
                              'price' => number_format(($vv['first_price']+ $otherfree)*$value['discount'],2),
                              'rule' => $vv,
                              'service' =>0,
                          ]; 
                  }
                }
                
                if($vv['type']=="2"){
           
                       if ($oWeigth >= $vv['weight'][0]){
                          if (isset($vv['weight'][1]) && $oWeigth<=$vv['weight'][1]){
                              $lines['sortprice'] =(floatval($vv['weight_price']) + $otherfree)*$value['discount'] ;
                              $lines['predict'] = [
                                  'weight' => $oWeigth,
                                  'price' => number_format((floatval($vv['weight_price']) + $otherfree)*$value['discount'],2),
                                  'rule' => $vv,
                                  'service' =>0,
                              ];   
                          }
                       }
                   
                }
       
                if($vv['type']=="3"){
                   //判断时候需要取整
                    if($line['is_integer']==1){
                        $ww = ceil(floatval($oWeigth)/floatval($vv['weight_unit']));
                    }else{
                        $ww = floatval($oWeigth)/floatval($vv['weight_unit']);
                    }
                   if ($oWeigth >= $vv['weight'][0]){
                      if (isset($vv['weight'][1]) && $oWeigth<=$vv['weight'][1]){
                          !isset($vv['weight_unit']) && $vv['weight_unit']=1;
                          $lines['sortprice'] =(floatval($vv['weight_price']) *$ww  + floatval($otherfree))*$value['discount'] ;
                          $lines['predict'] = [
                              'weight' => $oWeigth,
                              'price' => number_format((floatval($vv['weight_price']) * $ww + floatval($otherfree))*$value['discount'],2),
                              'rule' => $vv,
                              'service' =>0,
                          ]; 
                      }
                   }
                }
               }
               
               break;
               
               case '6':
                $free_rule = json_decode($line['free_rule'],true);

                foreach ($free_rule as $k => $v) {
                    if($oWeigth >= $v['weight'][0] ){
                       //判断时候需要取整
                            if($line['is_integer']==1){
                                $ww = ceil((($oWeigth-$v['first_weight'])/$v['next_weight']));
                            }else{
                                $ww = ($oWeigth-$v['first_weight'])/$v['next_weight'];
                            }
                       
                           if ($oWeigth >= $v['first_weight']){
                                  $lines['sortprice'] =($v['first_price']+ $ww*$v['next_price'] + $otherfree)*$value['discount'];
                                  $lines['predict'] = [
                                      'weight' => $oWeigth,
                                      'price' => number_format(($v['first_price']+ $ww*$v['next_price'] + $otherfree)*$value['discount'],2),
                                      'rule' => $v
                                  ]; 
                            }else{
                              $lines['sortprice'] = $v['first_price'];
                              $lines['predict'] = [
                                      'weight' => $oWeigth,
                                      'price' => number_format(($v['first_price']+ $otherfree)*$value['discount'],2),
                                      'rule' => $v
                                  ]; 
                          }
                        }
               }
               break;
        }
        $PackageService = new PackageService(); 
        $pricethree = 0;
        $pricetwo = $lines['predict']['price'];
        if(count($pakdata['inpackservice'])>0){
          $servicelist = $pakdata['inpackservice'];
          foreach ($servicelist as $val){
              $servicedetail = $PackageService::detail($val['service_id']);
            //   dump($servicedetail);die;
              if($servicedetail['type']==0){
                  $lines['predict']['service'] = $lines['predict']['service'] + $servicedetail['price'];
                  $pricethree = floatval($pricethree) + floatval($servicedetail['price']);
              }
              if($servicedetail['type']==1){
                  $lines['predict']['service'] = floatval($pricetwo)*floatval($servicedetail['percentage'])/100 + floatval($lines['predict']['service']);
                  $pricethree = floatval($pricetwo)* floatval($servicedetail['percentage'])/100 + floatval($pricethree);
              }
          }
        }
           
        $lines['predict']['price'] = number_format(floatval($lines['predict']['price']),2);
        $settingdata  = SettingModel::getItem('store',$line['wxapp_id']);
        //不需要主动更新费用
        if($settingdata['is_auto_free']==0){
           $lines['predict']['price'] = 0;
        }
        
        //
        
         
        return $this->renderSuccess(['oWeigth'=>$oWeigth,'price'=>str_replace(',','',$lines['predict']['price']),'weightV'=>$weigthV,'packfree'=>$pricethree,'insure_free'=>$insure_free]);
    }
    
    /**
     * 评价列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function comment()
    {
        $model = new CommentModel;
        $list = $model->getList($type=1);
        // dump($list->toArray());die;
        foreach ($list as $k =>$v){
              $list[$k]["score"] = json_decode($v['score'],true);
        }
        return $this->fetch('tr_order/comment', compact('list'));
    }
    
        /**
     * 评价详情
     * @param $comment_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function commentdetail($comment_id)
    {
        // 评价详情

        $model = CommentModel::detail($comment_id);
        $model['score']=json_decode($model['score'],true);
        // dump($model->toArray());die;
        if (!$this->request->isAjax()) {
            return $this->fetch('comment_detail', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('comment'))) {
            return $this->renderSuccess('更新成功', url('tr_order/comment'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    /**
     * 合并订单
     * @return mixed
     * @throws \think\exception\DbException
     */
     
     public function hedan()
    {
       $model = new Inpack();
       $Package = new Package;
       $ids= input();
       $ids = array_keys($ids);
       $idsArr = explode(',',$ids[0]);
       $arruser = [];

       //判断所有包裹是否同一用户
      foreach($idsArr as $key =>$val ){
           $pack = $model->where('id',$val)->find();
           $arruser[] = $pack['member_id'];
      }
     
      if(count(array_unique($arruser))>1){
          return $this->renderError('请选择相同用户的集运单');
      }
       //将包裹的packids合并在一个集运单中，并将另外一个集运单状态设置为isdelete；
       //合并包裹思路一：将其他集运单状态改为删除，将快递单id添加到第一个集运单中；
       //合并包裹思路二：新创建新的集运单，之前的集运单全部改为删除状态；此方案可用于创建多用户拼邮；
      
        //思路 随意找到集运单的一个基本信息，去除id即可使用基础数据，创建新的order_sn即可
          foreach($idsArr as $key =>$val ){
                  $res = $model->where('id',$val)->update(['is_delete' => 1 ]);
                  if(!$res){
                    return $this->renderError('合并失败');
                  }
            }     
         
          $newpack = $model->find($idsArr[0])->toArray();
          unset($newpack['id']);
          $newpack['updated_time'] = getTime();
          $newpack['created_time'] = getTime();
          $newpack['is_delete'] = 0;
          $newpack['is_pay_type'] = $newpack['is_pay_type']['value'];
          $newpack['pay_type'] = $newpack['pay_type']['value'];
          $result = $model->insertGetId($newpack);
          if (!$result){
              return $this->renderSuccess('合并失败');
          }
          foreach ($idsArr as $va){
             $Package->where('inpack_id',$va)->update(['inpack_id'=>$result]); 
          }
       //返回成功状态并提示合并成功；
       return $this->renderSuccess('合并成功');
    }

    /**
     * 拆包合包
     * 将拆出的包裹合并成新的集运单
     * @return mixed
     * @throws \think\exception\DbException
     */
     
     public function packageinout()
    {
         $model = new Inpack();
         $Package = new Package;
         $PackageItem = new PackageItem();
        //批量移出集运单
          $ids= input("post.selectId/a");  //需要移出的包裹id；
          $item =input("post.selectItem"); // 集运单编号
          $detail = $model->find($item);
          
          $result = $Package->where('id','in',$ids)->update(['inpack_id'=>null]);
          if (!$result){
              return $this->renderSuccess('拆包失败');
          }
          //将选中的包裹单号合并为packs_id需要的数据类型
          $newpack = $detail->toArray();
        //   dump($detail);die;
          unset($newpack['id']);
          unset($newpack['is_pay_type']);
          unset($newpack['pay_type']);
          unset($newpack['pack_ids']);
          $newpack['order_sn'] = createSn();
          $newpack['is_pay_type'] = $detail['is_pay_type']['value'];
          $newpack['pay_type'] = $detail['pay_type']['value'];
          
          $resultid = $model->insertGetId($newpack);
             
          $resultpack = $Package->where('id','in',$ids)->update(['inpack_id'=>$resultid]);
        //   foreach ($ids as $value) {
        //       // code...
        //   }
          if ($resultpack){
            //   $PackageItem->where('order_id','in',$ids)->update(['order_id'=>]);
              return $this->renderSuccess('拆包合包成功');
          }
          return $this->renderError($Package->getError() ?: '拆包合包失败');
    }
    
    
    /**
     * 加入拼团订单
     * @return mixed
     * @throws \think\exception\DbException
     */
     
    public function pintuan()
    {
       $model = new Inpack();
       $pintuan_id= input('pintuan_id');
       $selectIds = input('selectIds');
       $idsArray = explode(',',$selectIds);
       if(empty($pintuan_id)){
           return $this->renderError($model->getError() ?: '请选择拼团订单');
       }
       $SharingOrderItem = new SharingOrderItem();
       $res= $SharingOrderItem->insertInpack($idsArray,$pintuan_id);
        if(!$res){
            return $this->renderError($SharingOrderItem->getError() ?: '添加失败');
        }
        return $this->renderSuccess('添加成功');
    }
    
    
    // 打印面单
    public function expressBill(){
       $id = $this->request->param('id');    
       $inpack = (new Inpack());
       $data = $inpack::details($id);
       if(!$data['t_order_sn']){
           return $this->renderError('转运单号为空');
       }
       $adminstyle = Setting::getItem('adminstyle',$data['wxapp_id']);
       $data['setting'] = Setting::getItem('store',$data['wxapp_id']);
       if(!empty($data['member_id'])){
           $member  = UserModel::detail($data['member_id']);
           $data['name'] = $member['nickName'];
           if($data['setting']['usercode_mode']['is_show']!=0){
              $data['member_id'] = $member['user_code'];
           }
       }
       $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorSVG(); #创建SVG类型条形码
       $data['barcode'] = $generatorSVG->getBarcode($data['t_order_sn'], $generatorSVG::TYPE_CODE_128,$widthFactor = 1.5, $totalHeight = 40);

       $data['shoujianren'] = $data['address']['name'];
       isset($data['setting']['address_setting']['is_tel_code'])  && $data['setting']['address_setting']['is_tel_code']==1 && $data['shoujianren']=$data['shoujianren'].$data['address']['tel_code'];
       $data['shoujianren']= $data['shoujianren'].'  '.$data['address']['phone'];

       $data['address']['xiangxi'] = $data['address']['country'];
       isset($data['setting']['address_setting']['is_province'])  && $data['setting']['address_setting']['is_province']==1 && $data['address']['xiangxi'] = $data['address']['xiangxi'].$data['address']['province'];
       
       isset($data['setting']['address_setting']['is_city'])  && $data['setting']['address_setting']['is_city']==1 && $data['address']['xiangxi'] = $data['address']['xiangxi'].$data['address']['city'];
       
       isset($data['setting']['address_setting']['is_region'])  && $data['setting']['address_setting']['is_region']==1 && $data['address']['xiangxi'] = $data['address']['xiangxi'].$data['address']['region'];
       
       isset($data['setting']['address_setting']['is_street'])  && $data['setting']['address_setting']['is_street']==1 && $data['address']['xiangxi'] = $data['address']['xiangxi'].$data['address']['street'];
       
       isset($data['setting']['address_setting']['is_door'])  && $data['setting']['address_setting']['is_door']==1 && $data['address']['xiangxi'] = $data['address']['xiangxi'].$data['address']['door'];
       
       $data['address']['xiangxi'] = $data['address']['xiangxi'] . $data['address']['detail'];
       

       switch ($adminstyle['delivertempalte']['orderface']) {
           case '10':
               echo $this->template10($data);
               break;
           case '20':
               echo $this->template20($data);
               break;
           default:
                echo $this->template10($data);
               break;
       }
    }
    
     // 打印标签
    public function expressLabel(){
       $id = $this->request->param('id');    
       $inpack = (new Inpack());
       $data = $inpack->getExpressData($id);
       if(!$data['order_sn']){
           return $this->renderError('转运单号为空');
       }
       $adminstyle = Setting::getItem('adminstyle',$data['wxapp_id']);
    //   dump($adminstyle);die;
       $data['setting'] = Setting::getItem('store',$data['wxapp_id']);
       if(!empty($data['member_id'])){
           $member  = UserModel::detail($data['member_id']);
           $data['name'] = $member['nickName'];
           if($data['setting']['usercode_mode']['is_show']!=0){
              $data['member_id'] = $member['user_code'];
           }
       } 
       
       if(!empty($data['address_id'])){
           $result = (new UserAddress())->where('address_id',$data['address_id'])->where('address_type',2)->find();
           empty($result) && $data['address_id']="未选自提点";
       }
       $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorSVG(); #创建SVG类型条形码
       $data['barcode'] = $generatorSVG->getBarcode($data['order_sn'], $generatorSVG::TYPE_CODE_128,$widthFactor =2, $totalHeight = 50);
       $data['cover_id'] = UploadFile::detail($data['setting']['cover_id']);
// dump($data->toArray());die;
       switch ($adminstyle['delivertempalte']['orderface']) {
           case '10':
               echo $this->label10($data);
               break;
           case '20':
               echo $this->label20($data);
               break;
           default:
                echo $this->label10($data);
               break;
       }
    }
    
         // 打印账单
    public function freelistLabel(){
       $id = $this->request->param('id');    
       $inpack = (new Inpack());
       $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorJPG(); #创建SVG类型条形码
       $data = $inpack->getExpressData($id);
       $data['setting'] = Setting::getItem('store',$data['wxapp_id']);
       $data['adminstyle'] = Setting::getItem('adminstyle',$data['wxapp_id']);
       $data['name'] = '';
       if(!empty($data['member_id'])){
           $member  = UserModel::detail($data['member_id']);
           $data['name'] = $member['nickName'];
           if($data['setting']['usercode_mode']['is_show']!=0){
              $data['member_id'] = $member['user_code'];
           }
       }
       if($data['status']==7){
           $data['receipt_time'] = $data['shoprk_time'];
       }
       if($data['status']<7){
           $data['receipt_time'] = $data['created_time'];
       }
       $data['total_free'] = $data['free']+$data['other_free']+$data['pack_free'];
       $data['barcode'] = base64_encode($generatorSVG->getBarcode($data['order_sn'], $generatorSVG::TYPE_CODE_128,$widthFactor =1.8, $totalHeight = 50));
       echo $this->free($data);
    }
    
   
    public function free($data){
    if($data['adminstyle']['freestyle']==10){
      $freestyle = '<tr>
    		<td width="152" height="36" class="pl center font_xl">
    		    金额Payment
    		</td>
    		<td width="240" class="center font_xl">
    		     '.$data['total_free'].'
    		</td>
    	</tr>';  
    }else{
        $freestyle = '<tr>
    		<td width="152" height="36" class="pl center font_xl">
    		    金额Payment
    		</td>
    		<td width="240" class="center font_xl">
    		    <table class="newtable">
        		    <tr>
        		        <td class="newtd">基础路线费用：'.$data['free'].'</td>
        		     </tr>
        		     <tr>
        		        <td class="newtd">打包服务费：'.$data['pack_free'].'</td>
        		     </tr>
        		     <tr>
        		        <td class="newtd">其他杂费：'.$data['other_free'].'</td>
        		     </tr>
        		     <tr>
        		        <td class="newtd">总费用：'.$data['total_free'].'</td>
        		     </tr>
    		    </table>
    		</td>
    	</tr>';
    }
    
    return  $html = '<style>
	* {
		margin: 0;
		padding: 0
	}

	table {
		margin-top: -1px;
		font: 12px "Microsoft YaHei", Verdana, arial, sans-serif;
		border-collapse: collapse
	}
	.newtable{
	    width:100%;
	    height:100%;
	}
    .newtd{
        border:none;
    }
	table.container {
    	margin-top:10px;
		width: 400px;
		height:600px;
		border: 2px solid #000;
		border-bottom: 0;
		margin:10px;
	}

	table td.center {
		text-align: center
	}

	table td{
		border: 1px solid #000
	}
	.font_xxl {
		font-size: 18px;
		font-weight: bold
	}
	.font_xl {
		font-size: 14px;
		font-weight: bold
	}
	
	.paddingleft{
	    padding-left:10px;
	}

	.font_xxxl {
		font-size: 32px;
		font-weight: bold
	}
	.font_12{font-size: 12px;font-weight: bold;}
</style>
<table class="container">
	<tr>
		<td width="152" height="26" class="pl center font_xxl">
		    '.$data['setting']['name'].'
		</td>
		<td width="240" class="center font_xxl">
		    '.$data['receipt_time'].'
		</td>
	</tr>
	<tr>
		<td width="152" height="36" class="pl center font_xl">
		    签收人Receiver
		</td>
		<td width="240" class="center font_xl">
		    '.$data['name'].'('.$data['member_id'].')'.'
		</td>
	</tr>
	<tr>
		<td width="152" height="36" class="pl center font_xl">
		    客户单号Tracking No
		</td>
		<td width="240" class="center font_xl">
		    '.$data['order_sn'].'
		</td>
	</tr>
	<tr>
		<td class="center" colspan=2 height="120">
		      <img style="width:250px;" src="data:image/png;base64,'. $data['barcode'] .'"/><br>
		      '.$data['order_sn'].'
		</td>
	</tr>
	<tr>
		<td width="152" height="36" class="pl center font_xl">
		    重量Weight
		</td>
		<td width="240" class="center font_xl">
		     '.$data['cale_weight'].'
		</td>
	</tr>
	'.$freestyle.'
	<tr>
		<td colspan=2 class="paddingleft left font_xl" height="36">
		   '.$data['address']['name']. hide_mobile($data['address']['phone']).'<br>
		   '.$data['address']['country'].'
					'.(!empty($data['address']['province'])?$data['address']['province']:'').'
					'.(!empty($data['address']['city'])?$data['address']['city']:'').'
					'.(!empty($data['address']['region'])?$data['address']['region']:'').'
					'.(!empty($data['address']['district'])?$data['address']['district']:'').'
					'.(!empty($data['address']['street'])?$data['address']['street']:'').'
					'.(!empty($data['address']['door'])?$data['address']['door']:'').'
				<strong>'.$data['address']['detail'].'</strong>
					'.$data['address']['code'].'
		</td>
	</tr>
</table>';
} 
    
    
    // 渲染标签模板A
    public function label10($data){
      return  $html = '<style>
	* {
		margin: 0;
		padding: 0
	}

	table {
		margin-top: -1px;
		font: 12px "Microsoft YaHei", Verdana, arial, sans-serif;
		border-collapse: collapse
	}

	table.container {
		width: 375px;
		border: 1px solid #000;
		border-bottom: 0
	}

	table td {
		border-top: 1px solid #000;
		border-bottom: 1px solid #000
	}

	table.nob {
		width: 100%
	}

	table.nob td {
		border: 0
	}

	table td.center {
		text-align: center
	}

	table td.right {
		text-align: right
	}

	table td.pl {
		padding-left: 5px
	}

	table td.br {
		border-right: 1px solid #000
	}

	table.nobt,
	table td.nobt {
		border-top: 0
	}

	table.nobb,
	table td.nobb {
		border-bottom: 0
	}

	.font_s {
		font-size: 10px;
		-webkit-transform: scale(0.84, 0.84);
		*font-size: 10px
	}

	.font_m {
		font-size: 16px
	}

	.font_l {
		font-size: 16px;
		font-weight: bold
	}

	.font_xl {
		font-size: 18px;
		font-weight: bold
	}

	.font_xxl {
		font-size: 28px;
		font-weight: bold
	}

	.font_xxxl {
		font-size: 32px;
		font-weight: bold
	}
	.padding-top-20{
	    padding-top:20px;
	}
	.country{
    	font-size: 37px;
        padding: 0px;
        margin: 0px;
        font-weight: bold;
        width: 100px;
	}
	.barcode{text-align:center;}
	.font_12{font-size: 12px;font-weight: bold;}
</style>
<table class="container">
    <tr><td width="152" height="76" class="pl center font_xxl">
		    <table class="nob">
		        <tr>
		            <td class="font_xxl ">'.$data['setting']['name'].'</td>
		        </tr>
		        <tr>
		            <td>'.$data['setting']['desc'].'</td>
		        </tr>
		    </table>
		</td>
	</tr>
	<tr>
		<td width="240" class="center padding-top-20">
			<table class="nob">
				<tr>
					<td class="barcode">'.$data['barcode'].'</td>
					
				</tr>
				<tr>
					<td class="center font_12">'.$data['order_sn'].'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td class="font_xxxl pl">
		   目的地： '.$data['country']['title'].'
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td>
			<table class="nob">
				<tr>
					<td class="pl" width="65" height="24">件数：</td>
					<td width="60">1</td>
					<td width="80">重：</td>
					<td>'.$data['cale_weight'].'</td>
				</tr>
				<tr>
					<td class="pl" height="50" valign="top">配货信息：</td>
					<td colspan="3"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';
    }
    
    
        // 渲染标签模板A
    public function label20($data){
     return  $html = '<style>
	* {
		margin: 0;
		padding: 0
	}

	table {
		margin-top: -1px;
		font: 12px "Microsoft YaHei", Verdana, arial, sans-serif;
		border-collapse: collapse
	}

	table.container {
	    width:527px;
		border-bottom: 0
	}
	
	.conta {
            display: flex; /* 设置容器为flex布局 */
            justify-content: center;
            align-items: center;
    }

	table td {
	}

	table.nob {
	    width:500px;
	}

	table.nob td {
		border: 0
	}

	table td.center {
		text-align: center
	}

	table td.right {
		text-align: right
	}

	table td.pl {
		padding-left: 5px;
		margin:4px 0;
	}

	table td.br {
		border-right: 1px solid #000
	}

	table.nobt,
	table td.nobt {
		border-top: 0
	}

	table.nobb,
	table td.nobb {
		border-bottom: 0
	}

	.font_s {
		font-size: 10px;
		-webkit-transform: scale(0.84, 0.84);
		*font-size: 10px
	}

	.font_m {
		font-size: 14px
	}

	.font_l {
		font-size: 16px;
		font-weight: bold
	}

	.font_xl {
		font-size: 18px;
		font-weight: bold
	}

	.font_xxl {
		font-size: 28px;
		font-weight: bold
	}

	.font_xxxl {
		font-size: 32px;
		font-weight: bold
	}
	tbody tr:nth-child(2n){
	    color:#000;
	}
	.country{
    	font-size: 37px;
        padding: 0px;
        margin: 0px;
        font-weight: bold;
        width: 100px;
	}
	.barcode{text-align:center;}
	.barcode svg{width:378px;}
	.font_12{font-size: 12px;font-weight: bold;}
</style>
<div style="547px;height:433px;margin:10px;border:2px solid #000;">
<div style="padding:10px;">
<table class="container" style="height:180px;">
	<tr>
		<td  height="76" class="font_xxxl">
		    <table class="nob">
		        <tr>
		            <td class="font_xxxl conta">'.$data['setting']['name'].'</td>
		        </tr>
		        <tr>
		            <td  class="font_xl conta">'.$data['setting']['desc'].'</td>
		        </tr>
		    </table>
		</td>
	</tr>
	<tr>
		<td  class="center">
			<table class="nob">
				<tr>
					<td class="barcode center">'.$data['barcode'].'</td>
				</tr>
				<tr>
					<td class="center font_xl">'.$data['order_sn'].'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<div style="height:1px;border-top:2px dashed #000;margin:10px 0px 20px 0px;"></div>
<table class="container" >
    <tr>
        <td>
            <table>
            <tr>
        		<td class="font_xl pl">
        		   CustomerID : '.$data['member_id'].'
        		</td>
    	    </tr>
        	<tr>
        		<td class="font_xl pl">
        		   Destination: '.$data['address']['country'].'
        		</td>
        	</tr>
        	<tr>
        		<td class="font_xl pl">
        		   Pickup Point: '.$data['address']['code'].'
        		</td>
        	</tr>
        	<tr>
        		<td class="font_xl pl">
        		   Qty: 1 pkgs
        		</td>
        	</tr>
        	<tr>
        		<td class="font_xl pl">
        		   Weight: '.$data['cale_weight'].'kgs
        		</td>
        	</tr>
            </table>
        </td>
        <td class="barcode"><img style="width:200px;" src="'.$data['cover_id']['file_path'].'"/></td>
    </tr>
</table>
</div>
</div>
';
}
    
    // 渲染面单生成网页数据
    public function template10($data){
      return  $html = '<style>
      @font-face
        {
            font-family:ttt;
            src: url(assets/common/fonts/msyh.ttf)
        }
	* {
		margin: 0;
		padding: 0;
		font-family: ttt, sans-serif;
		
	}

	table {
		margin-top: -1px;
		font: 12px,msyh, dejavu serif, arial, sans-serif;
		border-collapse: collapse
	}

	table.container {
		width: 375px;
		border: 1px solid #000;
		border-bottom: 0
	}

	table td {
		border-top: 1px solid #000;
		border-bottom: 1px solid #000
	}

	table.nob {
		width: 100%
	}

	table.nob td {
		border: 0
	}

	table td.center {
		text-align: center
	}

	table td.right {
		text-align: right
	}

	table td.pl {
		padding-left: 5px
	}

	table td.br {
		border-right: 1px solid #000
	}

	table.nobt,
	table td.nobt {
		border-top: 0
	}

	table.nobb,
	table td.nobb {
		border-bottom: 0
	}

	.font_s {
		font-size: 10px;
		-webkit-transform: scale(0.84, 0.84);
		*font-size: 10px
	}

	.font_m {
		font-size: 16px
	}

	.font_l {
		font-size: 16px;
		font-weight: bold
	}

	.font_xl {
		font-size: 18px;
		font-weight: bold
	}

	.font_xxl {
		font-size: 28px;
		font-weight: bold
	}

	.font_xxxl {
		font-size: 32px;
		font-weight: bold
	}
	.country{
    	font-size: 37px;
        padding: 0px;
        margin: 0px;
        font-weight: bold;
        width: 100px;
	}
	.barcode{text-align:center;}
</style>
<table class="container">
	<tr>
		<td width="140" height="76" class="pl center font_xxl">'.$data['setting']['name'].'</td>
		<td width="252" class="center">
			<table class="nob">
				<tr>
					<td>'.$data['barcode'].'
					</td>
				</tr>
				<tr>
					<td class="center font_l">'.$data['t_order_sn'].'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td height="56">
			<table class="nob">
				<tr>
					<td class="pl" height="28">寄件：</td>
					<td>'. $data['storage']['linkman'].'
						'.$data['storage']['phone'].'('.$data['storage']['shop_name'].')</td>
				</tr>
				<tr>
					<td></td>
					<td>'.$data['storage']['address'].'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container nobb">
	<tr>
		<td height="66" class="nobb">
			<table class="nob">
				<tr>
					<td class="pl" height="28">收件：</td>
					<td><strong>'.$data['shoujianren'].'</strong></td>
				</tr>
				<tr>
					<td height="38" class="country">CN</td>
					<td valign="top"><strong>
					'.$data['address']['xiangxi'].'</strong>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container nobt">
	<tr>
		<td class="nobt">
			<table class="nob">
				<tr>
					<td class="pl" width="110" height="24">付款方式：</td>
					<td width="60">寄付</td>
					<td width="100">收件人/代签人：</td>
					<td></td>
				</tr>
				<tr>
					<td class="pl" height="24">计费重量（KG）：</td>
					<td>'.$data['cale_weight'].'</td>
					<td>签收时间：</td>
					<td>年&emsp;月&emsp;日</td>
				</tr>
				<tr>
					<td class="pl">运费金额（元）：</td>
					<td>'.$data['real_payment'].'</td>
					<td colspan="2" class="font_s">快件送达收件人地址，经收件人或收件人允许的代收人签字视为送达。</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td>
			<table class="nob">
				<tr>
					<td class="pl" width="65" height="24">件数：</td>
					<td width="60">1</td>
					<td width="80">重：</td>
					<td>'.$data['cale_weight'].'</td>
				</tr>
				<tr>
					<td class="pl" height="50" valign="top">配货信息：</td>
					<td colspan="3"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td class="center" height="65">
			<table class="nob">
				<tr>
					<td class="barcode">'.$data['barcode'].'</td>
				</tr>
				<tr>
					<td class="center font_l">'.$data['t_order_sn'].'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td width="187" height="65" class="br">
			<table class="nob">
				<tr>
					<td class="pl">寄件：</td>
					<td>'.$data['storage']['linkman'].$data['storage']['phone'].$data['storage']['address'].'</td>
				</tr>
			</table>
		</td>
		<td>
			<table class="nob">
				<tr>
					<td class="pl">收件：</td>
					<td>'.$data['address']['name'].' 
					'.$data['address']['phone'].'</br>
					'.$data['address']['country'].'
					'.$data['address']['detail'].'
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td width="200" height="80">
			<table class="nob">
				<tr>
					<td class="pl">备注：</td>
				</tr>
				<tr>
					<td class="pl font_m font_s">'.$data['remark'].'</td>
				</tr>
			</table>
		</td>
		<td class="center">
			<table class="nob">
				<tr>
					<td class="font_xxxl">'.substr($data['address']['phone'],-4).'</td>
				</tr>
				<tr>
					<td class="">-手机尾号-</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td height="30" class="pl">网址：'.$_SERVER["SERVER_NAME"].'</td>
		<td>客服电话：'.$data['setting']['service_phone'].'</td>
	</tr>
</table>
';
    }
    
  // 渲染面单生成网页数据
    public function template20($data){
      return  $html = '<style>
	* {
		margin: 0;
		padding: 0
	}

	table {
		margin-top: -1px;
		font: 12px "Microsoft YaHei", Verdana, arial, sans-serif;
		border-collapse: collapse
	}

	table.container {
		width: 375px;
		border: 1px solid #000;
		border-bottom: 0
	}

	table td {
		border-top: 1px solid #000;
		border-bottom: 1px solid #000
	}

	table.nob {
		width: 100%
	}

	table.nob td {
		border: 0
	}

	table td.center {
		text-align: center
	}

	table td.right {
		text-align: right
	}

	table td.pl {
		padding-left: 5px
	}

	table td.br {
		border-right: 1px solid #000
	}

	table.nobt,
	table td.nobt {
		border-top: 0
	}

	table.nobb,
	table td.nobb {
		border-bottom: 0
	}

	.font_s {
		font-size: 10px;
		-webkit-transform: scale(0.84, 0.84);
		*font-size: 10px
	}

	.font_m {
		font-size: 16px
	}

	.font_l {
		font-size: 16px;
		font-weight: bold
	}

	.font_xl {
		font-size: 18px;
		font-weight: bold
	}

	.font_xxl {
		font-size: 28px;
		font-weight: bold
	}

	.font_xxxl {
		font-size: 32px;
		font-weight: bold
	}
	.country{
    	font-size: 37px;
        padding: 0px;
        margin: 0px;
        font-weight: bold;
        width: 100px;
	}
	.barcode{text-align:center;}
</style>
<table class="container">
	<tr>
		<td width="140" height="76" class="pl center font_xl">'.$data['line']['name'].'</td>
		<td width="252" class="center">
			<table class="nob">
				<tr>
					<td>'.$data['barcode'].'
					</td>
				</tr>
				<tr>
					<td class="center font_l">'.$data['t_order_sn'].'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td height="56">
			<table class="nob">
				<tr>
					<td class="pl" height="28">寄件：</td>
					<td>'.$data['storage']['shop_name'].$data['storage']['address'].'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container nobb">
	<tr>
		<td height="66" class="nobb">
			<table class="nob">
				<tr>
					<td class="pl" height="28">收件：</td>
					<td><strong>'.$data['address']['name'].  '+'.$data['address']['tel_code'].'  '.$data['address']['phone'].'</strong></td>
				</tr>
				<tr>
					<td height="38" class="country">CN</td>
					<td valign="top"><strong>
					'.$data['address']['country'].'
					'.$data['address']['province'].'
					'.$data['address']['city'].'
					'.$data['address']['region'].'
					'.$data['address']['district'].'
					'.$data['address']['street'].'
					'.$data['address']['door'].'
					'.$data['address']['detail'].'</strong>
					'.$data['address']['code'].'
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container nobt">
	<tr>
		<td class="nobt">
			<table class="nob">
				<tr>
					<td class="pl" width="110" height="24">付款方式：</td>
					<td width="60">寄付</td>
					<td width="100">收件人/代签人：</td>
					<td></td>
				</tr>
				<tr>
					<td class="pl" height="24">计费重量（KG）：</td>
					<td>'.$data['cale_weight'].'</td>
					<td>签收时间：</td>
					<td>年&emsp;月&emsp;日</td>
				</tr>
				<tr>
					<td colspan="4" class="font_s">快件送达收件人地址，经收件人或收件人允许的代收人签字视为送达。</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td>
			<table class="nob">
				<tr>
					<td class="pl" width="65" height="24">件数：</td>
					<td width="60">1</td>
					<td width="80">重：</td>
					<td>'.$data['cale_weight'].'</td>
				</tr>
				<tr>
					<td class="pl" height="50" valign="top">配货信息：</td>
					<td colspan="3"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td class="center" height="65">
			<table class="nob">
				<tr>
					<td class="barcode">'.$data['barcode'].'</td>
				</tr>
				<tr>
					<td class="center font_l">'.$data['t_order_sn'].'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td width="187" height="65" class="br">
			<table class="nob">
				<tr>
					<td class="pl">寄件：</td>
					<td>'.$data['storage']['address'].'</td>
				</tr>
			</table>
		</td>
		<td>
			<table class="nob">
				<tr>
					<td class="pl">收件：</td>
					<td>'.$data['address']['name'].' 
					'.$data['address']['phone'].'</br>
					'.$data['address']['country'].'
					'.$data['address']['detail'].'
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td width="200" height="80">
			<table class="nob">
				<tr>
					<td class="pl">备注：</td>
				</tr>
				<tr>
					<td class="pl font_m font_s">'.$data['remark'].'</td>
				</tr>
			</table>
		</td>
		<td class="center">
			<table class="nob">
				<tr>
					<td class="font_xxxl">'.substr($data['address']['phone'],-4).'</td>
				</tr>
				<tr>
					<td class="">-手机尾号-</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';
    }
   
    // 批量打印面单 [生成pdf]
    public function expressBillbatch(){
        $selectIds = $this->postData("selectIds");
        $inpack = (new Inpack());   
        
        
        $data = $inpack->getExpressBatchData($selectIds);
        // dump($data);die;
        $html = '';
        $setting = Setting::getItem('store',$data[0]['wxapp_id']);
        $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorSVG(); #创建SVG类型条形码
        foreach($data as $v){
           $v['setting'] = $setting;
           $v['barcode'] = $generatorSVG->getBarcode($v['order_sn'], $generatorSVG::TYPE_CODE_128,$widthFactor =2, $totalHeight = 50);
           
           $v['shoujianren'] = $v['address']['name'];
           isset($v['setting']['address_setting']['is_tel_code'])  && $v['setting']['address_setting']['is_tel_code']==1 && $v['shoujianren']=$v['shoujianren'].$v['address']['tel_code'];
           $v['shoujianren']= $v['shoujianren'].'  '.$v['address']['phone'];
    
           $v['address']['xiangxi'] = $v['address']['country'];
           isset($v['setting']['address_setting']['is_province'])  && $v['setting']['address_setting']['is_province']==1 && $v['address']['xiangxi'] = $v['address']['xiangxi'].$v['address']['province'];
           
           isset($v['setting']['address_setting']['is_city'])  && $v['setting']['address_setting']['is_city']==1 && $v['address']['xiangxi'] = $v['address']['xiangxi'].$v['address']['city'];
           
           isset($v['setting']['address_setting']['is_region'])  && $v['setting']['address_setting']['is_region']==1 && $v['address']['xiangxi'] = $v['address']['xiangxi'].$v['address']['region'];
           
           isset($v['setting']['address_setting']['is_street'])  && $v['setting']['address_setting']['is_street']==1 && $v['address']['xiangxi'] = $v['address']['xiangxi'].$v['address']['street'];
           
           isset($v['setting']['address_setting']['is_door'])  && $v['setting']['address_setting']['is_door']==1 && $v['address']['xiangxi'] = $v['address']['xiangxi'].$v['address']['door'];
           
           $v['address']['xiangxi'] = $v['address']['xiangxi'] . $v['address']['detail'];
           
           $html.= $this->template10($v); 
           $html.="</hr>";
        }
        $html = mb_convert_encoding($html,'UTF-8','auto'); 
        $options = new Options();
        $options->set('enable_remote', TRUE);
        $options->set('defaultFont','monospace.ttf');
        $dompdf = new Dompdf($options);
        
        // dump($html);die;
        $dompdf->loadHtml($html, 'UTF-8');
        // 加载HTML到Dompdf
        // $dompdf->loadHtml(charsetToUTF8($html));
        
        // 设置（纸张大小和方向）
        // 可在https://dompdf.github.io/查看支持的纸张尺寸
        $dompdf->setPaper('A4', 'portrait');
         
        // 渲染HTML为PDF
        $dompdf->render();
        // $dompdf->stream("output.pdf", array("Attachment" => false));
        // 指定文件名和路径
        $filename =  rand(100000,999999);
        $filePath = WEB_PATH.'/excel/'. $filename .'.pdf';
 
        // 将PDF内容保存到服务器文件
        file_put_contents($filePath, $dompdf->output()); 
        return base_url().'/excel/'.$filename.'.pdf'; 
        // 输出PDF到浏览器（用于直接预览）
        // echo($html);
        // file_put_contents('expressBill.html', charsetToUTF8($html));
    }
    
    
    // 修改用户地址
    public function updateAddress(){
        $selectIds = $this->postData();
        $inpack = (new Inpack()); 
        if(!$selectIds['id'] || !$selectIds['address_id']){
            return $this->renderError('修改失败');
            
        }
        $address =(new UserAddress())->where('address_id',$selectIds['address_id'])->find();
        $result = $inpack->where('id',$selectIds['id'])->update(['address_id'=>$selectIds['address_id'],'country_id'=>$address['country_id']]);
        return $this->renderSuccess('修改成功');
    }

    /**包裹导出功能**/
    //导出成excel文档
     public function loaddingOutExcel(){
         //引入excel插件
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        //获取需要导出的数据列表
        $ids= input("post.selectId/a");
        $seach= input("post.seach/a");
        //1 待入库 2 已入库 3 已分拣上架  4 待打包  5 待支付  6 已支付 7 已分拣下架  8 已打包  9 已发货 10 已收货 11 已完成
        $map =[-1=>'问题件',1=>'待入库',2=>'已入库',3=>'已分拣上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        $status = [1=>'待查验',2=>'待支付',3=>'已支付','4'=>'已拣货','5'=>'已打包','6'=>'已发货','7'=>'已收货','8'=>'已完成','-1'=>'已取消'];
       
        if($ids){
           $data = (new Inpack())->whereIn('id',$ids)->select()->each(function ($item, $key) use($map){
                    $item["user"] = (new UserModel())->where('user_id',$item['member_id'])->field('user_id,nickName,mobile,user_code')->find();
                    $item['t_name'] = (new Line())->where('id',$item['line_id'])->value('name');
                    $item['total_free'] = $item['free']+ $item['pack_free'] +$item['other_free'] +$item['insure_free'];
                    
                    //集运单包裹中的物品分类和价格
                    $packdata = explode(",",$item['pack_ids']);
                    $packClass = [];
                    $packprice = 0;
             
                    foreach($packdata as $key => $vale){
                        $expressnum = (new Package())->where('id',$vale)->find();
                        $packitem = (new PackageItem())->where('express_num',$expressnum['express_num'])->select();
                        $packClass[$key]="";
                        $packprice=0;
                        if(count($packitem)>0){
                            $packClass[$key] = $packitem[0]['class_name'];
                            $packprice += $packitem[0]['all_price'];
                        } 
                    }
                    
                    $item['packClass'] = implode($packClass);
                    $item['packprice'] = $packprice;
                    // dump($packClass);die;
                    //折扣信息
                    
                    $discountData = (new UserLine())->where(['user_id'=>$item["user"]['user_id'],'line_id'=>$item['line_id']])->find();
                    if($discountData){
                        $item['discount'] = $discountData['discount'];
                    }else{
                        $item['discount'] = 1;
                    }
                                   
                    $item['discount_price'] = $item['discount'] * $item['free'];
               
                    $item['status_text'] = $map[$item['status']];
                    $item['address'] =(new UserAddress())->where('address_id',$item['address_id'])->find();
                    return $item;
                }); 
        }else{
            if(!empty($seach['search'])){
                 $where['member_id'] = $seach['search']; //用户id
            }
            if(!empty($seach['status'])){
                 $where['status'] = $seach['status'];    //包裹状态
            }
            if(!empty($seach['start_time']) && !empty($seach['end_time'])){
                 $where['entering_warehouse_time']=['between',[$seach['start_time'],$seach['end_time']]];
            }
            if(!empty($seach['extract_shop_id'])){
                $where['storage_id'] = $seach['extract_shop_id'];  //仓库
            }
            if(!empty($seach['express_num'])){
                 $where['express_num'] = $seach['express_num'];  //快递单号
            }
            $data =(new Inpack())->where($where)->select()->each(function ($item, $key) use($map){
                    $item['total_free'] = $item['free']+ $item['pack_free'] +$item['other_free'] +$item['insure_free'];
                    $item["user"] = (new UserModel())->where('user_id',$item['member_id'])->field('user_id,nickName,mobile,user_code')->find();
                    //判断是否有优惠折扣
                    $discountData = (new UserLine())->where(['user_id'=>$item["user"]['user_id'],'line_id'=>$item['line_id']])->find();
                    if($discountData){
                  
                        $item['discount'] = $discountData['discount'];
                    }else{
                        $item['discount'] = 1;
                    }
                                   
                    $item['discount_price'] = $item['discount'] * $item['free'];
                    $item['status_text'] = $map[$item['status']];
                    $item['phone'] =(new UserAddress())->where('address_id',$item['address_id'])->find();
                    return $item;
                });
        }
        
          $style_Array=array(
            'font'    => array (
               'bold'      => true
              ),
             'alignment' => array (
                      'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
               ),
              'borders' => array (
                   'top'     => array (
                           'style' => \PHPExcel_Style_Border::BORDER_THIN
                       )
                ),
          );
         
        $setting = SettingModel::getItem('store',$data[0]['wxapp_id']);
        $objPHPExcel->getActiveSheet()->getStyle( 'A4:S4')->applyFromArray($style_Array);
        //第一行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A1',$setting['name'].'── 业务结算清单');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(24);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:S1');
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(36);
        // $objPHPExcel->getActiveSheet()->setRowHeight(25);
        //第二行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('B2','致'.$data[0]['address']['name'].'  '.'导出日期：'.getTime());
        $objPHPExcel->getActiveSheet()->mergeCells('B2:J2');
        
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A4', '序号')
                ->setCellValue('B4', '集运线路')
                ->setCellValue('C4', '平台订单号')
                ->setCellValue('D4', '目的地')
                ->setCellValue('E4', '重量')
                ->setCellValue('F4', '标准价')
                ->setCellValue('G4', '商品品类')
                ->setCellValue('H4', '商品价格')
                ->setCellValue('I4', '用户ID')
                ->setCellValue('J4', '姓名')
                ->setCellValue('K4', '手机号')
                ->setCellValue('L4', '个人通关号码')
                ->setCellValue('M4', '身份证')
                ->setCellValue('N4', '地址')
                ->setCellValue('O4', '邮编')
                ->setCellValue('P4', '承运商')
                ->setCellValue('Q4', '发货单号')
                ->setCellValue('R4', '备注')
                ->setCellValue('S4', '状态')
                ->setCellValue('T4', '业务日期')
                ->setCellValue('U4', '签收时间')
                ->setCellValue('V4', '专属客服');
                   
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:U')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A4:U4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:U')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objPHPExcel->getActiveSheet()->getStyle('A:T')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置行高
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
        //设置颜色

        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(8);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('N')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('O')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('P')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('Q')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('R')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('S')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('T')->setWidth(22);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('U')->setWidth(22);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('V')->setWidth(22);
        for($i=0;$i<count($data);$i++){
            // dump($data->toArray());die;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+5),$i+1);//序号
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+5),$data[$i]['t_name']);//集运路线
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+5),$data[$i]['order_sn'].' ');//平台订单号
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+5),$data[$i]['address']['country']);//目的地
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+5),$data[$i]['cale_weight']?$data[$i]['cale_weight']:$data[$i]['weight']);//重量
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+5),$data[$i]['total_free']);//标准价
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+5),$data[$i]['packClass']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+5),$data[$i]['packprice']);//标准价 ***********
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+5),$data[$i]['user']['user_id']);//用户id
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+5),$data[$i]['address']['name']);//用户昵称
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($i+5),$data[$i]['address']['phone']);//专属客服
            $objPHPExcel->getActiveSheet()->setCellValue('L'.($i+5),$data[$i]['address']['clearancecode']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('M'.($i+5),$data[$i]['address']['identitycard']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('N'.($i+5),$data[$i]['address']['detail']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('O'.($i+5),$data[$i]['address']['code']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('P'.($i+5),$data[$i]['t_name'].' ');//内部单号
            $objPHPExcel->getActiveSheet()->setCellValue('Q'.($i+5),$data[$i]['t_order_sn'].' ');//内部单号
            $objPHPExcel->getActiveSheet()->setCellValue('R'.($i+5),$data[$i]['remark']);//备注
            $objPHPExcel->getActiveSheet()->setCellValue('S'.($i+5),$status[$data[$i]['status']]);//转单号码
            $objPHPExcel->getActiveSheet()->setCellValue('T'.($i+5),$data[$i]['created_time']);//业务日期
            $objPHPExcel->getActiveSheet()->setCellValue('U'.($i+5),$data[$i]['receipt_time']);//签收时间
            $objPHPExcel->getActiveSheet()->setCellValue('V'.($i+5),$data[$i]['user']['user_id']);//专属客服
        }
        //7.设置保存的Excel表格名称
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('业务结算清单');
        //9.设置浏览器窗口下载表格
        if($setting['usercode_mode']['is_show']==0){
            $filename = $data[0]['user']['user_id'].'-'. date("Ymd") . ".xlsx";
        }else{
            $filename = $data[0]['user']['user_code'].'-'. date("Ymd") . ".xlsx";
        }
        
        // $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $ov = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $ov->save("excel/" . $filename);
        return $this->renderSuccess("导出成功", [
            "file_name" => "https://".$_SERVER["HTTP_HOST"] . "/excel/" . $filename,
        ]);
     }
     
    /**导出集运订单中的包裹明细**/
    //导出成excel文档
     public function exportInpackpackage(){
         //引入excel插件
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        //获取需要导出的数据列表
        $param= $this->request->param();
        $data = (new Package())->with(['storage','Member','categoryAttr','batch'])->where('inpack_id',$param['id'])->where('is_delete',0)->select();
        
          $style_Array=array(
            'font'    => array (
               'bold'      => true
              ),
             'alignment' => array (
                      'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
               ),
              'borders' => array (
                   'top'     => array (
                           'style' => \PHPExcel_Style_Border::BORDER_THIN
                       )
                ),
          );
        $status = [1=>'待入库',2=>'已入库',3=>'已上架','4'=>'待打包','5'=>'待支付','6'=>'已支付','7'=>'加入批次','8'=>'已打包','9'=>'已发货','10'=>'已收货','11'=>'已完成'];
        $setting = SettingModel::getItem('store',$data[0]['wxapp_id']);
        $objPHPExcel->getActiveSheet()->getStyle( 'A4:P4')->applyFromArray($style_Array);
        //第一行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A1',$setting['name'].'── 包裹明细');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(24);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:P1');
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(36);
        //第二行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('B2','致'.$data[0]['member']['nickName'].'导出日期：'.getTime());
        $objPHPExcel->getActiveSheet()->mergeCells('B2:P2');
        
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A4', '序号')
                ->setCellValue('B4', '快递单号')
                ->setCellValue('C4', '批次号')
                ->setCellValue('D4', '包裹尺寸')
                ->setCellValue('E4', '包裹重量')
                ->setCellValue('F4', '包裹位置')
                ->setCellValue('G4', '包裹状态')
                ->setCellValue('H4', '扫描状态')
                ->setCellValue('I4', '入库时间')
                ->setCellValue('J4', '查验时间')
                ->setCellValue('K4', '所属用户')
                ->setCellValue('L4', '所在仓库')
                ->setCellValue('M4', '包裹类别')
                ->setCellValue('N4', '物品名称')
                ->setCellValue('O4', '单价')
                ->setCellValue('P4', '数量');
                   
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:P')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A4:P4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:P')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objPHPExcel->getActiveSheet()->getStyle('A:P')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置行高
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
        //设置颜色

        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(8);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('N')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('O')->setWidth(8);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('P')->setWidth(8);
        $length = 1;
        // dump(count($data['category_attr']));die;
        
        // dump($length);die;
        for($i=0;$i<count($data);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+5),$i+1);//序号
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+5),$data[$i]['express_num']);//快递单号
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+5),$data[$i]['batch']['batch_name']);//平台订单号
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+5),$data[$i]['length'].'/'.$data[$i]['width'].'/'.$data[$i]['height']);//目的地
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+5),$data[$i]['weight']);//标准价
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+5),$data[$i]['storage']['shop_name']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+5),$status[$data[$i]['status']]);//标准价 ***********
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+5),$data[$i]['is_scan']==1?"未扫码":"已扫码");//用户id
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+5),$data[$i]['entering_warehouse_time']);//用户昵称
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+5),$data[$i]['scan_time']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($i+5),$data[$i]['member']['nickName']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('L'.($i+5),$data[$i]['storage']['shop_name']);//快递类别  ***********
            $length = 1;
            if(count($data[$i]['category_attr'])>0){
                $length = count($data[$i]['category_attr']);
            }
            for($j=0;$j< $length;$j++){
                $objPHPExcel->getActiveSheet()->setCellValue('M'.($i+5),isset($data[$i]['category_attr'][$j]['class_name'])?$data[$i]['category_attr'][$j]['class_name']:'');//快递类别  ***********
                $objPHPExcel->getActiveSheet()->setCellValue('N'.($i+5),isset($data[$i]['category_attr'][$i]['goods_name'])?$data[$i]['category_attr'][$j]['goods_name']:'');//重量
                $objPHPExcel->getActiveSheet()->setCellValue('O'.($i+5),isset($data[$i]['category_attr'][$j]['one_price'])?$data[$i]['category_attr'][$j]['one_price']:'');//重量
                $objPHPExcel->getActiveSheet()->setCellValue('P'.($i+5),isset($data[$i]['category_attr'][$j]['product_num'])?$data[$i]['category_attr'][$j]['product_num']:'');//重量
            }
           
    
        }
        //7.设置保存的Excel表格名称
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('业务结算清单');
        //9.设置浏览器窗口下载表格
        $filename = "用户包裹"  . rand(1000000, 9999999) . ".xlsx";
        // $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $ov = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $ov->save("excel/" . $filename);
        return $this->renderSuccess("导出成功", [
            "file_name" => "https://".$_SERVER["HTTP_HOST"] . "/excel/" . $filename,
        ]);
     }    
     
     /**导出集运业务结算**/
    //导出成excel文档
     public function exportInpack(){
         //引入excel插件
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        //获取需要导出的数据列表
        $ids= input("post.selectId/a");
        $seach= input("post.seach/a");
        //1 待入库 2 已入库 3 已分拣上架  4 待打包  5 待支付  6 已支付 7 已分拣下架  8 已打包  9 已发货 10 已收货 11 已完成
        $map =[-1=>'问题件',1=>'待入库',2=>'已入库',3=>'已分拣上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        $status = [1=>'待查验',2=>'待支付',3=>'已支付','4'=>'已拣货','5'=>'已打包','6'=>'已发货','7'=>'已收货','8'=>'已完成','-1'=>'已取消'];
       
        if($ids){
           $data = (new Inpack())->with(['storage','shop','country'])->whereIn('id',$ids)->select()->each(function ($item, $key) use($map){
                    $item["user"] = (new UserModel())->where('user_id',$item['member_id'])->field('user_id,nickName,mobile')->find();
                    $item['t_name'] = (new Line())->where('id',$item['line_id'])->value('name');
                    $item['shopCapital'] = (new Capital())->where(['inpack_id'=> $item['order_sn'],'shop_id' => $item['shop_id']])->value('money');
                    $item['straogeCapital'] = (new Capital())->where(['inpack_id'=> $item['order_sn'],'shop_id' => $item['storage_id']])->value('money');
                    
                    
                    //集运单包裹中的物品分类和价格
                    $packdata = explode(",",$item['pack_ids']);
                    $packClass = [];
                    $packprice = 0;
             
                    foreach($packdata as $key => $vale){
                        $expressnum = (new Package())->where('id',$vale)->find();
                        $packitem = (new PackageItem())->where('express_num',$expressnum['express_num'])->select();
                        $packClass[$key]="";
                        $packprice=0;
                        if(count($packitem)>0){
                            $packClass[$key] = $packitem[0]['class_name'];
                            $packprice += $packitem[0]['all_price'];
                        } 
                    }
                    
                    $item['packClass'] = implode($packClass);
                    $item['packprice'] = $packprice;
                    // dump($packClass);die;
                    //折扣信息
                    
                    $discountData = (new UserLine())->where(['user_id'=>$item["user"]['user_id'],'line_id'=>$item['line_id']])->find();
                    if($discountData){
                        $item['discount'] = $discountData['discount'];
                    }else{
                        $item['discount'] = 1;
                    }
                                   
                    $item['discount_price'] = $item['discount'] * $item['free'];
               
                    $item['status_text'] = $map[$item['status']];
                    $item['address'] =(new UserAddress())->where('address_id',$item['address_id'])->find();
                    return $item;
                }); 
                
        }else{
            if(!empty($seach['search'])){
                 $where['member_id'] = $seach['search']; //用户id
            }
            if(!empty($seach['status'])){
                 $where['status'] = $seach['status'];    //包裹状态
            }
            if(!empty($seach['start_time']) && !empty($seach['end_time'])){
                 $where['entering_warehouse_time']=['between',[$seach['start_time'],$seach['end_time']]];
            }
            if(!empty($seach['extract_shop_id'])){
                $where['storage_id'] = $seach['extract_shop_id'];  //仓库
            }
            if(!empty($seach['express_num'])){
                 $where['express_num'] = $seach['express_num'];  //快递单号
            }
            $data =(new Inpack())->where($where)->select()->each(function ($item, $key) use($map){
                    
                    $item["user"] = (new UserModel())->where('user_id',$item['member_id'])->field('user_id,nickName,mobile')->find();
                    //判断是否有优惠折扣
                    $discountData = (new UserLine())->where(['user_id'=>$item["user"]['user_id'],'line_id'=>$item['line_id']])->find();
                    if($discountData){
                  
                        $item['discount'] = $discountData['discount'];
                    }else{
                        $item['discount'] = 1;
                    }
                                   
                    $item['discount_price'] = $item['discount'] * $item['free'];
                    $item['status_text'] = $map[$item['status']];
                    $item['phone'] =(new UserAddress())->where('address_id',$item['address_id'])->find();
                    return $item;
                });
        }
        
          $style_Array=array(
            'font'    => array (
               'bold'      => true
              ),
             'alignment' => array (
                      'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
               ),
              'borders' => array (
                   'top'     => array (
                           'style' => \PHPExcel_Style_Border::BORDER_THIN
                       )
                ),
          );
         
        $setting = SettingModel::getItem('store',$data[0]['wxapp_id']);
        $objPHPExcel->getActiveSheet()->getStyle( 'A4:R4')->applyFromArray($style_Array);
        //第一行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A1',$setting['name'].'── 分成结算');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(24);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:R1');
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(36);
        // $objPHPExcel->getActiveSheet()->setRowHeight(25);
        //第二行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('B2','致'.$data[0]['address']['name'].'  '.'导出日期：'.getTime());
        $objPHPExcel->getActiveSheet()->mergeCells('B2:J2');
        
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A4', '序号')
                ->setCellValue('B4', '集运线路')
                ->setCellValue('C4', '平台订单号')
                ->setCellValue('D4', '目的地')
                ->setCellValue('E4', '重量')
                ->setCellValue('F4', '支付金额')
                ->setCellValue('G4', '寄件仓库')
                ->setCellValue('H4', '寄件分成')
                ->setCellValue('I4', '派件仓库')
                ->setCellValue('J4', '派件分成')
                ->setCellValue('K4', '发货单号')
                ->setCellValue('L4', '派件入库时间')
                ->setCellValue('M4', '派件签收时间')
                ->setCellValue('N4', '分成结算日期');
                   
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:R')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A4:R4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:R')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objPHPExcel->getActiveSheet()->getStyle('A:R')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置行高
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
        //设置颜色

        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(8);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('N')->setWidth(25);

        
        for($i=0;$i<count($data);$i++){
            // dump($data->toArray());die;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+5),$i+1);//序号
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+5),$data[$i]['t_name']);//集运路线
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+5),$data[$i]['order_sn']);//平台订单号
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+5),$data[$i]['country']['title']);//目的地
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+5),$data[$i]['weight']);//重量
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+5),$data[$i]['real_payment']);//标准价
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+5),$data[$i]['storage']['shop_name']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+5),$data[$i]['straogeCapital']);//标准价 ***********
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+5),$data[$i]['shop']['shop_name']);//用户id
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+5),$data[$i]['shopCapital']);//用户昵称
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($i+5),$data[$i]['t_order_sn']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('L'.($i+5),$data[$i]['shoprk_time']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('M'.($i+5),$data[$i]['receipt_time']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('N'.($i+5),$data[$i]['settle_time']);//快递类别  ***********
    
        }
        //7.设置保存的Excel表格名称
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('业务结算清单');
        //9.设置浏览器窗口下载表格
        $filename = "用户包裹"  . rand(1000000, 9999999) . ".xlsx";
        // $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $ov = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $ov->save("excel/" . $filename);
        return $this->renderSuccess("导出成功", [
            "file_name" => "https://".$_SERVER["HTTP_HOST"] . "/excel/" . $filename,
        ]);
     }

    /**导出集运清关文件**/
    //导出成excel文档
     public function clearance(){
         //引入excel插件
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        //获取需要导出的数据列表
        $ids= input("post.selectId/a");
        //1 待入库 2 已入库 3 已分拣上架  4 待打包  5 待支付  6 已支付 7 已分拣下架  8 已打包  9 已发货 10 已收货 11 已完成
        $map =[-1=>'问题件',1=>'待入库',2=>'已入库',3=>'已分拣上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        $status = [1=>'待查验',2=>'待支付',3=>'已支付','4'=>'已拣货','5'=>'已打包','6'=>'已发货','7'=>'已收货','8'=>'已完成','-1'=>'已取消'];
        $datas = [];
        $setting = SettingModel::getItem('aiidentify',$this->getWxappId());
        if($setting['is_baiduaddress']==0){
            return $this->renderError("尚未开启智能AI识别功能，请更改API");
        }
        $BaiduTextTran = new BaiduTextTran($setting);
        if($ids){
           $data = (new Inpack())->with(['storage','shop','country','user','line','address'])->whereIn('id',$ids)->select()->each(function ($item, $key) use($map,$BaiduTextTran){
                    // $item["user"] = (new UserModel())->where('user_id',$item['member_id'])->field('user_id,nickName,mobile')->find();
                    // $item['t_name'] = (new Line())->where('id',$item['line_id'])->value('name');

                    //集运单包裹中的物品分类和价格
                    $packdata =(new Package())->with(['categoryAttr'])->where('inpack_id',$item['id'])->select();
                    $item['packdata'] = $packdata;
                    // dump($packdata->toArray());die;
                    
                    
                    // $item['discount_price'] = $item['discount'] * $item['free'];
                    // $item['status_text'] = $map[$item['status']];
                    $item['weightkg'] = '011';
                    $item['categoryname'] = 'B';
                    $item['trademode'] = '0110';
                    $item['taxexemptionnature'] = '0110';
                    $item['currency'] = '142';
                    $item['BusinessUnitCode'] = '';
                    $item['cardnumber'] = '51000000000000 ';
                    
                    $item['linkman'] = $item['storage']['linkman'];
                    $item['enlinkman'] = pinyin::getPinyin($item['storage']['linkman']);
                    $item['fhcity'] = $item['storage']['region']['city'];
                    $item['enfhcity'] = pinyin::getPinyin($item['storage']['region']['city']);
                    $enfhcity = $BaiduTextTran->gettexttrans($item['storage']['region']['city'])['result']['trans_result'][0]['dst'];
                    if(!empty($enfhcity)){
                        $item['enfhcity'] = $enfhcity;  //英文
                    }
                    $item['fhphone'] = $item['storage']['phone'].' ';
                    $item['fhaddress'] = $item['storage']['address'];  
                    $item['fhenaddress'] = pinyin::getPinyin($item['storage']['address']);  //英文
                    $enaddress= $BaiduTextTran->gettexttrans($item['storage']['address'])['result']['trans_result'][0]['dst'];
                    if(!empty($enaddress)){
                        $item['fhenaddress'] = $enaddress;  //英文
                    }
                   
                    
                    
                    $item['sjname'] = $item['address']['name'];
                    $item['sjenname'] = pinyin::getPinyin($item['address']['name']);
                    $item['sjcity'] = $item['address']['city'];
                    $item['sjphone'] = $item['address']['phone'];
                    $item['sjaddress'] = $item['address']['detail'];
                    $item['sjenaddress'] = pinyin::getPinyin($item['address']['detail']);  //英文
                    $sjaddress= $BaiduTextTran->gettexttrans($item['address']['detail'])['result']['trans_result'][0]['dst'];
                    if(!empty($sjaddress)){
                        $item['sjenaddress'] = $sjaddress;  //英文
                    }
                    //  dump($item['sjenaddress']);die;
                    return $item;
                }); 
        }
          $op = 0;
          foreach ($data as $value){
              if(count($value['packdata'])>0){
                        foreach ($value['packdata'] as $key=> $val){
                            if(count($val['category_attr'])>0){
                                foreach ($val['category_attr'] as $k=> $v){
                                    //  dump($value);
                                    // dump($value['goods_name']);
                                    // $datas[$op] = $value;
                                    $datas[$op]['weightkg'] = $value['weightkg'];
                                    $datas[$op]['categoryname'] = $value['categoryname'];
                                    $datas[$op]['trademode'] = $value['trademode'];
                                    $datas[$op]['taxexemptionnature'] = $value['taxexemptionnature'];
                                    $datas[$op]['currency'] = $value['currency'];
                                    $datas[$op]['BusinessUnitCode'] = $value['BusinessUnitCode'];
                                    $datas[$op]['cardnumber'] = $value['cardnumber'];
                                    $datas[$op]['linkman'] = $value['linkman'];
                                    $datas[$op]['enlinkman'] = $value['enlinkman'];
                                    $datas[$op]['fhcity'] = $value['fhcity'];
                                    $datas[$op]['enfhcity'] = $value['enfhcity'];
                                    $datas[$op]['fhphone'] = $value['fhphone'];
                                    $datas[$op]['fhaddress'] = $value['fhaddress'];
                                    $datas[$op]['fhenaddress'] = $value['fhenaddress'];
                                    $datas[$op]['sjname'] = $value['sjname'];
                                    $datas[$op]['sjenname'] = $value['sjenname'];
                                    $datas[$op]['sjcity'] = $value['sjcity'];
                                    $datas[$op]['sjphone'] = $value['sjphone'];
                                    $datas[$op]['sjaddress'] = $value['sjaddress'];
                                    $datas[$op]['sjenaddress'] = $value['sjenaddress'];
                                    $datas[$op]['t_order_sn'] = $value['t_order_sn'];
                                    
                                    $datas[$op]['goods_name'] = $v['goods_name'];
                                    $datas[$op]['class_name_en'] = $v['class_name_en'];
                                    $datas[$op]['barcode'] = $v['barcode'].' ';
                                    $datas[$op]['origin_region'] = $v['origin_region'];
                                    $datas[$op]['spec'] = $v['spec'];
                                    $datas[$op]['one_price'] = $v['one_price'];
                                    $datas[$op]['product_num'] = $v['product_num'];
                                    $datas[$op]['unit_weight'] = $v['unit_weight'];
                                    $datas[$op]['net_weight'] = $v['net_weight'];
                                    // dump($datas[$op]);
                                    $op=$op+1;
                                }
                            }
                        }
                    }
                       
          }
        //   dump($datas[0]['class_name_en']);dump($datas[1]['class_name_en']);die;
          $style_Array=array(
            'font'    => array (
               'bold'      => true
              ),
             'alignment' => array (
                      'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
               ),
              'borders' => array (
                   'top'     => array (
                           'style' => \PHPExcel_Style_Border::BORDER_THIN
                       )
                ),
          );
         
        $setting = SettingModel::getItem('store',$data[0]['wxapp_id']);
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $titlemap = [
            ['text'=>'运单号','value'=>"t_order_sn",'width'=>20],
            ['text'=>'分类','value'=>'categoryname','width'=>10],
            ['text'=>'贸易方式','value'=>'trademode','width'=>10],
            ['text'=>'征免性质','value'=>'taxexemptionnature','width'=>10],
            ['text'=>'币制','value'=>'currency','width'=>10],
            ['text'=>'经营单位代码','value'=>'BusinessUnitCode','width'=>30],
            ['text'=>'B类身份证号码','value'=>'cardnumber','width'=>20],
            ['text'=>'发件人名称','value'=>"linkman",'width'=>10],
            ['text'=>'英文名称','value'=>'enlinkman','width'=>10],
            ['text'=>'城市','value'=>'fhcity','width'=>10],
            ['text'=>'城市英文','value'=>'enfhcity','width'=>10],
            ['text'=>'电话','value'=>'fhphone','width'=>15],
            ['text'=>'发件人地址','value'=>'fhaddress','width'=>30],
            ['text'=>'地址英文','value'=>'fhenaddress','width'=>30],
            
            ['text'=>'收件人名称','value'=>'sjname','width'=>10],
            ['text'=>'英文名称','value'=>'sjenname','width'=>10],
            ['text'=>'城市','value'=>'sjcity','width'=>10],
            ['text'=>'电话','value'=>'sjphone','width'=>15],
            ['text'=>'地址','value'=>'sjaddress','width'=>30],
            ['text'=>'地址英文','value'=>'sjenaddress','width'=>30],
            
            ['text'=>'品名','value'=>'goods_name','width'=>10],
            ['text'=>'英文品名','value'=>'class_name_en','width'=>10],
            ['text'=>'编码','value'=>'barcode','width'=>10],
            ['text'=>'生产厂商','value'=>'origin_region','width'=>10],
            ['text'=>'规格','value'=>'spec','width'=>10],
            ['text'=>'价值','value'=>'one_price','width'=>10],
            ['text'=>'件数','value'=>'product_num','width'=>10],
            ['text'=>'毛重','value'=>'unit_weight','width'=>10],
            ['text'=>'净重','value'=>'net_weight','width'=>10],
            ['text'=>'数量','value'=>'product_num','width'=>10],
            ['text'=>'单位','value'=>'weightkg','width'=>10]
        ];
      
        
        $wordMap = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF'];
        
        //设置excel标题
        for ($i = 0; $i < count($titlemap); $i++) {
           $objPHPExcel->setActiveSheetIndex(0)->setCellValue($wordMap[$i].'1', $titlemap[$i]['text']);
        }
       
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:AF')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:AF1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A4:AF4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:AF')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objPHPExcel->getActiveSheet()->getStyle('A:AF')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置行高
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
        
        
        //设置excel标题宽度
        for ($i = 0; $i < count($titlemap); $i++) {
           $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($wordMap[$i])->setWidth($titlemap[$i]['width']);
        }
         //设置excel内容
    //   dump($datas[2]['class_name_en']);die;
        for($i=0;$i<count($datas);$i++){
            for ($j = 0; $j < count($titlemap); $j++) {
                $objPHPExcel->getActiveSheet()->setCellValue($wordMap[$j].($i+2),($datas[$i][$titlemap[$j]['value']]));
            }
        }
            // dump($titlemap);die;
        //7.设置保存的Excel表格名称
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('清关模板');
        //9.设置浏览器窗口下载表格
        $filename = "清关包裹"  . rand(1000000, 9999999) . ".xlsx";
        // $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $ov = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $ov->save("excel/" . $filename);
        return $this->renderSuccess("导出成功", [
            "file_name" => "https://".$_SERVER["HTTP_HOST"] . "/excel/" . $filename,
        ]);
     }
     
    /**导出invoice模板**/
     public function invoice(){
         //引入excel插件
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        //获取需要导出的数据列表
        $param= $this->request->param();
        //1 待入库 2 已入库 3 已分拣上架  4 待打包  5 待支付  6 已支付 7 已分拣下架  8 已打包  9 已发货 10 已收货 11 已完成
        $map =[-1=>'问题件',1=>'待入库',2=>'已入库',3=>'已分拣上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        $status = [1=>'待查验',2=>'待支付',3=>'已支付','4'=>'已拣货','5'=>'已打包','6'=>'已发货','7'=>'已收货','8'=>'已完成','-1'=>'已取消'];
        $datas = [];
        $setting = SettingModel::getItem('aiidentify',$this->getWxappId());
        if($setting['is_baiduaddress']==0){
            return $this->renderError("尚未开启智能AI识别功能，请更改API");
        }
        $BaiduTextTran = new BaiduTextTran($setting);
        $data = (new Inpack())->with(['storage','shop','country','user','line','address'])->where('id',$param['id'])->select()->each(function ($item, $key) use($map,$BaiduTextTran){
                //集运单包裹中的物品分类和价格
                $packdata =(new Package())->with(['categoryAttr'])->where('inpack_id',$item['id'])->select();
                $item['packdata'] = $packdata;
                return $item;
            }); 
          $op = 0;
          foreach ($data as $value){
              if(count($value['packdata'])>0){
                        foreach ($value['packdata'] as $key=> $val){
                            if(count($val['category_attr'])>0){
                                foreach ($val['category_attr'] as $k=> $v){
                                    $datas[$op] = $v;
                                    $op=$op+1;
                                }
                            }
                        }
                    }
                       
          }

          $style_Array=array(
              'font'    => array (
                 'bold'      => true
              ),
              'alignment' => array (
                 'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               ),
              'borders' => array (
                 'top' => array ('style' => \PHPExcel_Style_Border::BORDER_THIN)
                ),
          );
          
           $style_one =array(
            'font'    => array (
               'bold'      => true
              ),
             'alignment' => array (
                      'wrapText' => true, // 设置文本换行
                      'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                      'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
               ),
              'borders' => array (
                   'top'     => array (
                           'style' => \PHPExcel_Style_Border::BORDER_THIN
                       )
                ),
          );

        $objPHPExcel->setActiveSheetIndex(0);
        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //第一行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A1','***　INVOICE　***');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(24);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(36);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(40);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(10);
        
        //第二行的样式
        $objPHPExcel->getActiveSheet()->mergeCells('A2:C2');
        $objPHPExcel->getActiveSheet()->mergeCells('D2:G2');
        $objPHPExcel->getActiveSheet()->getStyle( 'A2:H2')->applyFromArray($style_one);
        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(136);
        $objPHPExcel->getActiveSheet()->setCellValue('A2',"Shipper". "\r\n" . "Company name: CO., LTD.". "\r\n" . "Address:". "\r\n" . "Contact name:" . "\r\n" . "Phone:". "\r\n" . "法人番号:");
        $objPHPExcel->getActiveSheet()->setCellValue('H2','20240515'); 
        
        //第三行的样式
        $objPHPExcel->getActiveSheet()->mergeCells('A3:C3');
        $objPHPExcel->getActiveSheet()->mergeCells('D3:G3');
        $objPHPExcel->getActiveSheet()->getStyle( 'A3:H3')->applyFromArray($style_one);
        $objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(136);
        $objPHPExcel->getActiveSheet()->setCellValue('A3',"CONSIGNEE:                                       ". "\n" . "ATTN:");        
        
        //第四行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A4','Port of Loading');
        $objPHPExcel->getActiveSheet()->getStyle( 'A4:H4')->applyFromArray($style_Array);
        $objPHPExcel->getActiveSheet()->setCellValue('B4','KIX');
        $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
        $objPHPExcel->getActiveSheet()->mergeCells('G4:H4');
        $objPHPExcel->getActiveSheet()->setCellValue('E4','G.Total');
        $objPHPExcel->getActiveSheet()->setCellValue('G4','￥683,684');
        
        //第五行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A5','Port of Discharge');
        $objPHPExcel->getActiveSheet()->getStyle( 'A5:H5')->applyFromArray($style_Array);
        $objPHPExcel->getActiveSheet()->setCellValue('B5','WEH');
        $objPHPExcel->getActiveSheet()->mergeCells('E5:F5');
        $objPHPExcel->getActiveSheet()->mergeCells('G5:H5');
        $objPHPExcel->getActiveSheet()->setCellValue('E5','Mode of TPT');
        $objPHPExcel->getActiveSheet()->setCellValue('G5','AVIATION');
        
        //第六行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A6','Vessel / Voy .');
        $objPHPExcel->getActiveSheet()->getStyle( 'A6:H6')->applyFromArray($style_Array);
        $objPHPExcel->getActiveSheet()->mergeCells('A6:B6');
        $objPHPExcel->getActiveSheet()->mergeCells('E6:F6');
        $objPHPExcel->getActiveSheet()->mergeCells('G6:H6');
        $objPHPExcel->getActiveSheet()->setCellValue('E6','Term');
        $objPHPExcel->getActiveSheet()->setCellValue('G6','CIF');
        
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A7', 'JAN CODE')
                ->setCellValue('B7', 'Classify')
                ->setCellValue('C7', 'Description')
                ->setCellValue('D7', 'CO')
                ->setCellValue('E7', 'QTY')
                ->setCellValue('F7', 'UNIT')
                ->setCellValue('G7', 'UNIT PRICE')
                ->setCellValue('H7', 'S.TOTAL');
      
      for($i=0;$i<count($datas);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+8),$datas[$i]['express_num']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+8),$datas[$i]['class_name_en']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+8),$datas[$i]['goods_name_jp']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+8),'JP');
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+8),$datas[$i]['product_num']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+8),'PCS');
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+8),$datas[$i]['one_price']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+8),$datas[$i]['all_price']);
    
        }
        //7.设置保存的Excel表格名称
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('INVOICE');
        
        //9.设置浏览器窗口下载表格
        $filename = "INVOICE"  . rand(1000000, 9999999) . ".xlsx";
        // $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $ov = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $ov->save("excel/" . $filename);
        return $this->renderSuccess("导出成功", [
            "file_name" => "https://".$_SERVER["HTTP_HOST"] . "/excel/" . $filename,
        ]);
     }
     
     /**导出invoice模板**/
     public function batchinvoice(){
         //引入excel插件
        vendor('PHPExcel.PHPExcel');
        $Batch = new Batch;
        $objPHPExcel = new \PHPExcel();
        //获取需要导出的数据列表
        $param= $this->request->param();
        //1 待入库 2 已入库 3 已分拣上架  4 待打包  5 待支付  6 已支付 7 已分拣下架  8 已打包  9 已发货 10 已收货 11 已完成
        $map =[-1=>'问题件',1=>'待入库',2=>'已入库',3=>'已分拣上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        $status = [1=>'待查验',2=>'待支付',3=>'已支付','4'=>'已拣货','5'=>'已打包','6'=>'已发货','7'=>'已收货','8'=>'已完成','-1'=>'已取消'];
        $datas = [];
        $setting = SettingModel::getItem('aiidentify',$this->getWxappId());
        if($setting['is_baiduaddress']==0){
            return $this->renderError("尚未开启智能AI识别功能，请更改API");
        }
        $BaiduTextTran = new BaiduTextTran($setting);
    
        $data = (new Inpack())->with(['storage','shop','country','user','line','address'])->where('batch_id',$param['id'])->select()->each(function ($item, $key) use($map,$BaiduTextTran){
                //集运单包裹中的物品分类和价格
                $packdata =(new Package())->with(['categoryAttr'])->where('inpack_id',$item['id'])->select();
                $item['packdata'] = $packdata;
                return $item;
            }); 
          $op = 0;
          foreach ($data as $value){
              if(count($value['packdata'])>0){
                        foreach ($value['packdata'] as $key=> $val){
                            if(count($val['category_attr'])>0){
                                foreach ($val['category_attr'] as $k=> $v){
                                    $datas[$op] = $v;
                                    $op=$op+1;
                                }
                            }
                        }
                    }
                       
          }

          $style_Array=array(
              'font'    => array (
                 'bold'      => true
              ),
              'alignment' => array (
                 'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               ),
              'borders' => array (
                 'top' => array ('style' => \PHPExcel_Style_Border::BORDER_THIN)
                ),
          );
          
           $style_one =array(
            'font'    => array (
               'bold'      => true
              ),
             'alignment' => array (
                      'wrapText' => true, // 设置文本换行
                      'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                      'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
               ),
              'borders' => array (
                   'top'     => array (
                           'style' => \PHPExcel_Style_Border::BORDER_THIN
                       )
                ),
          );

        $objPHPExcel->setActiveSheetIndex(0);
        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //第一行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A1','***　INVOICE　***');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(24);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(36);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(40);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(10);
        
        //第二行的样式
        $objPHPExcel->getActiveSheet()->mergeCells('A2:C2');
        $objPHPExcel->getActiveSheet()->mergeCells('D2:G2');
        $objPHPExcel->getActiveSheet()->getStyle( 'A2:H2')->applyFromArray($style_one);
        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(136);
        $objPHPExcel->getActiveSheet()->setCellValue('A2',"Shipper". "\r\n" . "Company name: CO., LTD.". "\r\n" . "Address:". "\r\n" . "Contact name:" . "\r\n" . "Phone:". "\r\n" . "法人番号:");
        $objPHPExcel->getActiveSheet()->setCellValue('H2','20240515'); 
        
        //第三行的样式
        $objPHPExcel->getActiveSheet()->mergeCells('A3:C3');
        $objPHPExcel->getActiveSheet()->mergeCells('D3:G3');
        $objPHPExcel->getActiveSheet()->getStyle( 'A3:H3')->applyFromArray($style_one);
        $objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(136);
        $objPHPExcel->getActiveSheet()->setCellValue('A3',"CONSIGNEE:                                       ". "\n" . "ATTN:");        
        
        //第四行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A4','Port of Loading');
        $objPHPExcel->getActiveSheet()->getStyle( 'A4:H4')->applyFromArray($style_Array);
        $objPHPExcel->getActiveSheet()->setCellValue('B4','KIX');
        $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
        $objPHPExcel->getActiveSheet()->mergeCells('G4:H4');
        $objPHPExcel->getActiveSheet()->setCellValue('E4','G.Total');
        $objPHPExcel->getActiveSheet()->setCellValue('G4','￥683,684');
        
        //第五行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A5','Port of Discharge');
        $objPHPExcel->getActiveSheet()->getStyle( 'A5:H5')->applyFromArray($style_Array);
        $objPHPExcel->getActiveSheet()->setCellValue('B5','WEH');
        $objPHPExcel->getActiveSheet()->mergeCells('E5:F5');
        $objPHPExcel->getActiveSheet()->mergeCells('G5:H5');
        $objPHPExcel->getActiveSheet()->setCellValue('E5','Mode of TPT');
        $objPHPExcel->getActiveSheet()->setCellValue('G5','AVIATION');
        
        //第六行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A6','Vessel / Voy .');
        $objPHPExcel->getActiveSheet()->getStyle( 'A6:H6')->applyFromArray($style_Array);
        $objPHPExcel->getActiveSheet()->mergeCells('A6:B6');
        $objPHPExcel->getActiveSheet()->mergeCells('E6:F6');
        $objPHPExcel->getActiveSheet()->mergeCells('G6:H6');
        $objPHPExcel->getActiveSheet()->setCellValue('E6','Term');
        $objPHPExcel->getActiveSheet()->setCellValue('G6','CIF');
        
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A7', 'JAN CODE')
                ->setCellValue('B7', 'Classify')
                ->setCellValue('C7', 'Description')
                ->setCellValue('D7', 'CO')
                ->setCellValue('E7', 'QTY')
                ->setCellValue('F7', 'UNIT')
                ->setCellValue('G7', 'UNIT PRICE')
                ->setCellValue('H7', 'S.TOTAL');
      
      for($i=0;$i<count($datas);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+8),$datas[$i]['barcode'].' ');
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+8),$datas[$i]['class_name_en']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+8),$datas[$i]['goods_name_jp']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+8),'JP');
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+8),$datas[$i]['product_num']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+8),'PCS');
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+8),$datas[$i]['one_price']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+8),$datas[$i]['all_price']);
    
        }
        //7.设置保存的Excel表格名称
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('INVOICE');
        
        //9.设置浏览器窗口下载表格
        $filename = "INVOICE_"  . rand(1000000, 9999999) . ".xlsx";
        // $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $ov = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $ov->save("excel/" . $filename);
        return $this->renderSuccess("导出成功", [
            "file_name" => "https://".$_SERVER["HTTP_HOST"] . "/excel/" . $filename,
        ]);
     }
     
     /**导出集运清关文件**/
    //导出成excel文档
     public function batchclearance(){
         //引入excel插件
        vendor('PHPExcel.PHPExcel');
        $Batch = new Batch;
        $param= $this->request->param();
        $objPHPExcel = new \PHPExcel();
        //获取需要导出的数据列表
        $ids= input("post.selectId/a");
        //1 待入库 2 已入库 3 已分拣上架  4 待打包  5 待支付  6 已支付 7 已分拣下架  8 已打包  9 已发货 10 已收货 11 已完成
        $map =[-1=>'问题件',1=>'待入库',2=>'已入库',3=>'已分拣上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        $status = [1=>'待查验',2=>'待支付',3=>'已支付','4'=>'已拣货','5'=>'已打包','6'=>'已发货','7'=>'已收货','8'=>'已完成','-1'=>'已取消'];
        $datas = [];
        $setting = SettingModel::getItem('aiidentify',$this->getWxappId());
        if($setting['is_baiduaddress']==0){
            return $this->renderError("尚未开启智能AI识别功能，请更改API");
        }
        $BaiduTextTran = new BaiduTextTran($setting);
  
           $data = (new Inpack())->with(['storage','shop','country','user','line','address'])->whereIn('batch_id',$param['id'])->select()->each(function ($item, $key) use($map,$BaiduTextTran){
                    //集运单包裹中的物品分类和价格
                    $packdata =(new Package())->with(['categoryAttr'])->where('inpack_id',$item['id'])->select();
                    $item['packdata'] = $packdata;
                    $item['weightkg'] = '011';
                    $item['categoryname'] = 'B';
                    $item['trademode'] = '0110';
                    $item['taxexemptionnature'] = '0110';
                    $item['currency'] = '142';
                    $item['BusinessUnitCode'] = '';
                    $item['cardnumber'] = '51000000000000 ';
                    // dump($item['storage']->toArray());die;
                    $item['linkman'] = $item['storage']['linkman'];
                    $item['enlinkman'] = pinyin::getPinyin($item['storage']['linkman']);
                    $item['fhcity'] = $item['storage']['region']['city'];
                    $item['enfhcity'] = pinyin::getPinyin($item['storage']['region']['city']);
                    $enfhcity = $BaiduTextTran->gettexttrans($item['storage']['region']['city'])['result']['trans_result'][0]['dst'];
                    if(!empty($enfhcity)){
                        $item['enfhcity'] = $enfhcity;  //英文
                    }
                    $item['fhphone'] = $item['storage']['phone'].' ';
                    $item['fhaddress'] = $item['storage']['region']['province'] . $item['storage']['region']['city'] . $item['storage']['region']['region'] . $item['storage']['address'];  
                    $item['fhenaddress'] = pinyin::getPinyin($item['storage']['region']['province'] . $item['storage']['region']['city'] . $item['storage']['region']['region'] . $item['storage']['address']);  //英文
                    $enaddress= $BaiduTextTran->gettexttrans($item['storage']['address'])['result']['trans_result'][0]['dst'];
                    if(!empty($enaddress)){
                        $item['fhenaddress'] = $enaddress;  //英文
                    }
                   
                    
                    //   dump( $item['address']->toArray());die;
                    $item['sjname'] = $item['address']['name'];
                    $item['sjenname'] = pinyin::getPinyin($item['address']['name']);
                    $item['sjcity'] = $item['address']['city'];
                    $item['sjphone'] = $item['address']['phone'];
                    $item['sjaddress'] = $item['address']['province'].$item['address']['city'].$item['address']['region'].$item['address']['detail'];
                    $item['sjenaddress'] = pinyin::getPinyin($item['address']['province'].$item['address']['city'].$item['address']['region'].$item['address']['detail']);  //英文
                    $sjaddress= $BaiduTextTran->gettexttrans($item['address']['province'].$item['address']['city'].$item['address']['region'].$item['address']['detail'])['result']['trans_result'][0]['dst'];
                    if(!empty($sjaddress)){
                        $item['sjenaddress'] = $sjaddress;  //英文
                    }
                   
                    return $item;
                }); 

          $op = 0;
          foreach ($data as $value){
              if(count($value['packdata'])>0){
                        foreach ($value['packdata'] as $key=> $val){
                            if(count($val['category_attr'])>0){
                                foreach ($val['category_attr'] as $k=> $v){
                                    //  dump($value);
                                    // dump($value['goods_name']);
                                    // $datas[$op] = $value;
                                    $datas[$op]['weightkg'] = $value['weightkg'];
                                    $datas[$op]['categoryname'] = $value['categoryname'];
                                    $datas[$op]['trademode'] = $value['trademode'];
                                    $datas[$op]['taxexemptionnature'] = $value['taxexemptionnature'];
                                    $datas[$op]['currency'] = $value['currency'];
                                    $datas[$op]['BusinessUnitCode'] = $value['BusinessUnitCode'];
                                    $datas[$op]['cardnumber'] = $value['cardnumber'];
                                    $datas[$op]['linkman'] = $value['linkman'];
                                    $datas[$op]['enlinkman'] = $value['enlinkman'];
                                    $datas[$op]['fhcity'] = $value['fhcity'];
                                    $datas[$op]['enfhcity'] = $value['enfhcity'];
                                    $datas[$op]['fhphone'] = $value['fhphone'];
                                    $datas[$op]['fhaddress'] = $value['fhaddress'];
                                    $datas[$op]['fhenaddress'] = $value['fhenaddress'];
                                    $datas[$op]['sjname'] = $value['sjname'];
                                    $datas[$op]['sjenname'] = $value['sjenname'];
                                    $datas[$op]['sjcity'] = $value['sjcity'];
                                    $datas[$op]['sjphone'] = $value['sjphone'];
                                    $datas[$op]['sjaddress'] = $value['sjaddress'];
                                    $datas[$op]['sjenaddress'] = $value['sjenaddress'];
                                    $datas[$op]['t_order_sn'] = $value['t_order_sn'];
                                    
                                    $datas[$op]['goods_name'] = $v['goods_name'];
                                    $datas[$op]['class_name_en'] = $v['class_name_en'];
                                    $datas[$op]['barcode'] = $v['barcode'].' ';
                                    $datas[$op]['origin_region'] = $v['origin_region'];
                                    $datas[$op]['spec'] = $v['goods_name'].'|'.$v['spec'];
                                    $datas[$op]['one_price'] = $v['one_price'];
                                    $datas[$op]['product_num'] = $v['product_num'];
                                    $datas[$op]['unit_weight'] = $v['unit_weight'];
                                    $datas[$op]['net_weight'] = $v['net_weight'];
                                    // dump($datas[$op]);
                                    $op=$op+1;
                                }
                            }
                        }
                    }
                       
          }
        //   dump($datas[0]['class_name_en']);dump($datas[1]['class_name_en']);die;
          $style_Array=array(
            'font'    => array (
               'bold'      => true
              ),
             'alignment' => array (
                      'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
               ),
              'borders' => array (
                   'top'     => array (
                           'style' => \PHPExcel_Style_Border::BORDER_THIN
                       )
                ),
          );
         
        $setting = SettingModel::getItem('store',$data[0]['wxapp_id']);
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $titlemap = [
            ['text'=>'运单号','value'=>"t_order_sn",'width'=>20],
            ['text'=>'分类','value'=>'categoryname','width'=>10],
            ['text'=>'贸易方式','value'=>'trademode','width'=>10],
            ['text'=>'征免性质','value'=>'taxexemptionnature','width'=>10],
            ['text'=>'币制','value'=>'currency','width'=>10],
            ['text'=>'经营单位代码','value'=>'BusinessUnitCode','width'=>30],
            ['text'=>'B类身份证号码','value'=>'cardnumber','width'=>20],
            ['text'=>'发件人名称','value'=>"linkman",'width'=>10],
            ['text'=>'英文名称','value'=>'enlinkman','width'=>10],
            ['text'=>'城市','value'=>'fhcity','width'=>10],
            ['text'=>'城市英文','value'=>'enfhcity','width'=>10],
            ['text'=>'电话','value'=>'fhphone','width'=>15],
            ['text'=>'发件人地址','value'=>'fhaddress','width'=>30],
            ['text'=>'地址英文','value'=>'fhenaddress','width'=>30],
            
            ['text'=>'收件人名称','value'=>'sjname','width'=>10],
            ['text'=>'英文名称','value'=>'sjenname','width'=>10],
            ['text'=>'城市','value'=>'sjcity','width'=>10],
            ['text'=>'电话','value'=>'sjphone','width'=>15],
            ['text'=>'地址','value'=>'sjaddress','width'=>30],
            ['text'=>'地址英文','value'=>'sjenaddress','width'=>30],
            
            ['text'=>'品名','value'=>'goods_name','width'=>10],
            ['text'=>'英文品名','value'=>'class_name_en','width'=>10],
            ['text'=>'编码','value'=>'barcode','width'=>10],
            ['text'=>'生产厂商','value'=>'origin_region','width'=>10],
            ['text'=>'规格','value'=>'spec','width'=>10],
            ['text'=>'价值','value'=>'one_price','width'=>10],
            ['text'=>'件数','value'=>'product_num','width'=>10],
            ['text'=>'毛重','value'=>'unit_weight','width'=>10],
            ['text'=>'净重','value'=>'net_weight','width'=>10],
            ['text'=>'数量','value'=>'product_num','width'=>10],
            ['text'=>'单位','value'=>'weightkg','width'=>10]
        ];
      
        
        $wordMap = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF'];
        
        //设置excel标题
        for ($i = 0; $i < count($titlemap); $i++) {
           $objPHPExcel->setActiveSheetIndex(0)->setCellValue($wordMap[$i].'1', $titlemap[$i]['text']);
        }
       
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:AF')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:AF1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A4:AF4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:AF')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objPHPExcel->getActiveSheet()->getStyle('A:AF')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置行高
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
        
        
        //设置excel标题宽度
        for ($i = 0; $i < count($titlemap); $i++) {
           $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($wordMap[$i])->setWidth($titlemap[$i]['width']);
        }
         //设置excel内容
    //   dump($datas[2]['class_name_en']);die;
        for($i=0;$i<count($datas);$i++){
            for ($j = 0; $j < count($titlemap); $j++) {
                $objPHPExcel->getActiveSheet()->setCellValue($wordMap[$j].($i+2),($datas[$i][$titlemap[$j]['value']]));
            }
        }
            // dump($titlemap);die;
        //7.设置保存的Excel表格名称
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('清关模板');
        //9.设置浏览器窗口下载表格
        $filename = "清关包裹"  . rand(1000000, 9999999) . ".xlsx";
        // $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $ov = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $ov->save("excel/" . $filename);
        return $this->renderSuccess("导出成功", [
            "file_name" => "https://".$_SERVER["HTTP_HOST"] . "/excel/" . $filename,
        ]);
     }

}
