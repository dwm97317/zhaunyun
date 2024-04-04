<?php
namespace app\store\model;
use think\Model;
use think\Db;
use app\api\model\Logistics;
use app\api\model\Package;
use app\store\model\Package as PackageModel;
use app\api\model\User;
use app\api\model\user\BalanceLog;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use app\common\model\Inpack as InpackModel;
use app\common\service\Email;
use app\store\service\BarCodeService;
use app\api\service\trackApi\TrackApi;
use app\common\model\User as UserModel;
use app\common\model\Setting as SettingModel;
use app\store\model\InpackImage;
use app\store\model\UserAddress;
use app\store\model\Ditch as DitchModel;
use app\store\model\Express;
use app\common\service\Message;
/**
 * 打包模型
 * Class Delivery
 * @package app\common\model
 */
class Inpack extends InpackModel
{
    
    public  $status = [
        'all' => [-1,1,2,3,4,5,6,7,8,9],
        'cancel' => [-1],
        'verify' => [1], // 查验
        'pay' => [-1,2,3,4,5,6,7,8,9], // 未支付
        'payed' => [2,3,4,5], // 已支付
        'sending' => [6],
        'sended' => [7],
        'complete' => [8]
     ];
     
     /**
     * 订单列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($dataType, $query = [])
    {
        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        !isset($query['limitnum']) && $query['limitnum'] = 10;
        
        $setting = SettingModel::detail("adminstyle")['values'];
        $order = ['updated_time'=>'desc'];
        if(isset($setting['inpackorderby'])){
            $order = [$setting['inpackorderby']['order_mode']=>$setting['inpackorderby']['order_type']];
        }
        
        // 获取数据列表
        $res= $this
            ->alias('pa')
            ->field('pa.*,ba.batch_id,ba.batch_name,ba.batch_no,u.nickName')
            ->with(['line','address','storage','user','shop','usercoupon'])
            ->join('user u','u.user_id = pa.member_id','left')
            ->join('batch ba','ba.batch_id = pa.batch_id','left')
            ->where('pa.status','in',$this->status[$dataType])
            ->where('pa.is_delete',0)
            ->order($order)
            ->paginate($query['limitnum'], false, [
                'query' => \request()->request()
            ]);
            // dump($res->toArray());die;
         return $res;
    }
    
    
    /**
     * 未支付订单列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getNoPayList($dataType, $query = [])
    {
        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        !isset($query['limitnum']) && $query['limitnum'] = 10;
        
        $setting = SettingModel::detail("adminstyle")['values'];
        $order = ['updated_time'=>'desc'];
        if(isset($setting['inpackorderby'])){
            $order = [$setting['inpackorderby']['order_mode']=>$setting['inpackorderby']['order_type']];
        }
        // 获取数据列表
        $res= $this
            ->alias('pa')
            ->with(['line','address','storage','user'])
            ->join('user u','u.user_id = pa.member_id','left')
            // ->join('user_address add','add.address_id = pa.address_id','left')
            ->where('pa.status','in',$this->status[$dataType])
            ->where('pa.is_delete',0)
            ->where('pa.is_pay',2)
            ->order($order)
            ->paginate($query['limitnum'], false, [
                'query' => \request()->request()
            ]);
            // dump($res->toArray());die;
         return $res;
    }
    
    /**
     * 快速打包订单列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getQuicklypack($dataType, $query = [])
    {
        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        !isset($query['limitnum']) && $query['limitnum'] = 10;
        
        $setting = SettingModel::detail("adminstyle")['values'];
        $order = ['updated_time'=>'desc'];
        if(isset($setting['inpackorderby'])){
            $order = [$setting['inpackorderby']['order_mode']=>$setting['inpackorderby']['order_type']];
        }
        // 获取数据列表
        $res= $this
            ->alias('pa')
            ->with(['line','address','storage','user'])
            ->join('user u','u.user_id = pa.member_id','left')
            // ->join('user_address add','add.address_id = pa.address_id','left')
            ->where('pa.status','in',$this->status[$dataType])
            ->where('pa.is_delete',0)
            ->where('pa.address_id',null)
            ->order($order)
            ->paginate($query['limitnum'], false, [
                'query' => \request()->request()
            ]);
            // dump($res->toArray());die;
         return $res;
    }
    
    
    
    // 代购单 同步
    public function anyicData($v){
        $data['source'] = 4;
        $data['express_num'] = $v['express_num']; 
        $data['express_id'] = $v['express_id'];
        $data['real_payment'] = $v['free'];
        $data['price'] = $v['price'];
        $data['member_id'] = $v['member_id'];
        $data['storage_id'] = $v['storage_id'];
        $data['created_time'] = getTime();
        $data['updated_time'] = getTime();
        $data['status'] = 2;
        $data['is_pay'] = 1;
        $data['pay_time'] =  getTime();
        $data['entering_warehouse_time'] = getTime();
        $data['order_sn'] = createSn();
        $data['country_id'] = 12;
        $data['wxapp_id'] = self::$wxapp_id;
        $packageRes = (new Package())->insert($data);  
        if (!$packageRes){
            return false;
        }
        return true;
    }
    
    
     /***
     * 保存集运单编辑内容
     * 2022年11月5日 冯
     * @param $data []
     */
    public function edit($data){
      
        //修改保存集运图片
        $imgres = true;
        if(isset($data['images'])){
           $inpackImg = new  InpackImage;
           $inpackImg->where('inpack_id',$data['id'])->delete();
           $img = [
                'inpack_id' => $data['id'],
                'wxapp_id' =>  self::$wxapp_id,
            ];
           foreach ($data['images'] as $val){
               $img['image_id'] = $val;
               $imgres = $inpackImg->insert($img);
           }
           unset($data['images']);
        }
  
        $pack = $this->where('id',$data['id'])->find();
        $userData = (new UserModel)->where('user_id',$pack['member_id'])->find();
        $pack['userName']=$userData['nickName'];
        //判断是否更新状态到已查验
        if(isset($data['verify']) && ($data['verify'] ==1)){
            $data['status'] = 2;
            $data['pick_time'] = getTime();
            //发送订阅消息以及模板消息,包裹查验完成，等待支付
            $noticesetting = SettingModel::getItem('notice');
            //根据设置内容，判断是否需要发送通知；
            
         
            
            $pack['remark']= $noticesetting['check']['describe'];
            $pack['total_free'] = $pack['free']+$pack['other_free']+$pack['pack_free'];
            //获取模板消息设置，根据设置选择调用的函数

            //发送邮件通知
             $email = SettingModel::getItem('email');
            if($email['is_enable']==1 && (isset($pack['member_id']) || !empty($pack['member_id']))){
                $EmailUser = UserModel::detail($pack['member_id']);
                $EmailData['code'] = $data['id'];
                $EmailData['logistics_describe']=$noticesetting['check']['describe'];
                (new Email())->sendemail($EmailUser,$EmailData,$type=1);
            }
        }
        
        
        unset($data['verify']);
        // dump($pack);die;
        $rers =  $this->where('id',$data['id'])->update($data);
        $tplmsgsetting = SettingModel::getItem('tplMsg');
            if($tplmsgsetting['is_oldtps']==1){
                  //发送旧版本订阅消息以及模板消息
                //   $sub = $this->sendEnterMessage([$post]);
                  $res =$this->sendEnterMessage([$pack],'payment');
            }else{
                  //发送新版本订阅消息以及模板消息
                  Message::send('package.dabaosuccess',$pack);
                  Message::send('package.payorder',$pack);
            }
       if($rers || $imgres){return true;}
       return false;
    }
    
    
    /***
     * 修改集运单的状态 
     * @param $data []
     */
    public function modify($data){
                
        $field = ['line_id','length','width','height','weight','verify','free','pack_free','cale_weight','volume','other_free','remark','t_number','t_name','t_order_sn'];
        $update = [];
        //物流模板设置
        $noticesetting = SettingModel::getItem('notice');
        $tplmsgsetting = SettingModel::getItem('tplMsg');
        foreach ($field as $v){
            if (isset($data[$v]))
               $update[$v] = $data[$v];
        }
      
        $update['updated_time'] = getTime();
   
        if (isset($data['pay_method'])){ 
            $pack = $this->where(['id'=>$data['id']])->field('id,order_sn,weight,free,other_free,pack_free,member_id,created_time,wxapp_id,pack_ids')->find();
            if ($data['pay_method']==1){
            
                $update['status'] = 5;
                $update['pay_time'] = getTime();
                $update['is_pay'] = 1;
                // 立即扣款 
                $allPrice = $data['free'] + $data['pack_free'] + $data['other_free'];
                $user = (new User())->find($pack['member_id']);
                Db::startTrans();
                if ($user['balance']<$allPrice){
                    return $this->renderError('余额不足,请充值');
                }
                $memberUp = (new User())->where(['user_id'=>$user['user_id']])->update([
                  'balance'=>$user['balance']-$allPrice,
                  'pay_money' => $user['pay_money']+ $allPrice,
                ]);
                 if (!$memberUp){
                     Db::rollback();
                     return $this->renderError('支付失败,请重试');
                 }
                  // 新增余额变动记录
                 BalanceLog::add(SceneEnum::CONSUME, [
                  'user_id' => $user['user_id'],
                  'money' => $allPrice,
                  'remark' => '包裹单号'.$pack['order_sn'].'的运费支付',
                  'sence_type' => 2,
                  'wxapp_id' => (new PackageModel())->getWxappId(),
              ], [$user['nickName']]);
            }else{
                $update['status'] = 5;
            }
        }
        
        if (!isset($data['type'])){
             // 更新查验物流信息
                $pack = $this->where(['id'=>$data['id']])->find();
                $userData = (new UserModel)->where('user_id',$pack['member_id'])->find();
                if (isset($data['verify']) && $data['verify']==1){
                 
                $update['status'] = 2;
                if ($pack['source']==2){
                    $update['status'] = 2;
                }
                unset($update['verify']);
               
                $pack['total_free'] = $pack['free']+$pack['other_free']+$pack['pack_free'];
               
                $packids = explode(',',$pack['pack_ids']);
                //判断是否需要添加物流信息
                if($noticesetting['enter']['is_enable']==1){
                   foreach ($packids as $v){
                    Logistics::add($v,$noticesetting['enter']['describe']);
                    } 
                }
                
                //发送订阅消息以及模板消息
                $pack['userName']=$userData['nickName'];
                $pack['remark']= $noticesetting['enter']['describe'];
                
                if($tplmsgsetting['is_oldtps']==1){
                    $res =$this->sendEnterMessage([$pack],'payment');
                }else{
                    Message::send('package.sendpack',$pack);
                }
                
                //发送邮件通知
                $emailsetting = SettingModel::getItem('email');
                if($emailsetting['is_enable']==1 && (isset($pack['member_id']) || !empty($pack['member_id']))){
                    $EmailUser = UserModel::detail($pack['member_id']);
                    $EmailData['code'] = $data['id'];
                    $EmailData['logistics_describe']=$noticesetting['enter']['describe'];
                    (new Email())->sendemail($EmailUser,$EmailData,$type=1);
                }  
                
            }
            if (isset($data['pay_status'])){
                if (isset($update['status']) && $update['status']!=6){
                    $update['status'] = $data['pay_status'];
                }
                if($data['pay_status']){
                    $update['status'] = 3;
                    $update['pay_time'] = getTime();
                }

                // 更新查验物流信息
                $pack = $this->where(['id'=>$data['id']])->field('id,order_sn,pack_ids')->find();
                $packids = explode(',',$pack['pack_ids']);
                (new Package())->where('id','in',$packids)->update(['status'=>6]);
                unset($update['pay_status']);
            }
        }else{
            $update['status'] = '6';
             // 更新查验物流信息
            $pack = $this->where(['id'=>$data['id']])->find();
            $useraddress = UserAddress::detail($pack['address_id']);
            // dump(substr($useraddress['phone'],-4));die;
            $userData = (new UserModel)->where('user_id',$pack['member_id'])->find();
            $packids = explode(',',$pack['pack_ids']);
            // foreach ($packids as $v){
            //     Logistics::add($v,'包裹已经为您发货,正在发往所在目的地');
            // }
            // $track =  getFileData('assets/track.json');
            // $trackByKey = array_column($track,null,'key');  
            // $pack['t_name'] = !empty($update['t_name1'])?$trackByKey[$update['t_name1']]['_name']:$update['t_name'];
            // if ($update['t_name']=='' && $update['t_number']){
            //     $update['t_name'] = $pack['t_name'];
            //     $update['t_number'] = $update['t_number'];
            // }
            
 
          
            $pack['t_order_sn'] = $data['t_order_sn'];
            
            //   dump($update);die;
             //判断是否需要添加物流信息
            if($noticesetting['dosend']['is_enable']==1){
                if(!empty($pack['t_order_sn'])){
                   $noticesetting['dosend']['describe'] = '包裹转单操作，新单号为'.$pack['t_order_sn']; 
                }
                if(strpos($noticesetting['dosend']['describe'],'code')){
                     $des = str_ireplace('{code}', $pack['t_order_sn'], $noticesetting['dosend']['describe']);
                     Logistics::addInpackLog($pack['order_sn'],$des,$pack['t_order_sn']);
                }else{
                     Logistics::addInpackLog($pack['order_sn'],$noticesetting['dosend']['describe'],$pack['t_order_sn']);
                }   
            }
            ////注册发货单到17track,当是选择可以查询的物流时，自有物流不可查询
            if($data['transfer']==1 && $noticesetting['is_track_fahuo']['is_enable']==1){
                $express = (new Express())->where('express_code',$data['tt_number'])->find();
                // dump($express);die;
                $trackd = (new TrackApi())
                ->register([
                    'track_sn'=>$pack['t_order_sn'],
                    't_number'=>$data['tt_number'],
                    'phone' => substr($useraddress['phone'],-4),
                    'wxapp_id' =>$userData['wxapp_id']
                ]);
                $update['t_name'] = $express['express_name'];
                $update['t_number'] = $data['tt_number'];
                $update['transfer'] = $data['transfer'];
            }
            
            if($data['transfer']==0){
                $ditchdetail = DitchModel::detail($data['t_number']);
                $update['t_name'] = $ditchdetail['ditch_name'];
                $update['t_number'] = $ditchdetail['ditch_id'];
                $update['transfer'] = $data['transfer'];
            }
            

            //发送订阅消息以及模板消息
                $pack['userName']=$userData['nickName'];
                $pack['remark']='包裹已经发货';
                $pack['total_free'] = $pack['free'] + $pack['pack_free'] + $pack['other_free'] ;
                // $res =$this->sendEnterMessage([$pack],'payment');
                if($tplmsgsetting['is_oldtps']==1){
                    $res =$this->sendEnterMessage([$pack],'payment');
                }else{
                    Message::send('package.sendpack',$pack);
                }
            //发送邮件通知
            $emailsetting = SettingModel::getItem('email');
            if($emailsetting['is_enable']==1 && (isset($pack['member_id']) || !empty($pack['member_id']))){
                $EmailUser = UserModel::detail($pack['member_id']);
                $EmailData['code'] = $data['id'];
                $EmailData['logistics_describe']=$noticesetting['dosend']['describe'];
                (new Email())->sendemail($EmailUser,$EmailData,$type=1);
            }
        }
        unset($update['verify']);
        
        if($data['type']=='change'){
       
            $upd['t2_number'] = $update['t_number'];
            $upd['t2_name'] = $update['t_name'];
            $upd['t2_order_sn'] = $update['t_order_sn'];
            $upd['updated_time'] = $update['updated_time'];
            $upd['status'] = $update['status'];
            $update = $upd;
            // dump($update);die;
            ////注册发货单到17track,当是选择可以查询的物流时，自有物流不可查询
            if($data['transfer']==1 && $noticesetting['is_track_zhuandan']['is_enable']==1){
                $trackd = (new TrackApi())
                ->register([
                    'track_sn'=>$upd['t2_order_sn'],
                    't_number'=>$upd['t2_number'],
                    'phone' => substr($useraddress['phone'],-4),
                    'wxapp_id' =>$userData['wxapp_id']
                ]);
            }
        }
        $resss = $this->where(['id'=>$data['id']])->update($update);
        Db::commit();
        return  $resss;
    }
    
