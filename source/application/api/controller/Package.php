<?php
namespace app\api\controller;
use app\api\model\Express;
use app\api\model\store\Shop;
use app\api\model\Country;
use app\api\model\Category;
use app\api\model\Inpack;
use app\api\model\Line;
use app\api\model\Logistics;
use app\api\model\Package as PackageModel;
use app\api\model\InpackService as InpackServiceModel;
use app\api\model\PackageItem as PackageItemModel;
use app\api\model\User;
use app\api\model\user\BalanceLog;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use app\api\model\UserAddress;
use app\api\model\Setting as SettingModel;
use app\api\model\dealer\Setting as SettingDealerModel;
use app\api\model\dealer\Referee as RefereeModel;
use app\api\service\sharing\SharingOrder as ShareOrderService;
use app\api\model\sharing\SharingOrderItem;
use app\common\model\dealer\Capital;
use app\common\model\store\shop\Capital as CapitalModel;
use app\common\model\dealer\Order as DealerOrder;
use app\common\library\Pinyin;
use app\common\model\dealer\User as DealerUser;
use app\api\model\Coupon as CouponModel;
use app\api\model\UserCoupon;
use app\common\model\PackageImage;
use think\Db;
use app\common\service\Message;
use app\api\model\store\shop\Clerk;
use app\api\service\Payment as PaymentService;
use app\common\enum\OrderType as OrderTypeEnum;
use app\api\service\trackApi\TrackApi;
use app\common\library\Ditch\config;
use app\common\model\Ditch as DitchModel;
use app\store\model\store\Shop as ShopModel;
use app\common\library\Ditch\BaiShunDa\bsdexp;
use app\common\library\Ditch\Jlfba\jlfba;
use app\common\library\Ditch\kingtrans;
use app\common\library\Ditch\Hualei;
use app\common\library\Ditch\Xzhcms5;
use app\common\library\Ditch\Aolian;
use app\common\library\Ditch\Yidida;
use app\api\model\ShelfUnitItem;
use app\api\model\Barcode;
use app\api\model\InpackImage;
use app\api\model\InpackItem;
use app\api\model\user\PointsLog as PointsLogModel;
use app\api\model\PackageClaim;
use app\api\model\LineCategory;

/**
 * 页面控制器
 * Class Index
 * @package app\api\controller
 */
class Package extends Controller
{
     
