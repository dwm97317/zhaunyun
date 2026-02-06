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
use app\store\model\Countries;
use app\store\model\Ditch as DitchModel;
use app\store\model\Express;
use app\common\service\Message;
use app\common\model\DitchNumber;
use app\store\model\Line;
use app\store\model\InpackItem;

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
        'complete' => [8],
        'exceed'=>[6],
     ];
     
         /**
     * 超时件订单列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getExceedCountList($dataType)
    {
        // 获取数据列表
        return $this
            ->alias('pa')
            ->with(['line','address','storage','user'])
            ->join('user u','u.user_id = pa.member_id','left')
            ->join('user_address add','add.address_id = pa.address_id','left')
            // ->join('line li','li.id = pa.line_id','left')
            ->where('pa.status','in',$this->status[$dataType])
            ->where('pa.is_delete',0)
            ->where('pa.exceed_date','>',0)
            ->where('pa.exceed_date','<',time())
            ->order(['pa.status' => 'desc'])
            ->count();
    }
    
    /**
     * 获取待审核订单数量
     * @return int
     */
    public function getPaymentAuditCount()
    {
        return $this
            ->alias('pa')
            ->where('pa.is_delete',0)
            ->where('pa.is_pay',3)  // 支付待审核
            ->where('pa.is_pay_type',6)  // 线下支付
            ->count();
    }
    
    /**
     * 订单支付审核列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getPaymentAuditList($dataType, $query = [])
    {
        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        !isset($query['limitnum']) && $query['limitnum'] = 10;
        
        $setting = SettingModel::detail("adminstyle")['values'];
        $order = ['pa.updated_time'=>'desc'];
        if(isset($setting['inpackorderby'])){
            $order = [$setting['inpackorderby']['order_mode']=>$setting['inpackorderby']['order_type']];
        }
        if(isset($query['orderparam']) && !empty($query['orderparam']) && isset($query['descparam'])){
            $order = [$query['orderparam']=>$query['descparam']];
        }
        
        // 获取待审核的订单：状态为待支付(status=2)且使用线下支付(is_pay_type=6)或者其他需要审核的支付方式
        $res = $this
            ->alias('pa')
            ->field('pa.*,ba.batch_id,ba.batch_name,ba.batch_no,u.nickName')
            ->with(['line','address','storage','user','shop','usercoupon','packagelist','packageitems'])
            ->join('user u','u.user_id = pa.member_id','left')
            ->join('batch ba','ba.batch_id = pa.batch_id','left')
            ->where('pa.is_delete',0)
            ->where('pa.is_pay',3)  // 未支付
            ->where('pa.is_pay_type',6)  // 线下支付
            ->order($order)
            ->paginate($query['limitnum'], false, [
                'query' => \request()->request()
            ]);
            
        return $res;
    }
     
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
        
        // dump($query['limitnum']);die;
        $setting = SettingModel::detail("adminstyle")['values'];
        $order = ['updated_time'=>'desc'];
        if(isset($setting['inpackorderby'])){
            $order = [$setting['inpackorderby']['order_mode']=>$setting['inpackorderby']['order_type']];
        }
        if(isset($query['orderparam']) && !empty($query['orderparam']) && isset($query['descparam'])){
            $order = [$query['orderparam']=>$query['descparam']];
        }
        
        // 获取数据列表
        $res= $this
            ->alias('pa')
            ->field('pa.*,ba.batch_id,ba.batch_name,ba.batch_no,u.nickName')
            ->with(['line','address','storage','user','shop','usercoupon','packagelist','packageitems','sharingorder'])
            ->join('user u','u.user_id = pa.member_id','left')
            ->join('batch ba','ba.batch_id = pa.batch_id','left')
            ->where('pa.status','in',$this->status[$dataType])
            ->where('pa.is_delete',0)
            ->order($order)
            ->paginate($query['limitnum'], false, [
                'query' => \request()->request()
            ]);

        // Fix: Ensure Mother SN and Status are populated and propagated
        $res->each(function($item, $key){
            // 1. Fix Mother Order SN if missing
            if(empty($item['t_order_sn']) && isset($item['packageitems']) && count($item['packageitems']) > 0){
                $item['t_order_sn'] = $item['packageitems'][0]['t_order_sn'];
            }

            // 2. Determine Best Available Status
            // Check mother status
            $currentStatus = (isset($item['last_trace_code']) && $item['last_trace_code'] != '0') ? $item['last_trace_code'] : '';
            
            // Check first child status if mother is empty
            if (empty($currentStatus) && isset($item['packageitems']) && count($item['packageitems']) > 0) {
                 $firstChildStatus = $item['packageitems'][0]['last_trace_code'];
                 if (!empty($firstChildStatus) && $firstChildStatus != '0') {
                     $currentStatus = $firstChildStatus;
                 }
            }

            // 3. Apply Status to Mother
            if (!empty($currentStatus)) {
                $item['last_trace_code'] = $currentStatus;
            }

            // 4. Propagate Status to All Children (if they don't have one)
            if (isset($item['packageitems']) && count($item['packageitems']) > 0) {
                // We need to modify the collection/array of items. 
                // Since this is likely a Collection of objects or arrays, we iterate by reference if possible
                // ThinkPHP Collections items can be array or Object.
                foreach ($item['packageitems'] as &$box) {
                    if (empty($box['last_trace_code']) || $box['last_trace_code'] == '0') {
                        $box['last_trace_code'] = $currentStatus;
                    }
                }
            }

            return $item;
        });

         return $res;
    }
    
    
        /**
     * 未支付月结订单列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getArrearsList($dataType, $query = [])
    {
        // 检索查询条件
        empty($query['limitnum']) && $query['limitnum']=10;
        !empty($query) && $this->ArrearsetWhere($query);
        // 获取数据列表
        $res= $this
            ->alias('pa')
            ->with(['line','address','storage','user'])
            ->join('user u','u.user_id = pa.member_id','left')
            ->join('user_address add','add.address_id = pa.address_id','left')
            ->where('pa.status','in',$dataType)
            ->where('pa.is_delete',0)
            ->where('pa.is_pay',2)
            ->where('pa.pay_type',2)
            ->order(['pa.created_time' => 'desc'])
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
     * 超时件订单列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getExceedList($dataType, $query = [])
    {
        // 检索查询条件
        empty($query['limitnum']) && $query['limitnum']=10;
        !empty($query) && $this->setWhere($query);
        // 获取数据列表
        $res= $this
            ->alias('pa')
            ->with(['line','address','storage','user'])
            ->join('user u','u.user_id = pa.member_id','left')
            ->join('user_address add','add.address_id = pa.address_id','left')
            // ->join('line li','li.id = pa.line_id','left')
            ->where('pa.status','in',$this->status[$dataType])
            ->where('pa.is_delete',0)
            ->where('pa.exceed_date','>',0)
            ->where('pa.exceed_date','<',time())
            ->order(['pa.status' => 'desc'])
            ->paginate($query['limitnum'], false, [
                'query' => \request()->request()
            ]);
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
    
    public function ArrearsetWhere($query){
        !empty($query['status']) && $this->where('pa.status','in',$query['status']);
        !empty($query['order_sn']) && $this->where('pa.order_sn|pa.t_order_sn','like','%'.$query['order_sn'].'%');
        !empty($query['extract_shop_id']) && $this->where('pa.storage_id','=',$query['extract_shop_id']);
        !empty($query['user_code']) && $this->where('u.user_code','=',$query['user_code']);
        !empty($query['service_id']) && $this->where('u.service_id','=',$query['service_id']);
        !empty($query['user_id']) && $this->where('pa.member_id','=',$query['user_id']);
        !empty($query['line_id']) && $this->where('pa.line_id','=',$query['line_id']);
        !empty($query['start_time']) && $this->where('receipt_time', '>', $query['start_time']);
        !empty($query['end_time']) && $this->where('receipt_time', '<', $query['end_time']." 23:59:59");
        !empty($query['search']) && $this->where('pa.member_id|u.nickName|u.user_code','like','%'.$query['search'].'%');
        return $this;
    }
    
        /**
     * 货到付款订单列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getnopayorderList($dataType, $query = [])
    {
        // 检索查询条件
        empty($query['limitnum']) && $query['limitnum']=10;
        !empty($query) && $this->ArrearsetWhere($query);
        // 获取数据列表
        $res= $this
            ->alias('pa')
            ->with(['line','address','storage','user'])
            ->join('user u','u.user_id = pa.member_id','left')
            ->join('user_address add','add.address_id = pa.address_id','left')
            ->where('pa.status','in',$dataType)
            ->where('pa.is_delete',0)
            ->where('pa.is_pay',2)
            ->where('pa.pay_type',1)
            ->order(['pa.created_time'=>'desc'])
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
        //   dump($data);die;
        if(isset($data['images'])){
           $inpackImg = new  InpackImage;
           $inpackImg->where('inpack_id',$data['id'])->where('image_type',10)->delete();
           $img = [
                'inpack_id' => $data['id'],
                'image_type'=>10,
                'wxapp_id' =>  self::$wxapp_id,
            ];
           foreach ($data['images'] as $val){
               $img['image_id'] = $val;
               $imgres = $inpackImg->insert($img);
           }
           unset($data['images']);
        }
        
        if(isset($data['wvimages'])){
           $inpackImg = new  InpackImage;
           $inpackImg->where('inpack_id',$data['id'])->where('image_type',20)->delete();
           $img = [
                'inpack_id' => $data['id'],
                'image_type'=>20,
                'wxapp_id' =>  self::$wxapp_id,
            ];
           foreach ($data['wvimages'] as $val){
               $img['image_id'] = $val;
               $imgres = $inpackImg->insert($img);
           }
           unset($data['wvimages']);
        }
  
        $pack = $this->where('id',$data['id'])->find();
        $userData = (new UserModel)->where('user_id',$pack['member_id'])->find();
        $pack['userName']=$userData['nickName'];
        //判断是否更新状态到已查验
        // dump($data);die;
        if(isset($data['verify']) && ($data['verify'] ==1)){
            $data['status'] = 2;
            $data['pick_time'] = getTime();
            //发送订阅消息以及模板消息,包裹查验完成，等待支付
            $noticesetting = SettingModel::getItem('notice',$pack['wxapp_id']);
            //根据设置内容，判断是否需要发送通知；
            $pack['remark']= $noticesetting['check']['describe'];
            $pack['total_free'] = $pack['free']+$pack['other_free']+$pack['pack_free'];
            //生成物流轨迹信息
            Logistics::addInpackLogs($pack['order_sn'],$noticesetting['check']['describe']);
            //获取模板消息设置，根据设置选择调用的函数
            $tplmsgsetting = SettingModel::getItem('tplMsg',$pack['wxapp_id']);
            
            // 获取费用审核设置，判断是否需要审核后才发送支付通知
            $adminstyle = SettingModel::getItem('adminstyle', $pack['wxapp_id']);
            $is_verify_free = isset($adminstyle['is_verify_free']) ? $adminstyle['is_verify_free'] : 0;
            $canSendPayOrder = true; // 是否可以发送支付通知
            
            // 如果开启了费用审核，需要检查是否已审核
            if($is_verify_free == 1) {
                $is_doublecheck = isset($pack['is_doublecheck']) ? $pack['is_doublecheck'] : 0;
                $canSendPayOrder = ($is_doublecheck == 1); // 只有已审核才能发送
            }
            
            if($tplmsgsetting['is_oldtps']==1){
                  //发送旧版本订阅消息以及模板消息
                  $res =$this->sendEnterMessage([$pack],'payment');
            }else{
                  //发送新版本订阅消息以及模板消息
                  Message::send('package.dabaosuccess',$pack);
                  // 只有满足条件时才发送支付通知
                  if($canSendPayOrder) {
                      Message::send('package.payorder',$pack);
                  }
            }
            //发送邮件通知
            $email = SettingModel::getItem('email',$pack['wxapp_id']);
            if($email['is_enable']==1 && (isset($pack['member_id']) || !empty($pack['member_id']))){
                $EmailUser = UserModel::detail($pack['member_id']);
                $EmailData['code'] = $data['id'];
                $EmailData['logistics_describe']=$noticesetting['check']['describe'];
                (new Email())->sendemail($EmailUser,$EmailData,$type=1);
            }
        }
        
        
        unset($data['verify']);
        if(isset($data['item'])){
            $inpackitem = $data['item'];
            if(!empty($inpackitem['length']) && !empty($inpackitem['width']) && !empty($inpackitem['height'])){
                $inpackitem['inpack_id'] = $data['id'];
                (new InpackItem())->addItem($inpackitem);
            }
        }
        
        // 检查费用审核状态是否从0变为1（费用审核通过）
        $needSendPayOrder = false;
        if(isset($data['is_doublecheck']) && $data['is_doublecheck'] == 1) {
            $oldPack = $this->where('id',$data['id'])->find();
            if($oldPack && isset($oldPack['is_doublecheck']) && $oldPack['is_doublecheck'] == 0) {
                // 费用审核从0变为1，需要发送支付通知
                $needSendPayOrder = true;
            }
        }
        
        unset($data['item']);
        $rers =  $this->where('id',$data['id'])->update($data);
        
        // 如果费用审核刚通过，发送支付通知
        if($needSendPayOrder) {
            $packAfterUpdate = $this->where('id',$data['id'])->find();
            $userData = (new UserModel)->where('user_id',$packAfterUpdate['member_id'])->find();
            $packAfterUpdate['userName'] = $userData['nickName'];
            $packAfterUpdate['total_free'] = $packAfterUpdate['free'] + $packAfterUpdate['other_free'] + $packAfterUpdate['pack_free'];
            
            $noticesetting = SettingModel::getItem('notice', $packAfterUpdate['wxapp_id']);
            $packAfterUpdate['remark'] = $noticesetting['check']['describe'];
            
            $tplmsgsetting = SettingModel::getItem('tplMsg', $packAfterUpdate['wxapp_id']);
            if($tplmsgsetting['is_oldtps'] == 1) {
                // 发送旧版本订阅消息以及模板消息
                $res = $this->sendEnterMessage([$packAfterUpdate], 'payment');
            } else {
                // 发送新版本订阅消息以及模板消息
                Message::send('package.payorder', $packAfterUpdate);
            }
        }
        
       if($rers || $imgres){return true;}
       return false;
    }
    
    
    
     /***
     * 修改集运单的状态 
     * @param $data []
     */
    public function zddeliverySave($data){
    
        $DitchNumber = new DitchNumber();       
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

        // if (isset($data['pay_method'])){ 
        //     $pack = $this->where(['id'=>$data['id']])->field('id,order_sn,weight,free,other_free,pack_free,member_id,created_time,wxapp_id,pack_ids')->find();
        //     if ($data['pay_method']==1){
            
        //         $update['status'] = 5;
        //         $update['pay_time'] = getTime();
        //         $update['is_pay'] = 1;
        //         // 立即扣款 
        //         $allPrice = $data['free'] + $data['pack_free'] + $data['other_free'];
        //         $user = (new User())->find($pack['member_id']);
        //         Db::startTrans();
        //         if ($user['balance']<$allPrice){
        //             return $this->renderError('余额不足,请充值');
        //         }
        //         $memberUp = (new User())->where(['user_id'=>$user['user_id']])->update([
        //           'balance'=>$user['balance']-$allPrice,
        //           'pay_money' => $user['pay_money']+ $allPrice,
        //         ]);
        //          if (!$memberUp){
        //              Db::rollback();
        //              return $this->renderError('支付失败,请重试');
        //          }
        //           // 新增余额变动记录
        //          BalanceLog::add(SceneEnum::CONSUME, [
        //           'user_id' => $user['user_id'],
        //           'money' => $allPrice,
        //           'remark' => '包裹单号'.$pack['order_sn'].'的运费支付',
        //           'sence_type' => 2,
        //           'wxapp_id' => (new PackageModel())->getWxappId(),
        //       ], [$user['nickName']]);
        //     }else{
        //         $update['status'] = 5;
        //     }
        // }
        
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
            $update['sendout_time'] = getTime();
             // 更新查验物流信息
            $pack = $this->where(['id'=>$data['id']])->find();
            $useraddress = UserAddress::detail($pack['address_id']);
            // dump(substr($useraddress['phone'],-4));die;
            $userData = (new UserModel)->where('user_id',$pack['member_id'])->find();
            $packids = explode(',',$pack['pack_ids']);

            $pack['t_order_sn'] = $data['t_order_sn'];
            
            //   dump($noticesetting);die;
            //判断是否需要添加物流信息
            if($noticesetting['zhuandan']['is_enable']==1){
                if(!empty($pack['t_order_sn'])){
                   $noticesetting['zhuandan']['describe'] = '包裹转单操作，新单号为'.$pack['t_order_sn']; 
                }
                if(strpos($noticesetting['zhuandan']['describe'],'code')){
                     $des = str_ireplace('{code}', $pack['t_order_sn'], $noticesetting['zhuandan']['describe']);
                     Logistics::inpackstatus($pack['order_sn'],$des,$pack['t_order_sn'],6);
                }else{
                     Logistics::inpackstatus($pack['order_sn'],$noticesetting['zhuandan']['describe'],$pack['t_order_sn'],6);
                }   
            }
            ////注册发货单到17track,当是选择可以查询的物流时，自有物流不可查询
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
                // 使用缓存获取渠道配置
                $ditchdetail = \app\common\service\DitchCache::getConfig($data['t_number']);
                if (!$ditchdetail) {
                    return $this->renderError('渠道配置不存在');
                }
                $update['t_name'] = $ditchdetail['ditch_name'];
                $update['t_number'] = $ditchdetail['ditch_id'];
                $update['transfer'] = $data['transfer'];
     
                //查询是否是渠道商那边的单号
                $resultDitchNumber = $DitchNumber->where('ditch_id',$data['t_number'])->where('ditch_number',$data['t_order_sn'])->find();
                if(!empty($resultDitchNumber)){
                    if($resultDitchNumber['status']==0){
                         $resultDitchNumber->save(['status'=>1,'order_no'=>$pack['order_sn']]);
                    }else{
                        return $this->renderError('此渠道商单号已被使用');
                    }
                }
            
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
                $EmailData['logistics_describe']=$noticesetting['zhuandan']['describe'];
                (new Email())->sendemail($EmailUser,$EmailData,$type=1);
            }
        }

        if($data['type']=='change'){
       
            $upd['t2_number'] = $update['t_number'];
            $upd['t2_name'] = $update['t_name'];
            $upd['t2_order_sn'] = $update['t_order_sn'];
            $upd['updated_time'] = $update['updated_time'];
            $upd['status'] = $update['status'];
            $update = $upd;
         
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
    /***
     * 修改集运单的状态 
     * @param $data []
     */
    public function modify($data){
        $DitchNumber = new DitchNumber();
        $Line = new Line();
        $field = ['line_id','length','width','height','weight','verify','free','pack_free','cale_weight','volume','other_free','remark','t_number','t_name','t_order_sn', 'transfer'];
        $update = [];
        
        foreach ($field as $v){
            if (isset($data[$v]))
               $update[$v] = $data[$v];
        }
            $update['updated_time'] = getTime();
            $update['status'] = '6';
            $update['sendout_time'] = getTime();
             // 更新查验物流信息
            $pack = $this->where(['id'=>$data['id']])->find();
            //物流模板设置
            $noticesetting = SettingModel::getItem('notice',$pack['wxapp_id']);
            $tplmsgsetting = SettingModel::getItem('tplMsg',$pack['wxapp_id']);
            $useraddress = UserAddress::detail($pack['address_id']);
            // dump(substr($useraddress['phone'],-4));die;
            $userData = (new UserModel)->where('user_id',$pack['member_id'])->find();
            // $packids = explode(',',$pack['pack_ids']);

            $pack['t_order_sn'] = $data['t_order_sn'];
            
            //   dump($data);die;
             //判断是否需要添加物流信息
            if($noticesetting['dosend']['is_enable']==1){
                // if(!empty($pack['t_order_sn'])){
                //   $noticesetting['dosend']['describe'] = $noticesetting['dosend']['describe'] .$pack['t_order_sn']; 
                // }
                if(strpos($noticesetting['dosend']['describe'],'code')){
                     $des = str_ireplace('{code}', $pack['t_order_sn'], $noticesetting['dosend']['describe']);
                     Logistics::addInpackLog($pack['order_sn'],$des,$pack['t_order_sn']);
                }else{
                     Logistics::inpackstatus($pack['order_sn'],$noticesetting['dosend']['describe'],$pack['t_order_sn'],6);
                }   
            }
            ////注册发货单到17track,当是选择可以查询的物流时，自有物流不可查询
            if($data['transfer']==1 && $noticesetting['is_track_fahuo']['is_enable']==1){
                $express = (new Express())->where('express_code',$data['tt_number'])->find();
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
                // 使用缓存获取渠道配置
                $ditchdetail = \app\common\service\DitchCache::getConfig($data['t_number']);
                if (!$ditchdetail) {
                    return $this->renderError('渠道配置不存在');
                }
                $update['t_name'] = $ditchdetail['ditch_name'];
                $update['t_number'] = $ditchdetail['ditch_id'];
                $update['transfer'] = $data['transfer'];
                //查询是否是渠道商那边的单号
                $resultDitchNumber = $DitchNumber->where('ditch_id',$data['t_number'])->where('ditch_number',$data['t_order_sn'])->find();
                if(!empty($resultDitchNumber)){
                    if($resultDitchNumber['status']==0){
                         $resultDitchNumber->save(['status'=>1,'order_no'=>$pack['order_sn']]);
                    }else{
                        return $this->renderError('此渠道商单号已被使用');
                    }
                }
            }
            //把包裹状态更改为已发货
           (new Package())->where('inpack_id',$data['id'])->update(['status'=>9]);

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
                $resmsg = str_ireplace('{code}',$pack['t_order_sn'],$noticesetting['dosend']['describe']);
                $EmailData['logistics_describe'] = $resmsg;
                (new Email())->sendemail($EmailUser,$EmailData,$type=1);
            }
        unset($update['verify']);
        $linedetail = $Line->details($pack['line_id']);
        $update['sendout_time'] = getTime();
        $update['exceed_date'] = $linedetail['exceed_date']==0?0:(time()+$linedetail['exceed_date']*86400);
        $resss = $this->where(['id'=>$data['id']])->update($update);
        Db::commit();
        return  $resss;
    }
    
    // 添加包裹
    // public function appendData($data){
    //     //$pack : 要插入的快递信息
    //     //$inpack : 被插入的集运单
    //     $inpack = (new Inpack())->find($data['id']);    
    //     if (!$pack = (new Package())->where(['express_num'=>$data['express_num'],'is_delete'=>0])->find()){
    //         $resl =  (new Package())->where(['inpack_id'=>$data['id'],'is_delete'=>0])->find();
    //         // dump($data);die;
    //         if(empty($resl)){
    //             $this->error = '该包裹不在库中';
    //             return false;
    //         }
    //         unset($resl['id']);
    //         $resl['express_num'] = $data['express_num'];
    //         $resl['entering_warehouse_time'] = getTime();
    //         $resl['updated_time'] = getTime();
    //         $resl['created_time'] = getTime();
    //         $newid = (new Package())->insertGetId($resl->toArray());
    //         $inpack->save(['pack_ids'=>$inpack['pack_ids'].','.$newid]);
    //     }
    //     // 判断包裹是否已经在其他集运包裹中；如果不在，则可以添加进来；
    //     if(in_array($pack['status'],[5,6,7,8,9,10,11])){
    //         $this->error = '该包裹已在集运单中';
    //         return false;
    //     }
        
    //     //判断需要插入的快递单号是否被领取，如果领取memberid不存在，则设置为集运单的memberid；并修改状态为已认领；
    //     if(!$pack['member_id']){
    //       (new Package())->where(['id'=>$pack['id']])->update(['member_id'=>$inpack['member_id'],'is_take' => 2]);
    //     }
    //     $packages = explode(',',$inpack['pack_ids']);
    //     $packages[] = $pack['id'];
    //     $inpackData['pack_ids'] = implode(',',$packages);
    //     $res = $this->where(['id'=>$data['id']])->update($inpackData);
    //     (new Package())->where(['id'=>$pack['id']])->update(['status'=>5,'inpack_id'=>$data['id']]);
    //     if ($res){
    //         return true;
    //     }
    //     $this->error = '包裹添加失败';
    //     return false;
    // }
      
    
    // 添加包裹
    public function appendData($data){
        // 解析多个快递单号（支持逗号和换行分隔）
        $express_nums = $this->parseExpressNums($data['express_num']);
        
        if (empty($express_nums)) {
            $this->error = '请输入有效的快递单号';
            return false;
        }
        
        $success_count = 0;
        $error_msgs = [];
        
        // 循环处理每个快递单号
        foreach ($express_nums as $express_num) {
            $result = $this->appendSingleExpressNum($data['id'], $express_num);
            if ($result) {
                $success_count++;
            } else {
                $error_msgs[] = "快递单号 {$express_num}: " . $this->getError();
            }
        }
        
        if ($success_count > 0) {
            if (count($error_msgs) > 0) {
                $this->error = "成功添加 {$success_count} 个包裹。失败的包裹：" . implode('; ', $error_msgs);
            }
            return true;
        } else {
            $this->error = '所有包裹添加失败：' . implode('; ', $error_msgs);
            return false;
        }
    }
    
    /**
     * 解析快递单号字符串（支持逗号和换行分隔）
     */
    private function parseExpressNums($express_num_str) {
        if (empty($express_num_str)) {
            return [];
        }
        
        // 先按换行分隔，再按逗号分隔
        $lines = preg_split('/[\r\n]+/', trim($express_num_str));
        $express_nums = [];
        
        foreach ($lines as $line) {
            $nums = explode(',', $line);
            foreach ($nums as $num) {
                $num = trim($num);
                if (!empty($num)) {
                    $express_nums[] = $num;
                }
            }
        }
        
        // 去重
        return array_unique($express_nums);
    }
    
    /**
     * 处理单个快递单号
     */
    private function appendSingleExpressNum($inpack_id, $express_num) {
        //$pack : 要插入的快递信息
        //$inpack : 被插入的集运单
        $inpack = (new Inpack())->find($inpack_id);    
        if (!$pack = (new Package())->where(['express_num'=>$express_num,'is_delete'=>0])->find()){
            $resl =  (new Package())->where(['inpack_id'=>$inpack_id,'is_delete'=>0])->find();
            if(empty($resl)){
                $this->error = '该包裹不在库中';
                return false;
            }
            unset($resl['id']);
            $resl['express_num'] = $express_num;
            $resl['entering_warehouse_time'] = getTime();
            $resl['updated_time'] = getTime();
            $resl['created_time'] = getTime();
            $newid = (new Package())->insertGetId($resl->toArray());
            $inpack->save(['pack_ids'=>$inpack['pack_ids'].','.$newid]);
            return true;
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
        $res = $this->where(['id'=>$inpack_id])->update($inpackData);
        (new Package())->where(['id'=>$pack['id']])->update(['status'=>5,'inpack_id'=>$inpack_id]);
        if ($res){
            return true;
        }
        $this->error = '包裹添加失败';
        return false;
    }
    
    //获取集运单的相关信息->with(['line','storage','inpackimage.file'])
    public static function details($id){
        return self::get($id, ['inpackimage.file','wvimages.file','line','address','certimage','packagelist.categoryAttr','packageitems','inpackdetail']);
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
        !empty($query['is_pay']) && $this->where('pa.is_pay','=',$query['is_pay']);
        isset($query['is_doublecheck']) && $query['is_doublecheck']!='' &&  $query['is_doublecheck']>=-1 && $this->where('pa.is_doublecheck','=',$query['is_doublecheck']);
        // 订单类型筛选：1=拼团 2=直邮 3=拼邮
        if(isset($query['inpack_type']) && $query['inpack_type']!='') {
            if($query['inpack_type'] == '3') {
                $this->where('pa.inpack_type', 'in', [0, 3]); // 拼邮包含inpack_type=0和3
            } else {
                $this->where('pa.inpack_type', '=', $query['inpack_type']);
            }
        }
        if(!empty($query['time_type'])){
            !empty($query['start_time']) && $this->where($query['time_type'], '>=', $query['start_time']);
            !empty($query['end_time']) && $this->where($query['time_type'], '<=', $query['end_time']." 23:59:59");
        }
        
        if(!empty($query['search_type'])){
           
            switch ($query['search_type']) {
                case 'all':
                   !empty($query['search']) && $this->where('pa.member_id|u.nickName|u.user_code|pa.usermark|u.mobile','like','%'.$query['search'].'%');
                    break;
                
                case 'user_code':
                   !empty($query['search']) && $this->where('u.user_code','=',$query['search']);
                    break;
                
                case 'member_id':
                   !empty($query['search']) && $this->where('pa.member_id','=',$query['search']);
                    break;
                
                case 'user_mark':
                   !empty($query['search']) && $this->where('pa.usermark','=',$query['search']);
                    break;
                    
                case 'mobile':
                   !empty($query['search']) && $this->where('u.mobile','=',$query['search']);
                    break;
                    
                case 'nickName':
                   !empty($query['search']) && $this->where('u.nickName','=',$query['search']);
                    break;
                    
                default:
                   !empty($query['search']) && $this->where('pa.member_id|u.nickName|u.user_code|pa.usermark|u.mobile','like','%'.$query['search'].'%');
                    break;
            }
        }
        
        if(!empty($query['tr_number'])){
            $express_num = str_replace("\r\n","\n",trim($query['tr_number']));
            $express_num = explode("\n",$express_num);
            $express_num = implode(',',$express_num);
            $where['t_order_sn'] = array('in', $express_num);
            $this->where($where);
        }
        return $this;
    }
    
        
    //查询物流轨迹
    public function getlog($query=[]){
        $Inpack = new Inpack();
        $Logistics = new Logistics();
        $DitchModel = new DitchModel();
        $setting = SettingModel::detail("notice")['values'];
        $detail = $Inpack::details($query['id']);
        $logic = $Logistics->getorderno($detail['order_sn']); 
        // dump($logic);die;
        $logib= [];
        //自有物流
        if($detail['transfer']==0){
            $ditchdatas = $DitchModel->where('ditch_id','=',$detail['t_number'])->find();
            $result = Logistics::searchLog($ditchdatas,$detail['t_order_sn']);
            is_array($result)  && $logib = $result;
        }
        //17track物流
        if($detail['transfer']==1){
            if($setting['is_track_fahuo']['is_enable']==1){//如果预报推送物流，则查询出来
                $logib = $Logistics->getZdList($detail['t_order_sn'],$detail['t_number'],$detail['wxapp_id']);
            }
        }
        $logic = array_merge($logic,$logib);
        return $logic;
    }
    
    
    // 获取面单相关数据
    public function getExpressData($id){
        $data = $this->with(['address','storage','packagelist.shelfunititem.shelfunit','packageitems','user','inpackservice.service'])->find($id);
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
    
    public function sharingorder(){
        return $this->belongsTo('app\store\model\sharing\SharingOrder','share_id','order_id');
    }
    
    public function country(){
        return $this->belongsTo('app\store\model\Countries','country_id','id');
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
    /**
     * API 推送成功后的自动完成动作
     * 
     * 将订单标记为已发货（状态9），并同步包裹状态
     * 
     * @param int $id 集运单ID
     * @param array $data 包含 t_order_sn, t_name, t_number, transfer
     * @return bool
     */
    public function pushSuccessComplete($id, $data) {
        $detail = $this->where(['id' => $id])->find();
        if (!$detail) return false;
        
        $update = [
            't_order_sn' => $data['t_order_sn'],
            't_name'     => isset($data['t_name']) ? $data['t_name'] : '',
            't_number'   => isset($data['t_number']) ? $data['t_number'] : '',
            'transfer'   => isset($data['transfer']) ? $data['transfer'] : 0,
            'status'     => 6,
            'sendout_time' => getTime(),
            'updated_time' => getTime()
        ];
        
        // 计算预估到达/超期日期
        $Line = (new Line());
        $box = (new Package());
        $linedetail = $Line->details($detail['line_id']);
        $update['exceed_date'] = $linedetail['exceed_date']==0?0:(time()+$linedetail['exceed_date']*86400);

        // 查找相关信息
        $userData = (new UserModel)->where('user_id', $detail['member_id'])->find();
        $noticesetting = SettingModel::getItem('notice', $detail['wxapp_id']);
        $tplmsgsetting = SettingModel::getItem('tplMsg', $detail['wxapp_id']);

        Db::startTrans();
        try {
            // 更新主单
            $this->where(['id' => $id])->update($update);
            
            // 更新名下所有包裹状态为 9
            $box->where('inpack_id', $id)->update(['status' => 9]);
            
            // 记录日志
            if (isset($noticesetting['dosend']) && $noticesetting['dosend']['is_enable'] == 1) {
                $des = str_ireplace('{code}', $data['t_order_sn'], $noticesetting['dosend']['describe']);
                Logistics::addInpackLog($detail['order_sn'], $des, $data['t_order_sn']);
            }

            // 发送消息通知
            $pack = $detail->toArray();
            $pack['t_order_sn'] = $data['t_order_sn'];
            $pack['userName'] = $userData ? $userData['nickName'] : '';
            $pack['remark'] = '包裹已通过API自动发货';
            $pack['total_free'] = $pack['free'] + $pack['pack_free'] + $pack['other_free'];
            
            if ($tplmsgsetting['is_oldtps'] == 1) {
                $this->sendEnterMessage([$pack], 'payment');
            } else {
                Message::send('package.sendpack', $pack);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }
}