    // 添加包裹
    public function appendData($data){
        //$pack : 要插入的快递信息
        //$inpack : 被插入的集运单
        $inpack = (new Inpack())->find($data['id']);    
        if (!$pack = (new Package())->where(['express_num'=>$data['express_num'],'is_delete'=>0])->find()){
            $resl =  (new Package())->where(['inpack_id'=>$data['id'],'is_delete'=>0])->find();
            // dump($data);die;
            if(empty($resl)){
                $this->error = '该包裹不在库中';
                return false;
            }
            unset($resl['id']);
            $resl['express_num'] = $data['express_num'];
            $resl['entering_warehouse_time'] = getTime();
            $resl['updated_time'] = getTime();
            $resl['created_time'] = getTime();
            $newid = (new Package())->insertGetId($resl->toArray());
            $inpack->save(['pack_ids'=>$inpack['pack_ids'].','.$newid]);
        }
        // 判断包裹是否已经在其他集运包裹中；如果不在，则可以添加进来；
        if(in_array($pack['status'],[5,6,7,8,9,10,11])){
            $this->error = '该包裹已在集运单中';
            return false;
        }
        
        //判断需要插入的快递单号是否被领取，如果领取memberid不存在，则设置为集运单的memberid；并修改状态为已认领；
        if(!$pack['member_id']){
           (new Package())->where(['id'=>$pack['id']])->update(['member_id'=>$inpack['member_id'],'is_take' => 2]);
        }
        $packages = explode(',',$inpack['pack_ids']);
        $packages[] = $pack['id'];
        $inpackData['pack_ids'] = implode(',',$packages);
        $res = $this->where(['id'=>$data['id']])->update($inpackData);
        (new Package())->where(['id'=>$pack['id']])->update(['status'=>5,'inpack_id'=>$data['id']]);
        if ($res){
            return true;
        }
        $this->error = '包裹添加失败';
        return false;
    }
      
    
    //获取集运单的相关信息->with(['line','storage','inpackimage.file'])
    public static function details($id){
        return self::get($id, ['inpackimage.file','line']);
    }