     /* @var \app\api\model\User $user */
     private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        if (\request()->param('token')){
            // 用户信息
            $this->user = $this->getUser();
        }else{
            $this->user['user_id'] = 0;
        }
    }
    
    //获取累计的优惠金额
    public function getcouponTotal(){
        $Inpack = new Inpack();
        $count = $Inpack->where('member_id',$this->user['user_id'])->where('is_delete',0)->sum('user_coupon_money');
        return $this->renderSuccess(compact('count'));
    }
    
     //退货申请
    public function returnd(){
        if (!$this->user['user_id']){
            return $this->renderError('请先登录');
        }
        $post = $this->postData();
        if(!$post['express_num']){
            return $this->renderError('请选择需要退货单快递单');
        }
        if(!$post['address_id']){
            return $this->renderError('请选择退货地址');
        }
        $packModel = new PackageModel();
        $packres = $packModel->where('express_num',$post['express_num'])->where('is_delete',0)->find();
        if(empty($packres)){
            return $this->renderError('快递单不存在');
        }
        $packres->save([
            'is_take'=>4,  
            'address_id'=>$post['address_id'],  
            'remark'=>$post['remark'],
            'updated_time'=>getTime(),
        ]);
        $wxapp_id = \request()->get('wxapp_id');
        if(!empty($post['imageIds'])){
             $this->inImages($packres['id'],$post['imageIds'],$wxapp_id);
         }
        Logistics::add($packres['id'],'申报退货');
        return $this->renderSuccess('申请退货成功');
    }
    
    
     // 包裹批量预报
    public function newreport(){
        if (!$this->user['user_id']){
            return $this->renderError('请先登录');
        }
        $post = $this->postData();
        $packModel = new PackageModel();
        $array = preg_split('/\s+/', $post['packlist']);
        if(count($array)==0){
            return $this->renderError('请输入快递单号');
        }
        foreach ($array as $v){
            $packres = $packModel->where('express_num',$v)->where('is_delete',0)->find();
            if(empty($packres)){
                // 包裹不存在则添加新包裹，将单号，用户id，是否认领，创建时间，更新时间，入库状态
                $pack_id = $packModel->insertGetId([
                    'express_num'=>$v,
                    'order_sn' => CreateSn(),
                    'member_id'=>$this->user['user_id'],
                    'is_take'=>2,
                    'status'=>1,
                    'updated_time'=>getTime(),
                    'created_time'=>getTime(),
                    'wxapp_id'=>$this->wxapp_id
                ]);
               
            }else{
                //包裹存在，则更新绑定用户id，更新时间，绑定状态
                $packres->save([
                    'member_id'=>$this->user['user_id'],
                    'is_take'=>2,
                    'updated_time'=>getTime()
                ]);
                $pack_id = $packres['id'];
            }
        }
   
        Logistics::add($pack_id,'包裹预报成功');
        return $this->renderSuccess('申请预报成功');
    }
        
    //批量打包
    public function quickPackageItAll(){
        $param = $this->request->param();
        $PackageModel = new PackageModel;
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        $storesetting = SettingModel::getItem('store');

        $inpackOrder = [
          'order_sn' => createSn(),
          'remark' =>$param['remark'],
          'pack_services_id' => $param['pack_ids'],
          'storage_id' => $clerk['shop_id'],
          'address_id'=>0,
          'free' => 0,
          'weight' =>$param['weight'],
          'length' =>$param['length'],
          'width' =>$param['width'],
          'height' =>$param['height'],
          'cale_weight' =>0,
          'volume' => 0, //体积重
          'pack_free' => 0,
          'other_free' =>0,
          'insure_free'=>isset($param['insurefree'])?$param['insurefree']:0,
          'created_time' => getTime(),
          'updated_time' => getTime(),
          'status' => 1,
          'source' => 6,
          'wxapp_id' => $param['wxapp_id'],
          'line_id' => $param['line_id'],
        ];

        if(isset($param['address_id']) && !empty($param['address_id'])){
            $address = (new UserAddress())->find($param['address_id']); //获取地址信息
            $inpackOrder['member_id'] = $address['user_id'];
            $inpackOrder['address_id'] = $address['address_id'];
        }
        if(isset($param['user_id']) && !empty($param['user_id'])){
            $inpackOrder['member_id'] = $param['user_id'];
        }
        // 开启事务
        Db::startTrans();
        $inpack = (new Inpack())->insertGetId($inpackOrder);
        if(!empty($param['imageIds'])){
            foreach ($param['imageIds'] as $key => $value){
              $dataimg['image_id'] = $value;
              $dataimg['inpack_id'] = $inpack;
              $dataimg['wxapp_id'] = $param['wxapp_id'];
              $resimg  = (new InpackImage())->save($dataimg);
            }
        }
        //删除集运单图片
        if(!empty($param['deleteIds'])){
            foreach ($param['deleteIds'] as $key => $value){
              $resdeleteimg  = (new InpackImage())->where('id',$value)->delete();
            }
        }
         //处理包装服务
        (new InpackServiceModel())->doservice($inpack,$param['pack_ids']);
        //查询仓库是否存在此包裹，如果存在，则更新入库时间和入库的状态；
        //如果不存在，则需要入库操作；
        $express_nums = explode(',',$param['packids']);
        foreach ($express_nums as $key => $val){
            $number = $PackageModel->where(['express_num'=>$val,'is_delete'=>0])->find();
            if(!empty($number) && $number['status']>4){
                return $this->renderError($val.'已被打包');
            }
            if(!empty($number)){
                $number->where(['express_num'=>$val,'is_delete'=>0])->update([
                    'entering_warehouse_time'=>getTime(),
                    'status'=>8,
                    'inpack_id'=>$inpack,
                    'storage_id'=>$clerk['shop_id']
                ]);
            }else{
                $result = $PackageModel->insert([
                    'entering_warehouse_time'=>getTime(),
                    'status'=>8,
                    'express_num'=>$val,
                    'member_id'=>isset($inpackOrder['member_id'])?$inpackOrder['member_id']:0,
                    'storage_id'=>$clerk['shop_id'],
                    'updated_time'=>getTime(),
                    'created_time'=>getTime(),
                    'wxapp_id'=>$param['wxapp_id'],
                    'inpack_id'=>$inpack,
                    'order_sn'=> CreateSn()
                ]);
                 if (!$result){
                    Db::rollback();
                    return $this->renderError('提交打包失败');
                 }
            }
        }
        Db::commit();
        return $this->renderSuccess('提交打包成功');
        //包裹处理完成后，需要把包裹的id加入到集运订单中，创建新的快速打包订单；
        
        //快速打包订单添加好后，需要对订单进行用户归属，路线选择，用户地址选择等操作才能划分到正常订单行列；
    }
    
       // 根据批次获取订单列表
     public function withbatchidpackagelist(){
         $param = $this->request->param();
         $query = $param;
         if(isset($query['rfid_id'])){
             unset($query['rfid_id']);
         }
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
         $list = (new Inpack())->getAllList($query);
         foreach ($list as &$value) {
             $value['is_scan'] = false;
            if(isset($param['rfid_id']) && (in_array($value['rfid_id'],$param['rfid_id']))){
                $value['is_scan'] = true;
            }
         }
         return $this->renderSuccess($list);
     }
     
     //盘库完成
     public function inventorywarehouse(){
        $param = $this->request->param();
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $desc = [
            10=>"监管仓入库",
            20=>"监管仓出库",
            30=>"海外仓入库",
            40=>"海外仓出库",
            50=>"配送员入库",
            60=>"配送员出库",
        ];
        // dump($clerk);die;
        foreach ($param['id'] as &$value) {
            Logistics::addrfidLog($value['order_sn'],$desc[$param['type']],getTime(),$clerk['clerk_id']);
        }
        return $this->renderSuccess('盘库成功');
     }
    
    // 包裹批量出库
    //将提交的包裹进行1、货架下架；2、标记扫码状态；3、更改状态为已发货；
    public function alloutshop(){
        $param = $this->request->param();
        $PackageModel = new PackageModel;
        $ShelfUnitItem = new ShelfUnitItem;
        $noticesetting = SettingModel::getItem('notice');
        // dump());die;
        if(count($param['express_num'])==0){
             return $this->renderError("请录入需要出库的包裹单号");
        }
        // 开启事务
        Db::startTrans();
        foreach($param['express_num'] as $val){
            $pack = $PackageModel->where('is_delete',0)->where('express_num',$val)->whereOr(['origin_express_num'=>$val])->select();
            if(empty($pack)){
                $ShelfUnitItem->where('express_num',$val)->delete();
                return $this->renderError($val."暂未入库，请先入库后再执行出库操作");
            }
            
            if(!empty($pack) && count($pack)>=1){
                foreach ($pack as $v){
                    $v->save(['status'=>9,'is_scan'=>2]);
                    if($noticesetting['dosend']['is_enable']==1){
                        Logistics::add($v['id'],"包裹已发货");
                    }
                }
            }
        }
        Db::commit();
        return $this->renderSuccess('批量出库成功');
    }
    

     // 包裹预报
     public function report(){
         if (!$this->user['user_id']){
            return $this->renderError('请先登录');
         }
         $user = (new User())->find($this->user['user_id']);
         $userclient = SettingModel::detail("userclient")['values'];
         $Barcode = new Barcode;
         
         $post = $this->postData();
        //  dump($post);die;
         if($userclient['yubao']['is_country']==1){
            if ($post['country_id']){
              $country = (new Country())->getValueById($post['country_id'],'title');
              if (!$country){
                     return $this->renderError('国家信息错误');
                 }
              } 
         }
         
        
         if (!$post['storage_id']){
              return $this->renderError('请选择仓库');
         }
         $storage = (new Shop())->getValueById($post['storage_id'],'shop_name');
         if (!$storage){
             return $this->renderError('仓库信息错误');
         }
         if (!$post['express_sn']){
             return $this->renderError('快递单号错误');
         }
         if (preg_match('/[\x7f-\xff]/', $post['express_sn'])){
             return $this->renderError('快递单号不能使用汉字或字符');
         }
         if (!preg_match('/^[^,]+$/', $post['express_sn'])){
             return $this->renderError('单个预报不能添加逗号');
         }
        // dump(preg_match('/^[^,]+$/', $post['express_sn']));die;
         if(!preg_match('/^[^\s]*$/',$post['express_sn'])){
             return $this->renderError('快递单号不能有空格');
         }
         //为京东特别写的代码
         if(!stristr($post['express_sn'],'JD')){
          if(!preg_match('^\w{3,20}$^',$post['express_sn'])){
             return $this->renderError('快递单号不能使用特殊字符');
           } 
         }
         if(isset($post['express_id'])){
             $express = (new Express())->getValueById($post['express_id'],'express_name');
             $express_code = (new Express())->getValueById($post['express_id'],'express_code');
         }
         $goodslist = isset($post['goodslist'])?$post['goodslist']:[];
         if (isset($post['share_id']) && $post['share_id']){
             $ShareSettingService = (new ShareOrderService());
             if(!$ShareSettingService -> checkPubiler($user['user_id'],$post)){
                  return $this->renderError('您无权限参与该拼团活动，或受到限制');
             }
         } 
         $classItems = [];
         $barcodelist = [];
         $class_ids = $post['class_ids'];
       
         if ($class_ids || $goodslist){
             $classItem = $this->parseClass($class_ids);
            //   DUMP($classItem);DIE;
                foreach ($goodslist as $k => $val){
                     $classItems[$k]['class_name'] = !empty($classItem)?$classItem[0]['name']:$val['pinming'];
                     $classItems[$k]['class_id'] = !empty($classItem)?$classItem[0]['category_id']:0;
                     $classItems[$k]['one_price'] = isset($val['danjia'])?$val['danjia']:'';
                     $classItems[$k]['all_price'] = (!empty($val['danjia'])?$val['danjia']:0) * (!empty($val['shuliang'])?$val['shuliang']:0);
                     $classItems[$k]['product_num'] = isset($val['shuliang'])?$val['shuliang']:'';
                     $classItems[$k]['express_num'] = $post['express_sn'];
                     $classItems[$k]['goods_name'] = isset($val['pinming'])?$val['pinming']:'';
                     $classItems[$k]['express_name'] = isset($express)?$express:'';
                     $classItems[$k]['class_name_en'] = isset($val['goods_name_en'])?$val['goods_name_en']:''; // 英文品名
                     $classItems[$k]['goods_name_jp'] = isset($val['goods_name_jp'])?$val['goods_name_jp']:'';
                     $classItems[$k]['length'] = isset($val['depth'])?$val['depth']:'';
                     $classItems[$k]['width'] = isset($val['width'])?$val['width']:'';
                     $classItems[$k]['height'] = isset($val['height'])?$val['height']:'';
                     $classItems[$k]['unit_weight'] = isset($val['gross_weight'])?$val['gross_weight']:'';
                     $classItems[$k]['brand'] = isset($val['brand'])?$val['brand']:'';
                     $classItems[$k]['spec'] = isset($val['spec'])?$val['spec']:'';
                     $classItems[$k]['net_weight'] = isset($val['net_weight'])?$val['net_weight']:'';
                     $classItems[$k]['barcode'] = isset($val['barcode'])?$val['barcode']:'';
                     
                     
                     if(isset($val['barcode']) && !empty($val['barcode'])){
                         $barcoderesu =  $Barcode::useGlobalScope(false)->where('barcode',$val['barcode'])->find();
                         
                         $barcodelist['barcode'] = isset($val['barcode'])?$val['barcode']:$barcoderesu['barcode'];
                         $barcodelist['brand'] = isset($val['brand'])?$val['brand']:$barcoderesu['brand'];
                         $barcodelist['goods_name_en'] = isset($val['goods_name_en'])?$val['goods_name_en']:$barcoderesu['goods_name_en'];
                         $barcodelist['goods_name_jp'] = isset($val['goods_name_jp'])?$val['goods_name_jp']:$barcoderesu['goods_name_jp'];
                         $barcodelist['goods_name'] = isset($val['pinming'])?$val['pinming']:$barcoderesu['goods_name'];
                         $barcodelist['spec'] = isset($val['spec'])?$val['spec']:$barcoderesu['spec'];
                         $barcodelist['price'] = isset($val['danjia'])?$val['danjia']:$barcoderesu['price'];
                         $barcodelist['gross_weight'] = isset($val['gross_weight'])?$val['gross_weight']:$barcoderesu['gross_weight'];
                         $barcodelist['net_weight'] = isset($val['net_weight'])?$val['net_weight']:$barcoderesu['net_weight'];
                         $barcodelist['depth'] = isset($val['depth'])?$val['depth']:$barcoderesu['depth'];
                         $barcodelist['width'] = isset($val['width'])?$val['width']:$barcoderesu['width'];
                         $barcodelist['height'] = isset($val['height'])?$val['height']:$barcoderesu['height'];
                         
                         
                         if(empty($barcoderesu)){
                             $barresult = $Barcode::useGlobalScope(false)->insert($barcodelist);
                         }else{
                             $barcoderesu->save($barcodelist);
                         }
                     }
                     
                }
                
         }
       
         $packModel = new PackageModel();
         $packItemModel = new PackageItemModel();
         // todo 判断预报的单号是否存在（待认领或者已认领），如果存在且被认领则提示已认领，如果存在但未被认领则修改存在的记录所属用户，认领状态；
         $packres = $packModel->where('express_num',$post['express_sn'])->where('is_delete',0)->find();
         $wxapp_id = \request()->get('wxapp_id');
         if($packres && ($packres['is_take']==2)){
             return $this->renderError('快递单号已被预报');
         }
   
         // 开启事务
         Db::startTrans();
       
         $post['express_name'] = isset($express)?$express:'';
         $post['express_num'] = $post['express_sn'];

         $post['member_id'] = $this->user['user_id'];
         $post['member_name'] = $user['nickName'];
 
         if($packres && ($packres['is_take']==1)){
            
           $resup = $packModel->where('id',$packres['id'])->update(
               ['price'=>$post['price'],
               'remark'=>$post['remark'],
               'country_id'=>isset($post['country_id'])?$post['country_id']:$packres['country_id'],
               'express_name'=>isset($post['express_name'])?$post['express_name']:$packres['express_name'],
               'express_id'=>isset($post['express_id'])?$post['express_id']:$packres['express_id'],
               'member_id'=>$user['user_id'],
               'member_name'=>$user['nickName'],
               'storage_id'=>isset($post['storage_id'])?$post['storage_id']:$packres['storage_id'],
               'is_take'=>2,
               'class_ids'=>$class_ids
               ]);

            if (!$resup){
               return $this->renderError('申请预报失败');
            }
        
            if ($classItems){
                 $packItemRes = $packItemModel->saveAllData($classItems,$packres['id']);
                 if (!$packItemRes){
                    Db::rollback();
                    return $this->renderError('申请预报失败');
                 }
             }
             if(!empty($post['imageIds'])){
                 $this->inImages($packres['id'],$post['imageIds'],$wxapp_id);
             }
             
             Logistics::add($packres['id'],'包裹预报成功');
             Db::commit();
             return $this->renderSuccess('申请预报成功');
         }

         $post['order_sn'] = CreateSn();
         $post['is_take'] = 2;
         if (isset($post['share_id']) && $post['share_id']){
             $post['source'] = 6;
         }
         
         //注册到17track
         $noticesetting = SettingModel::getItem('notice');
         $storesetting = SettingModel::getItem('store');
        //  dump($noticesetting);die;
         if($noticesetting['is_track_yubao']['is_enable']==1 && isset($express_code)){
                $trackd = (new TrackApi())
                ->register([
                    'track_sn'=>$post['express_num'],
                    't_number'=>$express_code,
                    'wxapp_id' =>$this->wxapp_id
                ]);
            }
        
         $res = $packModel->saveData($post);
        //  dump($res);die;
         if (!$res){
             Db::rollback();
             return $this->renderError('申请预报失败');
         }
         
         if (isset($post['share_id']) && $post['is_share'] && $post['share_id']){
             $post['user_id'] = $user['user_id'];
             (new SharingOrderItem())->addItemPackage($post,$res);
         }
         if(!empty($post['imageIds'])){
            $this->inImages($res,$post['imageIds'],$wxapp_id);
         }
         if ($classItems){
             $packItemRes = $packItemModel->saveAllData($classItems,$res);
             if (!$packItemRes){
                Db::rollback();
                return $this->renderError('申请预报失败');
             }
         }
         Logistics::add($res,'包裹预报成功');
         Db::commit();
         return $this->renderSuccess('申请预报成功');
     }

     // 批量预报
     public function reportBatch(){
        if (!$this->user['user_id']){
            return $this->renderError('请先登录');
         }
         $user = (new User())->find($this->user['user_id']);
         $post = $this->postData();
         if (isset($post['country_id']) && !empty($post['country_id'])){
             $country = (new Country())->getValueById($post['country_id'],'title');
             if (!$country){
                 return $this->renderError('国家信息错误');
             }
         }
         
         if (!$post['storage_id']){
              return $this->renderError('请选择仓库');
         }
         $storage = (new Shop())->getValueById($post['storage_id'],'shop_name');
         if (!$storage){
             return $this->renderError('仓库信息错误');
         }
         if (!$post['express_sn']){
             return $this->renderError('快递单号错误');
         }
        if (!preg_match('/^[a-zA-Z0-9\s_\-，,]+$/', $post['express_sn'])) {
            return $this->renderError('快递单号只能包含大小写字母、数字、空格、下划线、横线或逗号');
        }
         $express = "国内快递";
         if(isset($post['express_id']) && $post['express_id'] !=0){
             $express = (new Express())->getValueById($post['express_id'],'express_name');
             if (!$express){
                 return $this->renderError('快递信息错误');
             }
         }
         
         $class_ids = $post['class_ids'];
         
         $packModel = new PackageModel();
         $packItemModel = new PackageItemModel();
         
         //将多个快递单号分拆为一个数组中英文逗号都可以进行转换
         $post['express_sn'] =trim($post['express_sn']);
         $post['express_sn'] =preg_replace("/\s|　/","",$post['express_sn']);
         $post['express_sn'] =str_replace('，',',',$post['express_sn']);
         $post['express_sn'] =str_replace('+',',',$post['express_sn']);
        
         $expno = explode(',',$post['express_sn']);
         $classItem = [];
              
         // 开启事务
         Db::startTrans();
         $post['express_name'] = $express;
         $post['member_id'] = $this->user['user_id'];
         $post['member_name'] = $user['nickName'];
        //  dump($expno);
         //循环对每个包裹进行预判是否已经入库
         foreach ($expno as $key => $v){
        //   dump($v);
            if(empty($v)  || $v==" " || $v==""){
               return $this->renderError('请不要加多余的逗号'); 
            }
            
            $post['express_num'] =  $v;
            $packres = $packModel->where('express_num',$v)->where('is_delete',0)->find();
            //当快递单号为一个数字加空格形式时候，则会提示报错
            if($packres && ($packres['is_take']==2)){
                return $this->renderError('快递单号'.$v.'已被预报');
            }
              
            if($packres && ($packres['is_take']==1)){
                $resup = $packModel->where('id',$packres['id'])->update(
                   ['price'=>$post['price'],
                   'remark'=>$post['remark'],
                   'country_id'=>isset($post['country_id'])?$post['country_id']:0,
                   'express_name'=>isset($post['express_name'])?$post['express_name']:'',
                   'express_id'=>isset($post['express_id'])?$post['express_id']:0,
                   'member_id'=>$user['user_id'],
                   'member_name'=>$user['nickName'],
                   'storage_id'=>isset($post['storage_id'])?$post['storage_id']:0,
                   'is_take'=>2
                   ]);
    
                if (!$resup){return $this->renderError('申请预报失败');}
                //存包裹信息
        
                if ($class_ids){
                    $classItem = $this->parseClass($class_ids);
                 
                     foreach ($classItem as $k => $val){
                           $classItem[$k]['class_id'] = $val['category_id'];
                           $classItem[$k]['express_name'] = $express;
                           $classItem[$k]['class_name'] = $val['name'];
                           $classItem[$k]['express_num'] = $v;
                           unset($classItem[$k]['category_id']); 
                           unset($classItem[$k]['name']);     
                     }
                     
                     if ($classItem){
                         $packItemRes = $packItemModel->saveAllData($classItem,$packres['id']);
                         if (!$packItemRes){
                            Db::rollback();
                            return $this->renderError('申请预报失败');
                         }
                     }
                 }
              
                 Logistics::add($packres['id'],'包裹预报成功');
                //  Db::commit();
             }
             
             if(!$packres){
                 $post['order_sn'] = CreateSn();
                 $post['is_take'] = 2;
                 $res = $packModel->saveData($post);
                 if (!$res){
                     return $this->renderError('申请预报失败');
                 }
  
                 if ($class_ids){
                        $classItem = $this->parseClass($class_ids);
                     
                         foreach ($classItem as $k => $val){
                        
                               $classItem[$k]['class_id'] = $val['category_id'];
                               $classItem[$k]['express_name'] = $express;
                               $classItem[$k]['class_name'] = $val['name'];
                               $classItem[$k]['express_num'] = $v;
                               unset($classItem[$k]['category_id']); 
                               unset($classItem[$k]['name']);     
                         }
                        
                         if ($classItem){
                             $packItemRes = $packItemModel->saveAllData($classItem,$res);
                             if (!$packItemRes){
                                Db::rollback();
                                return $this->renderError('申请预报失败');
                             }
                         }
                    }        
                    Logistics::add($res,'包裹预报成功');
             }
         }
         Db::commit();
         return $this->renderSuccess('申请预报成功');
     }
     
     /***
      * 用户预约包裹上门取件
      * 时间：2025年06月27日
      */
      public function doorpickup(){
         if (!$this->user['user_id']){
            return $this->renderError('请先登录');
         }
         $user = (new User())->find($this->user['user_id']);
         $post = $this->postData();
         $userclientsetting = SettingModel::getItem('userclient');   
         $storage =(new ShopModel())->getDefault();     
         if (!$storage){
             return $this->renderError('仓库信息错误');
         }
         if ($post['pack_type']==1 &&  !$post['address_id']){
              return $this->renderError('请选择收件地址');
         }
         if (!$post['jaddress_id']){
              return $this->renderError('请选择寄件地址');
         }
         
         //生成预约单号
         $express = createYysn();
         $classItem = [];
         if (isset($post['goodsInfo']) && isset($post['goodsInfo']['ids'])){
             $classItem = $this->parseClassName($post['goodsInfo']['ids']);
           
             foreach ($classItem as $k => $val){
                  $classItem[$k]['class_id'] = $val['category_id'];
                  $classItem[$k]['express_name'] = '预约取件';
                  $classItem[$k]['class_name'] = $val['name'];
                  $classItem[$k]['express_num'] = $express;
                  unset($classItem[$k]['category_id']); 
                  unset($classItem[$k]['name']);        
             }
         }
         
         $packModel = new PackageModel();
         $packItemModel = new PackageItemModel();
         // todo 判断预报的单号是否存在（待认领或者已认领），如果存在且被认领则提示已认领，如果存在但未被认领则修改存在的记录所属用户，认领状态；
         $packres = $packModel->where('express_num',$express)->where('is_delete',0)->find();

         if($post['packType']=='goods'){
              $remark = "保留商品包装";
          }else{
              $remark = "保留快递包装";
          }
         // 开启事务
         Db::startTrans();
       
         //如果是直邮包裹，需要同步创建集运订单
         if($post['pack_type']==1){
             $inpackOrder = [
              'order_sn' => createSn(),
              'storage_id' => $storage['shop_id'],
              'address_id'=>$post['address_id'],
              'free' => 0,
              'member_id'=>$this->user['user_id'],
              'weight' => $post['goodsInfo']['weight']??0,
              'length' => $post['goodsInfo']['length']??0,
              'width' => $post['goodsInfo']['width']??0,
              'height' => $post['goodsInfo']['height']??0,
              'cale_weight' => $post['goodsInfo']['weight']??0,
              'volume' => 0, //体积重
              'pack_free' => 0,
              'other_free' =>0,
              'insure_free'=>0,
              'created_time' => getTime(),
              'updated_time' => getTime(),
              'status' => 1,
              'source' => 4,
              'remark'=>$remark.'-'.$post['remark'],
              'line_id'=> $post['line_id'],
              'wxapp_id' => $this->wxapp_id,
            ];
           $inpack_id =  (new Inpack())->insertGetId($inpackOrder);
           $post['inpack_id'] = $inpack_id;
         }
       
       
         $post['express_name'] = '预约取件';
         $post['express_num'] = $express;
         $post['source'] = 7;
         $post['member_id'] = $this->user['user_id'];
         $post['member_name'] = $user['nickName'];
         $post['order_sn'] = CreateSn();
         $post['is_take'] = 2;
         $post['line_id'] = $post['line_id'];
         $post['visit_data_time'] = $post['pickup_date'].' '.$post['pickup_time'];
         $post['remark'] = $remark.'-'.$post['remark'];
         $res = $packModel->saveData($post);
        
         if (!$res){
             return $this->renderError('预约失败');
         }

         $clerk = (new Clerk())->where('visit_status',0)->where('is_delete',0)->select();
         
         $jaddress = (new UserAddress())->find($post['jaddress_id']); //获取地址信息
          //循环通知员工打包消息 
          foreach ($clerk as $key => $val){
              $data['member_id'] = $val['user_id'];
              $data['express_num'] = $express;
              $data['phone'] = $jaddress['phone'];
              $data['id'] = $res;
              $data['wxapp_id'] = $this->wxapp_id;
              $data['userName'] = $jaddress['name'];
              $data['visit_data_time'] = $post['pickup_date'].' '.$post['pickup_time'];
              $data['addressdetail'] = $jaddress['country'].$jaddress['province'].$jaddress['city'].$jaddress['detail'];
              Message::send('package.Reservationconfirmed',$data);   
          }
         //通知用户自己
         
         $userNotice['member_id'] = $this->user['user_id'];
         $userNotice['express_num'] = $express;
         $userNotice['userName'] = $jaddress['name'];
         $userNotice['addressdetail'] = $jaddress['detail'];
         $userNotice['wxapp_id'] = $this->wxapp_id;
         $userNotice['id'] = $res;
         $userNotice['time'] = date("Y-m-d H:i:s",time());
         Message::send('package.VisitOrdersuccess',$userNotice);   
         if ($classItem){
             $packItemRes = $packItemModel->saveAllData($classItem,$res);
             if (!$packItemRes){
                Db::rollback();
                return $this->renderError('预约失败');
             }
         }         
         Logistics::add($res,'预约成功');
         
         Db::commit();
         return $this->renderSuccess('预约成功');
     }
     
     // 格式化
     public function parseClassName($class_ids){
         $class_item = [];
         $class = (new Category())->whereIn('category_id',$class_ids)->field('category_id,name')->select()->toArray();
         return $class;
     }
     
     /***
      * 用户预约包裹上门取件
      * 时间：2022年06月29日
      */
      public function appreport(){
         if (!$this->user['user_id']){
            return $this->renderError('请先登录');
         }
         $user = (new User())->find($this->user['user_id']);
         $post = $this->postData();
         $userclientsetting = SettingModel::getItem('userclient');   
        //  dump($userclientsetting);die;
         if (!$post['country_id'] && $userclientsetting['visitdoor']['is_country']==1 && $userclientsetting['visitdoor']['is_country_force']==1){
              return $this->renderError('请选择国家');
         }
         if($post['country_id']){
            $country = (new Country())->getValueById($post['country_id'],'title');
             if (!$country){
                 return $this->renderError('国家信息错误');
             } 
         }
         
         if (!$post['storage_id'] && $userclientsetting['visitdoor']['is_shop']==1 && $userclientsetting['visitdoor']['is_shop_force']==1){
              return $this->renderError('请选择仓库');
         }
         $storage = (new Shop())->getValueById($post['storage_id'],'shop_name');
         if (!$storage){
             return $this->renderError('仓库信息错误');
         }
         
         
         if (!$post['address_id']){
              return $this->renderError('请选择收件地址');
         }
         if (!$post['jaddress_id']){
              return $this->renderError('请选择寄件地址');
         }
         
         //生成预约单号
         $express = createYysn();
         $class_ids = $post['class_ids'];
         $classItem = [];
         if ($class_ids){
             $classItem = $this->parseClass($class_ids);
             
             foreach ($classItem as $k => $val){
                   $classItem[$k]['class_id'] = $val['category_id'];
                   $classItem[$k]['express_name'] = '预约取件';
                   $classItem[$k]['class_name'] = $val['name'];
                   $classItem[$k]['express_num'] = $express;
                   unset($classItem[$k]['category_id']); 
                   unset($classItem[$k]['name']);        
             }
         }
         
         $packModel = new PackageModel();
         $packItemModel = new PackageItemModel();
         // todo 判断预报的单号是否存在（待认领或者已认领），如果存在且被认领则提示已认领，如果存在但未被认领则修改存在的记录所属用户，认领状态；
         $packres = $packModel->where('express_num',$express)->where('is_delete',0)->find();

   
         // 开启事务
         Db::startTrans();
       
         //如果是直邮包裹，需要同步创建集运订单
         if($post['pack_type']==1){
             $inpackOrder = [
              'order_sn' => createSn(),
              'storage_id' => $post['storage_id'],
              'address_id'=>$post['address_id'],
              'free' => 0,
              'member_id'=>$this->user['user_id'],
              'weight' =>0,
              'length' =>0,
              'width' =>0,
              'height' =>0,
              'cale_weight' =>0,
              'volume' => 0, //体积重
              'pack_free' => 0,
              'other_free' =>0,
              'insure_free'=>0,
              'created_time' => getTime(),
              'updated_time' => getTime(),
              'status' => 1,
              'source' => 4,
              'wxapp_id' => $this->wxapp_id,
              'line_id' => 0,
            ];
           $inpack_id =  (new Inpack())->insertGetId($inpackOrder);
           $post['inpack_id'] = $inpack_id;
         }
       
       
         $post['express_name'] = '预约取件';
         $post['express_num'] = $express;
         $post['source'] = 7;
         $post['member_id'] = $this->user['user_id'];
         $post['member_name'] = $user['nickName'];
         $post['order_sn'] = CreateSn();
         $post['is_take'] = 2;
         $post['visit_data_time'] = $post['pickup_date'].' '.$post['pickup_time']  ;
         $res = $packModel->saveData($post);
        
         if (!$res){
             return $this->renderError('预约失败');
         }
         //图片id
         $this->inImages($res,$post['imageIds'],$this->wxapp_id);
         $clerk = (new Clerk())->where('visit_status',0)->where('is_delete',0)->select();
         
         $jaddress = (new UserAddress())->find($post['jaddress_id']); //获取地址信息
          //循环通知员工打包消息 
          foreach ($clerk as $key => $val){
              $data['member_id'] = $val['user_id'];
              $data['express_num'] = $express;
              $data['phone'] = $jaddress['phone'];
              $data['id'] = $res;
              $data['wxapp_id'] = $this->wxapp_id;
              $data['userName'] = $jaddress['name'];
              $data['visit_data_time'] = $post['pickup_date'].' '.$post['pickup_time'];
              $data['addressdetail'] = $jaddress['country'].$jaddress['province'].$jaddress['city'].$jaddress['detail'];
              Message::send('package.Reservationconfirmed',$data);   
          }
         //通知用户自己
         
         $userNotice['member_id'] = $this->user['user_id'];
         $userNotice['express_num'] = $express;
         $userNotice['userName'] = $jaddress['name'];
         $userNotice['addressdetail'] = $jaddress['detail'];
         $userNotice['wxapp_id'] = $this->wxapp_id;
         $userNotice['id'] = $res;
         $userNotice['time'] = date("Y-m-d H:i:s",time());
         Message::send('package.VisitOrdersuccess',$userNotice);   
         if ($classItem){
             $packItemRes = $packItemModel->saveAllData($classItem,$res);
             if (!$packItemRes){
                Db::rollback();
                return $this->renderError('预约失败');
             }
         }         
         Logistics::add($res,'预约成功');
         
         Db::commit();
         return $this->renderSuccess('预约成功');
     }
     
     public function inImages($id,$imageIds,$wxapp_id){
        $PackageImage =  new PackageImage();
        if(isset($imageIds) && count($imageIds)>0){
                foreach ($imageIds as $key =>$val){
                    //校验图片是否又重复的
                     $result = (new $PackageImage)->where('package_id',$id)->where('image_id',$val)->find();
                     if(!isset($result)){
                         $update['package_id'] = $id;
                         $update['image_id'] = $val;
                         $update['wxapp_id'] =$wxapp_id;
                         $update['create_time'] = strtotime(getTime());
                         $resthen= (new PackageImage())->save($update);
                         if(!$resthen){
                              return false;
                         }
                     }
                }
            }    
        return true;
    }
     
     
     public function subtempate(){
         $values = SettingModel::getItem('submsg');
        //  dump($values);die;
         $templateid = [];
         foreach ($values['order'] as $v){
             if($v['template_id'])
                $templateid[] = $v['template_id'];
         }
         $values['template_ids'] = $templateid;
         return $this->renderSuccess($values);
     }
     
     // 分类列表
     public function category(){
        $data = (new Category())->getCategoryAll();
        foreach ($data as $k => $v){
             $data[$k]['is_show'] = false;
        }
        $data = makeTree($data,'category_id');
        return $this->renderSuccess($data);
     }
     
     // 分类列表
     public function getChildcategory(){
        $data = (new Category())->gethotCategoryAll();
        return $this->renderSuccess($data);
     }
     
     // 分类列表
     public function hotcategory(){
        $data = (new Category())->gethotCategoryAll();
        foreach ($data as $k => $v){
             $data[$k]['is_show'] = false;
        }
        // $data = makeTree($data,'category_id');
        return $this->renderSuccess($data);
     }
     
     // 未打包列表-v2
     public function getUnpackageList(){
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
        if(isset($param['category_id'])){
            $catelist = (new Category())->getSonCategoryAll($param['category_id']);
            $paramwhere['category_id'] = $catelist;
            $paramwhere['is_delete'] = 0;
            $paramwhere['is_take'] = 2;
            $paramwhere['member_id'] = $this->user['user_id'];
            $paramwhere['status'] = [2,3,4,7];
            $data = (new PackageModel())->searchPackageForCategory($paramwhere,$field);
            $totalWeight = $data['totalWeight'];
            unset($data['totalWeight']);
            return $this->renderSuccess(compact('data','totalWeight'));
        }
        $data = (new PackageModel())->Dbquery300($where,$field);
        return $this->renderSuccess(compact('data'));
     }
     
     
     // 未打包列表
     public function unpack(){
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
        $data = (new PackageModel())->Dbquery300($where,$field);
        
        return $this->renderSuccess($data);
     }
     
     // 批量预报
     public function reportBatchForquick($post){
        if (!$this->user['user_id']){
            return $this->renderError('请先登录');
         }
         $user = (new User())->find($this->user['user_id']);
        //  $post = $this->postData();
         if ($post['country_id']){
             $country = (new Country())->getValueById($post['country_id'],'title');
             if (!$country){
                 return $this->renderError('国家信息错误');
             }
         }
      
         if (!$post['storage_id']){
              return $this->renderError('请选择仓库');
         }
         $storage = (new Shop())->getValueById($post['storage_id'],'shop_name');
         if (!$storage){
             return $this->renderError('仓库信息错误');
         }
         if (!$post['express_id']){
             return $this->renderError('请选择快递');
         }
         
         if (!$post['express_sn']){
             return $this->renderError('快递单号错误');
         }
         
         if (preg_match('/[\x7f-\xff]/', $post['express_sn'])){
             return $this->renderError('快递单号不能使用汉字或字符');
         }
         if(!preg_match('/^[^\s]*$/',$post['express_sn'])){
             return $this->renderError('快递单号不能有空格');
         }
         if(!preg_match('^\w{3,20}$^',$post['express_sn'])){
             return $this->renderError('快递单号不能使用特殊字符');
         }
         $express = (new Express())->getValueById($post['express_id'],'express_name');
         if (!$express){
             return $this->renderError('快递信息错误');
         }
         $class_ids = $post['class_ids'];
         $Barcode = new Barcode;
         $packModel = new PackageModel();
         $packItemModel = new PackageItemModel();
         
         //将多个快递单号分拆为一个数组中英文逗号都可以进行转换
         $post['express_sn'] =trim($post['express_sn']);
         $post['express_sn'] =preg_replace("/\s|　/","",$post['express_sn']);
         $post['express_sn'] =str_replace('，',',',$post['express_sn']);
         $post['express_sn'] =str_replace('+',',',$post['express_sn']);
         $express_nums = [];
         $expno = explode(',',$post['express_sn']);
         $classItem = [];
         $classItems = [];
         $barcodelist = [];
         $goodslist = isset($post['goodslist'])?$post['goodslist']:[];
         $class_ids = $post['class_ids'];     
         // 开启事务
         Db::startTrans();
         $post['express_name'] = $express;
         $post['member_id'] = $this->user['user_id'];
         $post['member_name'] = $user['nickName'];
      
       
         //循环对每个包裹进行预判是否已经入库
         foreach ($expno as $key => $v){
        
            if(empty($v)  || $v==" " || $v==""){
               return $this->renderError('请不要加多余的逗号'); 
            }
            
            $post['express_num'] =  $v;
            $packres = $packModel->where('express_num',$v)->where('is_delete',0)->find();
            
            //当快递单号为一个数字加空格形式时候，则会提示报错
            // if($packres && ($packres['is_take']==2)){
                 
            //     return $this->renderError('快递单号'.$v.'已被预报');
            // }
            //   dump($express_nums);die;
            if($packres){
                $express_nums[] = $packres['id'];
                $resup = $packModel->where('id',$packres['id'])->update(
                   ['price'=>$post['price'],
                   'remark'=>$post['remark'],
                   'country_id'=>$post['country_id'],
                   'express_name'=>$post['express_name'],
                   'express_id'=>$post['express_id'],
                   'member_id'=>$user['user_id'],
                   'member_name'=>$user['nickName'],
                   'storage_id'=>$post['storage_id'],
                   'is_take'=>2
                   ]);
    
                if (!$resup){return $this->renderError('申请预报失败');}
               
                if ($class_ids || $goodslist){
                    $classItem = $this->parseClass($class_ids);
                        foreach ($goodslist as $k => $val){
                             $classItems[$k]['class_name'] = !empty($classItem)?$classItem[0]['name']:$val['pinming'];
                             $classItems[$k]['one_price'] = isset($val['danjia'])?$val['danjia']:'';
                             $classItems[$k]['all_price'] = (!empty($val['danjia'])?$val['danjia']:0) * (!empty($val['shuliang'])?$val['shuliang']:0);
                             $classItems[$k]['product_num'] = isset($val['shuliang'])?$val['shuliang']:'';
                             $classItems[$k]['express_num'] = $post['express_num'];
                             $classItems[$k]['goods_name'] = isset($val['pinming'])?$val['pinming']:'';
                             $classItems[$k]['express_name'] = $express;
                             $classItems[$k]['class_name_en'] = isset($val['goods_name_en'])?$val['goods_name_en']:''; // 英文品名
                             $classItems[$k]['goods_name_jp'] = isset($val['goods_name_jp'])?$val['goods_name_jp']:'';
                             $classItems[$k]['length'] = isset($val['depth'])?$val['depth']:'';
                             $classItems[$k]['width'] = isset($val['width'])?$val['width']:'';
                             $classItems[$k]['height'] = isset($val['height'])?$val['height']:'';
                             $classItems[$k]['unit_weight'] = isset($val['gross_weight'])?$val['gross_weight']:'';
                             $classItems[$k]['brand'] = isset($val['brand'])?$val['brand']:'';
                             $classItems[$k]['spec'] = isset($val['spec'])?$val['spec']:'';
                             $classItems[$k]['net_weight'] = isset($val['net_weight'])?$val['net_weight']:'';
                             $classItems[$k]['barcode'] = isset($val['barcode'])?$val['barcode']:'';
                             
                             
                             if(isset($val['barcode']) && !empty($val['barcode'])){
                                 $barcoderesu =  $Barcode::useGlobalScope(false)->where('barcode',$val['barcode'])->find();
                                 
                                 $barcodelist['barcode'] = isset($val['barcode'])?$val['barcode']:$barcoderesu['barcode'];
                                 $barcodelist['brand'] = isset($val['brand'])?$val['brand']:$barcoderesu['brand'];
                                 $barcodelist['goods_name_en'] = isset($val['goods_name_en'])?$val['goods_name_en']:$barcoderesu['goods_name_en'];
                                 $barcodelist['goods_name_jp'] = isset($val['goods_name_jp'])?$val['goods_name_jp']:$barcoderesu['goods_name_jp'];
                                 $barcodelist['goods_name'] = isset($val['pinming'])?$val['pinming']:$barcoderesu['goods_name'];
                                 $barcodelist['spec'] = isset($val['spec'])?$val['spec']:$barcoderesu['spec'];
                                 $barcodelist['price'] = isset($val['danjia'])?$val['danjia']:$barcoderesu['price'];
                                 $barcodelist['gross_weight'] = isset($val['gross_weight'])?$val['gross_weight']:$barcoderesu['gross_weight'];
                                 $barcodelist['net_weight'] = isset($val['net_weight'])?$val['net_weight']:$barcoderesu['net_weight'];
                                 $barcodelist['depth'] = isset($val['depth'])?$val['depth']:$barcoderesu['depth'];
                                 $barcodelist['width'] = isset($val['width'])?$val['width']:$barcoderesu['width'];
                                 $barcodelist['height'] = isset($val['height'])?$val['height']:$barcoderesu['height'];
                                 
                                 
                                 if(empty($barcoderesu)){
                                     $barresult = $Barcode::useGlobalScope(false)->insert($barcodelist);
                                 }else{
                                     $barcoderesu->save($barcodelist);
                                 }
                             }
                        }
                }
                if ($classItems){
                     $packItemRes = $packItemModel->saveAllData($classItems,$packres['id']);
                     if (!$packItemRes){
                        Db::rollback();
                        return $this->renderError('申请预报失败');
                     }
                 }
                
                 Logistics::add($packres['id'],'包裹预报成功');
                //  Db::commit();
             }
             
             if(!$packres){
                 $post['order_sn'] = CreateSn();
                 $post['is_take'] = 2;
                 $userclientsetting = SettingModel::getItem('userclient',\request()->get('wxapp_id'));
                 if($userclientsetting['yubao']['is_expressnum_enter']==1){
                    $post['entering_warehouse_time'] = getTime();
                    $post['status'] = 2;
                 }
                 $res = $packModel->saveData($post);
                 $express_nums[] = $res;
                 if (!$res){
                     return $this->renderError('申请预报失败');
                 }
  
             if ($class_ids || $goodslist){
                    $classItem = $this->parseClass($class_ids);
                        foreach ($goodslist as $k => $val){
                             $classItems[$k]['class_name'] = !empty($classItem)?$classItem[0]['name']:$val['pinming'];
                             $classItems[$k]['one_price'] = isset($val['danjia'])?$val['danjia']:'';
                             $classItems[$k]['all_price'] = (!empty($val['danjia'])?$val['danjia']:0) * (!empty($val['shuliang'])?$val['shuliang']:0);
                             $classItems[$k]['product_num'] = isset($val['shuliang'])?$val['shuliang']:'';
                             $classItems[$k]['express_num'] = $v;
                             $classItems[$k]['goods_name'] = isset($val['pinming'])?$val['pinming']:'';
                             $classItems[$k]['express_name'] = $express;
                             $classItems[$k]['class_name_en'] = isset($val['goods_name_en'])?$val['goods_name_en']:''; // 英文品名
                             $classItems[$k]['goods_name_jp'] = isset($val['goods_name_jp'])?$val['goods_name_jp']:'';
                             $classItems[$k]['length'] = isset($val['depth'])?$val['depth']:'';
                             $classItems[$k]['width'] = isset($val['width'])?$val['width']:'';
                             $classItems[$k]['height'] = isset($val['height'])?$val['height']:'';
                             $classItems[$k]['unit_weight'] = isset($val['gross_weight'])?$val['gross_weight']:'';
                             $classItems[$k]['brand'] = isset($val['brand'])?$val['brand']:'';
                             $classItems[$k]['spec'] = isset($val['spec'])?$val['spec']:'';
                             $classItems[$k]['net_weight'] = isset($val['net_weight'])?$val['net_weight']:'';
                             $classItems[$k]['barcode'] = isset($val['barcode'])?$val['barcode']:'';
                             
                             
                             if(isset($val['barcode']) && !empty($val['barcode'])){
                                 $barcoderesu =  $Barcode::useGlobalScope(false)->where('barcode',$val['barcode'])->find();
                                 
                                 $barcodelist['barcode'] = isset($val['barcode'])?$val['barcode']:$barcoderesu['barcode'];
                                 $barcodelist['brand'] = isset($val['brand'])?$val['brand']:$barcoderesu['brand'];
                                 $barcodelist['goods_name_en'] = isset($val['goods_name_en'])?$val['goods_name_en']:$barcoderesu['goods_name_en'];
                                 $barcodelist['goods_name_jp'] = isset($val['goods_name_jp'])?$val['goods_name_jp']:$barcoderesu['goods_name_jp'];
                                 $barcodelist['goods_name'] = isset($val['pinming'])?$val['pinming']:$barcoderesu['goods_name'];
                                 $barcodelist['spec'] = isset($val['spec'])?$val['spec']:$barcoderesu['spec'];
                                 $barcodelist['price'] = isset($val['danjia'])?$val['danjia']:$barcoderesu['price'];
                                 $barcodelist['gross_weight'] = isset($val['gross_weight'])?$val['gross_weight']:$barcoderesu['gross_weight'];
                                 $barcodelist['net_weight'] = isset($val['net_weight'])?$val['net_weight']:$barcoderesu['net_weight'];
                                 $barcodelist['depth'] = isset($val['depth'])?$val['depth']:$barcoderesu['depth'];
                                 $barcodelist['width'] = isset($val['width'])?$val['width']:$barcoderesu['width'];
                                 $barcodelist['height'] = isset($val['height'])?$val['height']:$barcoderesu['height'];
                                 
                                 
                                 if(empty($barcoderesu)){
                                     $barresult = $Barcode::useGlobalScope(false)->insert($barcodelist);
                                 }else{
                                     $barcoderesu->save($barcodelist);
                                 }
                             }
                        }
                }
                if ($classItems){
                     $packItemRes = $packItemModel->saveAllData($classItems,$res);
                     if (!$packItemRes){
                        Db::rollback();
                        return $this->renderError('申请预报失败');
                     }
                }
                Logistics::add($res,'包裹预报成功');
             }
         }
         Db::commit();
       
         return $express_nums;
     }
     
     // 快速打包处理
     public function postpackquick(){
         $param = $this->postData();
         $this->user = $this->getUser();
         $address = (new UserAddress())->find($param['address_id']); //获取地址信息
         $storesetting = SettingModel::getItem('store');
         $packModel = new PackageModel();
        //  dump($ids);die;
         $inpackOrder = [
          'order_sn' => $storesetting['createSn']==10?createSn():createSnByUserIdCid($this->user['user_id'],$address['country_id']),
          'remark' =>$param['remark'],
        //   'pack_ids' => $ids,
          'waitreceivedmoney'=>$param['waitreceivedmoney'],
          'storage_id' => isset($param['express']['storage_id'])?$param['express']['storage_id']:0,
          'address_id' => $param['address_id'],
          'usermark' => isset($param['usermark'])?$param['usermark']:'',
          'free' => 0,
          'weight' => 0,
          'cale_weight' => 0,
          'volume' => 0,
          'pack_free' => 0,
          'member_id' => $this->user['user_id'],
          'country_id' => $address['country_id'],
          'unpack_time' => getTime(),  //提交打包时间
          'created_time' => getTime(),  
          'updated_time' => getTime(),
          'status' => 1,
          'line_id' => $param['line_id'],
          'wxapp_id' => \request()->get('wxapp_id'),
        ];
        $user_id =$this->user['user_id'];
        if($storesetting['usercode_mode']['is_show']==1){
           $member =  (new User())->where('user_id',$this->user['user_id'])->find();
           $user_id = $member['user_code'];
        }
        $createSnfistword = $storesetting['createSnfistword'];
        $xuhao = ((new Inpack())->where(['member_id'=>$this->user['user_id'],'is_delete'=>0])->count()) + 1;
        if($param['express']['storage_id']){
            $shopname = ShopModel::detail($param['express']['storage_id']);  
        }else{
            $shopname['shop_alias_name'] = 'XS';
        }
           
        $orderno = createNewOrderSn($storesetting['orderno']['default'],$xuhao,$createSnfistword,$user_id,$shopname['shop_alias_name'],$address['country_id']);
        $inpackOrder['order_sn'] = $orderno;
        $inpack = (new Inpack())->insertGetId($inpackOrder); 
        if (!$inpack){
           return $this->renderError('打包包裹提交失败');
        }
        if(empty($param['express']['express_sn'])){
              $userclientsetting = SettingModel::getItem('userclient',\request()->get('wxapp_id'));
            //   dump($userclientsetting);die;
              $bianhao =  ($packModel->where(['member_id'=>$this->user['user_id'],'is_delete'=>0])->count()) + 1;
              $expressfistword = $userclientsetting['yubao']['orderno']['first_title'];
              $param['express']['express_sn'] = createNewOrderSn($userclientsetting['yubao']['orderno']['default'],$bianhao,$expressfistword,$user_id,$shopname['shop_alias_name'],$address['country_id']);
        }
            //   dump($param);die;
        $ids = $this->reportBatchForquick($param['express']);
        $packModel->whereIn('id',$ids)->update(['inpack_id'=>$inpack]);
        
        if(!empty($param['pack_ids'])){
             (new InpackServiceModel())->doservice($inpack,$param['pack_ids']);
        }
        return $this->renderSuccess('打包包裹提交成功');
     }

     // 提交订单
      public function applyforpackage(){
          $params = $this->request->param();
         
          $address = (new UserAddress())->find($params['address_id']);
          if (!$address){
             return $this->renderError('地址信息错误');
          }
          $line = (new Line())->find($params['line_id']);
          if (!$line){
            return $this->renderError('线路不存在,请重新选择');
          }
          $remark = "";
          if($params['form']){
              if($params['form']['packType']=='goods'){
                  $remark = "保留商品包装";
              }else{
                  $remark = "保留快递包装";
              }
          }
          if(!$params['form']['expressNo']){
              return $this->renderError('请填写快递单号');
          }
          $pack = (new PackageModel())->where('express_num',$params['form']['expressNo'])->where('is_delete',0)->find();
          if(!empty($pack['inpack_id'])){
              return $this->renderError('此快递单号已提交发货');
          }
          $wxapp_id = \request()->get('wxapp_id');
          $storesetting = SettingModel::getItem('store');
          $shopname = (new ShopModel())->getDefault();     
          $inpackOrder = [
              'order_sn' => $storesetting['createSn']==10?createSn():createSnByUserIdCid($this->user['user_id'],$address['country_id']),
              'remark' =>$remark,
              'storage_id' => $shopname['shop_id'],
              'address_id' => $params['address_id'],
              'delivery_method'=>isset($params['currentTab'])?$params['currentTab']:1,
              'pack_free' => 0,
              'member_id' => $this->user['user_id'],
              'country_id' => $address['country_id'],
              'unpack_time' => getTime(),  //提交打包时间
              'created_time' => getTime(),  
              'updated_time' => getTime(),
              'status' => 1,
              'line_id' => $params['line_id'],
              'wxapp_id' => $wxapp_id,
        ];
        $user_id =$this->user['user_id'];
        if($storesetting['usercode_mode']['is_show']==1){
           $member =  (new User())->where('user_id',$this->user['user_id'])->find();
           $user_id = $member['user_code'];
        }
        $createSnfistword = $storesetting['createSnfistword'];
        $xuhao = ((new Inpack())->where(['member_id'=>$this->user['user_id'],'is_delete'=>0])->count()) + 1;
        
        // dump($shopname->toArray());die;
        $orderno = createNewOrderSn($storesetting['orderno']['default'],$xuhao,$createSnfistword,$user_id,$shopname['shop_alias_name'],$address['country_id'],$address['country_id']);
        $inpackOrder['order_sn'] = $orderno;
        
        $inpack_id = (new Inpack())->insertGetId($inpackOrder); 
        if (!$inpack_id){
            return $this->renderError('提交失败');
        }

          if(empty($pack)){
              $data = [
                   'member_id' => $this->user['user_id'],
                   'created_time' => getTime(),  
                   'updated_time' => getTime(),
                   'storage_id' => $shopname['shop_id'],
                   'is_take'=>2,
                   'status'=>1,
                   'order_sn' => createSn(),
                   'remark' =>$remark,
                   'express_num'=>$params['form']['expressNo'],
                   'inpack_id'=>$inpack_id,
                   'wxapp_id' => $wxapp_id,
              ];
            //   dump($data);die;
              (new PackageModel())->saveData($data);
          }else{
              $pack->editData([
                  'member_id' => $this->user['user_id'],
                  'is_take'=>2,
                  'updated_time' => getTime(),
                  'remark' =>$remark,
              ]);
          }  
          return $this->renderSuccess('提交成功');
      }

     // 提交打包处理
     public function postPack(){
        $params = $this->request->param();
        $ids = $this->postData('packids')[0];
        $line_id = $this->postData('line_id')[0];
        $pack_ids = $this->postData('pack_ids')[0];
        $address_id = $this->postData('address_id')[0];
        $waitreceivedmoney = $this->postData('waitreceivedmoney')[0];
        $total_goods_value = $this->postData('total_goods_value')[0];
       
        $remark = $this->postData('remark')[0];
        if (!$ids){
            return $this->renderError('请选择要打包的包裹');
        }
        $idsArr = explode(',',$ids);
        $pack = (new PackageModel())->whereIn('id',$idsArr)->select();
        if (!$pack || count($pack) !== count($idsArr)){
            return $this->renderError('打包包裹数据错误');
        }
        $pack_storage = array_unique(array_column($pack->toArray(),'storage_id'));
        if (count($pack_storage)!=1){
             return $this->renderError('请选择同一仓库的包裹进行打包');
        }
        if (!$address_id){
            return $this->renderError('请先选择地址');
        }
        $address = (new UserAddress())->find($address_id);
        if (!$address){
            return $this->renderError('地址信息错误');
        }
        $line = (new Line())->find($line_id);
        if (!$line){
            return $this->renderError('线路不存在,请重新选择');
        }
        $free_rule = json_decode($line['free_rule'],true);
        $price = 0; // 总运费
        $allWeigth = (new PackageModel())->whereIn('id',$idsArr)->sum('weight');
        $caleWeigth = 0;
        $volumn =  (new PackageModel())->whereIn('id',$idsArr)->sum('volume');
        // 计算体积重
        $volumnweight = $volumn*1000000/$line['volumeweight'];
        if($line['volumeweight_type']==20){
            $volumnweight = round(($allWeigth + ($volumn*1000000/$line['volumeweight'] - $allWeigth)*$line['bubble_weight']/100),2);
        }
        $cale_weight = $allWeigth>$volumnweight?$allWeigth:$volumnweight;
        $storesetting = SettingModel::getItem('store');
        // 创建包裹订单
        $inpackOrder = [
          'order_sn' => $storesetting['createSn']==10?createSn():createSnByUserIdCid($this->user['user_id'],$address['country_id']),
          'remark' =>$remark,
          'pack_ids' => $ids,
          'waitreceivedmoney'=>$waitreceivedmoney,
          'total_goods_value'=>$total_goods_value,
          'pack_services_id' => $pack_ids, ///此值可作废
          'storage_id' => $pack[0]['storage_id'],
          'address_id' => $address_id,
          'free' => $price,
          'delivery_method'=>isset($params['currentTab'])?$params['currentTab']:1,
          'weight' => $allWeigth,
          'cale_weight' => $cale_weight,
          'line_weight'=> turnweight($storesetting['weight_mode']['mode'],$cale_weight,$line['line_type_unit']),
          'volume' => $volumnweight,
          'pack_free' => 0,
          'member_id' => $this->user['user_id'],
          'country_id' => $address['country_id'],
          'unpack_time' => getTime(),  //提交打包时间
          'created_time' => getTime(),  
          'updated_time' => getTime(),
          'status' => 1,
          'line_id' => $line_id,
          'wxapp_id' => \request()->get('wxapp_id'),
        ];
       
        $user_id =$this->user['user_id'];
        if($storesetting['usercode_mode']['is_show']==1){
           $member =  (new User())->where('user_id',$this->user['user_id'])->find();
           $user_id = $member['user_code'];
        }
    
        $createSnfistword = $storesetting['createSnfistword'];
        $xuhao = ((new Inpack())->where(['member_id'=>$this->user['user_id'],'is_delete'=>0])->count()) + 1;
        $shopname = ShopModel::detail($pack[0]['storage_id']);     
        $orderno = createNewOrderSn($storesetting['orderno']['default'],$xuhao,$createSnfistword,$user_id,$shopname['shop_alias_name'],$address['country_id'],$address['country_id']);
        $inpackOrder['order_sn'] = $orderno;
        
        $inpack = (new Inpack())->insertGetId($inpackOrder); 
        if (!$inpack){
           return $this->renderError('打包包裹提交失败');
        }
        
        //处理包装服务
        if(!empty($pack_ids)){
             (new InpackServiceModel())->doservice($inpack,$pack_ids);
        }
        $res = (new PackageModel())->whereIn('id',$idsArr)->update(
            [
                'status'=>5,
                'line_id'=>$line_id,
                'pack_service'=>$pack_ids,
                'address_id'=>$address_id,
                'updated_time'=>getTime(),
                'inpack_id'=>$inpack
            ]);
        
        $inpackdate = (new Inpack())->where('id',$inpack)->find();
        //更新包裹的物流信息
        //物流模板设置
        $packnum =[];
        $noticesetting = SettingModel::getItem('notice',\request()->get('wxapp_id'));
        if($noticesetting['packageit']['is_enable']==1){
            foreach ($idsArr as $key => $val){
                $packnum[$key] = (new PackageModel())->where('id',$val)->value('express_num');
                Logistics::addLogPack($val,$inpackdate['order_sn'],$noticesetting['packageit']['describe']);
            }
            //修改包裹的记录
            foreach ($packnum as $ky => $vl){
                Logistics::updateOrderSn($vl,$inpackdate['order_sn']);
            }
            
            //发送模板消息通知
        }
         //计算费用
         if($storesetting['is_auto_free']==1 && $allWeigth>0){
             getpackfree($inpackdate['id'],[]); 
         }
        
        
         $clerk = (new Clerk())->where('shop_id',$pack[0]['storage_id'])->where('mes_status',0)->where('is_delete',0)->select();
          
         if(!empty($clerk)){
         $data=[
            'id'=>$inpackdate['order_sn'],
            'nickName' => ($this->user)['nickName'],
            'userCode' => $user_id,
            'countpack' =>count($idsArr),
            'packtime' => getTime(),
            'packid' => $inpack,
            'shopname'=>$shopname['shop_name'],
            'wxapp_id' => \request()->get('wxapp_id'),
            'remark' =>$remark,
          ];
          $tplmsgsetting = SettingModel::getItem('tplMsg',\request()->get('wxapp_id'));
        //   dump($tplmsgsetting);die;
          if($tplmsgsetting['is_oldtps']==1){
              //循环通知员工打包消息 
              foreach ($clerk as $key => $val){
                  $data['clerkid'] = $val['user_id'];
                  Message::send('order.packageit',$data);   
              }
          }else{
              foreach ($clerk as $key => $val){
                  $data['member_id'] = $val['user_id'];
                  Message::send('package.outapply',$data);
              }
              
          }
         }
        
        if (!$res){
            return $this->renderError('打包包裹提交失败');
        }
        return $this->renderSuccess('打包包裹提交成功');
     }
     

     // 仓管员快速录单
     public function fastPack(){
        $line_id = $this->postData('line_id')[0];
        $pack_ids = $this->postData('pack_ids')[0];
        $length = $this->postData('length')[0];
        $width = $this->postData('width')[0];
        $height = $this->postData('height')[0];
        $payType = $this->postData('payType')[0];
        $weight = $this->postData('weight')[0];
        $address_id = $this->postData('address_id')[0];
        $remark = $this->postData('remark')[0];
        if (!$address_id){
            return $this->renderError('请先选择地址');
        }
        $address = (new UserAddress())->find($address_id);
        if (!$address){
            return $this->renderError('地址信息错误');
        }
        $line = (new Line())->find($line_id);
        if (!$line){
            return $this->renderError('线路不存在,请重新选择');
        }
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $storesetting = SettingModel::getItem('store');
        $free_rule = json_decode($line['free_rule'],true);
        $price = 0; // 总运费
        $allWeigth = 0;
        $caleWeigth = 0;
        $volumn = 0;
        //先生成包裹单
        $PackageModel = new PackageModel();
        $packOrder = [
            'order_sn' =>createSn(),
            'member_id' => $address['user_id'],
            'express_num' => createJysn(),
            'status' => 4,
            'storage_id' => $clerk['shop_id'],
            'remark' =>$remark,
            'line_id' => $line_id,
            'address_id' => $address_id,
            'country_id'=>$address['country_id'],
            'weight' => $weight,
            'width' =>$width,
            'height' => $height,
            'length' =>$length,
            'is_take' =>2,
            'entering_warehouse_time'=>getTime(),
            'created_time' => getTime(),  
            'updated_time' => getTime(),
            'wxapp_id' => \request()->get('wxapp_id'),
        ];
        $ids = $PackageModel->insertGetId($packOrder);
        
        // 创建包裹订单
        $inpackOrder = [
          'order_sn' => $storesetting['createSn']==10?createSn():createSnByUserIdCid($address['user_id'],$address['country_id']),
          'remark' =>$remark,
          'pay_type' => $payType,
          'pack_ids' => $ids,
          'pack_services_id' => $pack_ids, ///此值可作废
          'storage_id' => $clerk['shop_id'],
          'address_id' => $address_id,
          'free' => $price,
          'weight' => $weight,
          'width' =>$width,
          'height' => $height,
          'length' =>$length,
          'cale_weight' => $caleWeigth,
          'volume' => $volumn,
          'pack_free' => 0,
          'member_id' => $address['user_id'],
          'country_id' => $address['country_id'],
          'unpack_time' => getTime(),  //提交打包时间
          'created_time' => getTime(),  
          'updated_time' => getTime(),
          'status' => 1,
          'line_id' => $line_id,
          'wxapp_id' => \request()->get('wxapp_id'),
        ];
        $inpack = (new Inpack())->insertGetId($inpackOrder); 
        if (!$inpack){
           return $this->renderError('打包包裹提交失败');
        }
        
        //处理包装服务
        $res = (new InpackServiceModel())->doservice($inpack,$pack_ids);
        
        $inpackdate = (new Inpack())->where('id',$inpack)->find();
        //更新包裹的物流信息
        //物流模板设置
        $noticesetting = SettingModel::getItem('notice');
        if($noticesetting['packageit']['is_enable']==1){
            $packnum= (new PackageModel())->where('id',$ids)->value('express_num');
            Logistics::addLogPack($ids,$inpackdate['order_sn'],$noticesetting['packageit']['describe']);
            //修改包裹的记录
            Logistics::updateOrderSn($ids,$inpackdate['order_sn']);
        }
        
         $userData = User::detail($address['user_id']);
         
         $clerkData = (new Clerk())->where(['shop_id'=>$clerk['shop_id'],'is_delete'=>0,'mes_status' => 0])->find();
       
         if(!empty($clerkData)){
             $data=[
                'nickName' => $userData['nickName'],
                'userCode' => $userData['user_code'],
                'countpack' => 1,
                'packtime' => getTime(),
                'packid' => $inpack,
                'wxapp_id' => \request()->get('wxapp_id'),
                'remark' =>$remark,
              ];
             
              foreach($clerkData as $key => $val){
                  $data['clerkid'] = $val['user_id'];
                  Message::send('order.packageit',$data); 
              }
         }
        
        if (!$res){
            return $this->renderError('打包包裹提交失败');
        }
        return $this->renderSuccess('打包包裹提交成功');
     }
     
     // 待支付
     public function nopay(){
        $field = 'id,country_id,order_sn,member_id,storage_id,express_num,status,created_time,pack_free,free';
        $kw = input('keyword');
        $where = [
          'is_delete' => 0,
          'member_id' => $this->user['user_id'],
          'status' =>5,
        ];
        if ($kw){
            $where['express_num'] = $kw;
        }
        $data = (new PackageModel())->query($where,$field);
        $data = $this->getPackItemList($data);
        foreach ( $data as $k => $v){ 
                 $data[$k]['total_free'] = $v['free'] + $v['pack_free'];
        }
        return $this->renderSuccess($data); 
     }

     // 包裹列表
     public function packageList(){
         $this->user = $this->getUser(); 
         $query = [];
         $status = $this->request->param('type');
         $statusMap = [
           'all' =>[1,2,3,4,5,6,7,8],
           'verify' => [1],     
           'nopay' => [2],
           'no_send' => [3,4,5],
           'send' => [6,7],
           'complete' => [8]
         ];
         if ($status)
         $query['status'] = $statusMap[$status];
         if($status == 'nopay'){
             $query['is_pay'] = 0;
         }
         $query['member_id'] = $this->user['user_id']; 
         $list = (new Inpack())->getList($query);
         foreach ($list as &$value) {
            $value['num'] =  (new PackageModel())->where('inpack_id',$value['id'])->where('is_delete',0)->count();
            $value['weight_unit'] = [10=>'g',20=>'kg',30=>'lbs',40=>'cbm'];
            $value['total_free'] = round($value['free'] + $value['pack_free'] + $value['other_free'] + $value['insure_free'],2);
         }
         return $this->renderSuccess($list);
     }
     
     // 包裹列表
     public function packageListPlus(){
         $this->user = $this->getUser(); 
         $query = [];
         $status = $this->request->param('type');
         $statusMap = [
           'all' =>[1,2,3,4,5,6,7,8],
           'verify' => [1],     
           'nopay' => [2,3,4,5,6,7,8],
           'no_send' => [2,3,4,5],
           'send' => [6],
           'reach' => [7],
           'complete' => [8]
         ];
         if ($status)
         $query['status'] = $statusMap[$status];
         if($status == 'nopay'){
             $query['is_pay'] = 2;
         }
         $query['member_id'] = $this->user['user_id']; 
         $param = $this->request->param();
          if(isset($param['usermark'])){
              $query['usermark'] = $param['usermark']; 
         }
         $list = (new Inpack())->getList($query);
         foreach ($list as &$value) {
            $value['num'] = count($value['packageitems'])>0?count($value['packageitems']):1;
            $value['weight_unit'] = [10=>'g',20=>'kg',30=>'lbs',40=>'cbm'];
            $value['total_free'] = round($value['free'] + $value['pack_free'] + $value['other_free'] + $value['insure_free'],2);
         }
         return $this->renderSuccess($list);
     }
     
      // 可以参与拼团的包裹列表
     public function pintuanpackageList(){
         $this->user = $this->getUser(); 
         $query = [];
         $query['status'] = [1,2,3,4,5];
         $query['is_pay'] = 2;
         $query['inpack_type'] = 0;
         $query['member_id'] = $this->user['user_id']; 
         $list = (new Inpack())->getList($query);
         foreach ($list as &$value) {
            $value['num'] =  (new PackageModel())->where('inpack_id',$value['id'])->where('is_delete',0)->count();
            $value['total_free'] = $value['free'] + $value['pack_free'] + $value['other_free'];
         }
         return $this->renderSuccess($list);
     }
     
     // 包裹列表 - 取消包裹
     public function cancle(){
         $id = $this->postData('id');
         $info = (new PackageModel())->field('id,status,source')->find($id[0]);
         
         if (!in_array($info['status'],[1,2,3,4,5,6,7,8])){
              return $this->renderError('该包裹已发货,无法为您拦截取消');
         }
         // 判断是否为拼团订单
         if ($info['source']==6){
             $SharingOrderItem = (new SharingOrderItem());
             $SharingOrderItem->removeByPack($info['id']);
         }
         $res = (new PackageModel())->where(['id'=>$info['id']])->update(['status'=>'-1']);
         if (!$res){
              return $this->renderError('取消失败');
         }
         return $this->renderSuccess("取消成功");
     }
     
     // 包裹列表 - 取消包裹
     public function canclePack(){
         $param = $this->request->param();
      
         $info = (new Inpack())->field('id,status,is_pay,pack_ids,real_payment,order_sn,member_id,wxapp_id')->find($param['id']);
         if (!in_array($info['status'],[1,2,3,4])){
              return $this->renderError('该包裹已发货,无法为您拦截取消');
         }
      
         // 判断该订单是否已支付 且 实际付款金额>0
         if ($info['is_pay']==1 && $info['real_payment']>0){
            
             // 退款流程
            $remark =  '集运订单'.$info['order_sn'].'的支付退款';
            (new User())->banlanceUpdate('add',$info['member_id'],$info['real_payment'],$remark);
         }
        
         
         $padata= explode(',',$info['pack_ids']);
         if($info['pack_ids']){
             foreach($padata as $key=>$val){
                 (new PackageModel())->where('id',$val)->update(['status'=>2,'inpack_id'=>null]);
             }
         }else{
             (new PackageModel())->where('inpack_id',$info['id'])->update(['status'=>2,'inpack_id'=>null]);
         }
        
         $res = (new Inpack())->where(['id'=>$info['id']])->update(['status'=>'-1','cancel_reason'=>$param["reason"],'cancel_time'=>getTime()]);
         if (!$res){
              return $this->renderError('取消失败');
         }
         return $this->renderSuccess("取消成功");
     }

     // 待认领
     public function packageForTaker(){
         $this->user = $this->getUser(); 
         $kw = input('keyword');
         $where = [
           'is_delete' => 0,
           'is_take' =>1,
         ];
         if ($kw){
             $where['express_num'] = $kw;
         }
         $data = (new PackageModel())->with('packageimage.file')->where($where)->paginate(15);
         foreach($data as $k => $v){
              $data[$k]['express_num'] = func_substr_replace($v['express_num'],'*',4,2);
         }
         return $this->renderSuccess($data);
     }
     
     // 包裹认领
     public function getTakePackage(){
        $post = $this->postData();
        if (!$post['express_sn']){
          return $this->renderError('请输入快递单号');
        }
        $classIds = $post['class_ids'];
        if  (!$classIds && !is_string($classIds)){
          return $this->renderError('认领信息错误');
        }
        $classIdsArr = explode(',',$classIds);
        if (count($classIdsArr)<=0){
          return $this->renderError('认领信息错误');
        }
        $package = (new PackageModel())->where(['express_num'=>$post['express_sn']])->where('is_delete',0)->find();
       
        if (!$package){
          return $this->renderError('认领信息错误');
        }
        if($package['is_take'] ==2 && $package['member_id'] >0){
            return $this->renderError('包裹已被认领');
        }
        (new PackageModel())->where(['id'=>$package['id']])->update(['member_id'=>$this->user['user_id'],'is_take'=>2]);
        if (isset($class_ids)){
            $packItemModel = new PackageItemModel();
            $classItem = $this->parseClass($class_ids);
            foreach ($classItem as $k => $val){
                    $classItem[$k]['class_id'] = $val['category_id'];
                    $classItem[$k]['express_name'] = '';
                    $classItem[$k]['class_name'] = $val['name'];
                    $classItem[$k]['express_num'] = $post['express_sn'];
                    unset($classItem[$k]['category_id']); 
                    unset($classItem[$k]['name']);        
            }
            $packItemRes = $packItemModel->saveAllData($classItem,$package['id']);
        }
        //根据设置，决定是否需要认领审核
        $setting = SettingModel::detail("userclient",$package['wxapp_id'])['values'];
        if($setting['other']['is_packreport_verity']==1){
            (new PackageClaim())->saveData([
                'package_id'=>$package['id'],
                'user_id'=>$this->user['user_id'],
            ]);
            //处理通知信息
             $clerk = (new Clerk())->where('shop_id',$package['storage_id'])->where('claim_status',0)->where('is_delete',0)->select();
             if(!empty($clerk)){
                $data = [
                    'member_id'=>$this->user['user_id'],
                    'express_num'=>$package['express_num'],
                    'userName'=>$this->user['nickName'],
                    'calim_time'=>getTime(),
                    'wxapp_id'=>$package['wxapp_id'],
                    'weight'=>$package['weight'],
                ];
                foreach ($clerk as $key => $val){
                    $data['clerkid'] = $val['user_id'];
                    $reeee = Message::send('package.claimpackage',$data);  
                }
             }
        }
        return $this->renderSuccess('认领成功');
     }

     // 包裹数据
     public function getPackItemList($data){
        $orderItem = [];
        foreach($data as $k => $v){
            $orderItem[] = $v['id'];
        }
        $orderIdItem = [];
        $orderItemList = (new PackageItemModel())->whereIn('order_id',$orderItem)->field('order_id,id,class_name')->select();
        if ($orderItemList->isEmpty()){
            return $data;
        }
        foreach ($orderItemList as $v){
            $orderIdItem[$v['order_id']][] = $v->toArray();
        }
        foreach($data as $k =>$v){
            if (isset($orderIdItem[$v['id']]))
                $data[$k]['class_name'] = implode(',',array_column($orderIdItem[$v['id']],'class_name'));
        }
        return $data;
     }
     
     // 待查验
     public function verify(){
        $field = 'id,country_id,order_sn,storage_id,status,express_num,created_time';
        $status = \request()->get('status');
        $keyword = \request()->get('keyword');
        $where[] = ['is_delete','=',0];
        $where[] = ['member_id','=',$this->user['user_id']];
        $where[] = ['status','in',[2,3]];
        if ($keyword){
            $where[] = ['express_num','like','%'.$keyword.'%'];
        }
        $inpack = (new Inpack())->where(['status'=>$status])->select();
        $ids = [];
        foreach ($inpack as $v){
            $_ids = explode(',',$v['pack_ids']);
            array_merge($ids,$_ids);
        }
        $where[] = ['id','in',$ids]; 
        $data = (new PackageModel())->Dbquery($where,$field);
        $data = $this->getPackItemList($data);
        return $this->renderSuccess($data); 
     }

     // 未入库
     public function outside(){
        $this->user = $this->getUser(); 
        if(!\request()->get('token')){
            return $this->renderError('请先登录');
        }
        $field = 'id,inpack_id,country_id,order_sn,storage_id,express_num,created_time,source,status,usermark';
        $where = [
          'is_delete' => 0,
          'status' =>\request()->get('status'),
          'member_id' => $this->user['user_id']
        ];
        $param = $this->request->param();
        if(isset($param['usermark'])){
            $where['usermark'] = $param['usermark'];
        }
        $data = (new PackageModel())->query($where,$field);
        // dump($data);die;
        $data = $this->getPackItemList($data);
        return $this->renderSuccess($data);
     }
     
     
     // 搜索未入库已入库的包裹数量
     public function searchlist(){
        $this->user = $this->getUser(); 
        if(!\request()->get('token')){
            return $this->renderError('请先登录');
        }
        $field = 'id,country_id,order_sn,storage_id,express_num,created_time,source,status';
        $where = [
          'is_delete' => 0,
          'status' =>\request()->get('status'),
          'member_id' => $this->user['user_id']
        ];
        $keyword = \request()->get('keyword');
        $data = (new PackageModel())->querysearch($where,$field,$keyword);
        $data = $this->getPackItemList($data);
        return $this->renderSuccess($data);
     }
     
     
     
     //统计各个状态的包裹的数量
     public function countpack(){
        $this->user = $this->getUser(); 
        $PackageModel = new PackageModel();
        if(!\request()->get('token')){
            return $this->renderError('请先登录');
        }
        $where = [
          'is_delete' => 0,
          'member_id' => $this->user['user_id']
        ];
        $param = $this->request->param();
        if(isset($param['usermark'])){
            $where['usermark'] = $param['usermark'];
        }
        $data = [
            'allcount' => $PackageModel->querycount($where,$status=0),
            'nocount' => $PackageModel->querycount($where,$status=1),
            'yescount' => $PackageModel->querycount($where,$status=2),
            'yetsend' => $PackageModel->querycount($where,$status=9),
            'procount' => $PackageModel->querycount($where,$status=-1),
            'yishouhuo' => $PackageModel->querycount($where,$status=10),
            'yijingqianshou' => $PackageModel->querycount($where,$status=11),
            'daifahuo' => $PackageModel->querycount($where,$status=8),
            'daizhifu' => $PackageModel->querycount($where,$status=5),
            'daidabao' => $PackageModel->querycount($where,$status=4)
        ];
        return $this->renderSuccess($data);
     }
     
     
     /**
      * 恢复问题件
      * 更新于2022年5月7日
      * by feng
      */
     public function rechangepackage(){
        if(!\request()->post('token')){
            return $this->renderError('请先登录');
        }
        //找到包裹最新的状态，在日志记录中找到最新一条的状态值
        $packageStatus = (new Logistics())->where('express_num',\request()->post('express_num'))->order('id desc')->limit(1)->find();
        //恢复状态为最新的状态
        $res = (new PackageModel())->where('id',\request()->post('id'))->update(['status' => $packageStatus['status']]);
      
        if(!$res){
            return $this->renderError('恢复问题件失败');
        }
               
        $field = 'id,country_id,order_sn,storage_id,express_num,created_time,status';
        $where = [
          'is_delete' => 0,
          'status' => -1,
          'member_id' => $this->user['user_id']
        ];
        $data = (new PackageModel())->query($where,$field);
        $data = $this->getPackItemList($data);
        return $this->renderSuccess($data);
     }
     
     // 包裹更新
     public function packageUpdate(){
          $post = $this->postData();
          if (!$post['country_id']){
              return $this->renderError('请选择国家');
          }
          $country = (new Country())->getValueById($post['country_id'],'title');
          if (!$country){
              return $this->renderError('国家信息错误');
          }
          if (!$post['storage_id']){
              return $this->renderError('请选择仓库');
          }
          $storage = (new Shop())->getValueById($post['storage_id'],'shop_name');
          if (!$storage){
              return $this->renderError('仓库信息错误');
          }
          if (!$post['express_id']){
              return $this->renderError('请选择快递');
          }
          $express = (new Express())->getValueById($post['express_id'],'express_name');
          if (!$express){
              return $this->renderError('快递信息错误');
          }
          if (!$post['id']){
             return $this->renderError('包裹参数错误');
          }
          $class_ids = $post['class_ids'];
          $classItem = [];
         $goodslist = isset($post['goodslist'])?$post['goodslist']:[];
         $classItems = [];
         $packItemModel = new PackageItemModel();
         if ($class_ids || $goodslist){
             $classItem = $this->parseClass($class_ids);
 
                foreach ($goodslist as $k => $val){
                     $classItems[$k]['class_name'] = !empty($classItem)?$classItem[0]['name']:$val['goods_name'];
                     $classItems[$k]['one_price'] = $val['one_price'];
                     $classItems[$k]['all_price'] = (!empty($val['one_price'])?$val['one_price']:0) * (!empty($val['product_num'])?$val['product_num']:0);
                     $classItems[$k]['product_num'] = $val['product_num'];
                     $classItems[$k]['express_num'] = $post['express_sn'];
                     $classItems[$k]['goods_name'] = $val['goods_name'];
                     $classItems[$k]['express_name'] = $express;
                    
                    //  $packItemModel->where('id',$val['id'])->update($classItems);
                }
         }
           
          
          $packModel = new PackageModel();
         
        //   $result = $packModel->where('express_num',$post['express_sn'])->find();
        //   if(!$result){
        //       return $this->renderError('包裹不已存在');
        //   }
          // 开启事务
          Db::startTrans();
          $post['express_name'] = $express;
          $post['express_num'] = $post['express_sn'];
          
          unset($post['express_sn']);
          unset($post['class_ids']);
          unset($post['token']);
          unset($post['goodslist']);
          $res = $packModel->saveData($post);
          if (!$res){
              Db::rollback();
              return $this->renderError('申请修改失败');
          }
          if ($classItems){
              // 删除之前的数据
              $map = [
                 'order_id' => $post['id'],
              ];
              $packItemModel -> where($map) -> delete();
              $packItemRes = $packItemModel->saveAllData($classItems,$post['id']);
              if (!$packItemRes){
                Db::rollback();
                return $this->renderError('申请修改失败');
              }
          }
          Db::commit();
          return $this->renderSuccess('申请修改成功');
     }

     // 包装服务
     public function postservice(){
        $data = Db::name('package_services')->where(['wxapp_id'=>(new PackageModel())->getWxappId()])->select()->toArray();
        foreach($data as $k => $val){
           $data[$k]['is_show'] = false;
        }
        return $this->renderSuccess($data);
     }
     
    /***
     * 集运单提交支付接口
     * 可用的支付接口
     */ 
    public function payType(){
        $setting = SettingModel::detail("notice")['values'];
        dump();die;
    }
    

    /***
     * 集运单提交支付接口
     * 去支付
     */
     public function doPay(){
         $id = $this->postData('id')[0]; //集运单id
         $couponId = $this->postData('coupon_id')[0]; //优惠券id
         $paytype = $this->postData('paytype')[0];  //支付类型
         if($paytype==20){
             $paytype = 1;
         }
         if($paytype==10){
             $paytype = 2;
         }
         $client = $this->postData('client')[0];  //支付所在客户端 client:"MP-WEIXIN"
         $pack = (new Inpack())->field('id,status,pack_ids,free,order_sn,pack_free,other_free,remark,storage_id,is_pay,pay_order')->find($id);
         //生成支付订单号
         $payorderSn = createOrderSn();
         (new Inpack())->where('id',$pack['id'])->update(['pay_order'=>$payorderSn]);
         if ($pack['status'] != 2 && $pack['is_pay'] != 2) {
            return $this->renderError('包裹状态不正确');
         }
         $user = $this->user;
         $amount = $pack['free'] + $pack['pack_free'] + $pack['other_free'];
         if($couponId){
             $amount = $this->UseConponPrice($couponId,$amount);
         }
       
     
         if($pack['status']!=2){
            $update['status'] =  $pack['status'];
         }else{
             $update['status'] =3;
         }
         
         Db::startTrans();
         $update['real_payment'] = $amount;
         $update['is_pay'] = 1;
         $update['pay_time'] = getTime();
         $coupon['user_coupon_id'] = $couponId;
         $coupon['user_coupon_money'] = $pack['free'] + $pack['pack_free'] + $pack['other_free'] - $amount; //计算优惠了多少费用；
         try {
             (new Inpack())->where('id',$pack['id'])->update($update);
             (new Inpack())->where('id',$pack['id'])->update($coupon);
             //更新优惠券的状态为is_use
             (new UserCoupon())->where('user_coupon_id',$couponId)->update(['is_use'=>1]);
             
             $update['status'] = 6;
            //  dump($pack['pack_ids']);die;
             $up = (new PackageModel())->where('id','in',explode(',',$pack['pack_ids']))->update($update);
            // dump((new PackageModel())->getLastsql());die;
             if (!$up){
                Db::rollback();
                return $this->renderError('支付失败,请重试');
             }
             if($paytype==1){
               
                    // 构建微信支付
                    $payment = PaymentService::wechat(
                        $user,
                        $pack['id'],
                        $payorderSn,
                        $amount,
                        OrderTypeEnum::TRAN
                    );
                    // 支付状态提醒
                    $message = ['success' => '支付成功', 'error' => '订单未支付'];
                    return $this->renderSuccess(compact('payment', 'message'), $message);
             }elseif($paytype==2){
                    if ($user['balance']<$amount){
                        return $this->renderError('余额不足,请充值');
                     }
                    //减少余额
                     $memberUp = (new User())->where(['user_id'=>$user['user_id']])->update([
                       'balance'=>$user['balance']-$amount,
                       'pay_money' => $user['pay_money']+ $amount,
                     ]);
                           
                     if (!$memberUp){
                         Db::rollback();
                         return $this->renderError('支付失败,请重试');
                     }
                     //记录支付类型
                      $payres = (new Inpack())->where('id',$pack['id'])->update(['is_pay_type' => 2]);
                      if(!$payres){
                          Db::rollback();
                          return $this->renderError('支付失败,请重试');
                      }          
                      // 新增余额变动记录
                     BalanceLog::add(SceneEnum::CONSUME, [
                      'user_id' => $user['user_id'],
                      'money' => $amount,
                      'remark' => '包裹单号'.$pack['order_sn'].'的运费支付',
                      'sence_type' => 2,
                  ], [$user['nickName']]);
                  
                    // $message = ['success' => '支付成功', 'error' => '支付失败'];
                    // return $this->renderSuccess(compact('message'), $message);
                  
             }elseif($paytype==3){
                 // 构建微信支付
                    $payment = PaymentService::Hantepay(
                        $user,
                        $pack['id'],
                        $payorderSn,
                        $amount,
                        OrderTypeEnum::TRAN
                    );
                    // 支付状态提醒
                    $message = ['success' => '支付成功', 'error' => '订单未支付'];
                    return $this->renderSuccess(compact('payment', 'message'), $message);
             }
             
         //处理通知信息
         $clerk = (new Clerk())->where('shop_id',$pack['storage_id'])->where('mes_status',0)->where('is_delete',0)->select();
       
         if(!empty($clerk)){
         $data=[
            'amount' =>$amount,
            'paytime' => getTime(),
            'packid' => $id,
            'wxapp_id' => \request()->get('wxapp_id'),
            'remark' => $pack['remark'],
          ];
           
          foreach ($clerk as $key => $val){
                 $data['clerkid'] = $val['user_id'];
                 $reeee = Message::send('order.paymessage',$data);  
          }
         }
          // 处理分销逻辑的源头
          $this->dealerData(['amount'=>$amount,'order_id'=>$id],$user);
          
         }catch(\Exception $e){
              dump($e);die;
             return $this->renderError('支付失败,请重试');
         }
         Db::commit();
         return $this->renderSuccess('支付成功');
     }
     
     /***
     * 集运单提交支付接口
     * 去支付
     */
     public function newdoPay(){
         $id = $this->postData('id')[0]; //集运单id
         $couponId = $this->postData('coupon_id')[0]; //优惠券id
         $paytype = $this->postData('paytype')[0];  //支付类型
         $client = $this->postData('client')[0];  //支付所在客户端 client:"MP-WEIXIN"
         $pack = (new Inpack())
                ->field('id,insure_free,status,pack_ids,free,order_sn,pack_free,other_free,remark,storage_id,is_pay,pay_order,wxapp_id')->find($id);
         //生成支付订单号
         $payorderSn = createOrderSn();
         (new Inpack())->where('id',$pack['id'])->update(['pay_order'=>$payorderSn]);
         if ($pack['status'] != 2 && $pack['is_pay'] != 2) {
            return $this->renderError('包裹状态不正确');
         }
         $user = $this->user;
         $amount = $pack['free'] + $pack['pack_free'] + $pack['other_free'] + $pack['insure_free'];
         if($couponId){
             $amount = $this->UseConponPrice($couponId,$amount);
         }
        //  if($pack['status']!=2){
        //     $update['status'] =  $pack['status'];
        //  }else{
        //     $update['status'] =2;
        //  }
         $params = $this->request->param();
   
         Db::startTrans();
         $coupon['user_coupon_id'] = $couponId;
         $coupon['user_coupon_money'] = $pack['free'] + $pack['pack_free'] + $pack['other_free']  + $pack['insure_free'] - $amount; //计算优惠了多少费用；
         try {
             (new Inpack())->where('id',$pack['id'])->update($coupon);
             
             switch ($paytype) {
                case 10:
                    // 构建余额支付
                    if ($user['balance']<$amount){
                        return $this->renderError('余额不足,请充值');
                    }
                    //减少余额
                    $memberUp = (new User())->where(['user_id'=>$user['user_id']])->update([
                       'balance'=>$user['balance']-$amount,
                       'pay_money' => $user['pay_money']+ $amount,
                    ]);
                
                    if (!$memberUp){
                        Db::rollback();
                        return $this->renderError('支付失败,请重试');
                    }
                    //记录支付类型
                    $payres = (new Inpack())->where('id',$pack['id'])->update([
                        'is_pay_type' => 2,
                        'is_pay' => 1,
                        'pay_time' => getTime(),
                        'status' =>3,
                        'real_payment'=>$amount,
                    ]);
                    if(!$payres){
                          Db::rollback();
                          return $this->renderError('支付失败,请重试');
                    }
                    //更新优惠券的状态为is_use
                    (new UserCoupon())->where('user_coupon_id',$couponId)->update(['is_use'=>1]);
                    (new PackageModel())->where('inpack_id',$pack['id'])->update([
                        'is_pay'=>1,
                        'status'=>6,
                        'pay_time'=>getTime(),
                        'real_payment'=>$amount,
                    ]);
                    // 新增余额变动记录
                    BalanceLog::add(SceneEnum::CONSUME, [
                      'user_id' => $user['user_id'],
                      'money' => $amount,
                      'remark' => '包裹单号'.$pack['order_sn'].'的运费支付',
                      'sence_type' => 2,
                  ], [$user['nickName']]);
                  
                    $message = ['success' => '支付成功', 'error' => '支付失败'];
                     break;
                case 20:
                    // 构建微信支付
                     
                    $payment = PaymentService::wechat(
                        $user,
                        $pack['id'],
                        $payorderSn,
                        $amount,
                        OrderTypeEnum::TRAN
                    );
                    // 支付状态提醒
                    $message = ['success' => '支付成功', 'error' => '订单未支付'];
                    
                    break;
                
                
                case 30: 
                    // 构建汉特支付
                    $payment = PaymentService::Hantepay(
                        $user,
                        $pack['id'],
                        $payorderSn,
                        $amount,
                        OrderTypeEnum::TRAN
                    );
                    // 支付状态提醒
                    $message = ['success' => '支付成功', 'error' => '订单未支付'];
                   
                     break;
                case 40:
                    $payment = PaymentService::Omipay(
                        $user,
                        $pack['id'],
                        $payorderSn,
                        $amount,
                        OrderTypeEnum::TRAN
                    );
                    $message = ['success' => '支付成功', 'error' => '订单未支付'];
                   
                     break;
                     
                case 50:
                    // 构建服务商支付
                     
                    $payment = PaymentService::wechatdivide(
                        $user,
                        $pack['id'],
                        $payorderSn,
                        $amount,
                        OrderTypeEnum::TRAN
                    );
                    // 支付状态提醒
                    $message = ['success' => '支付成功', 'error' => '订单未支付'];
                    
                    break;
                    
                case 60:
                    // 构建線下支付
                   if(!isset($params['image']) && count($params['image'])==0){
                        Db::rollback();
                        return $this->renderError('请上传支付凭证');
                   }
                    //记录支付类型
                    $payres = (new Inpack())->where('id',$pack['id'])->update([
                        'is_pay_type' => 6,
                        'cert_image'=>$params['image'][0],
                        'is_pay' => 3,
                        'real_payment'=>$amount,
                        ]);
                    if(!$payres){
                          Db::rollback();
                          return $this->renderError('支付失败,请重试');
                    }
                    if(!empty($coupon['user_coupon_id'])){
                        (new UserCoupon())->where('user_coupon_id',$coupon['user_coupon_id'])->update(['is_use'=>1]);
                    }
                    $clerk = (new Clerk())->where('shop_id',$pack['storage_id'])->where('mes_status',0)->where('is_delete',0)->select();
                    // dump($clerk);die;
                    if(!empty($clerk)){
                          //循环通知员工打包消息 
                          foreach ($clerk as $key => $val){
                              $data = [
                                    'wxapp_id'=> $pack['wxapp_id'],
                                    'member_id'=>$val['user_id'],
                                    'order_no'=>$pack['order_sn'],
                                    'member_name'=>$user['nickName'].$user['user_id'],
                                    'pay_time'=>getTime(),
                                ];
                              Message::send('package.orderreview',$data);  
                          }
                    }
                    $message = ['success' => '支付成功', 'error' => '订单未支付'];
                    break;
                     
                 default:
                     // code...
                     break;
             }

             
         //处理通知信息
         $clerk = (new Clerk())->where('shop_id',$pack['storage_id'])->where('mes_status',0)->where('is_delete',0)->select();
       
         $tplmsgsetting = SettingModel::getItem('tplMsg',$pack['wxapp_id']);
         if(!empty($clerk)){
             $data=[
                'amount' =>$amount,
                'paytime' => getTime(),
                'packid' => $pack['order_sn'],
                'wxapp_id' => \request()->get('wxapp_id'),
                'remark' => $pack['remark'],
              ];
            
            if($tplmsgsetting['is_oldtps']==1){
                  //循环通知员工打包消息 
                  foreach ($clerk as $key => $val){
                      $data['clerkid'] = $val['user_id'];
                      Message::send('order.paymessage',$data);   
                  }
              }else{
                  foreach ($clerk as $key => $val){
                      $data['member_id'] = $val['user_id'];
                      Message::send('package.paysuccess',$data);
                  }
              }
         }
          // 处理分销逻辑的源头
          $this->dealerData(['amount'=>$amount,'order_id'=>$id],$user);
          
         }catch(\Exception $e){
              dump($e);die;
             return $this->renderError('支付失败,请重试');
         }
         Db::commit();
         return $this->renderSuccess(compact('payment', 'message'), $message);
     }
     

     // 包裹信息
     public function details(){
        $field_group = [
           'edit' => [
              'id,order_sn,storage_id,country_id,express_name,express_num,express_id,free,pack_free,price,address_id,status,line_id,remark,weight,usermark,volume'
           ],
        ];
        $id = \request()->post('id');
        $method = $this->postData('method');
        $data = (new PackageModel())->getDetails($id,$field_group[$method[0]]);
        $packItem = (new PackageItemModel())->where(['order_id'=>$data['id']])->select();
        $data['free_total'] = $data['free']+$data['pack_free'];
        $data['shop'] = '';
        if ($packItem){
            $data['shop'] = implode(',',array_column($packItem->toArray(),'class_name'));
            $data['shop_ids'] = implode(',',array_column($packItem->toArray(),'class_id'));
            $data['item'] = $packItem;
        }
        if ($data['address_id']){
            $data['address'] = (new UserAddress())->find($data['address_id']);
        }
        if ($data['line_id']){
            $data['line'] = (new Line())->field('id,name,limitationofdelivery,image_id')->find($data['line_id']);
            $data['line'] = $this->withImageById($data['line'],'image_id');
        }
        return $this->renderSuccess($data);
     }
     
     
     //集运订单信息详情-V2.2.46之前版本使用此接口
     public function details_pack(){
        $field_group = [
           'edit' => [
              'id,insure_free,order_sn,pack_ids,storage_id,free,pack_free,other_free,address_id,weight,cale_weight,volume,length,width,height,status,line_id,remark,country_id,t_order_sn,user_coupon_id,user_coupon_money,pay_type,is_pay,is_pay_type'
           ],
        ];
        $id = \request()->post('id');
        $couponId = \request()->post('coupon_id');//优惠券id
        
        $method = $this->postData('method');
        $data = (new Inpack())->getDetails($id,$field_group[$method[0]]);
        $package = (new PackageModel())->where('inpack_id',$data['id'])->field('id,express_num,price,express_name,entering_warehouse_time,remark,weight,height,length,width')->with(['packageimage.file'])->select();
        $packItem = (new PackageItemModel())->where('order_id','in',explode(',',$data['pack_ids']))->select();
        $packItemGroup = [];
        foreach ($packItem as $val){
             $packItemGroup[$val['order_id']][] = $val['class_name'];
        }

        foreach ($package as $k => $v){
             
             $package[$k]['class_name'] = '';
             if (isset($packItemGroup[$v['id']])){
                 $package[$k]['class_name'] = implode(',',$packItemGroup[$v['id']]);
             }
        }
        $data['free_total'] = $data['free']+$data['pack_free'];
        $data['shop'] = '';
        if ($data['address_id']){
            $data['address'] = (new UserAddress())->find($data['address_id']);
        }
        if ($data['line_id']){
            $data['line'] = (new Line())->field('id,name,limitationofdelivery,image_id')->find($data['line_id']);
            $data['line'] = $this->withImageById($data['line'],'image_id');
        }
        $data['free_total'] = round($data['free'] + $data['pack_free'] + $data['other_free'] + $data['insure_free'],2);
        $data['fyouhui_total'] = $this->UseConponPrice($couponId,$data['free_total']);
        $data['item'] = $package;
        if (isset($data['line']['image'])){
            $data['image'] = $data['line']['image'];
        }
        return $this->renderSuccess($data);
     }
     
     //集运订单信息详情 V2.2.47及以上使用此版本；
     public function packdetails(){
        $field_group = [
           'edit' => [
              'id,total_goods_value,line_weight,is_need_insure,order_sn,pack_ids,storage_id,free,insure_free,pack_free,other_free,address_id,weight,cale_weight,volume,length,width,height,status,line_id,remark,country_id,t_order_sn,user_coupon_id,user_coupon_money,pay_type,is_pay,is_pay_type'
           ],
        ];
        $id = \request()->post('id');
        $couponId = \request()->post('coupon_id');//优惠券id
        
        $method = $this->postData('method');
        $data = (new Inpack())->getDetailsplus($id,$field_group[$method[0]]);
        $data['free_total'] = round($data['free'] + $data['pack_free'] + $data['other_free'] + $data['insure_free'],2);
        $data['fyouhui_total'] = $this->UseConponPrice($couponId,$data['free_total']);
        //获取子订单记录
        $data['sonitem'] = (new InpackItem())->where('inpack_id',$id)->select();
        $data['weight_unit'] = [10=>'g',20=>'kg',30=>'lbs',40=>'cbm'];
        return $this->renderSuccess($data);
     }
     
     //编辑修改订单
     public function editInpack(){
        $params = $this->request->param();
        $data = (new Inpack())->getDetails($params['id'],'*');
        if(empty($data)){
              return $this->renderError('订单不存在,请重试');
        }
        $data->save([
             'total_goods_value'=>isset($params['total_goods_value'])?$params['total_goods_value']:0,
             'insure_free'=>isset($params['insure_free'])?$params['insure_free']:0,
             'is_need_insure'=> (isset($params['is_need_insure']) && $params['is_need_insure']==true)?1:0,
             'updated_time'=>getTime()
        ]);
        return $this->renderSuccess("添加成功");
     }
     
     //添加分箱-仓管端使用
     public function addSonInpackItem(){
         $param = $this->request->param();
         $InpackItem = new InpackItem();
         $InpackItem->where('inpack_id',$param['id'])->delete();
         
         $settingdata  = SettingModel::getItem('store');
         $packData = (new Inpack())->where('id',$param['id'])->find();
         if(empty($packData)){
            return $this->renderError('订单不存在,请重试');
         }
         if(count($param['sonlist'])>0){
             for ($i = 0; $i < count($param['sonlist']); $i++) {
                     $data['inpack_id'] = $param['id'];
                     $data['width'] = $param['sonlist'][$i]['width'];
                     $data['length'] = $param['sonlist'][$i]['length'];
                     $data['height'] = $param['sonlist'][$i]['height'];
                     $data['weight'] = $param['sonlist'][$i]['weight'];
                     
                     $InpackItem->add($data);
             }
         }
         //完成集运单价格的计算；
        
        $oWeigth = $InpackItem->where('inpack_id',$param['id'])->sum('weight'); //合并重量
        $cale_weight = $InpackItem->where('inpack_id',$param['id'])->sum('cale_weight'); //合并计费重量
        $line_weight = $InpackItem->where('inpack_id',$param['id'])->sum('line_weight'); //合并计费重量
        $volume_weight = $InpackItem->where('inpack_id',$param['id'])->sum('volume_weight'); //合并体积重
        
        $inpackdetail = (new Inpack())->getDetails($param['id'],'*');
        //默认按每个箱子的重量跟体积重比大小，20=总订单的实重跟体积重比较
        if($inpackdetail['line']['billing_method']==20){
            $cale_weight = $oWeigth>$volume_weight?$oWeigth:$volume_weight;
        }
        
        $inpackdetail->save([
            'cale_weight'=>$cale_weight,
            'weight'=>$oWeigth,
            'volume'=>$volume_weight,
            'line_weight'=>$line_weight
        ]);
        if($settingdata['is_auto_free']==1){
            getpackfree($param['id'],$param['sonlist']);   
        }
         return $this->renderSuccess("添加成功");
     }
     
     //获取分箱-仓管端使用
     public function getInpackItem(){
         $param = $this->request->param();
         $InpackItem = new InpackItem();
         $list = $InpackItem->where('inpack_id',$param['inpack_id'])->select();
         return $this->renderSuccess($list);
     }
     
     public function turnweight($weight_mode,$oWeigth,$line_type_unit){
       
         switch ($weight_mode) {
           case '10':
                if($line_type_unit == 20){
                    $oWeigth = 0.001 * $oWeigth;
                }
                if($line_type_unit == 30){
                    $oWeigth = 0.00220462262185 * $oWeigth;
                }
               break;
           case '20':
                if($line_type_unit == 10){
                    $oWeigth = 1000 * $oWeigth;
                }
                if($line_type_unit == 30){
                    $oWeigth = 2.20462262185 * $oWeigth;
                }
               break;
           case '30':
               if($line_type_unit == 10){
                    $oWeigth = 453.59237 * $oWeigth;
                }
                if($line_type_unit == 20){
                    $oWeigth = 0.45359237 * $oWeigth;
                }
               break;
           default:
               if($line_type_unit == 10){
                    $oWeigth = 1000 * $oWeigth;
                }
                if($line_type_unit == 30){
                    $oWeigth = 2.20462262185 * $oWeigth;
                }
               break;
       }
        $oWeigth = round($oWeigth,2);
        return $oWeigth;
     }
     

     
     
     //计算使用优惠券后的价格；
     public function UseConponPrice($couponId,$total){
         $totalFree = 0;
        if(isset($couponId)){
             $couponData = (new UserCoupon())->where('user_coupon_id',$couponId)->find();
             
             switch ($couponData['coupon_type']['value']) {
                 case '10':
                     $totalFree = $total - $couponData['reduce_price'];
                     break;
                 case '20':
                     $totalFree = $total*($couponData['discount']/10);
                     break;
             }
             return sprintf("%01.2f", $totalFree);
        }
       return sprintf("%01.2f", $totalFree);
     }
     
     
     
     // 更换地址
     public function addressUpdate(){
        $address_id = $this->postData('address_id')[0];
        $id = $this->postData('id')[0];
       
        $pack = (new Inpack())->field('id,status')->find($id);
        if ($pack['status'] >= 6){
            return $this->renderError('包裹已发货,无法更改地址');
        }
        $address = (new UserAddress())->find($address_id);
        if (!$address){
            return $this->renderError('地址信息错误,请确认地址是否正确');
        }
        $up = (new Inpack())->where(['id'=>$id])->update([
           'address_id' => $address_id,
           'updated_time' => getTime(),
        ]);
        if (!$up){
          return $this->renderError('更新地址失败');
        }
        return $this->renderSuccess("更新地址成功");
     }
     
     /**
      * 轨迹列表
      * 重构时间 2023年12月08日
      * 输入参数进行国际和国内信息的查询，对所有快递单号进行检索属于的集运单以及对应的国际单号
      * 并将该国际单号数据进行获取展示；
      * */
     public function getlogistics(){
        $express = $this->postData('code')[0];
        $Logistics = new Logistics();
        $PackageModel = new PackageModel();
        $DitchModel= new DitchModel();
        $Inpack = new Inpack();
        $Express = new Express();
        //查询单号是否有国内包裹的物流信息;
        $packData = $PackageModel->where(['express_num'=>$express,'is_delete' => 0])->find();
        $inpackData = $Inpack->where('order_sn|t_order_sn',$express)->where(['is_delete' => 0])->find();  //国际单号
        $inpackData2 = $Inpack->where(['t2_order_sn'=>$express,'is_delete' => 0])->find();  //转单号
        $inpackData3 = $Inpack->where(['t2_order_sn'=>$express,'is_delete' => 0])->find();  //转单号
        
        $logic = $Logistics->getZdList($packData['express_num'],$express_code,$packData['wxapp_id']);
        //查询包裹系统内部的轨迹
        
        //查询系统内部订单的轨迹
        
        //查询发货后的物流轨迹
        return $this->renderSuccess(compact('logic'));
     }
     
     /**
      * 轨迹列表
      * 重构时间 2022年06月27日
      * 输入参数进行国际和国内信息的查询，对所有快递单号进行检索属于的集运单以及对应的国际单号
      * 并将该国际单号数据进行获取展示；
      * */
     public function logicistplus(){
        $express = $this->postData('code')[0];
        $logic = $logic4 = $logictik =[];
        $result=[];
        $logib = [];
        $logia = [];
        $logguoji=[];
        $logzd = [];
        $logici = [];
        $logicti = [];
        $Logistics = new Logistics();
        $PackageModel = new PackageModel();
        $DitchModel= new DitchModel();
        $Inpack = new Inpack();
        $Expresss = new Express();
        $setting = SettingModel::detail("notice")['values'];
        //查询出来这个单号是包裹单号、国际单号、转单号
        $packData = $PackageModel->where(['express_num'=>$express,'is_delete' => 0])->find();

        $inpackData = $Inpack->where('t_order_sn',$express)->where(['is_delete' => 0])->find(); //国际单号
        //  dump($inpackData);die;
        $inpackData2 = $Inpack->where(['t2_order_sn'=>$express,'is_delete' => 0])->find();  //转单号
        $inpackData4 = $Inpack->where(['order_sn'=>$express,'is_delete' => 0])->find();
        //如果是包裹单号，可以反查下处于哪个集运单；
        //   dump($inpackData4);die;
        
        if(!empty($packData)){
            //查出的系统内部物流信息
            $logic = $Logistics->getList($express);
              
            if(count($logic)>0){
               
                $logia = $Logistics->getorderno($logic[0]['order_sn']);
            }
            $express_code = $Expresss->getValueById($packData['express_id'],'express_code');
          
            if($setting['is_track_yubao']['is_enable']==1){//如果预报推送物流，则查询出来
             
                $logib = $Logistics->getZdList($packData['express_num'],$express_code,$packData['wxapp_id']);
                
            }
            
            
            // $inpackData3 = $Inpack->where('id', $packData['inpack_id'])->where('is_delete',0)->find();
            // if(!empty($inpackData3) && !empty($inpackData3['t_order_sn'])){
            //     $logzd = $Logistics->getZdList($inpackData3['t_order_sn'],$inpackData3['t_number'],$inpackData3['wxapp_id']);
            // }
            // if(!empty($inpackData3) && !empty($inpackData3['t2_order_sn'])){
            //     $logguoji = $Logistics->getZdList($inpackData3['t2_order_sn'],$inpackData3['t2_number'],$inpackData3['wxapp_id']);
            // }
        
            $logic = array_merge($logia,$logib,$logic);
            if(empty($logic)){
                $inpackData2 = $Inpack->where('id',$packData['inpack_id'])->where(['is_delete' => 0])->find(); //国际单号
                // dump($inpackData2);die;
            }
        }
        

        if(!empty($inpackData) ){
           
            if($inpackData['transfer']==0){
                $ditchdatas = $DitchModel->where('ditch_id','=',$inpackData['t_number'])->find();
                // dump($ditchdatas);die;
                 //锦联
                if($ditchdatas['ditch_no']==10001){
                    $jlfba =  new jlfba(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token']]);
                    $result = $jlfba->query($express);
                }
                //百顺达
                if($ditchdatas['ditch_no']==10002){
                    $bsdexp =  new bsdexp(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token']]);
                    //   dump($bsdexp);die;
                    $result = $bsdexp->query($express);
                }
                //K5
                if($ditchdatas['ditch_no']==10003){
                    $kingtrans =  new kingtrans(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $kingtrans->query($express);
                }
                //华磊api
                if($ditchdatas['ditch_no']==10004){
                    $Hualei =  new Hualei(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Hualei->query($express);
                    //  dump($result);die;
                }
                
                //星泰api
                if($ditchdatas['ditch_no']==10005){
                    $Xzhcms5 =  new Xzhcms5(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Xzhcms5->query($express);
                }
                
                //澳联
                if($ditchdatas['ditch_no']==10006){
                    $Aolian =  new Aolian(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Aolian->query($express);
                }
                
                //易抵达
                if($ditchdatas['ditch_no']==10007){
                    $Yidida =  new Yidida(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Yidida->query($express);
                }
                //当是自有专线物流时
                // $logictjki = [];
                // if($ditchdatas['type']==0){
                //   $logictjki = $Logistics->getorderno($inpackData['order_sn']);  
                // }
                // //查询国际物流部分
                // // $logic = $Logistics->getZdList($inpackData['t_order_sn'],$inpackData['t_number'],$inpackData['wxapp_id']);
                // // dump($result);die;
                // $logic = array_merge($result,$logictjki);
                $logic = $result;
             
            }else{
                // $logic = $Logistics->getZdList($inpackData['t_order_sn'],$inpackData['t_number'],$inpackData['wxapp_id']);
                    if(!empty($inpackData['t2_order_sn'])){
                        $logic = $Logistics->getZdList($inpackData['t2_order_sn'],$inpackData['t2_number'],$inpackData['wxapp_id']);
                    }
                   
                    if(!empty($inpackData['t_order_sn'])){
                        $logic = $Logistics->getZdList($inpackData['t_order_sn'],$inpackData['t_number'],$inpackData['wxapp_id']);
                    }

            }
            // dump($inpackData);die;
            $packinck = $PackageModel->where(['inpack_id'=>$inpackData['id'],'is_delete' => 0])->find();
           
            if(!empty($packinck)){
                $logictik = $Logistics->getList($packinck['express_num']);
                //  dump($packinck);die;
            }
            if(empty($logia) && (empty($result) || $ditchdatas['type']==0)){
                $logic543 = $Logistics->getorderno($inpackData['order_sn']); 
                $logic = array_merge($logic,$logic543);
           
            }
            
            // $logictii = $Logistics->getlogisticssn($inpackData['t_order_sn']);dump($logictii);die;
            $logic = array_merge($logic,$logictik);
        
            // $logic = array_merge($logic,$result);
        }
        //  dump($inpackData4);die;
        if(!empty($inpackData4)){
            //   dump($inpackData2);die;
            if(!empty($inpackData4['t2_order_sn'])){
                $logici = $Logistics->getZdList($inpackData4['t2_order_sn'],$inpackData4['t2_number'],$inpackData4['wxapp_id']);
            }
           
            if(!empty($inpackData['t_order_sn'])){
                $logici = $Logistics->getZdList($inpackData4['t_order_sn'],$inpackData4['t_number'],$inpackData4['wxapp_id']);
            }
            
        }
 
        $logic = array_merge($logic,$logici);

        return $this->renderSuccess(compact('logic'));
     }
     
     
     public function logicist(){
        $express = $this->postData('code')[0];
        $logic = $logic4 = $logictik =[];
        $result = [];
        $logib = [];
        $logicddd = [];
        $logicdd = [];
        $logia = [];
        $logicv = [];
        $logguoji=[];
        $logzd = [];
        $logici = [];
        $logicti = [];
        $Logistics = new Logistics();
        $PackageModel = new PackageModel();
        $DitchModel= new DitchModel();
        $Inpack = new Inpack();
        $Express = new Express();
        $setting = SettingModel::detail("notice")['values'];
        //查询出来这个单号是包裹单号、国际单号、转单号|
        $packData = $PackageModel->where(['express_num'=>$express,'is_delete' => 0])->find();
       
        $inpackData = $Inpack->where('t_order_sn|order_sn|t2_order_sn',$express)->where(['is_delete' => 0])->find(); //国际单号
        // $inpackData2 = $Inpack->where(['t2_order_sn'=>$express,'is_delete' => 0])->find();  //转单号
        // $inpackData4 = $Inpack->where(['order_sn'=>$express,'is_delete' => 0])->find();
        //如果是包裹单号，可以反查下处于哪个集运单；
     
              
        if(!empty($packData)){
            //查出的系统内部物流信息
            $logic = $Logistics->getList($express);
 
            if(count($logic)>0){
              
                $logia = $Logistics->getorderno($logic[0]['order_sn']);
            }
            $express_code = $Express->getValueById($packData['express_id'],'express_code');
                
            if($setting['is_track_yubao']['is_enable']==1){//如果预报推送物流，则查询出来
                $logib = $Logistics->getZdList($packData['express_num'],$express_code,$packData['wxapp_id']);
            }
            $logicv = array_merge($logia,$logib,$logic);
            if(!empty($packData['inpack_id'])){
                $inpackData = $Inpack->where('id',$packData['inpack_id'])->where(['is_delete' => 0])->find(); //国际单号
            }
        }
       

        if(!empty($inpackData) ){
          
            if($inpackData['transfer']==0){
                $ditchdatas = $DitchModel->where('ditch_id','=',$inpackData['t_number'])->find();
                
                 //锦联
                if($ditchdatas['ditch_no']==10001){
                    $jlfba =  new jlfba(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token']]);
                    $result = $jlfba->query($express);
                }
                //百顺达
                if($ditchdatas['ditch_no']==10002){
                    $bsdexp =  new bsdexp(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token']]);
                    //   dump($bsdexp);die;
                    $result = $bsdexp->query($express);
                }
                //K5
                if($ditchdatas['ditch_no']==10003){
                    $kingtrans =  new kingtrans(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $kingtrans->query($express);
                }
                //华磊api
                if($ditchdatas['ditch_no']==10004){
                    $Hualei =  new Hualei(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Hualei->query($express);
                    //  dump($result);die;
                }
                
                //星泰api
                if($ditchdatas['ditch_no']==10005){
                    $Xzhcms5 =  new Xzhcms5(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Xzhcms5->query($express);
                }
                
                //澳联
                if($ditchdatas['ditch_no']==10006){
                    $Aolian =  new Aolian(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Aolian->query($express);
                }
                
                //易抵达
                if($ditchdatas['ditch_no']==10007){
                    $Yidida =  new Yidida(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Yidida->query($express);
                }
                is_array($result)  && $logic = $result;
               
                
            }else{
               
                if(!empty($inpackData['t_order_sn'])){
                     $logicddd = $Logistics->getZdList($inpackData['t_order_sn'],$inpackData['t_number'],$inpackData['wxapp_id']);
                }
                if(!empty($inpackData['t2_order_sn'])){
                     $logicdd = $Logistics->getZdList($inpackData['t2_order_sn'],$inpackData['t2_number'],$inpackData['wxapp_id']);
                }
                $logic = array_merge($logicddd,$logicdd);
            }
           
            $packinck = $PackageModel->where(['inpack_id'=>$inpackData['id'],'is_delete' => 0])->find();
            if(!empty($packinck)){
                $logictik = $Logistics->getorderno($inpackData['order_sn']);
                // dump($logictik);die;
            }else{
                if(!empty($packinck['express_num'])){
                    $logictik = $Logistics->getList($packinck['express_num']);
                }
            }
            $logici = array_merge($logicv,$logictik);
        }
           
        $logic = array_merge($logic,$logici,$logicv);
        return $this->renderSuccess(compact('logic'));
     }
     
     
     // 包裹统计
     public function packTotal(){
         $model =  (new Inpack());
         $param = $this->request->param();
         if(empty($this->user['user_id'])){
            $return = [ 
                'all'=>0,
                'nopay'=>0,
                'verify' =>0,
                'no_send' => 0,
                'send' =>0,
                'complete' => 0,
                'reach'=>0,
            ];
            return $this->renderSuccess($return);
         }
         $where = [
          'is_delete' => 0,
          'member_id' => $this->user['user_id']
        ];
         if(isset($param['usermark'])){
             $where['usermark'] = $param['usermark'];
         }   
         $return = [
           'all'=>$model->whereIn('status',[1,2,3,4,5,6,7,8])->where($where)->count(),
           'nopay' => $model->whereIn('status',[2,3,4,5,6,7,8])->where($where)->where('is_pay',2)->count(),
           'no_pay' => $model->whereIn('status',[2,3,4,5,6,7,8])->where($where)->where('is_pay',2)->count(),
           'verify' => $model->whereIn('status',[1])->where($where)->count(),
           'no_send' => $model->whereIn('status',[2,3,4,5])->where($where)->count(),
           'send' => $model->whereIn('status',[6])->where($where)->count(),
           'complete' => $model->whereIn('status',[8])->where($where)->count(),
           'reach'=>$model->whereIn('status',[7])->where($where)->count(),
         ];
         return $this->renderSuccess($return);
     }

     // 签收
     public function signedin(){
         $id = $this->postData('id')[0];
         $pack = (new Inpack())->find($id);
         if (!$pack){
             return $this->renderError('包裹数据错误');
         }
         if ($pack['status']!=6){
             return $this->renderError('包裹状态错误');
         }

         (new Inpack())->where(['id'=>$id])->update(['status'=>7]);
         $up = (new PackageModel())->where('inpack_id',$id)->update(['status'=>10]);
         
         $inpacklist =  (new PackageModel())->where('inpack_id',$id)->where('is_delete',0)->select();
        //  dump($inpacklist);die;
         foreach($inpacklist as $v){
            Logistics::add($v['id'],'包裹已经本人签收,如有问题,请联系客服');
         }
         if (!$up){
          return $this->renderError('签收失败');
         }
        $userresult = (new User())->where(['user_id' => $pack['member_id']])->where('is_delete',0)->find();
        if(empty($userresult)){
            return $this->renderError('用户信息错误');
        }
       
        // 处理积分赠送,给用户增加积分，生成一条积分增加记录；
        //根据积分设置的百分比来计算出需要赠送的积分数量，根据is_logistics_area来决定使用的用户范围，10所有人，20grade会员
        $setting = SettingModel::getItem('points');
        if($setting['is_open']==1 && $setting['is_logistics_gift']==1){
            if($setting['is_logistics_area']==20 && $userresult['grade_id']>0){
                $giftpoint = floor($pack['real_payment']*$setting['logistics_gift_ratio']/100);
            }else if($setting['is_logistics_area']==10){
                $giftpoint = floor($pack['real_payment']*$setting['logistics_gift_ratio']/100);
            }
        }

        $userresult->setInc('points',$giftpoint);
        // 新增积分变动记录
        PointsLogModel::add([
            'user_id' => $pack['member_id'],
            'value' => $giftpoint,
            'type' => 1,
            'describe' => "订单".$pack['order_sn']."赠送积分".$giftpoint,
            'remark' => "积分来自集运订单:".$pack['order_sn'],
        ]);
        
        
        return $this->renderSuccess("签收成功");
     }

     // 格式化
     public function parseClass($class_ids){
         $class_item = [];
         $class_ids = explode(',',$class_ids);
         $class = (new Category())->whereIn('category_id',$class_ids)->field('category_id,name')->select()->toArray(); 

         return $class;
     }
   
     //获取默认国家
     public function getCountryName(){
         //根据用户获取该用户的默认地址，获取默认地址上的默认国家id
         //根据国家id获取国家名称，id等信息
         $this->user = $this->getUser();
         $counrty = (new UserAddress())->where(['address_id'=>($this->user)['address_id']])->find();
         return $this->renderSuccess($counrty);
     }
   
     // 国家列表
     public function country(){
        $where = '';
        $k = input('keyword');
        $newDataPyin = [];
        if ($k){
            $where = $k; 
        }else{
            $queryHotCountry = (new Country())->queryHotCountry($where);
            count($queryHotCountry)>0 && $newDataPyin['热门'] = $queryHotCountry;
        }    
        $data = (new Country())->queryCountry($where);
        $dataPyin = [];
        $AZGROUP = range("A","Z");
        foreach ($data as $k => $v){
            $_pyin = Pinyin::getPinyin($v['title']);
            $first = strtoupper(substr($_pyin,0,1));
            if ($first){
                $dataPyin[$first][] = $v;
            }
        }
        
        foreach ($AZGROUP as $v){
            if (isset($dataPyin[$v]))
                $newDataPyin[$v] = $dataPyin[$v];  
        }
        return $this->renderSuccess($newDataPyin);
     }
   
     // 运费查询
     public function getFree(){
         $country = $this->postData('country_id');
     }

     // 快递列表
     public function express(){
        $data = (new Express())->queryExpress();
        return $this->renderSuccess($data);
     }

    // 线路列表  过20220916后淘汰
    public function line(){
        $data = (new Line())->getLine([]);
        return $this->renderSuccess($data);
    }
    
    // 运输方式
    public function lineCategoryList(){
        $data = (new LineCategory())->getList([]);
        return $this->renderSuccess($data);
    }
    
    // 线路列表   版本20220916
    public function lineplus(){
        $params = $this->request->param();
        $data = (new Line())->getLineplus($params);
        return $this->renderSuccess($data);
    }
    
        // 线路列表   版本20220916
    public function lineForShop(){
        $param = $this->request->param();
        $idsArr = explode(',',$param['packids']);
        $pack = (new PackageModel())->whereIn('id',$idsArr)->select();
        if (!$pack || count($pack) !== count($idsArr)){
            return $this->renderError('打包包裹数据错误');
        }
        $pack_storage = array_unique(array_column($pack->toArray(),'storage_id'));
        if (count($pack_storage)!=1){
             return $this->renderError('请选择同一仓库的包裹进行打包');
        }
        if(!empty($param)){
            count($pack_storage)==1 && $param['shops'] = $pack_storage[0];
            $data = (new Line())->getLineForShop($param);
        }else{
            $data = (new Line())->getLine([]); 
        }
        
        return $this->renderSuccess($data);
    }

     // 仓库列表
     public function storage(){
        $this->user = $this->getUser();  
        $data = (new Shop())->getList();
        return $this->renderSuccess($data);
     }

     // 订单评论详情
     public function commentOrder(){
         $id = \request()->post('id');
         $data = (new Inpack())->getDetails($id,'id,line_id,storage_id');
         if ($data['line_id']){
            $data['line'] = (new Line())->field('id,name,limitationofdelivery,image_id')->find($data['line_id']);
            $data['line'] = $this->withImageById($data['line'],'image_id');
        }
        return $this->renderSuccess($data);
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
           'wxapp_id' => \request()->get('wxapp_id'),
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
                   'wxapp_id' => \request()->get('wxapp_id'),
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
                   'wxapp_id' => \request()->get('wxapp_id'),
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
            'wxapp_id' => \request()->get('wxapp_id')
        ];
        
        $resCapi = (new Capital())->insertAll($dealerCapital);
        $resDeal = (new DealerOrder())->insert($dealerOrder);
        if(!$resCapi || !$resDeal){
            return false;
        }
        return true;
     }
     
}