    public function setWhere($query){
        // dump($query);die;
        !empty($query['status']) && $this->where('pa.status','in',$query['status']);
        !empty($query['order_sn']) && $this->where('pa.order_sn|pa.t_order_sn|pa.t2_order_sn','like','%'.$query['order_sn'].'%');
        !empty($query['extract_shop_id']) && $this->where('pa.storage_id','=',$query['extract_shop_id']);
        !empty($query['line_id']) && $this->where('pa.line_id','=',$query['line_id']);
        !empty($query['service_id']) && $this->where('u.service_id','=',$query['service_id']);
        !empty($query['user_code']) && $this->where('u.user_code','=',$query['user_code']);
        !empty($query['user_id']) && $this->where('pa.member_id','=',$query['user_id']);
        !empty($query['batch_no']) && $this->where('ba.batch_name|ba.batch_no','=',$query['batch_no']);
        !empty($query['batch_id']) && $this->where('pa.batch_id','=',$query['batch_id']);
        if(!empty($query['time_type'])){
            !empty($query['start_time']) && $this->where($query['time_type'], '>=', $query['start_time']);
            !empty($query['end_time']) && $this->where($query['time_type'], '<=', $query['end_time']." 23:59:59");
        }
        !empty($query['search']) && $this->where('pa.member_id|u.nickName|u.user_code','like','%'.$query['search'].'%');
        if(!empty($query['tr_number'])){
            $express_num = str_replace("\r\n","\n",trim($query['tr_number']));
            $express_num = explode("\n",$express_num);
            $express_num = implode(',',$express_num);
            $where['t_order_sn'] = array('in', $express_num);
            $this->where($where);
        }
        return $this;
    }
    
    // 获取面单相关数据
    public function getExpressData($id){
        $data = $this->with(['address','storage'])->find($id);
        if ($data['member_id']){
            $data['member'] = (new UserModel())->find($data['member_id']);
        }
        return $data;
    }
    
    // 批量获取面单相关数据
    public function getExpressBatchData($ids){
        $data = $this->with(['address','storage'])->whereIn('id',$ids)->select();
        $BarCodeService = new BarCodeService();
        foreach ($data as $k => $val){
             if ($val['member_id']){
                $data[$k]['member'] = (new UserModel())->find($val['member_id']);
             }
             $code = $val['t_order_sn']??0;
             if (!file_exists('barcode/'.$code.'.png')){
                 $codeRes = $BarCodeService->Generator($code);
             }
             $codeUrl = 'barcode/'.$code.'.png';
             $data[$k]['codeurl'] = $codeUrl;
        }
        return $data;
    }
    
    public function remove(){
         return $this->save(['status'=>-1]);
    }
    
    public function removedelete(){
         return $this->save(['is_delete'=>1]);
    }
   
    public function line(){
        return $this->belongsTo('line','line_id');
    }
    
    public function address(){
        return $this->belongsTo('app\store\model\UserAddress','address_id');
    }
    
    public function storage(){
        return $this->belongsTo('app\store\model\store\Shop','storage_id');
    }
    
    public function shop(){
        return $this->belongsTo('app\store\model\store\Shop','shop_id');
    }
    
    public function batch(){
        return $this->belongsTo('app\store\model\Batch');
    }
    /**
     * 获取订单总量
     * @param null $day
     * @return string
     * @throws \think\Exception
     */
    public function getPayOrderTotal($startDate = null, $endDate = null)
    {
        $filter = [
            'is_delete' => 0,
            'is_pay' => 1
        ];    
        if (!is_null($startDate) && !is_null($endDate)) {
              $filter['pay_time'] = ["between time",[$startDate, date('Y-m-d',strtotime($endDate)+86400)]];   
        }
        return $this
            ->where('status', 'in', [3,4,5,6,7,8])
            ->where($filter)
            ->count('id');
    }
    
    // 统计成交额
    public function getPackageTotalPrice($startDate = null, $endDate = null){
 
        if (!is_null($startDate) && !is_null($endDate)) {
            $map['pay_time'] = ["between time",[$startDate, date('Y-m-d',strtotime($endDate)+86400)]];
        }
        return $this->where('is_pay', '=', 1)
            ->where($map)
            ->where('status', '>', 2)
            ->where('is_delete', '=', 0)
            ->sum('real_payment');
    }
    
    /**
     * 获取已付款订单总数 (可指定某天)
     * @param null $startDate
     * @param null $endDate
     * @return int|string
     * @throws \think\Exception
     */
    public function getPayPackageTotal($startDate = null, $endDate = null)
    {
        $filter = [
            'is_pay' => 1,
            'status' => ['<>','-1'],
        ];
        if (!is_null($startDate) && !is_null($endDate)) {
            $filter['pay_time'] = ["between time",[$startDate, date('Y-m-d',strtotime($endDate)+86400)]];
        }
        return $this->where($filter)
            ->where('is_delete', '=', 0)
            ->count();
    }
    
        
    // 下单用户数统计
    public function getPayPackageUserTotal($day){
         $filter = [
            'is_delete' => 0,
        ];
        if (!is_null($day) && !is_null($day)) {
            $filter['pay_time'] = ["between time",[$day, date('Y-m-d',strtotime($day)+86400)]];
        }
        $userIds = $this->distinct(true)
             ->where('status', '>', 2)
            ->where($filter)
            ->column('member_id');
        return count($userIds);
    }
    
        /**
     * 获取某天的总销售额
     * @param null $startDate
     * @param null $endDate
     * @return float|int
     */
    public function getOrderTotalPrice($startDate = null, $endDate = null)
    {
        $filter = [
            'is_delete' => 0,
            'is_pay' => 1
        ];
        if (!is_null($startDate) && !is_null($endDate)) {
            $filter['pay_time'] = ["between time",[$startDate, date('Y-m-d',strtotime($endDate)+86400)]];        
        }
        return $this->where($filter)->sum('real_payment');
    }
}
