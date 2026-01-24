<?php
namespace app\api\controller;

use app\api\model\Inpack;
use app\api\model\Line;
use app\api\model\Category;
use app\api\model\Logistics;
use app\api\model\Package;
use app\api\model\Shelf;
use app\api\model\ShelfUnit;
use app\api\model\ShelfUnitItem;
use app\api\model\PackageItem as PackageItemModel;
use app\api\model\SendPreOrder;
use app\api\model\store\shop\Clerk;
use app\api\model\User as UserModel;
use app\api\model\UserAddress;
use app\store\model\PackageImage;
use app\common\service\Email;
use app\common\service\Message;
use app\common\model\User as UserCommonModel;
use app\common\model\store\Shop;
use think\Db;
use app\api\model\InpackImage;
use app\common\model\InpackService;
use app\api\model\Setting as SettingModel;
use app\common\model\store\shop\Capital;
use app\api\model\user\UserMark;
use app\common\service\package\Printer;
use app\api\model\Barcode;
use app\api\model\PackageClaim;
use app\api\model\ConsumablesLog;
use app\api\model\Consumables;
use app\api\model\user\PointsLog as PointsLogModel;
/**
 * 用户管理
 * Class User
 * @package app\api
 */
class Useropration extends Controller
{
    
    /* @var \app\api\model\User $user */
    private $user;
    private $role = [];

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
            $this->user['user_id'] = 1;
        }
        // $this->setRole();
    }

    
    // 设置角色
    private function setRole(){
        $userInfo = $this->user;
        // dump($userInfo);die;
        switch($userInfo['user_type']){
          case 0:
          $role_name = '普通用户';
          break;
          case 1:
          $role_name = '入库员';
          break;
          case 2:
          $role_name = '分拣员';
          break;
          case 3:
          $role_name = '打包员';
          break; 
          case 4:
          $role_name = '签收员'; 
          break; 
          case 5:
          $role_name = '仓管员'; 
          break; 
          default:
          $role_name = '未知角色';
          break;  
        }
        $userRole['role_name'] = $role_name;
        $userRole['role_type'] = $userInfo['user_type'];
        $this->userRole = $userRole;
    }
    
     // 扫码查件
    public function searchPackInStorage(){
      // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      $code = $this->postData('code')[0];
      $map[] = ['is_delete','=',0];
      $map[] = ['storage_id','=',$clerk['shop_id']];
      $map[] = ['express_num','=',$code]; 
      $res = (new Package())->setQuery($map)->with(['storage','shelfunititem.shelfunit.shelf','member'])->find();
      if (!$res){
        return $this->renderError('包裹未查询到');
      }
      // 找到此用户所有的货位
      if($res['member_id']!=0){
          $result = (new shelfunit())->with(['shelf'])->where('user_id',$res['member_id'])->select();
          $res['shelfunit'] = $result;
      }
      return $this->renderSuccess($res);
    }
    
    
    public function getShelfList(){
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $result = (new shelf())->select();
        return $this->renderSuccess($result);
    }
    
     //获取货位列表
    public function getShelfUnitList(){
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $param = $this->request->param();
        $result = (new shelfunit())->with(['user'])->where('shelf_id',$param['id'])->select();
        return $this->renderSuccess($result);
    }
    
    /**
     * 获取用户可用包裹列表（已入库且未加入其他订单）
     * @return array
     */
    public function getAvailablePackages(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        
        $params = $this->postData();
        $member_id = isset($params['member_id']) ? $params['member_id'] : 0;
        $keyword = isset($params['keyword']) ? $params['keyword'] : '';
        
        if (empty($member_id)) {
            return $this->renderError('用户ID不能为空');
        }
        
        // 查询条件：该用户的包裹，已入库状态(status=2,3,4)，未加入其他订单(order_id=0或null)
        $query = (new Package())
            ->where('member_id', $member_id)
            ->where('storage_id', $clerk['shop_id'])
            ->where('is_delete', 0)
            ->whereIn('status', [2, 3, 4]) // 已入库状态
            ->where(function($q){
                $q->where('inpack_id', 0)->whereOr('inpack_id', null);
            });
        
        // 关键词搜索
        if (!empty($keyword)) {
            $query->where('express_num', 'like', '%' . $keyword . '%');
        }
        
        $list = $query->with(['shelfunititem.shelfunit.shelf', 'packageimage'])
            ->order('entering_warehouse_time DESC')
            ->select();
        
        return $this->renderSuccess($list);
    }
    
    /**
     * 将包裹添加到订单
     * @return array
     */
    public function addPackagesToOrder(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        
        $params = $this->postData();
        $inpack_id = isset($params['order_id']) ? $params['order_id'] : 0;
        $package_ids = isset($params['package_ids']) ? $params['package_ids'] : '';
        
        if (empty($inpack_id)) {
            return $this->renderError('订单ID不能为空');
        }
        
        if (empty($package_ids)) {
            return $this->renderError('请选择要添加的包裹');
        }
        
        // 检查订单是否存在
        $order = (new Inpack())->find($inpack_id);
        if (!$order) {
            return $this->renderError('订单不存在');
        }
        
        // 将包裹ID字符串转为数组
        $packageIdArr = explode(',', $package_ids);
        
        // 开启事务
        Db::startTrans();
        try {
            // 更新包裹的订单ID
            $updateData = [
                'inpack_id' => $inpack_id,
                'updated_time' => getTime()
            ];
            
            (new Package())->whereIn('id', $packageIdArr)->update($updateData);
            
            // 更新订单的包裹ID列表
            $existingPackIds = $order['pack_ids'] ? explode(',', $order['pack_ids']) : [];
            $newPackIds = array_unique(array_merge($existingPackIds, $packageIdArr));
            
            (new Inpack())->where('id', $inpack_id)->update([
                'pack_ids' => implode(',', $newPackIds),
                'updated_time' => getTime()
            ]);
            
            // 记录物流信息
            foreach ($packageIdArr as $pid) {
                Logistics::add2($pid, '包裹已添加到订单', $clerk['clerk_id']);
            }
            
            Db::commit();
            return $this->renderSuccess('添加成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->renderError('添加失败：' . $e->getMessage());
        }
    }
    
    //绑定货位跟用户
     public function bindShelfUser(){
        $param = $this->request->param(); 
        $result = (new shelfunit())->where('shelf_unit_id',$param['shelf_unit_id'])->find();
        if(empty($result)){
            return $this->renderError('此货位不存在');
        }
        $values = SettingModel::getItem('store');
        if($values['usercode_mode']['is_show']==1){
            $userdetail = (new UserModel())->where('user_code', '=',$param['user_id'])->where('is_delete',0)->find();
        }else{
            $userdetail = UserModel::detail($param['user_id'],$with=[]);
        }
        if(empty($userdetail)){
            return $this->renderError('此用户不存在');
        }
        $result->save(['user_id'=>$userdetail['user_id']]);
        return $this->renderSuccess("货位绑定成功");
     }
     
     //  清空货架
     public function clearShelf(){
        $param = $this->request->param(); 
        $result = (new shelfunit())->where('shelf_unit_id',$param['shelf_unit_id'])->find();
        if(empty($result)){
            return $this->renderError('此货位不存在');
        }
        $ress = $result->save(['user_id'=>0]);
        $res = (new ShelfUnitItem())->where('shelf_unit_id',$param['shelf_unit_id'])->delete();
        if(!$res && !$ress){
            return $this->renderError('清除失败');
        }
        
        return $this->renderSuccess("清除成功");
     }
    
    //获取用户唛头
    public function getusermark(){
        $param  = $this->request->param();
        $UserMark = new UserMark;
        $list['user'] = UserModel::detail($param['user_id'],$with=[]);
        $list = $UserMark->getList($param['user_id']);
        return $this->renderSuccess($list);
    }
    
        /**
     * 仓库管理员 待认领
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function packageclaimlist(){
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $params = $this->request->param();
        $PackageClaim = new PackageClaim();
        if(!empty($params['keyword'])){
            $list = $PackageClaim->alias('a')->with(['user','package'])
            ->join('user u', 'a.user_id = u.user_id',"LEFT")
            ->join('package pa', 'pa.id = a.package_id',"LEFT")
            ->where('pa.express_num|u.user_code|u.user_id|u.nickName','like','%'.$params['keyword'].'%')
            ->where('a.status',0)
            ->select();
        }else{
            $list = $PackageClaim->with(['user','package'])->where('status',0)->select();
        }
        return $this->renderSuccess($list);
    }
    
    /**
     * 待认领任务列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function doclaim(){
        $packageModel = new PackageClaim();
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $map = $this->request->param();
        $detail = $packageModel->where('id',$map['id'])->find();
       
        if($map['status']=='1'){
            $res= (new Package())->where('id',$detail['package_id'])->update([
                'member_id'=>$detail['user_id'],
                'is_take'=>2,
                'updated_time'=>getTime()
            ]);
        }
        if(!empty($detail)){
            $detail->save([
                'status'=>$map['status'],
                'clerk_remark'=>$map['remark'],
                'clerk_name'=>$clerk['real_name'],
                'clerk_time'=>time(),
            ]);
            return $this->renderSuccess("处理成功");
        }
        return $this->renderError('认领任务不存在');
    }
    
    // 仓库打包员 打包列表
    public function inpack(){
         // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        $status_map = [
            2 => 1,
            1 => [1,2]
        ];
        $status = isset($this->postData('status')[0])?$this->postData('status')[0]:0;
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $keyword = $this->postData("keyword")[0];
        $map['storage_id'] = $clerk['shop_id'];
        $map['status'] = $status_map[$status]; 
        $inpack = (new Inpack());
        if ($keyword){
            $inpack ->where(function($query)use($keyword){
                $query->whereOr('member_id','=',$keyword);
            });
        }
        $data = $inpack->with('member')->where($map)->order(['created_time DESC'])->paginate(15); 
        foreach($data as $k => $v){
            $data[$k]['num'] = count(explode(',',$v['pack_ids']));
        }  
       
        return $this->renderSuccess($data);
    }
    
    //根据订单编号搜订单
    public function searchInpack(){
        $param = $this->request->param();
        $inpack = (new Inpack());
        $result = $inpack->where('order_sn',$param['code'])->where('is_delete',0)->find();
        if(!empty($result)){
            return $this->renderSuccess($result);
        }
        return $this->renderError('查不到订单');
    }
    
    
    //保存集运单签收图片
    public function saveOrderImage(){
        $data = $this->postData();
        $indata = $data;
        unset($indata['id']);
        unset($indata['token']);
        unset($indata['imageIds']);
        $inpack = new Inpack();
       
        //更新集运单图片
        $resimg = $resdeleteimg = true;
        $dataimg = [
                'inpack_id' =>$data['id'],
                'wxapp_id' =>  \request()->get('wxapp_id'),
        ];
        if(!empty($data['imageIds'])){
            foreach ($data['imageIds'] as $key => $value){
              $dataimg['image_id'] = $value;
              $resimg  = (new InpackImage())->save($dataimg);
            }
            $indata['is_focus_image'] = 1;
            $res = $inpack->where('id',$data['id'])->update($indata);
        }


        if($res ||  $resimg){
            return $this->renderSuccess('编辑成功');
        }
        return $this->renderError('更新失败');
    }
    
    // 海外入库员 - 入库校验
    public function checkzPack(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('storage')->find();
        if (!$clerk){
           return $this->renderError('角色权限非法');
        }
    
        $post = $this->postData('code')[0];
        //特殊处理京东单号
        //处理USPS单号问题
        //处理FEDEX单号问题
        $map[] = ['is_delete','=',0];
        $map[] = ['status','=',6];
        $map[] = ['order_sn','=',$post]; 
        $res = (new Inpack())
            ->setQuery($map)
            ->field('id,order_sn,member_id,line_id,storage_id,status,width,height,weight,length,remark')
            ->with(['storage','line'])
            ->find();
        //当查询不到包裹时，尝试查询是否是集运单的国际单号；
// dump($res);die;
        if(empty($res)){
            $Inpack = new Inpack();
            $maps['is_delete'] =0;
            $maps['status'] = 6;
            $maps['t_order_sn'] = $post; 
            $inpackres = $Inpack->where($maps)->with('storage')->find();
            if(!empty($inpackres)){
                $where = ['user_id'=>$inpackres['member_id']];
                $userdata = UserModel::detail($where,$with=[]);
                if(!empty($userdata)){
                   $inpackres['is_pre'] =20; //是集运单号
                   $inpackres['user_code'] =  $userdata['user_code'];
                   return $this->renderSuccess($inpackres);
                }else{
                    
                    $clerk['is_pre'] = 0; //是包裹单号
                    return $this->renderSuccessPlus($clerk,'单号状态不不存在或未到货1');
                }
            }else{
                return $this->renderSuccessPlus($clerk,'单号状态不不存在或未到货2');
            }
        }
        $where = ['user_id'=>$res['member_id']];
        $userdata = UserModel::detail($where,$with=[]);
        !empty($userdata) && $res['user_code'] =  $userdata['user_code'];
        if ($res){
          $res['is_pre'] = 10; //是包裹单号
          return $this->renderSuccess($res);
        }
        return $this->renderSuccessPlus($clerk);
    }
    
     
    /**
     * 仓管端直邮入库
     * @param Array
     * @return bool
     * @throws \think\exception\DbException
     */
    public function inzyStoragePlus() {
        // 员工信息
       $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('shop')->find();
       if (!$clerk){return $this->renderError('角色权限非法');}
       $Inpack = new Inpack();
       $id = $this->postData('id')[0];
       if (!$id){
             return $this->renderError('单号不存在');
        }
       $user_id = $this->postData('user_id')[0];
       $user_code = $this->postData('user_Code')[0];
       $express_num = $this->postData('express_num')[0];
       
       $weight = $this->postData('weight')[0];
       $height = $this->postData('height')[0];
       $length = $this->postData('length')[0];
       $width = $this->postData('width')[0];
       
       $imageIds = $this->postData('imageIds');
       $wxapp_id = \request()->get('wxapp_id');
       $remark = $this->postData('remark')[0];
       $shelf_unit = $this->postData('shelf_unit')[0];
       //1、当是集运订单的单号时，存储当前的入库仓库id，使用字段shop_id
       
        $packData = $Inpack::detail($id);

        $userInfo = $this->user;
        
        $order['status'] = 8;
        $order['shop_id'] = $clerk['shop_id'];
        if ($user_id){
            $order['member_id'] = $user_id;
        }
        $order['weight'] = $weight;
        
        $order['source'] = 8;
        $order['length'] = $length;
        $order['width'] = $width;
        $order['height'] = $height;
        $order['updated_time'] = getTime();
        $order['shoprk_time'] = getTime();
        $order['remark'] = $remark;
        $order['exceed_date'] = 0;
        $restwo = $packData->save($order); //获取到包裹的id
        if (!$restwo){
             return $this->renderError('包裹入库失败');
        }
        //存储上传的图片
        $this->inorderImages($packData['id'],$imageIds,$wxapp_id);
        $settingdata  = SettingModel::getItem('keeper',$wxapp_id);
        if(isset($settingdata['shopkeeper']) && $settingdata['shopkeeper']['is_auto_free_reach']==1){
            getpackfree($id,[]);   
        }
        //存入货架信息
        $takecode = rand(100000,999999);
        if(!empty($shelf_unit)){
            $this->saveToShelf($packData['id'],$userInfo['user_id'],$packData['t_order_sn'],$shelf_unit);
            $takecode =$this->takecode($id);
        }

        $logis['code'] = $express_num;
        $logis['logistics_describe']='包裹已到达'.$clerk['shop']['shop_name'].'取货码：'.$takecode;
        // dump($clerk['shop']['shop_name']);die;
        $Inpack->UpdateShop($id,$clerk['shop_id'],$takecode);
        //2、添加一条入库记录； 
        Logistics::addInpackGetLog($id,'包裹已到达'.$clerk['shop']['shop_name'].'请及时前来取货',$express_num,$clerk['clerk_id']);
        //3、发送模板消息到货并通知用户取货；
        $data['id'] = $packData['id'];
        $data['order_sn'] = $packData['order_sn'];
        $data['order'] = $packData;
        $data['order']['total_free'] = $packData['free'];
        $data['order']['userName'] = $userInfo['nickName'];
        $data['order_type'] = 10;
        $data['order']['remark'] = $logis['logistics_describe'];
        $data['t_order_sn'] =  $packData['t_order_sn']; //运单号
        $data['wxapp_id'] =  $packData['wxapp_id']; 
        $data['shop_id'] =  $packData['shop_id']; 
        $data['shoprk_time'] =  $packData['shoprk_time'];
        $data['member_id'] = $packData['member_id'];
        
        $tplmsgsetting = SettingModel::getItem('tplMsg');
        if($tplmsgsetting['is_oldtps']==1){
            $resss = Message::send('order.payment',$data);
        }else{
             Message::send('package.toshop',$data);
        }
        //4、发送邮件通知
        !empty($userInfo['email']) && (new Email())->sendemail($userInfo,$logis,$type=1);
        return $this->renderSuccess('集运单入库成功');    
    }
 
    /**
     * $id 包裹的id
     * $imageIds 图片数组
     */
    public function inorderImages($id,$imageIds,$wxapp_id){
 
        $InpackImage =  new InpackImage();
        if(isset($imageIds) && count($imageIds)>0){
                foreach ($imageIds as $key =>$val){
                    //校验图片是否又重复的
                     $result = $InpackImage->where('inpack_id',$id)->where('image_id',$val)->find();
                     if(empty($result)){
                         $update['inpack_id'] = $id;
                         $update['image_id'] = $val;
                         $update['wxapp_id'] =$wxapp_id;
                         $update['create_time'] = strtotime(getTime());
                         $resthen= $InpackImage->save($update);
                         if(!$resthen){
                              return false;
                         }
                     }
                }
            }    
        return true;
    } 
    
    //包裹批量上架
    public function updatetoshelf(){
       $param = $this->postData();
       $this->user = $this->getUser();
       $shelfdata =['shelf_unit' => $param['shelf_unit_id']];
       $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
       // 查询货位信息
       $shelfunitresult = (new shelfunit())->where('shelf_unit_id',$param['shelf_unit_id'])->find();
       if(empty($shelfunitresult)){
            return $this->renderError('货位不存在');
       }
       foreach ($param['code'] as $key=>$val){
           $shelfdata['express_num'] = $val;
           $package = (new Package())->where(['express_num'=>$val,'is_delete'=>0])->find();
           
           if(!empty($package)){
                $shelfdata['pack_id'] = $package['id'];
                $shelfdata['user_id'] = $package['member_id'];
                $result = (new ShelfUnitItem())->where('pack_id',$shelfdata['pack_id'])->find();
                if(!empty($result)){
                    $result->post($shelfdata);
                    // 如果需要绑定专属，就绑定
                    if(isset($param['bind_exclusive_location']) && $param['bind_exclusive_location']==1 && $package['member_id']>0 && $shelfunitresult['user_id']==0){
                        $shelfunitresult->save(['user_id'=>$package['member_id']]);
                    }
                }else{
                    (new ShelfUnitItem())->post($shelfdata);
                    // 如果需要绑定专属，就绑定
                    if(isset($param['bind_exclusive_location']) && $param['bind_exclusive_location']==1 && $package['member_id']>0 && $shelfunitresult['user_id']==0){
                        $shelfunitresult->save(['user_id'=>$package['member_id']]);
                    }
                }
                // 更新包裹状态 - 添加错误处理
                $saveResult = (new Package())->where(['express_num'=>$val,'is_delete'=>0])->update([
                    'is_shelf' => 1,
                    'updated_time' =>getTime()
                ]);
               
                if($saveResult === 0){
                    // 记录日志或返回错误信息
                    return $this->renderError('包裹状态更新失败: ' . $val);
                }
                
           }else{
               $settingkeeper  = SettingModel::getItem('keeper');
               if($settingkeeper['shopkeeper']['is_sacn_shelf']==1){
                   //查询不到就直接入库；
                    $update = [
                      'order_sn' => createSn(),
                      'status' => 2,
                      'storage_id' =>$clerk['shop_id'],
                      'express_num' =>$val,
                      'updated_time'=>getTime(),
                      'created_time'=>getTime(),
                      'entering_warehouse_time'=>getTime(),
                      'wxapp_id' =>  \request()->get('wxapp_id'),
                      'is_shelf' => 1,
                    ];
                    $pid =(new Package())->insertGetId($update);
                    $shelfdata['pack_id'] = $pid;
                    $shelfdata['user_id'] = '';
                    (new ShelfUnitItem())->post($shelfdata);
               }else{
                    return $this->renderError('单号'.$val.'未入库');
               }
          }
       }
        return $this->renderSuccess("上架成功");
    }


    // 仓库打包员 打包列表
    public function inpackTotal(){
       // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      $packModel = (new Inpack());
      $res = [
         'no' => $packModel ->where(['status'=>1,'storage_id'=>$clerk['shop_id']])->count(),
         'in' => $packModel -> where(['status'=>2,'storage_id'=>$clerk['shop_id']])->count()
      ];
      return $this->renderSuccess($res);
  }

    // 打包详情
    public function inpackDetails(){
          $id = $this->postData('id')[0];
          $data = (new Inpack())->alias('p')->with('member')->find($id);
          $item = explode(',',$data['pack_ids']);
          if ($data['address_id']){
              $data['address'] = (new UserAddress())->find($data['address_id']);
          }
          if ($data['line_id']){
              $data['line'] = (new Line())->field('name')->find($data['line_id']);
          }
          $pack =  (new Package())->whereIn('id',$item)->with(['country','packitem','packageimage.file'])->select();
          $pack = $this->getPackItemList($pack);
          $data['item'] = $pack;
          return $this->renderSuccess($data);
    }
    
    
    //编辑集运单
    public function editOrder(){
        $data = $this->postData();
        $indata = $data;
        unset($indata['id']);
        unset($indata['token']);
        unset($indata['imageIds']);
        unset($indata['deleteIds']);
        unset($indata['vwimageIds']);
        unset($indata['vwdeleteIds']);
        unset($indata['consumables']);
        unset($indata['boxes']);
        $inpack = new Inpack();
        $settingkeeper  = SettingModel::getItem('keeper');
        if($settingkeeper['shopkeeper']['is_rfid']==1 && !empty($data['rfid_id'])){
             $rfidses = $inpack->where('rfid_id',$data['rfid_id'])->where('id','<>',$data['id'])->find();
             if($rfidses){
                 return $this->renderError('RFID已被使用，请更换RFID');
             }
        }
        $res = $inpack->where('id',$data['id'])->update($indata);
        //更新集运单图片
        $resimg = $resdeleteimg = true;
        $dataimg = [
            'inpack_id' =>$data['id'],
            'wxapp_id' =>  \request()->get('wxapp_id'),
        ];
        if(!empty($data['imageIds'])){
            foreach ($data['imageIds'] as $key => $value){
              $dataimg['image_id'] = $value;
              $dataimg['image_type'] = 10;
              $resimg  = (new InpackImage())->save($dataimg);
            }
        }
        if(!empty($data['vwimageIds'])){
            foreach ($data['vwimageIds'] as $key => $value){
              $dataimg['image_id'] = $value;
              $dataimg['image_type'] = 20;
              $resimg  = (new InpackImage())->save($dataimg);
            }
        }
        //删除集运单图片
        if(!empty($data['deleteIds'])){
            foreach ($data['deleteIds'] as $key => $value){
              $resdeleteimg  = (new InpackImage())->where('image_type',10)->where('id',$value)->delete();
            }
        }
        if(!empty($data['vwdeleteIds'])){
            foreach ($data['vwdeleteIds'] as $key => $value){
              $resdeleteimg  = (new InpackImage())->where('id',$value)->where('image_type',20)->delete();
            }
        }
        // 添加耗材使用记录，并减少库存
        if(!empty($data['consumables'])){
            foreach ($data['consumables'] as $key => $value){
              // 更新用户可用积分
              (new ConsumablesLog())->add([
                 'con_id'=>$value['id'],
                 'inpack_id'=>$data['id'],
                 'num'=>$value['usenum'],
                 'remark'=>"仓管端选择"
               ]);
               Consumables::detail($value['id'])->setDec('num',$value['usenum']);
            }
        }
        //完成集运单价格的计算；
        $settingdata  = SettingModel::getItem('keeper');
        if(isset($settingdata['shopkeeper']) && $settingdata['shopkeeper']['is_auto_free_edit']==1){
            $boxes = [];
            if (!empty($data['boxes'])) {
                $boxes = $data['boxes'];
            }
            getpackfree($data['id'],$boxes);   
        }
        
        //根据设置内容，判断是否需要发送通知；
        
        $packData = $inpack::detail($data['id']);
        $noticesetting = SettingModel::getItem('notice',$packData['wxapp_id']);
        $tplmsgsetting = SettingModel::getItem('tplMsg',$packData['wxapp_id']);
        
        // 获取费用审核设置，判断是否需要审核后才发送支付通知
        $adminstyle = SettingModel::getItem('adminstyle', $packData['wxapp_id']);
        $is_verify_free = isset($adminstyle['is_verify_free']) ? $adminstyle['is_verify_free'] : 0;
        $canSendPayOrder = true; // 是否可以发送支付通知
        
        // 如果开启了费用审核，需要检查是否已审核
        if($is_verify_free == 1) {
            $is_doublecheck = isset($packData['is_doublecheck']) ? $packData['is_doublecheck'] : 0;
            $canSendPayOrder = ($is_doublecheck == 1); // 只有已审核才能发送
        }
        
        $userData = (new UserModel)->where('user_id',$packData['member_id'])->find();
        $packData['userName']=$userData['nickName'];
        $packData['order'] = $packData;
        $packData['order_type'] = 10;
        $packData['remark']= $noticesetting['check']['describe'];
        $packData['total_free'] = $packData['total_free'];
        if($tplmsgsetting['is_oldtps']==1){
          //发送旧版本订阅消息以及模板消息
          Message::send('order.payment',$packData);
        }else{
          //发送新版本订阅消息以及模板消息
          // 只有满足条件时才发送支付通知
          if($canSendPayOrder) {
              Message::send('package.payorder',$packData);
          }
        }
        //发送邮件通知
        if(isset($packData['member_id']) || !empty($packData['member_id'])){
            $EmailUser = UserModel::detail($packData['member_id']);
            $EmailData['code'] = $packData['id'];
            $EmailData['logistics_describe']=$noticesetting['check']['describe'];
            (new Email())->sendemail($EmailUser,$EmailData,$type=1);
        }
        
        if($res || $resimg || $resdeleteimg){
            return $this->renderSuccess('编辑成功');
        }
        return $this->renderError('更新失败');
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

    // 仓库分拣员 - 扫货架码
    public function getShelf(){
        $code = $this->postData('code')[0];
        
        $shelfUnit = (new ShelfUnit())->where(['shelf_unit_code'=>$code])->find();
        // dump($shelfUnit);die;
        // $shelfUnit['shelf_unit_code'] = (new ShelfUnit())->getShelfUnit($shelfUnit['shelf_id']);
        if (!$shelfUnit){
          return $this->renderError('未查询到货架');
        }
        return $this->renderSuccess($shelfUnit);
    }
    
    
    /**
     * 仓管查询包裹
     * @param int $payType
     * @return bool
     * @throws \think\exception\DbException
     */
    public function searchPack(){
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        
        $code = $this->postData()['search'];
        $type = $this->postData()['type'];
        $Inpack = new Inpack();
        $Package = new Package();
        //搜索
        $pack = [];$i=0;
        if($type==1){
            //查询是否是手机尾号，用户id，用户code，取货码
            $UserModel = new UserModel();
            $UserAddress = new UserAddress();
            $userData = $UserModel->where('user_id|user_code|mobile','like','%'.$code.'%')->field('user_id')->select()->toArray();
            $userData2 = $UserAddress->field('user_id')->where('phone','like','%'.$code.'%')->select()->toArray();
            $userCon = array_merge($userData,$userData2);
            $userArr =[];
            if(count($userCon)>0){
                foreach ($userCon as $k => $v){
                    $userArr[$k] = $v['user_id'];
                }
            }
            $usernewArr= array_unique($userArr);
            foreach ($usernewArr as $key => $val){
                //查询出所有的shop_id为仓管所在仓库的集运单；
               $rest= $Inpack->where('member_id',$val)
                ->where('shop_id',$clerk['shop_id'])
                ->where('status','<',8)
                ->where('is_delete',0)
                ->with(['Member','address','service','shelfunititem.shelfunit.shelf'])
                ->select();
               if(count($rest)>0){
                   $pack[$i] = $rest;
                   $i += 1;
               }
            }
         
            $packs = $Inpack->where('is_delete',0)
            ->where('take_code|order_sn|t_order_sn',$code)
            ->where('shop_id',$clerk['shop_id'])
            ->where('status','<',8)
            ->with(['Member','address','service','shelfunititem.shelfunit.shelf'])
            ->select();
           
            if(count($packs)>0){
                $pack[0] = $packs;
            }
            return $this->renderSuccess($pack);
        }
        
        //扫码签收
        if($type==2){
            $pack[$i] = $Inpack->where('is_delete',0)
                ->where('shop_id',$clerk['shop_id'])
                ->where('status','<',8)->where('order_sn|t_order_sn',$code)
                ->with(['Member','address','service','shelfunititem.shelfunit.shelf'])
                ->select();
            return $this->renderSuccess($pack); 
        }
    }
    
    /**
     * 仓管查询包裹
     * @param int $payType
     * @return bool
     * @throws \think\exception\DbException
     */
    public function directsearchPack(){
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        
        $code = $this->postData()['search'];
        $type = $this->postData()['type'];

        $Package = new Package();
        //搜索
        $pack = [];$i=0;
        if($type==1){
            //查询是否是手机尾号，用户id，用户code，取货码
            $UserModel = new UserModel();
            $UserAddress = new UserAddress();
            $userData = $UserModel->where('user_id|user_code|mobile','like','%'.$code.'%')->field('user_id')->select()->toArray();
         
            $userData2 = $UserAddress->field('user_id,region')->where('phone','like','%'.$code.'%')->select()->toArray();
            $userCon = array_merge($userData,$userData2);
            $userArr =[];
            if(count($userCon)>0){
                foreach ($userCon as $k => $v){
                    $userArr[$k] = $v['user_id'];
                }
            }
            $usernewArr= array_unique($userArr);
            foreach ($usernewArr as $key => $val){
                //查询出所有的shop_id为仓管所在仓库的集运单；
               $rest= $Package->where('member_id',$val)
               ->where('storage_id',$clerk['shop_id'])
               ->where('status','<',10)
               ->where('is_delete',0)
               ->with(['Member','address','shelfunititem.shelfunit.shelf'])
               ->select();
               if(count($rest)>0){
                   $pack[$i] = $rest;
                   $i += 1;
               }
            }
         
            $packs = $Package->where('express_num',$code)
            ->where('storage_id',$clerk['shop_id'])
            ->where('status','<',10)
            ->where('is_delete',0)
            ->with(['Member','address','shelfunititem.shelfunit.shelf'])
            ->select();
            
            if(count($packs)>0){
                $pack[0] = $packs;
            }
            return $this->renderSuccess($pack);
        }
        //扫码签收
        if($type==2){
            $pack[$i] = $Package->where('storage_id',$clerk['shop_id'])
            ->where('status','<',10)
            ->where('is_delete',0)
            ->where('express_num',$code)
            ->with(['Member','address','shelfunititem.shelfunit.shelf'])
            ->select();
            return $this->renderSuccess($pack); 
        }
    }
    
     /**
     * 仓管签收包裹
     * @param int $payType
     * @return bool
     * @throws \think\exception\DbException
     */
    public function changeStatusByAdmin(){
        $id = $this->postData()['id'];
        if(!$id){
             return $this->renderError('参数错误');
        }
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('shop')->find();
        $Inpack = new Inpack();
        $res = $Inpack->where('id',$id)->update(['status'=> 8,'receipt_time'=>getTime()]);
        $packData = $Inpack::detail($id);
        //进行模板消息，邮件通知
        $userInfo = $this->user;
        $logis['code'] = $packData['t_order_sn'];
        $logis['logistics_describe']='包裹已签收，感谢您的支持';
        //2、添加一条入库记录； 
        Logistics::addInpackGetLog2($id,'包裹已签收，感谢您的支持',$packData['t_order_sn'],$clerk['clerk_id']);
        //3、发送模板消息到货并通知用户取货；
        $data['t_order_sn'] = $packData['order_sn'];
        $data['order'] = $packData;
        $data['order']['total_free'] = $packData['free'];
        $data['order']['userName'] = $userInfo['nickName'];
        $data['order_type'] = 10;
        $data['order']['remark'] = $logis['logistics_describe'];
        //
        $resss = Message::send('order.payment',$data);  
        //4、发送邮件通知
        !empty($userInfo['email']) && (new Email())->sendemail($userInfo,$logis,$type=1);
        //5、清空货架数据
        (new ShelfUnitItem())->where(['pack_id'=>$packData['order_sn']])->delete();
        //6、发送积分
        $setting = SettingModel::getItem('points');
        if($setting['is_open']==1 && $setting['is_logistics_gift']==1){
            if($setting['is_logistics_area']==20 && $userInfo['grade_id']>0){
                $giftpoint = floor($packData['real_payment']*$setting['logistics_gift_ratio']/100);
            }else if($setting['is_logistics_area']==10){
                $giftpoint = floor($packData['real_payment']*$setting['logistics_gift_ratio']/100);
            }
        }

        $userInfo->setInc('points',$giftpoint);
        // 新增积分变动记录
        PointsLogModel::add([
            'user_id' => $packData['member_id'],
            'value' => $giftpoint,
            'type' => 1,
            'describe' => "订单".$packData['order_sn']."赠送积分".$giftpoint,
            'remark' => "积分来自集运订单:".$packData['order_sn'],
        ]);
        
        return $this->renderSuccess('',"操作成功");
    }
    
    
    /**
     * 直邮包裹签收包裹
     * @param int $payType
     * @return bool
     * @throws \think\exception\DbException
     */
    public function directPackageQianshou(){
        $id = $this->postData()['id'];
        if(!$id){
             return $this->renderError('参数错误');
        }
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('shop')->find();
        $Package = new Package();
        $Inpack = new Inpack();
        $res = $Package->where('id',$id)->update(['status'=> 10,'receipt_time'=>getTime()]);
        $packData = $Package->getDetails($id,"*");
        if(!empty($packData['inpack_id'])){
            $Inpack->where('id',$packData['inpack_id'])->update(['status'=>8,'receipt_time'=>getTime()]);
        }
        //进行模板消息，邮件通知
        $userInfo = $this->user;
        $logis['code'] = $packData['express_num'];
        $logis['logistics_describe']='包裹已签收，感谢您的支持';
        //2、添加一条入库记录； 
        Logistics::add2($id,'包裹已签收，感谢您的支持',$packData['express_num'],$clerk['clerk_id']);
        //3、发送模板消息到货并通知用户取货；
        $data['order_sn'] = $packData['order_sn'];
        $data['order'] = $packData;
        $data['order']['t_order_sn'] =$packData['express_num'];
        $data['order']['total_free'] = $packData['free'];
        $data['order']['userName'] = $userInfo['nickName'];
        $data['order_type'] = 10;
        $data['order']['remark'] = $logis['logistics_describe'];
        $resss = Message::send('order.payment',$data);  
        //4、发送邮件通知
        !empty($userInfo['email']) && (new Email())->sendemail($userInfo,$logis,$type=1);
        //5、清空货架数据
        (new ShelfUnitItem())->where(['express_num'=>$packData['express_num']])->delete();
        return $this->renderSuccess('',"操作成功");
    }
     
    // 仓管员 - 入库校验
    public function checkPack(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('storage')->find();
        if (!$clerk){
           return $this->renderError('角色权限非法');
        }
    
        $post = $this->postData('code')[0];
        //特殊处理京东单号
       
        $map[] = ['is_delete','=',0];
        $map[] = ['express_num','=',$post]; 
        $res = (new Package())
            ->append(['ismorepack'])
            ->setQuery($map)
            ->field('id,express_num,order_sn,member_id,storage_id,status,width,height,weight,length,remark,admin_remark')
            ->with(['storage','packageimage.filepackage'])
            ->find();
        if ($res) {  // 确保 $res 不是 null
            $res->ismorepack = 0;  // 如果是对象
            // 或者 $res['ismorepack'] = 0; 如果是数组
        }
      
        //当查询不到包裹时，尝试查询是否是集运单的国际单号；
        //当查询是JD包裹时，可以用原来的单号再查询一遍
        $tyoi = stripos($post, "JD");
      
        if(empty($res) && $tyoi==0){
            $ex = explode('-',$post);
            $post = $ex[0];
            $maps[] = ['is_delete','=',0];
            $maps[] = ['express_num','=',$post]; 
            $res = (new Package())
                ->setQuery($maps)
                ->append(['ismorepack'])
                ->field('id,express_num,order_sn,member_id,storage_id,status,width,height,weight,length,remark,admin_remark')
                ->with(['storage','packageimage.filepackage'])
                ->find();
            if ($res) {  // 确保 $res 不是 null
                $res->ismorepack = 1;  // 如果是对象
                // 或者 $res['ismorepack'] = 0; 如果是数组
            }
                // dump($res);die;
        }
    
        if(empty($res) && !$tyoi){
      
            $Inpack = new Inpack();
            $mapss['is_delete'] =0;
            $mapss['t_order_sn'] = $post; 
                    //   dump($maps);die;
            $inpackres = $Inpack->where($mapss)->with('storage')->find();
           
            $where = ['user_id'=>$inpackres['member_id']];
            $userdata = UserModel::detail($where,$with=[]);
            if(!empty($userdata)){
               $inpackres['is_pre'] =20; //是集运单号
               $inpackres['user_code'] =  $userdata['user_code'];
               return $this->renderSuccess($inpackres);
            }else{
                
                $clerk['is_pre'] = 0; //是包裹单号
                return $this->renderSuccess($clerk);
            }
        }
     
        $where = ['user_id'=>$res['member_id']];
        $userdata = UserModel::detail($where,$with=[]);
        !empty($userdata) && $res['user_code'] =  $userdata['user_code'];
        if ($res){
          if($res['storage_id']==0){
              $res['storage'] = Shop::detail($clerk['shop_id']);
              $res['storage_id'] = $clerk['shop_id'];
          }
          $res['is_pre'] = 10; //是包裹单号
          return $this->renderSuccess($res);
        }
        return $this->renderSuccessPlus($clerk);
    }
    
        /**
     * 仓管端提交入库
     * BY FENG 2022年12月27日 
     * 代码比较乱，等后续有时间再优化
     * @param Array
     * @return bool
     * @throws \think\exception\DbException
     */
    public function editPackagePlus() {
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('shop')->find();
        if (!$clerk){return $this->renderError('角色权限非法');}
      
       $packItemModel = new PackageItemModel();
       $id = $this->postData('id')[0];
       $user_id = $this->postData('user_id')[0];
       $class_ids = $this->postData('class_ids')[0];
       $user_code = $this->postData('user_Code')[0];
       $is_pre = $this->postData('is_pre')[0];
       $express_num = $this->postData('express_num')[0];
       $weight = $this->postData('weight')[0];
       $height = $this->postData('height')[0];
       $length = $this->postData('length')[0];
       $width = $this->postData('width')[0];
       $imageIds = $this->postData('imageIds');
       $wxapp_id = \request()->get('wxapp_id');
       $remark = $this->postData('remark')[0];
       $useremark = $this->postData('usermark')[0];
       $shelf_unit = $this->postData('shelf_unit')[0];
       $shelf_id = $this->postData('shelf_id')[0];
       $goodslist = $this->postData('goodslist');
       $is_verify = $this->postData('is_verify')[0];

       //有对应数据情况下
       $data = (new Package())->find($id);
       if ($user_id){
            $user = (new UserModel())->find($user_id);
            if (!$user)
            return $this->renderError('用户不存在');
        }
              
        if($user_code){
            $users = (new UserModel())->where('user_code',$user_code)->find();
            if (!$users){
                return $this->renderError('用户不存在');}
            $user_id = $users['user_id'];
        }
       //更新包裹信息
       $update['member_id'] = !empty($user_id)?$user_id:$data['member_id'];
       $update['length'] = !empty($length)?$length:$data['length'];
       $update['height'] = !empty($height)?$height:$data['height'];
       $update['width'] = !empty($width)?$width:$data['width'];
       $update['weight'] = !empty($weight)?$weight:$data['weight'];
       $update['admin_remark'] = !empty($remark)?$remark:$data['admin_remark'];
       $update['storage_id'] = $clerk['shop_id'];
       $update['usermark'] = !empty($usermark)?$usermark:$data['usermark'];
       $update['shelf_id'] = $shelf_id;
       $update['is_take'] = !empty($update['member_id'])?2:1;
       $update['is_verify'] = $is_verify;
       $update['updated_time'] = getTime();
       if($length>0 && $width>0 && $height>0){
             $update['volume'] = $width*$length*$height/1000000;
       }
       
        $Barcode = new Barcode();
        //存储包裹的分类信息；
        $classItem = [];
       if ($class_ids || $goodslist){
            $classItem = $this->parseClass($class_ids);
            foreach ($goodslist as $k => $val){
                 $classItems[$k]['class_name'] = !empty($classItem)?$classItem[0]['name']:$val['pinming'];
                 $classItems[$k]['one_price'] = isset($val['danjia'])?$val['danjia']:'';
                 $classItems[$k]['all_price'] = (!empty($val['danjia'])?$val['danjia']:0) * (!empty($val['shuliang'])?$val['shuliang']:0);
                 $classItems[$k]['product_num'] = isset($val['shuliang'])?$val['shuliang']:'';
                 $classItems[$k]['express_num'] = $express_num;
                 $classItems[$k]['goods_name'] = isset($val['pinming'])?$val['pinming']:'';
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
       if (isset($classItems)){
             $packItemModel->where('order_id',$data['id'])->delete();
             $packItemRes = $packItemModel->saveAllData($classItems,$data['id']);
             if (!$packItemRes){
                return $this->renderError('包裹类目更新失败');
             }
        }
    
       $res = (new Package())->where(['id'=>$id])->update($update);
       if (!$res){
        return $this->renderError('包裹更新失败');
       }
       //插入图片
       $this->inImages($id,$imageIds,$wxapp_id);
       //更新日志
       Logistics::add2($id,'更新包裹信息',$clerk['clerk_id']);
       

       
        //存入货架信息
        if(!empty($shelf_unit)){
             $shelfunit = new ShelfUnitItem();
             $shelf = [
                'pack_id' => $data['id'],
                'user_id' => isset($user_id)?$user_id:$data['member_id'],
                'express_num' => $data['express_num'],
                'shelf_unit' => $shelf_unit,
             ];  
             $shelfunit->post($shelf);
         }
         return $this->renderSuccess('包裹入库成功'); 
    }
    
    /**
     * 仓管端快速入库
     * @param Array
     * @return bool
     * @throws \think\exception\DbException
     */
      public function inStorageByPda() {
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('shop')->find();
        if (!$clerk){return $this->renderError('角色权限非法');}
        $params = $this->request->param();
        
        if(count($params['code'])==0){
            return $this->renderError('请录入包裹单号');
        }
        foreach ($params['code'] as $val){
            $packdatas = (new Package())->where('express_num',$val)->where('is_delete',0)->find();
            if($packdatas){
                $packdatas->save([
                    'status'=>2,
                    'storage_id'=>$clerk['shop_id'],
                    'entering_warehouse_time'=>getTime(),
                    'updated_time'=>getTime(),
                    'wxapp_id'=>\request()->get('wxapp_id')
                ]);
            }else{
                $order = [
                    'status'=>2,
                    'source'=>8,
                    'express_num'=>$val,
                    'storage_id'=>$clerk['shop_id'],
                    'entering_warehouse_time'=>getTime(),
                    'updated_time'=>getTime(),
                    'wxapp_id'=>\request()->get('wxapp_id')
                ];  
                (new Package())->insert($order);
            }
        }
        return $this->renderSuccess('','包裹入库成功');   
      }
     
 
    /**
     * 仓管端提交入库
     * BY FENG 2022年12月27日 
     * 代码比较乱，等后续有时间再优化
     * @param Array
     * @return bool
     * @throws \think\exception\DbException
     */
    public function inStoragePlus() {
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('shop')->find();
        if (!$clerk){return $this->renderError('角色权限非法');}
      
       $packItemModel = new PackageItemModel();
       $id = $this->postData('id')[0];
       $user_id = $this->postData('user_id')[0];
       $class_ids = $this->postData('class_ids')[0];
       $user_code = $this->postData('user_Code')[0];
       $is_pre = $this->postData('is_pre')[0];
       $express_num = $this->postData('express_num')[0];
       $weight = $this->postData('weight')[0];
       $height = $this->postData('height')[0];
       $length = $this->postData('length')[0];
       $width = $this->postData('width')[0];
       $imageIds = $this->postData('imageIds');
       $wxapp_id = \request()->get('wxapp_id');
       $remark = $this->postData('remark')[0];
       $useremark = $this->postData('usermark')[0];
       $shelf_unit = $this->postData('shelf_unit')[0];
       $shelf_id = $this->postData('shelf_id')[0];
       $goodslist = $this->postData('goodslist');
       $is_verify = $this->postData('is_verify')[0];
       $is_print = $this->postData('is_print')[0];
       //再加一层校验；
        $map[] = ['is_delete','=',0];
        $map[] = ['express_num','=',$express_num];
        //对京东单号进行特殊处理
        // if(strstr($express_num,'JD')){
        //     $a = explode('-',$express_num);
        //     count($a)>1 && $express_num = $a[0];
        // }
       //再加一层校验；
        $map[] = ['is_delete','=',0];
        $map[] = ['express_num','=',$express_num]; 
        $is_exitres = (new Package())->setQuery($map)->find();
        // dump($is_exitres);die;
        if($is_exitres){
            $is_pre = 10;
        }
       //$is_pre==0 为没有查询到包裹数据的情况，需要插入新数据
       if ($is_pre==0){
           if ($user_id){
                $user = (new UserModel())->find($user_id);
                if (!$user)
                    return $this->renderError('用户不存在');
            }
              
            if($user_code){
                $users = (new UserModel())->where('user_code',$user_code)->find();
                if (!$users){
                    return $this->renderError('用户不存在');}
                $user_id = $users['user_id'];
            }
           $packdatas = (new Package())->where('express_num',$express_num)->where('is_delete',0)->find();
           if(!empty($packdatas)){
              if($length>0 && $width>0 && $height>0){
                 $volume = $width*$length*$height/1000000;
              }
              $order = [
                'status'=>2,
                'member_id'=> !empty($user_id)?$user_id:$packdatas['member_id'],
                'source'=>8,
                'express_num'=>$express_num,
                'storage_id'=>$clerk['shop_id'],
                'is_take'=>!empty($user_id)?2:$packdatas['is_take'],
                'entering_warehouse_time'=>getTime(),
                'updated_time'=>getTime(),
                'length'=>$length,
                'width'=>$width,
                'height'=>$height,
                'admin_remark'=>$remark,
                'usermark'=>$useremark,
                'shelf_id'=>$shelf_id,
                'weight'=>$weight,
                'volume'=>isset($volume)?$volume:$packdatas['volume'],
                'class_ids'=>$class_ids
              ];
              $packdatas->save($order);
              $restwo = $packdatas['id'];
           }else{
            $order['status'] = 2;
            $order['is_take'] = 1;
            $order['storage_id'] = $clerk['shop_id'];
            if ($user_id){
                $order['member_id'] = $user_id;
                $order['is_take'] = 2;
            }
            $order['order_sn'] = createSn();
            $order['weight'] = $weight;
            $order['source'] = 8;
            $order['length'] = $length;
            $order['width'] = $width;
            $order['height'] = $height;
            $order['express_num'] = $express_num;
            $order['updated_time'] = getTime();
            $order['created_time'] = getTime();
            $order['admin_remark'] = $remark;
            $order['usermark'] = $useremark;
            $order['shelf_id'] = $shelf_id;
            $order['entering_warehouse_time'] = getTime();
            $order['class_ids'] =$class_ids;
            if($length>0 && $width>0 && $height>0){
                 $order['volume'] = $width*$length*$height/1000000;
            }
            
            $restwo = (new Package())->saveData($order); //获取到包裹的id
            if (!$restwo){
                 return $this->renderError('包裹入库失败');
            }
           }
           $Barcode = new Barcode();
               //存储包裹的分类信息；
            $classItem = [];
           if ($class_ids || $goodslist){
            $classItem = $this->parseClass($class_ids);
          
            if(count($goodslist)>0 && !empty($goodslist[0]['pinming'])){
                foreach ($goodslist as $k => $val){
                     $classItems[$k]['class_name'] = !empty($classItem)?$classItem[0]['name']:$val['pinming'];
                     $classItems[$k]['class_id'] = !empty($classItem)?$classItem[0]['category_id']:0;
                     $classItems[$k]['one_price'] = isset($val['danjia'])?$val['danjia']:'';
                     $classItems[$k]['all_price'] = (!empty($val['danjia'])?$val['danjia']:0) * (!empty($val['shuliang'])?$val['shuliang']:0);
                     $classItems[$k]['product_num'] = isset($val['shuliang'])?$val['shuliang']:'';
                     $classItems[$k]['express_num'] = $express_num;
                     $classItems[$k]['goods_name'] = isset($val['pinming'])?$val['pinming']:'';
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
            }else{
                foreach ($classItem as $k => $val){
                    // dump($val);doe;
                   $classItems[$k]['class_id'] = $val['category_id'];
                   $classItems[$k]['express_name'] = "";
                   $classItems[$k]['class_name'] = $val['name'];
                   $classItems[$k]['express_num'] = $express_num;
                   $classItems[$k]['all_price'] = '';
                   unset($classItem[$k]['category_id']); 
                   unset($classItem[$k]['name']);        
             }
            }
         }
        

             if (isset($classItems)){
                 $packItemRes = $packItemModel->saveAllData($classItems,$restwo);
                 if (!$packItemRes){
                    return $this->renderError('包裹类目更新失败');
                 }
             }
            //存储上传的图片
            $this->inImages($restwo,$imageIds,$wxapp_id);
            $shopData =  (new Shop())->where('shop_id',$clerk['shop_id'])->find();
            //存入货架信息
            if(!empty($shelf_unit)){
                 $shelfunit = new ShelfUnitItem();
                 $shelf = [
                    'pack_id' => $restwo,
                    'user_id' => $user_id,
                    'express_num' => $express_num,
                    'shelf_unit' => $shelf_unit,
                 ];  
                 $shelfunit->post($shelf);
            }else{
                $this->fenpeihuowei($user_id,$restwo,$express_num,$clerk['wxapp_id']);
            }
             
            //邮件通知
            //判断是否有用户id，发送邮件
              if(isset($user_id) || !empty($user_id)){
                  $EmailUser = UserCommonModel::detail($user_id);
// dump($order);die;
                  if($EmailUser['email']){
                      $EmailData['code'] = $order['express_num'];
                      $EmailData['logistics_describe']='包裹已入库，可提交打包';
                     (new Email())->sendemail($EmailUser,$EmailData,$type=1);
                  }
                    $data['order'] = [];
                    //发送订阅消息以及模板消息
                    //发送订阅消息，模板消息
                    $order['id'] = $restwo;
                    $order['wxapp_id'] = $wxapp_id;
                    $order['shop_name'] = $shopData['shop_name'];
                    $data['order'] = $order;
                    $data['order']['member_name'] = $EmailUser['nickName'];
                    $data['order_type'] = 10;
                    $data['order']['remark'] ="包裹已入库，可提交打包" ;
                    if($user_id!=0){
                        $tplmsgsetting = SettingModel::getItem('tplMsg');
                        if($tplmsgsetting['is_oldtps']==1){
                          //发送旧版本订阅消息以及模板消息
                          $sub = (new Package())->sendEnterMessage([$order]);
                        }else{
                          //发送新版本订阅消息以及模板消息
                          Message::send('package.inwarehouse',$order);
                        }
                    }
              }
            //判断是否打印标签$is_print
            $packagePrintData = (new Package())->where(['id'=>$restwo])->find();
            $printerConfig = SettingModel::getItem('printer',$wxapp_id);
            if($is_print==1 && $printerConfig['is_open']==1 && $printerConfig['printer_id']>0 && count($printerConfig['printsite'])>0){
                 (new Printer())->printTicket($packagePrintData,10);
            }
            Logistics::add2($restwo,'仓管员手动入库',$clerk['clerk_id']);
            return $this->renderSuccess('包裹入库成功');
          
       }elseif($is_pre==10){
       //有对应数据情况下
       if(empty($id)){
           return $this->renderError('包裹ID不能为空');
       }
       $data = (new Package())->find($id);
       if(empty($data)){
           return $this->renderError('包裹不存在');
       }
    //   if ($data['status']==2){
    //      return $this->renderError('包裹已入库');
    //   }
       if ($user_id){
            $user = (new UserModel())->find($user_id);
            if (!$user)
            return $this->renderError('用户不存在');
        }
              
        if($user_code){
            $users = (new UserModel())->where('user_code',$user_code)->find();
            if (!$users){
                return $this->renderError('用户不存在');}
            $user_id = $users['user_id'];
        }
       //更新包裹信息
       $update['member_id'] = !empty($user_id)?$user_id:$data['member_id'];
       $update['is_take'] = !empty($user_id)?2:$data['is_take'];
       $update['length'] = !empty($length)?$length:$data['length'];
       $update['height'] = !empty($height)?$height:$data['height'];
       $update['width'] = !empty($width)?$width:$data['width'];
       $update['weight'] = !empty($weight)?$weight:$data['weight'];
       $update['admin_remark'] = !empty($remark)?$remark:$data['admin_remark'];
       $update['storage_id'] = $clerk['shop_id'];
       $update['status'] = 2;
       $update['usermark'] = !empty($usermark)?$usermark:$data['usermark'];
       $update['shelf_id'] = $shelf_id;
       $update['is_verify'] = $is_verify;
       $update['class_ids'] = $class_ids;
       $update['updated_time'] = getTime();
       $update['entering_warehouse_time'] = getTime();
       if($length>0 && $width>0 && $height>0){
             $update['volume'] = $width*$length*$height/1000000;
       }
       
    //   dump($update);die;
       $res = (new Package())->where(['id'=>$id])->update($update);
       //插入图片
       // 使用 $data['id'] 确保 package_id 不为空
       $packageId = !empty($data['id']) ? $data['id'] : $id;
       log_write("插入图片前获取的=".$id.'，id='.$data['id']);
       if(!empty($packageId)){
           $this->inImages($packageId,$imageIds,$wxapp_id);
       }
       //更新日志
       Logistics::add2($id,'包裹已到达仓库',$clerk['clerk_id']);
       if (!$res){
        return $this->renderError('包裹入库失败');
       }
       //存储包裹的分类信息；
        $classItem = [];
         if ($class_ids){
             //清理之前的类目，更新掉
             $packItemModel->where('order_id',$id)->delete();
             $classItem = $this->parseClass($class_ids);
             foreach ($classItem as $k => $val){
                   $classItem[$k]['class_id'] = $val['category_id'];
                   $classItem[$k]['express_name'] = $data['express_name'];
                   $classItem[$k]['class_name'] = $val['name'];
                   $classItem[$k]['express_num'] = $data['express_num'];
                   $classItem[$k]['all_price'] = '';
                   unset($classItem[$k]['category_id']); 
                   unset($classItem[$k]['name']);        
             }
         }
         if ($classItem){
             $packItemRes = $packItemModel->saveAllData($classItem,$id);
             if (!$packItemRes){
                return $this->renderError('包裹类目更新失败');
             }
         }
       
         //存入货架信息
         if(!empty($shelf_unit)){
             $shelfunit = new ShelfUnitItem();
             $shelf = [
                'pack_id' => $data['id'],
                'user_id' => isset($user_id)?$user_id:$data['member_id'],
                'express_num' => $data['express_num'],
                'shelf_unit' => $shelf_unit,
             ];  
             $shelfunit->post($shelf);
         }else{
             $this->fenpeihuowei($user_id,$id,$express_num,$clerk['wxapp_id']);
         }
         
       
       //仓库id存在，则查询到仓库名称，传入模板消息
        if($data['storage_id']){
            $shopData =  (new Shop())->where('shop_id',$data['storage_id'])->find();
            $post['shop_name'] = $shopData['shop_name'];
        }
       //包裹入库通知
       $data = array_merge($data->toArray(),$update);
       $data['shop_name']= $shopData['shop_name'];
       
       $tplmsgsetting = SettingModel::getItem('tplMsg');
       if($tplmsgsetting['is_oldtps']==1){
          //发送旧版本订阅消息以及模板消息
          $sub = (new Package())->sendEnterMessage([$data]);
       }else{
          //发送新版本订阅消息以及模板消息
          Message::send('package.inwarehouse',$data);
       }
       
       
       if($data['member_id']){
          //邮件通知
           $data['code'] = $data['express_num'];
           $data['logistics_describe']='包裹已入库，可提交打包';
           $user = (new UserModel())->find($data['member_id']);
           if($user['email']){
               (new Email())->sendemail($user,$data,$type=1);
           } 
       }
        //判断是否打印标签
        $packagePrintData = (new Package())->where(['id'=>$id])->find();
        $printerConfig = SettingModel::getItem('printer',$wxapp_id);
        if($is_print==1 && $printerConfig['is_open']==1 && $printerConfig['printer_id']>0 && count($printerConfig['printsite'])>0){
             (new Printer())->printTicket($packagePrintData,10);
        }
       return $this->renderSuccess('包裹入库成功'); 
           
       }elseif($is_pre==20){
        //1、当是集运订单的单号时，存储当前的入库仓库id，使用字段shop_id
        $Inpack = new Inpack();
        $packData = $Inpack::detail($id);
        $userInfo = $this->user;
        //存入货架信息
        $takecode = rand(100000,999999);
        if(!empty($shelf_unit)){
            $this->saveToShelf($packData['id'],$userInfo['user_id'],$packData['t_order_sn'],$shelf_unit);
            $takecode =$this->takecode($id);
        }
        
        
        $logis['code'] = $express_num;
        $logis['logistics_describe']='包裹已到达'.$clerk['shop']['shop_name'].'取货码：'.$takecode;
        // dump($clerk['shop']['shop_name']);die;
        $Inpack->UpdateShop($id,$clerk['shop_id'],$takecode);
        //2、添加一条入库记录； 
        Logistics::addInpackGetLog2($id,'包裹已到达'.$clerk['shop']['shop_name'].'请及时前来取货',$express_num,$clerk['clerk_id']);
        //3、发送模板消息到货并通知用户取货；
        
        $data['order_sn'] = $packData['order_sn'];
        $data['order'] = $packData;
        $data['order']['total_free'] = $packData['free'];
        $data['order']['userName'] = $userInfo['nickName'];
        $data['order_type'] = 10;
        $data['order']['remark'] = $logis['logistics_describe'];
        $resss = Message::send('order.payment',$data);  
        //4、发送邮件通知
        !empty($userInfo['email']) && (new Email())->sendemail($userInfo,$logis,$type=1);
        
        
        return $this->renderSuccess('集运单入库成功');    
       }
       
    }
    
     //分配货位
    public function fenpeihuowei($member_id,$pack_id,$express_num,$wxapp_id){
        $keepersetting = SettingModel::getItem('keeper',$wxapp_id);
        if(isset($keepersetting['shopkeeper']['is_auto_shelfunit']) && $keepersetting['shopkeeper']['is_auto_shelfunit']==0){
            return false;
        }
        
        $resultshelfunit = (new ShelfUnit())->where('wxapp_id',$wxapp_id)->select();
        if(empty($resultshelfunit) && count($resultshelfunit)==0){
            return false;
        }
        $selectedShelfUnitId = null;
        // 1. 检查包裹是否有专属货位，如果有则使用专属货位
        if(!empty($member_id)){
            $userShelfUnits = $this->getUserBindShelfUnits($member_id, $wxapp_id);
            if(!empty($userShelfUnits)){
                $selectedShelfUnitId = $userShelfUnits['shelf_unit_id'];
            }else{
                // 如果没有专属货位，看后台是否来决定是否给用户自动分配
                //如果没有专属货位，就查询出空货位并给他分配一个，并绑定货位跟用户
                if($keepersetting['shopkeeper']['is_auto_setshelfuser']==1){
                    $emptyShelfUnits = $this->getEmptyShelfUnits($wxapp_id, 1);
                    if(empty($emptyShelfUnits)){
                         $this->error = "没有空余货位，请添加更多货位";
                         return false;
                    }
                    $selectedShelfUnitId = $emptyShelfUnits[0]['shelf_unit_id'];
                    (new ShelfUnit())->where('shelf_unit_id',$selectedShelfUnitId)->update([
                        'user_id'=>$member_id
                    ]);
                }else{
                    //如果没有归属货位，并且也没有需要自动分配货位，就先查询下该用户是否有其他包裹有在货位上的，放在相同货位 
                    $userShelfUnits = $this->getUserOtherPackagesShelfUnits($member_id, $wxapp_id, 1);
                    if(!empty($userShelfUnits) && count($userShelfUnits)>0){
                        // 找到用户其他包裹的货位，优先分配
                        $selectedShelfUnitId = $userShelfUnits[0]['shelf_unit_id'];
                    }
                    // 找到包裹数量最少的货位分配
                    if(empty($selectedShelfUnitId)){
                        $leastPackagesShelfUnits = $this->getLeastPackagesShelfUnits($wxapp_id, 1);
                        if(!empty($leastPackagesShelfUnits) && count($leastPackagesShelfUnits)>0){
                            $selectedShelfUnitId = $leastPackagesShelfUnits[0]['shelf_unit_id'];
                        }
                    }
                    
                }
                
            }
        }else{
            //查询无主货架，随机分配一个无主货架
            $emptyShelfUnits = $this->getNouserShelfUnits($wxapp_id);
            if(!empty($emptyShelfUnits)){
                $selectedShelfUnitId = $emptyShelfUnits['shelf_unit_id'];
            }else{
                 //如果没有无主货架，则商家不存在货位，则不需要保存货位信息；
                return true;
            }
        }

        $resultpack = (new ShelfUnitItem())->where('pack_id',$pack_id)->find();
        if(empty($resultpack)){
            // 6. 分配货位
            $shelfData = [
                'pack_id' => $pack_id,
                'wxapp_id' => $wxapp_id,
                'express_num' => $express_num,
                'user_id' => !empty($member_id)?$member_id:0,
                'shelf_unit_id' => $selectedShelfUnitId,
                'created_time' => getTime()
            ];
            
            (new ShelfUnitItem())->save($shelfData);
        }else{
            $resultpack->save([
                'shelf_unit_id' => $selectedShelfUnitId,
                'user_id'=>!empty($member_id)?$member_id:$resultpack['user_id']
            ]);
        }
        return true;
    }
    
    //找到没有归属的货位
    public function getUserBindShelfUnits($member_id, $wxapp_id) {
        return (new ShelfUnit())
                    ->where('wxapp_id', $wxapp_id)
                    ->where('user_id', $member_id)
                    ->where('is_nouser',0)
                    ->where('is_big', 0)
                    ->where('status',1)
                    ->find();
    }
    
    /**
     * 获取用户其他包裹存放的货位
     * @param int $member_id
     * @param int $wxapp_id
     * @param int $limit
     * @return array
     */
    public function getUserOtherPackagesShelfUnits($member_id, $wxapp_id, $limit = 1) {
        return (new ShelfUnitItem())->field('shelf_unit_id, COUNT(*) as usage_count')
                    ->where('wxapp_id', $wxapp_id)
                    ->where('user_id', $member_id)
                    ->where('shelf_unit_id', '<>', 0)
                    ->group('shelf_unit_id')
                    ->order('usage_count ASC, shelf_unit_id ASC')
                    ->limit($limit)
                    ->select();
    }
    
    /**
     * 获取包裹数量最少的货位
     * @param int $wxapp_id
     * @param int $limit
     * @return array
     */
    public function getLeastPackagesShelfUnits($wxapp_id, $limit = 1) {
        return (new ShelfUnitItem())->alias('sf')
                    ->field('sf.shelf_unit_id, COUNT(*) as usage_count')
                    ->join('shelf_unit su', 'su.shelf_unit_id = sf.shelf_unit_id',"LEFT")
                    ->where('su.user_id', 0)
                    ->where('sf.wxapp_id', $wxapp_id)
                    ->where('sf.shelf_unit_id', '<>', 0)
                    ->group('sf.shelf_unit_id')
                    ->order('usage_count ASC, sf.shelf_unit_id ASC')
                    ->limit($limit)
                    ->select();
    }
    
     /**
     * 获取无主货位
     * @param int $wxapp_id
     * @param int $limit
     * @return array
     */
    public function getNouserShelfUnits($wxapp_id) {
        return (new ShelfUnit())
                ->where('user_id', 0)
                ->where('is_nouser', 1)
                ->where('is_big', 0)
                ->where('status',1)
                ->where('wxapp_id', $wxapp_id)
                ->orderRaw('RAND()')  // MySQL 随机排序
                ->find();  // 获取单个记录
    }
    
    /**
     * 获取空货位（没有任何包裹的货位）
     * @param int $wxapp_id
     * @param int $limit
     * @return array
     */
    public function getEmptyShelfUnits($wxapp_id, $limit = 1) {
        // 获取所有货位ID
        $allShelfUnits = (new ShelfUnit())
        ->field('shelf_unit_id')
        ->where('status',1)
        ->where('is_nouser',0)
        ->where('is_big', 0)
        ->where('user_id',0)
        ->select();
        $allShelfUnitIds = array_column($allShelfUnits->toArray(), 'shelf_unit_id');
        if (empty($allShelfUnitIds)) {
            return [];
        }
        
        // 获取已有包裹的货位ID
        $usedShelfUnits = (new ShelfUnitItem())->field('shelf_unit_id')
                    ->where('wxapp_id', $wxapp_id)
                    ->group('shelf_unit_id')
                    ->select();
        $usedShelfUnitIds = array_column($usedShelfUnits->toArray(), 'shelf_unit_id');
        
        // 找出空货位
        $emptyShelfUnitIds = array_diff($allShelfUnitIds, $usedShelfUnitIds);
        
        if (empty($emptyShelfUnitIds)) {
            return [];
        }
        
        // 随机选择空货位
        $selectedIds = array_slice(array_values($emptyShelfUnitIds), 0, $limit);
        
        return array_map(function($id) {
            return ['shelf_unit_id' => $id];
        }, $selectedIds);
    }
    

    
    /**
     * 仓管端直邮入库
     * BY FENG 2022年12月27日 
     * 代码比较乱，等后续有时间再优化
     * @param Array
     * @return bool
     * @throws \think\exception\DbException
     */
    public function directInStorage() {
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('shop')->find();
        if (!$clerk){return $this->renderError('角色权限非法');}
      
       $packItemModel = new PackageItemModel();
       $id = $this->postData('id')[0];
       $user_id = $this->postData('user_id')[0];
       $class_ids = $this->postData('class_ids')[0];
       $user_code = $this->postData('user_Code')[0];
       $is_pre = $this->postData('is_pre')[0];
       $express_num = $this->postData('express_num')[0];
       $weight = $this->postData('weight')[0];
       $height = $this->postData('height')[0];
       $length = $this->postData('length')[0];
       $width = $this->postData('width')[0];
       $imageIds = $this->postData('imageIds');
       $wxapp_id = \request()->get('wxapp_id');
       $remark = $this->postData('remark')[0];
       $useremark = $this->postData('usermark')[0];
       $shelf_unit = $this->postData('shelf_unit')[0];
       $shelf_id = $this->postData('shelf_id')[0];
       $goodslist = $this->postData('goodslist');
       $is_verify = $this->postData('is_verify')[0];

       //再加一层校验；
        $map[] = ['is_delete','=',0];
        $map[] = ['express_num','=',$express_num];
        //对京东单号进行特殊处理
        // if(strstr($express_num,'JD')){
        //     $a = explode('-',$express_num);
        //     count($a)>1 && $express_num = $a[0];
        // }
            //  dump($shelf_id);die;
       //$is_pre==0 为没有查询到包裹数据的情况，需要插入新数据
       if ($is_pre==0){
           if ($user_id){
                $user = (new UserModel())->find($user_id);
                if (!$user)
                    return $this->renderError('用户不存在');
            }
              
            if($user_code){
                $users = (new UserModel())->where('user_code',$user_code)->find();
                if (!$users){
                    return $this->renderError('用户不存在');}
                $user_id = $users['user_id'];
            }
           $packdatas = (new Package())->where('express_num',$express_num)->where('is_delete',0)->find();
           if(!empty($packdatas)){
              if($length>0 && $width>0 && $height>0){
                 $volume = $width*$length*$height/1000000;
              }
              $order = [
                'status'=>2,
                'member_id'=> !empty($user_id)?$user_id:$packdatas['member_id'],
                'source'=>8,
                'express_num'=>$express_num,
                'storage_id'=>$clerk['shop_id'],
                'is_take'=>!empty($user_id)?2:$packdatas['is_take'],
                'entering_warehouse_time'=>getTime(),
                'updated_time'=>getTime(),
                'length'=>$length,
                'width'=>$width,
                'height'=>$height,
                'admin_remark'=>$remark,
                'usermark'=>$useremark,
                'shelf_id'=>$shelf_id,
                'weight'=>$weight,
                'pack_type'=>1,
                'volume'=>isset($volume)?$volume:$packdatas['volume']
              ];
              $packdatas->save($order);
              $restwo = $packdatas['id'];
           }else{
                     

            $order['status'] = 2;
            $order['is_take'] = 1;
            $order['storage_id'] = $clerk['shop_id'];
            if ($user_id){
                $order['member_id'] = $user_id;
                $order['is_take'] = 2;
            }
            $order['order_sn'] = createSn();
            $order['weight'] = $weight;
            $order['source'] = 8;
            $order['length'] = $length;
            $order['width'] = $width;
            $order['height'] = $height;
            $order['pack_type'] = 1;
            $order['express_num'] = $express_num;
            $order['updated_time'] = getTime();
            $order['created_time'] = getTime();
            $order['admin_remark'] = $remark;
            $order['usermark'] = $useremark;
            $order['shelf_id'] = $shelf_id;
            $order['entering_warehouse_time'] = getTime();
            if($length>0 && $width>0 && $height>0){
                 $order['volume'] = $width*$length*$height/1000000;
            }
            
            $restwo = (new Package())->saveData($order); //获取到包裹的id
            if (!$restwo){
                 return $this->renderError('包裹入库失败');
            }
           }
           $Barcode = new Barcode();
               //存储包裹的分类信息；
            $classItem = [];
           if ($class_ids || $goodslist){
             $classItem = $this->parseClass($class_ids);
                foreach ($goodslist as $k => $val){
                     $classItems[$k]['class_name'] = !empty($classItem)?$classItem[0]['name']:$val['pinming'];
                     $classItems[$k]['class_id'] = !empty($classItem)?$classItem[0]['category_id']:0;
                     $classItems[$k]['one_price'] = isset($val['danjia'])?$val['danjia']:'';
                     $classItems[$k]['all_price'] = (!empty($val['danjia'])?$val['danjia']:0) * (!empty($val['shuliang'])?$val['shuliang']:0);
                     $classItems[$k]['product_num'] = isset($val['shuliang'])?$val['shuliang']:'';
                     $classItems[$k]['express_num'] = $express_num;
                     $classItems[$k]['goods_name'] = isset($val['pinming'])?$val['pinming']:'';
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
        

             if (isset($classItems)){
                 $packItemRes = $packItemModel->saveAllData($classItems,$restwo);
                 if (!$packItemRes){
                    return $this->renderError('包裹类目更新失败');
                 }
             }
            //存储上传的图片
            $this->inImages($restwo,$imageIds,$wxapp_id);
            $shopData =  (new Shop())->where('shop_id',$clerk['shop_id'])->find();
            //存入货架信息
            if(!empty($shelf_unit)){
                 $shelfunit = new ShelfUnitItem();
                 $shelf = [
                    'pack_id' => $restwo,
                    'user_id' => $user_id,
                    'express_num' => $express_num,
                    'shelf_unit' => $shelf_unit,
                 ];  
                 $shelfunit->post($shelf);
            }
             
            //邮件通知
            //判断是否有用户id，发送邮件
              if(isset($user_id) || !empty($user_id)){
                  $EmailUser = UserCommonModel::detail($user_id);
// dump($order);die;
                  if($EmailUser['email']){
                      $EmailData['code'] = $order['express_num'];
                      $EmailData['logistics_describe']='包裹已入库，可提交打包';
                     (new Email())->sendemail($EmailUser,$EmailData,$type=1);
                  }
                    $data['order'] = [];
                    //发送订阅消息以及模板消息
                    //发送订阅消息，模板消息
                    $order['id'] = $restwo;
                    $order['wxapp_id'] = $wxapp_id;
                    $order['shop_name'] = $shopData['shop_name'];
                    $data['order'] = $order;
                    $data['order']['member_name'] = $EmailUser['nickName'];
                    $data['order_type'] = 10;
                    $data['order']['remark'] ="包裹已入库，可提交打包" ;
                    if($user_id!=0){
                        $tplmsgsetting = SettingModel::getItem('tplMsg');
                        if($tplmsgsetting['is_oldtps']==1){
                          //发送旧版本订阅消息以及模板消息
                          $sub = (new Package())->sendEnterMessage([$order]);
                        }else{
                          //发送新版本订阅消息以及模板消息
                          Message::send('package.inwarehouse',$order);
                        }
                    }
              }
            //判断是否打印标签
            $packagePrintData = (new Package())->where(['id'=>$restwo])->find();
            (new Printer())->printTicket($packagePrintData,10);
            Logistics::add2($restwo,'仓管员手动入库',$clerk['clerk_id']);
            return $this->renderSuccess('包裹入库成功');
          
       }elseif($is_pre==10){
       //有对应数据情况下
       $data = (new Package())->find($id);
    //   if ($data['status']==2){
    //      return $this->renderError('包裹已入库');
    //   }
       if ($user_id){
            $user = (new UserModel())->find($user_id);
            if (!$user)
            return $this->renderError('用户不存在');
        }
              
        if($user_code){
            $users = (new UserModel())->where('user_code',$user_code)->find();
            if (!$users){
                return $this->renderError('用户不存在');}
            $user_id = $users['user_id'];
        }
       //更新包裹信息
       $update['member_id'] = !empty($user_id)?$user_id:$data['member_id'];
       $update['length'] = !empty($length)?$length:$data['length'];
       $update['height'] = !empty($height)?$height:$data['height'];
       $update['width'] = !empty($width)?$width:$data['width'];
       $update['weight'] = !empty($weight)?$weight:$data['weight'];
       $update['admin_remark'] = !empty($remark)?$remark:$data['admin_remark'];
       $update['storage_id'] = $clerk['shop_id'];
       $update['status'] = 2;
       $update['usermark'] = !empty($usermark)?$usermark:$data['usermark'];
       $update['shelf_id'] = $shelf_id;
       $update['is_verify'] = $is_verify;
       $update['pack_type'] = 1;
       $update['updated_time'] = getTime();
       $update['entering_warehouse_time'] = getTime();
       if($length>0 && $width>0 && $height>0){
             $update['volume'] = $width*$length*$height/1000000;
       }
       
    //   dump($update);die;
       $res = (new Package())->where(['id'=>$id])->update($update);
       //插入图片
       $this->inImages($id,$imageIds,$wxapp_id);
       //更新日志
       Logistics::add2($id,'包裹已到达仓库',$clerk['clerk_id']);
       if (!$res){
        return $this->renderError('包裹入库失败');
       }
       //存储包裹的分类信息；
        $classItem = [];
         if ($class_ids){
             //清理之前的类目，更新掉
             $packItemModel->where('order_id',$id)->delete();
             $classItem = $this->parseClass($class_ids);
             foreach ($classItem as $k => $val){
                   $classItem[$k]['class_id'] = $val['category_id'];
                   $classItem[$k]['express_name'] = $data['express_name'];
                   $classItem[$k]['class_name'] = $val['name'];
                   $classItem[$k]['express_num'] = $data['express_num'];
                   $classItem[$k]['all_price'] = '';
                   unset($classItem[$k]['category_id']); 
                   unset($classItem[$k]['name']);        
             }
         }
         if ($classItem){
             $packItemRes = $packItemModel->saveAllData($classItem,$id);
             if (!$packItemRes){
                return $this->renderError('包裹类目更新失败');
             }
         }
       
         //存入货架信息
         if(!empty($shelf_unit)){
             $shelfunit = new ShelfUnitItem();
             $shelf = [
                'pack_id' => $data['id'],
                'user_id' => isset($user_id)?$user_id:$data['member_id'],
                'express_num' => $data['express_num'],
                'shelf_unit' => $shelf_unit,
             ];  
             $shelfunit->post($shelf);
         }
         
       
       //仓库id存在，则查询到仓库名称，传入模板消息
        if($data['storage_id']){
            $shopData =  (new Shop())->where('shop_id',$data['storage_id'])->find();
            $post['shop_name'] = $shopData['shop_name'];
        }
       //包裹入库通知
       $data = array_merge($data->toArray(),$update);
       $data['shop_name']= $shopData['shop_name'];
       
       $tplmsgsetting = SettingModel::getItem('tplMsg');
       if($tplmsgsetting['is_oldtps']==1){
          //发送旧版本订阅消息以及模板消息
          $sub = (new Package())->sendEnterMessage([$data]);
       }else{
          //发送新版本订阅消息以及模板消息
          Message::send('package.inwarehouse',$data);
       }
       
       
       if($data['member_id']){
          //邮件通知
           $data['code'] = $data['express_num'];
           $data['logistics_describe']='包裹已入库，可提交打包';
           $user = (new UserModel())->find($data['member_id']);
           if($user['email']){
               (new Email())->sendemail($user,$data,$type=1);
           } 
       }
        //判断是否打印标签
        $packagePrintData = (new Package())->where(['id'=>$id])->find();
        (new Printer())->printTicket($packagePrintData,10);
       return $this->renderSuccess('包裹入库成功'); 
           
       }elseif($is_pre==20){
        //1、当是集运订单的单号时，存储当前的入库仓库id，使用字段shop_id
        $Inpack = new Inpack();
        $packData = $Inpack::detail($id);
        $userInfo = $this->user;
        //存入货架信息
        $takecode = rand(100000,999999);
        if(!empty($shelf_unit)){
            $this->saveToShelf($packData['id'],$userInfo['user_id'],$packData['t_order_sn'],$shelf_unit);
            $takecode =$this->takecode($id);
        }
        
        
        $logis['code'] = $express_num;
        $logis['logistics_describe']='包裹已到达'.$clerk['shop']['shop_name'].'取货码：'.$takecode;
        // dump($clerk['shop']['shop_name']);die;
        $Inpack->UpdateShop($id,$clerk['shop_id'],$takecode);
        //2、添加一条入库记录； 
        Logistics::addInpackGetLog2($id,'包裹已到达'.$clerk['shop']['shop_name'].'请及时前来取货',$express_num,$clerk['clerk_id']);
        //3、发送模板消息到货并通知用户取货；
        
        $data['order_sn'] = $packData['order_sn'];
        $data['order'] = $packData;
        $data['order']['total_free'] = $packData['free'];
        $data['order']['userName'] = $userInfo['nickName'];
        $data['order_type'] = 10;
        $data['order']['remark'] = $logis['logistics_describe'];
        $resss = Message::send('order.payment',$data);  
        //4、发送邮件通知
        !empty($userInfo['email']) && (new Email())->sendemail($userInfo,$logis,$type=1);
        
        
        return $this->renderSuccess('集运单入库成功');    
       }
       
    }
    
    
    //存入货架信息
    public function saveToShelf($order_id,$user_id,$express_num,$shelf_unit){
        $shelfunit = new ShelfUnitItem();
        $shelf = [
            'pack_id' => $order_id,
            'user_id' => $user_id,
            'express_num' => $express_num,
            'shelf_unit' => $shelf_unit,
         ];
         return $shelfunit->post($shelf);
    }
    
    //存入货架信息
    public function takecode($id){
        $shelfunit = new ShelfUnitItem();
        $take_code = $shelfunit->getShelfUnitByPackId($id);
        return $take_code;
    }
    
    
    // 仓管端提交入库
    public function instorage() {
        $packItemModel = new PackageItemModel();
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      
        if (!$clerk){
           return $this->renderError('角色权限非法');
        }
       $id = $this->postData('id')[0];
       $user_id = $this->postData('user_id')[0];
       $class_ids = $this->postData('class_ids')[0];
       $user_code = $this->postData('user_Code')[0];
       $is_pre = $this->postData('is_pre')[0];
       $express_num = $this->postData('express_num')[0];
       $weight = $this->postData('weight')[0];
       $height = $this->postData('height')[0];
       $length = $this->postData('length')[0];
       $width = $this->postData('width')[0];
       $imageIds = $this->postData('imageIds');
       $wxapp_id = \request()->get('wxapp_id');
       $remark = $this->postData('remark')[0];
       $shelf_unit = $this->postData('shelf_unit')[0];
       
       if(preg_match("/^42097321/i",$express_num,$resd)){
            $express_num = mb_substr($express_num,8);
        }
        //处理FEDEX单号问题
        if(preg_match("/^96220/i",$express_num,$resds) && strlen($express_num) > 30){
            $express_num = substr($express_num,-12);
        }
       //再加一层校验；
        $map[] = ['is_delete','=',0];
        $map[] = ['express_num','=',$express_num]; 
        $is_exitres = (new Package())->setQuery($map)->find();
        if($is_exitres){
            $is_pre =true;
        }
       //$is_pre 为没有查询到包裹数据的情况，需要插入新数据
       if ($is_pre==false){
            if ($user_id){
                $user = (new UserModel())->find($user_id);
                if (!$user)
                    return $this->renderError('用户不存在');
            }
              
            if($user_code){
                $users = (new UserModel())->where('user_code',$user_code)->find();
                if (!$users){
                    return $this->renderError('用户不存在');}
                $user_id = $users['user_id'];
            }
           
            $order['status'] = 2;
            $order['is_take'] = 1;
            $order['storage_id'] = $clerk['shop_id'];
            if ($user_id){
                $order['member_id'] = $user_id;
                $order['is_take'] = 2;
            }
            $order['order_sn'] = createSn();
            $order['weight'] = $weight;
            $order['source'] = 8;
            $order['length'] = $length;
            $order['width'] = $width;
            $order['height'] = $height;
            $order['express_num'] = $express_num;
            $order['updated_time'] = getTime();
            $order['created_time'] = getTime();
            $order['remark'] = $remark;
            $order['entering_warehouse_time'] = getTime();
            $restwo = (new Package())->saveData($order); //获取到包裹的id
            if (!$restwo){
                 return $this->renderError('包裹入库失败');
            }
            //存储包裹的分类信息；
            $classItem = [];
             if ($class_ids){
                 $classItem = $this->parseClass($class_ids);
                 foreach ($classItem as $k => $val){
                       $classItem[$k]['class_id'] = $val['category_id'];
                       $classItem[$k]['express_name'] = '';
                       $classItem[$k]['class_name'] = $val['name'];
                       $classItem[$k]['express_num'] = $express_num;
                       $classItem[$k]['all_price'] = '';
                       unset($classItem[$k]['category_id']); 
                       unset($classItem[$k]['name']);        
                 }
             }
             if ($classItem){
                 $packItemRes = $packItemModel->saveAllData($classItem,$restwo);
                 if (!$packItemRes){
                    return $this->renderError('包裹类目更新失败');
                 }
             }
            //存储上传的图片
            $this->inImages($restwo,$imageIds,$wxapp_id);
            $shopData =  (new Shop())->where('shop_id',$clerk['shop_id'])->find();
            //存入货架信息
             $shelfunit = new ShelfUnitItem();
             $shelf = [
                'pack_id' => $restwo,
                'user_id' => $user_id,
                'express_num' => $express_num,
                'shelf_unit' => $shelf_unit,
             ];  
             $shelfunit->post($shelf);
            //邮件通知
            //判断是否有用户id，发送邮件
              if(isset($user_id) || !empty($user_id)){
                  $EmailUser = UserCommonModel::detail($user_id);
                  if($EmailUser['email']){
                      $EmailData['code'] = $order['express_num'];
                      $EmailData['logistics_describe']='包裹已入库，可提交打包';
                     (new Email())->sendemail($EmailUser,$EmailData,$type=1);
                  }
                    $data['order'] = [];
                    //发送订阅消息以及模板消息
                    //发送订阅消息，模板消息
                    $order['id'] = $restwo;
                    $order['wxapp_id'] = $wxapp_id;
                    $order['shop_name'] = $shopData['shop_name'];
                    $data['order'] = $order;
                    $data['order']['member_name'] = $EmailUser['nickName'];
                    $data['order_type'] = 10;
                    $data['order']['remark'] ="包裹已入库，可提交打包" ;
                    if($user_id!=0){
                      Message::send('order.enter',$data);  
                    }
              }
            
            return $this->renderSuccess('包裹入库成功');
       }
       
       //有对应数据情况下
       $data = (new Package())->find($id);
       
       if ($data['status']==2){
         return $this->renderError('包裹已入库');
       }
       $update['status'] = 2;
       $update['updated_time'] = getTime();
       $update['entering_warehouse_time'] = getTime();
       $res = (new Package())->where(['id'=>$id])->update($update);
       //插入图片
       $this->inImages($id,$imageIds,$wxapp_id);
       //更新日志
       Logistics::add2($id,'包裹已到达仓库',$clerk['clerk_id']);
       if (!$res){
        return $this->renderError('包裹入库失败');
       }
       //存储包裹的分类信息；
      
        
        $classItem = [];
         if ($class_ids){
             //清理之前的类目，更新掉
             $packItemModel->where('order_id',$id)->delete();
             $classItem = $this->parseClass($class_ids);
             foreach ($classItem as $k => $val){
                   $classItem[$k]['class_id'] = $val['category_id'];
                   $classItem[$k]['express_name'] = '';
                   $classItem[$k]['class_name'] = $val['name'];
                   $classItem[$k]['express_num'] = $data['express_num'];
                   $classItem[$k]['all_price'] = '';
                   unset($classItem[$k]['category_id']); 
                   unset($classItem[$k]['name']);        
             }
         }
         if ($classItem){
             $packItemRes = $packItemModel->saveAllData($classItem,$id);
             if (!$packItemRes){
                return $this->renderError('包裹类目更新失败');
             }
         }
       
       //存入货架信息
         $shelfunit = new ShelfUnitItem();
         $shelf = [
            'pack_id' => $data['id'],
            'user_id' => isset($user_id)?$user_id:$data['member_id'],
            'express_num' => $data['express_num'],
            'shelf_unit' => $shelf_unit,
         ];  
         $shelfunit->post($shelf);
       
       //仓库id存在，则查询到仓库名称，传入模板消息
        if($data['storage_id']){
            $shopData =  (new Shop())->where('shop_id',$data['storage_id'])->find();
            $post['shop_name'] = $shopData['shop_name'];
        }
       //包裹入库通知
       $data = array_merge($data->toArray(),$update);
       $data['shop_name']= $shopData['shop_name'];
       $tplmsgsetting = SettingModel::getItem('tplMsg');
       if($tplmsgsetting['is_oldtps']==1){
          //发送旧版本订阅消息以及模板消息
          $sub = (new Package())->sendEnterMessage([$data]);
       }else{
          //发送新版本订阅消息以及模板消息
          Message::send('package.inwarehouse',$data);
       }
       
       if($data['member_id']){
          //邮件通知
           $data['code'] = $data['express_num'];
           $data['logistics_describe']='包裹已入库，可提交打包';
           $user = (new UserModel())->find($data['member_id']);
           if($user['email']){
               (new Email())->sendemail($user,$data,$type=1);
           } 
       }
       
       
       return $this->renderSuccess('包裹入库成功');
    }
    
    // 格式化
     public function parseClass($class_ids){
         $class_item = [];
         $class_ids = explode(',',$class_ids);
         $class = (new Category())->whereIn('category_id',$class_ids)->field('category_id,name')->select()->toArray(); 
         return $class;
     }
    
    /**
     * $id 包裹的id
     * $imageIds 图片数组
     */
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
    
        /**
     * $id 包裹的id
     * $imageIds 图片数组
     */
    public function deleteImage(){
        $param = $this->request->param();
        $PackageImage =  new PackageImage();
        if(isset($param['imageId'])){
            $PackageImage->where('image_id',$param['imageId'])->delete();
        }
        return true;
    }
    
    
    /**
     *  BY FENG 2022年12月27日
     * 包裹列表统计
     */
    public function packTotaldata(){
      // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      $packModel = (new Package());
      $inpackModel = (new Inpack());
      $today = date('Y-m-d');
      $tomorrow = date('Y-m-d', strtotime('+1 day'));
      $values = SettingModel::getItem('store')['retention_day'];
      $res = [
         'today' => $packModel->where(['storage_id'=>$clerk['shop_id']])->whereTime('entering_warehouse_time','between',[$today,$tomorrow])->count(),
         'all' => $packModel -> where(['storage_id'=>$clerk['shop_id'],'is_delete'=>0])->where('status','in',[-1,2,3,4,5,6,7])->count(),
         'pack' => $packModel->where('status','in',[2,3,4,5,6])->where(['storage_id'=>$clerk['shop_id'],'is_take'=>2,'is_delete'=>0])->count(), //待打包的集运单
         'waitTake' => $inpackModel->where(['status'=>7,'shop_id'=>$clerk['shop_id'],'is_delete'=>0])->count(),
         'appointment' => $packModel->where(['status'=>1,'storage_id'=>$clerk['shop_id'],'source'=>7])->count(),
         'retention' => $inpackModel->where(['status'=>7,'shop_id'=>$clerk['shop_id']])->where('updated_time','<= time',date('Y-m-d',time()-7*86400))->count(),
      ];
      return $this->renderSuccess($res);
    }
    
    // 包裹列表统计
    public function packTotal(){
      // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      $packModel = (new Package());
      $res = [
         'no' => $packModel ->where(['status'=>1,'storage_id'=>$clerk['shop_id'],'is_delete'=>0])->count(),
         'in' => $packModel -> where(['status'=>array('in',[2,3,4]),'storage_id'=>$clerk['shop_id'],'is_delete'=>0])->count()
      ];
      return $this->renderSuccess($res);
    }

    /**
     * 仓库管理员 包裹列表
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function packagelist(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $params = $this->request->param();
        // dump($params['type']);die;
        $today = date('Y-m-d',time());
        $todayend = date('Y-m-d',time()+86400);
        $yestoday = date('Y-m-d',time() - 86400);
        $firstMouth = date('Y-m-01');
        $status = $this->postData('status')?$this->postData('status')[0]:1;
        $map = ['status'=>$status];
        switch ($params['type']) {
            case '1':
                $map['status'] = [2];
                $map['entering_warehouse_time'] = [$today,$todayend];
                break;
             case '2':
                $map['status'] = [2];
                $map['entering_warehouse_time'] = [$yestoday,$today];
                break;
             case '3':
                $map['status'] = [2];
                $map['entering_warehouse_time'] = [$firstMouth,$today];
                break;
             case '4':
                $map['status'] = [1];
                break;
             case '5':
                $map['status'] = [2];
                break;
             case '6':
                $map['is_take'] = 1;
                $map['status'] = [2,3,4,5,6,7];
                break;
             case '7':
                $map['status'] = [1,2,3,4,5,6,7,8,9,10,11];
                $map['is_take'] = 2;
                break;
             case '8':
                $map['status'] = [8];
                break;
            case '9':
                $map['status'] = [9];
                break;
            case '10':
                $map['status'] = [11];
                break;
            case '11':
                $map['status'] = [-1];
                break;
            
            case '12':
                $map['source'] = 1;
                $map['created_time'] = [$today,$todayend];
                break;
                
            default:
                $map['status'] = [2];
                $map['entering_warehouse_time'] = [$today,$todayend];
                break;
        }

        $keyword = $this->postData("keyword")[0];
        $map['storage_id'] = $clerk['shop_id'];
        $map['keyword'] = $keyword;
        $shelfunit = new ShelfUnitItem();
        $packModel = (new Package());
            //   dump($map);die;
        $data = $packModel->getGList($map,'created_time');
        return $this->renderSuccess($data);
    }


/**
     * 仓库管理员 包裹列表
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function userpackagelist(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $params = $this->request->param();
        $UserModel = new UserModel();
        $today = date('Y-m-d',time());
        $todayend = date('Y-m-d',time()+86400);
        $yestoday = date('Y-m-d',time() - 86400);
        $firstMouth = date('Y-m-01');
        $keyword = $this->postData("keyword")[0];
        if(isset($keyword)  && !empty($keyword)){
            $member = $UserModel->where('nickName|user_code|user_id',$keyword)->where('is_delete',0)->find();
            if(!empty($member)){
                $map['member_id'] = $member['user_id'];
            }else{
                $map['member_id'] = $keyword;
            }
        }
        $map['storage_id'] = $clerk['shop_id'];
       
        $packModel = (new Package());
        $userlist = [];
        $data = $packModel->with(['shelfunititem.shelfunit.shelf'])->where('status','in',[2,3])->where('is_take',2)->where($map)->field('member_id')->select()->toArray();
        foreach($data as $key=>$value){
            $userlist[$key]=$value['member_id'];
        }
        $usernewArr= array_unique($userlist);
        $list = [];
        
        foreach ($usernewArr as $k => $v){
            $list[$k]['member']= $UserModel::detail($v);
            $list[$k]['package'] = $packModel->with(['shelfunititem.shelfunit.shelf'])->where('member_id',$v)->where('is_take',2)->where('status','in',[2,3])->select();
            $list[$k]['total'] = $packModel->where('member_id',$v)->where('is_take',2)->where('status','in',[2,3])->SUM('weight');
        }
        // dump($usernewArr);die;   
        return $this->renderSuccess($list);
    }

/**
     * 仓管端数据统计
     * BY FENG 2022年12月27日
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function screenData(){
        $data = $this->request->param();
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        $data['shop_id'] = $clerk['shop_id'];
        $packModel = (new Package());
        $today = date('Y-m-d',time());
        $todayend = date('Y-m-d',time()+86400);
        $yestoday = date('Y-m-d',time() - 86400);
        $firstMouth = date('Y-m-01');
        $where['is_delete'] = 0;
        $map['status']  = array('in',[2,3,4,5,6]);
        // dump(base_url());die;
        return $this->renderSuccess([
            //今日入库包裹
            [
                'icon' => base_url().'assets/api/images//today.png' ,
                'content'=>'今日入库数量',
                'num'=> $packModel->where($where)->where(['storage_id' =>$data['shop_id']])->where('entering_warehouse_time','between',[$today,$todayend])->count(),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=1"
            ],
            //今日入库重量
            [
                'icon' => base_url().'assets/api/images//today.png' ,
                'content'=>'今日入库重量',
                'num'=> round($packModel->where($where)->where(['storage_id' =>$data['shop_id']])->where('entering_warehouse_time','between',[$today,$todayend])->SUM('weight'),2),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=1"
            ],
            //今日预报数量
            [
                'icon' => base_url().'assets/api/images//today.png' ,
                'content'=>'今日预报数量',
                'num'=> $packModel->where($where)->where(['storage_id' =>$data['shop_id']])->where('created_time','between',[$today,$todayend])->where('source',1)->count(),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=12"
            ],
            //昨日入库
            [
                'icon' => base_url().'assets/api/images/yesterday.png' ,
                'content'=>'昨日入库数量',
                'num'=> $packModel->where($where)->where(['storage_id' =>$data['shop_id']])->where('entering_warehouse_time','between',[$yestoday,$today])->count(),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=2"
            ],
            //昨日入库重量
            [
                'icon' => base_url().'assets/api/images/yesterday.png' ,
                'content'=>'昨日入库重量',
                'num'=> round($packModel->where($where)->where(['storage_id' =>$data['shop_id']])->where('entering_warehouse_time','between',[$yestoday,$today])->SUM('weight'),2),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=2"
            ],
            //本月累计入库
            [
                'icon' => base_url().'assets/api/images//mouth.png' ,
                'content'=>'本月累计入库',
                'num'=> $packModel->where($where)->where(['storage_id' =>$data['shop_id']])->where('entering_warehouse_time','between',[$firstMouth,$today])->count(),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=3"
            ],
            //本月入库重量
            [
                'icon' => base_url().'assets/api/images//today_weight.png' ,
                'content'=>'本月入库重量',
                'num'=> round($packModel->where($where)->where(['storage_id' =>$data['shop_id']])->where('entering_warehouse_time','between',[$firstMouth,$today])->SUM('weight'),2),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=3"
            ], 
            //未入库包裹
            [
                'icon' => base_url().'assets/api/images//dzx_img212.png' ,
                'content'=>'未入库包裹',
                'num'=> $packModel->where($where)->where(['status' => 1,'storage_id' =>$data['shop_id']])->count(),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=4"
            ], 
            //已入库包裹
            [
                'icon' => base_url().'assets/api/images//dzx_img213.png',
                'content'=>'已入库包裹',
                'num'=> $packModel->where($where)->where(['status' => 2,'storage_id' =>$data['shop_id']])->count(),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=5"
            ], 
            
            //待认领包裹
            [
                'icon' => base_url().'assets/api/images//dzx_img218.png',
                'content'=>'待认领包裹',
                'num'=> $packModel->where($where)->where(['is_take'=>1,'storage_id' =>$data['shop_id']])->count(),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=6"
            ],
            
            //待打包包裹
            [
                'icon' => base_url().'assets/api/images//dzx_img216.png',
                'content'=>'待打包包裹',
                'num'=> $packModel->where($where)->where($map)->where(['storage_id' => $data['shop_id'],'is_take'=>2])->count(),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=7"
            ],
            //待发货包裹
            [
                'icon' => base_url().'assets/api/images//dzx_img217.png',
                'content'=>'待发货包裹',
                'num'=> $packModel->where($where)->where(['status' => 8,'storage_id' =>$data['shop_id']])->count(),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=8"
            ],
            //在途中包裹
            [
                'icon' => base_url().'assets/api/images//dzx_img214.png',
                'content'=>'已发货包裹',
                'num'=> $packModel->where($where)->where(['status' => 9,'storage_id' =>$data['shop_id']])->count(),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=9"
            ],
            //已完成包裹
            [
                'icon' => base_url().'assets/api/images//dzx_img215.png',
                'content'=>'已完成包裹',
                'num'=> $packModel->where($where)->where(['status' => 11,'storage_id' =>$data['shop_id']])->count(),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=10"
            ],
            //问题件包裹
            [
                'icon' => base_url().'assets/api/images//dzx_img219.png',
                'content'=>'问题件包裹',
                'num'=> $packModel->where($where)->where(['status' => -1,'storage_id' =>$data['shop_id']])->count(),
                'method'=>"/pages/cangkuyuans/packagelist/packagelist?type=11"
            ] 
        ]);
    }

    /**
     * 仓库管理员 包裹列表
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function packlist(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $status = $this->postData('status')?$this->postData('status')[0]:1;
        $map = ['status'=>$status];
        if($status==2){
            $map['status'] = [2,3,4];
        }
        $keyword = $this->postData("keyword")[0];
        $map['storage_id'] = $clerk['shop_id'];
        $map['is_delete'] = 0;
        $map['keyword'] = $keyword;
        $shelfunit = new ShelfUnitItem();
        $packModel = (new Package());
        if($status==1){
            $data = $packModel->getGList($map,'created_time');
        }else{
            $data = $packModel->getGList($map,'entering_warehouse_time');
        }
        return $this->renderSuccess($data);
    }

    // 包裹详情
    public function packdetails(){
          // 员工信息
          $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
          if (!$clerk){
              return $this->renderError('角色权限非法');
          }
          $packModel = (new Package());
          $id = $this->postData('id')?$this->postData('id')[0]:1;
        //   dump( $this->postData('id'));die;
          $data = $packModel ->with(['member','country','packageimage.file','jaddress','address'])->find($id);//->with('packageimage.file')
          // 获取物品详情
          $packItem = (new PackageItemModel())->where(['order_id'=>$data['id']])->field('class_name,id,class_id,order_id')->select();
          $data['shop'] = '';
          if ($packItem){
              $data['shop'] = implode(',',array_column($packItem->toArray(),'class_name'));
          }
          return $this->renderSuccess($data);
    }
    
    
        // 包裹详情
    public function packagedetails(){
          // 员工信息
          $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
          if (!$clerk){
              return $this->renderError('角色权限非法');
          }
          $packModel = (new Package());
          $id = $this->postData('id')?$this->postData('id')[0]:1;
          $data = $packModel->getpackageDetails($id);
          return $this->renderSuccess($data);
    }
    
    // 获取包裹详情
    public function getdetails(){
          // 员工信息
          $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
          if (!$clerk){
              return $this->renderError('角色权限非法');
          }
          $packModel = (new Package());
          $id = $this->postData('id')?$this->postData('id')[0]:1;
        //   dump( $this->postData('id'));die;
          $data = $packModel ->with(['member','country','packageimage.file'])->find($id);//->with('packageimage.file')
          // 获取物品详情
          $packItem = (new PackageItemModel())->where(['order_id'=>$data['id']])->field('class_name,id,class_id,order_id')->select();
          $data['shop'] = '';
          if ($packItem){
              $data['shop'] = implode(',',array_column($packItem->toArray(),'class_name'));
          }
          return $this->renderSuccess($data);
    }
    //编辑包裹
    public function editpack(){
        $data = $this->request->param();
        $packModel = (new Package());
        $res= $packModel->editData($data);
        if($res){
            return $this->renderSuccess("编辑成功");
        }
        return $this->renderError('编辑失败');
    }

    // 待查验清单
    public function loadingpack(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $status = $this->postData('status')[0];
        if($status=='2'){
            $status = [2,3,4,5];
        }
        $keyword = $this->postData('keyword')[0];
        $map['status'] = $status;
        $map['storage_id'] = $clerk['shop_id'];
        $map['keyword'] = $keyword;
        $packModel = (new Inpack());
        $data = $packModel->getGList($map,'updated_time');
        // dump($data->toArray());die;
        return $this->renderSuccess($data);
    }
    
     
    /**
     * 预约集运单
     * BY FENG 2022年12月27日
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function appointment(){
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $status = $this->postData('status')[0]?$this->postData('status')[0]:1;
        $keyword = $this->postData('keyword')[0];
        $map = ['is_take'=>2,'source'=>7,'status'=>$status,'keyword'=>$keyword];
        $packModel = (new Package());
        $data = $packModel->getYList($map);
        return $this->renderSuccess($data);
    }
    
    
    /**
     * 待取件集运单统计数据
     * BY FENG 2022年12月27日
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
     public function waitPickPackTotal(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $Inpack = (new Inpack());
        
        $res = [
           'no' => $Inpack ->where(['status'=>7,'storage_id'=>$clerk['shop_id'],'is_delete'=>0])->count(),
           'in' => $Inpack -> where(['status'=>8,'storage_id'=>$clerk['shop_id'],'is_delete'=>0])->count()
        ];
        return $this->renderSuccess($res);
    }
    
    /**
     * 待取件集运单
     * BY FENG 2023年1月4日
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function waitPickPack(){
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        // dump($clerk);die;
        $status = $this->postData('status')[0]?$this->postData('status')[0]:8;
        $keyword = $this->postData('keyword')[0];
        // dump($status);
        $map = ['status'=>$status,'keyword'=>$keyword,'storage_id'=> $clerk['shop_id']];
        $Inpack = (new Inpack());
        if($status==7){
            $data = $Inpack->getWList($map);
        }
        if($status==8){
            $data = $Inpack->getWYList($map);
        }
        
        return $this->renderSuccess($data);
    }
    
    /**
     * 预约集运单统计数据
     * BY FENG 2022年12月27日
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
     public function appointmentTotal(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $packModel = (new Package());
        $res = [
           'no' => $packModel ->where(['status'=>1,'is_take'=>2,'source'=>7])->count(),
           'in' => $packModel -> where(['status'=>2,'is_take'=>2,'source'=>7])->count()
        ];
        return $this->renderSuccess($res);
    }
    
    
    
    
    //环形数据
    public function getConsolidationOrderData(){
      $data = $this->request->param();
      $Inpack = new Inpack;
      $daichayan = $Inpack->where(['storage_id'=>$data['shop_id'],'status'=>1])->count();
      $daifahuo = $Inpack->where(['storage_id'=>$data['shop_id'],'status' => ['in',[2,3,4,5]] ])->count();
      $zaituzhong = $Inpack->where(['storage_id'=>$data['shop_id'],'status' => ['in',[6,7,8]]  ])->count();
      $total = $daichayan + $daifahuo + $zaituzhong;
      $datas = [
          ['name' =>'待查验','value'=>$daichayan,'labelShow'=>false],
          ['name' =>'待发货','value'=>$daifahuo,'labelShow'=>false],
          ['name' =>'在途中','value'=>$zaituzhong,'labelShow'=>false],
      ];
      return $this->renderSuccess([ 'series' => [0 => ['data'=>$datas]],'tongji' => ['total' => $total, 'daichayan'=> $daichayan,'daifahuo'=>$daifahuo,'zaituzhong'=>$zaituzhong] ]); 
    }
    
    //用户增长数据
    public function getUserGrowthChartData(){
        $UserModel = new UserModel();
        $start = strtotime(date('Y-m-d',time()));
        $total = $UserModel->where('is_delete',0)->count();
        $today = $UserModel->where('is_delete',0)->where('create_time','between',[$start,$start + 86400])->count();
        // dump(date('j',time()-86400));die;
        $userD = [
            $UserModel->where('is_delete',0)->where('create_time','between',[$start-86400*6,$start-86400*5])->count(),
            $UserModel->where('is_delete',0)->where('create_time','between',[$start-86400*5,$start-86400*4])->count(),
            $UserModel->where('is_delete',0)->where('create_time','between',[$start-86400*4,$start-86400*3])->count(),
            $UserModel->where('is_delete',0)->where('create_time','between',[$start-86400*3,$start-86400*2])->count(),
            $UserModel->where('is_delete',0)->where('create_time','between',[$start-86400*2,$start-86400])->count(),
            $UserModel->where('is_delete',0)->where('create_time','between',[$start-86400,$start])->count()
        ];
        $datas = ['name' =>'用户数量','data'=>$userD,'labelShow'=>false];
        return $this->renderSuccess([
            'result'=>['categories'=>[
                date('j',time()-86400*6).'号',
                date('j',time()-86400*5).'号',
                date('j',time()-86400*4).'号',
                date('j',time()-86400*3).'号',
                date('j',time()-86400*2).'号',
                date('j',time()-86400).'号'], 
                'series' =>[0=>$datas] ],
                'tongji' => ['total' => $total, 'today'=>$today] 
        ]);
    }
    
    //包裹增长数据
    public function getPackageGrowthData(){
        $data = $this->request->param();
        $packModel = (new Package());
        $Inpack = new Inpack;
        $start = strtotime(date('Y-m-d',time()));
        $benzhou = [
            $packModel->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*6),date('Y-m-d',$start-86400*5)])->count(),
            $packModel->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*5),date('Y-m-d',$start-86400*4)])->count(),
            $packModel->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*4),date('Y-m-d',$start-86400*3)])->count(),
            $packModel->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*3),date('Y-m-d',$start-86400*2)])->count(),
            $packModel->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*2),date('Y-m-d',$start-86400)])->count(),
            $packModel->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400),date('Y-m-d',$start)])->count(),
        ];
        
        $benyue = [
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*6),date('Y-m-d',$start-86400*5)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*5),date('Y-m-d',$start-86400*4)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*4),date('Y-m-d',$start-86400*3)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*3),date('Y-m-d',$start-86400*2)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*2),date('Y-m-d',$start-86400)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400),date('Y-m-d',$start)])->count(),
        ];
        $datas = [0=>['name' =>'包裹','data'=>$benzhou,'labelShow'=>false],1=>['name' =>'订单','data'=>$benyue,'labelShow'=>false]];
        return $this->renderSuccess([
            'result'=>[
                'categories'=>[
                    date('j',time()-86400*6).'号',
                    date('j',time()-86400*5).'号',
                    date('j',time()-86400*4).'号',
                    date('j',time()-86400*3).'号',
                    date('j',time()-86400*2).'号',
                    date('j',time()-86400).'号'], 
                'series' =>$datas,
                'min' => 0
            ],
                
        ]);
    }
    
    
    //集运单走势
    public function getConsolidationOrderTransactionData(){
        $data = $this->request->param();
        $Inpack = new Inpack;
        $start = strtotime(date('Y-m-d',time()));
        $benyue = [
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*6),date('Y-m-d',$start-86400*5)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*5),date('Y-m-d',$start-86400*4)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*4),date('Y-m-d',$start-86400*3)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*3),date('Y-m-d',$start-86400*2)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*2),date('Y-m-d',$start-86400)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400),date('Y-m-d',$start)])->count(),
        ];
        $datas = ['name' =>'订单数量','data'=>$benyue];
        return $this->renderSuccess([
            'result'=>[
                'categories'=>[
                    date('j',time()-86400*6).'号',
                    date('j',time()-86400*5).'号',
                    date('j',time()-86400*4).'号',
                    date('j',time()-86400*3).'号',
                    date('j',time()-86400*2).'号',
                    date('j',time()-86400).'号'], 
                'series' =>[0=>$datas],
            ],
        ]);
    }
    
    //排名
    public function getRankingList(){
        $data = $this->request->param();
        $Inpack = new Inpack;
        $line = new Line();
        $linedata = $line->getListAll([]);
        $total = $Inpack->where('storage_id',$data['shop_id'])->count();  
        foreach ($linedata as $key=>$val){
           $datas[$key]['name'] = $val['name'];
           $datas[$key]['num'] = $Inpack->where('storage_id',$data['shop_id'])->where('line_id',$val['id'])->count();  
           $datas[$key]['total'] = $total;
        }
        $sort = array_column($datas,'num');
        array_multisort($sort,SORT_DESC,$datas);
        
        // dump($datas);die;
        return $this->renderSuccess([
            'result'=>array_slice($datas,0,10),
        ]);
    }
    
    /**
     * 预约件确认入库
     * BY FENG 2022年12月27日
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
     public function appointmentToShop(){
        $packModel = (new Package());
        $data = $this->postData();
        $res = $packModel->where('id',$data['id'])->update(['status'=>2]);
        if(!$res){
            return $this->renderError('取货失败');
        }
        return $this->renderSuccess('确认取货');
    }
    
    
    
    // 集运清单是否查验完成
    public function isoverpack(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $status = $this->postData('status')[0]?$this->postData('status')[0]:1;
        $keyword = $this->postData('keyword')[0];
        $map['status'] = $status;
        if($status==2){
            $map['status'] = [2,3,4,5];
        }
        $map['is_delete'] = 0;
        $map['storage_id'] = $clerk['shop_id'];
        $map['keyword'] = $keyword;
        $packModel = (new Inpack());
        $Package = new Package();
        $sdata = $packModel->getGList($map,'unpack_time');
        foreach ($sdata as $key => $val){
            $pack_id = explode(',',$val['pack_ids']);
            $sdata[$key]['is_scan'] = 2;
                $is_scan = $Package->field('is_scan')->where('inpack_id',$val['id'])->where('is_scan',1)->find();
                if(!empty($is_scan)){
                    $sdata[$key]['is_scan'] = 1;
                }
        }
        $data =$sdata;
        return $this->renderSuccess($data);
    }

    public function loadingpackTotal(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $packModel = (new Inpack());
       
        $res = [
           'no' => $packModel->where(['status'=>1,'storage_id'=>$clerk['shop_id'],'is_delete'=>0])->count(),
           'in' => $packModel-> where(['status'=> array('in',[2,3,4,5]),'storage_id'=>$clerk['shop_id'],'is_delete'=>0])->count()
        ];
        //  dump($packModel->getLastsql());die;
        return $this->renderSuccess($res);
    }

    /**
     *包裹转移 
     */
    public function packageMove(){
        $id = $this->postData('id')[0];
        $shelf_unit_id = $this->postData('shelf_unit_id')[0];
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $shelf = (new ShelfUnit())->find($shelf_unit_id);
        if (!$shelf){
            return $this->renderError('货架单元不存在');
        }
        $pack_info = (new Package())->field('id,status,wxapp_id,express_num,member_id')->find($id);
        if(!empty($pack_info)){
           if ($pack_info['status']>6){
                return $this->renderError('包裹状态错误');
           }
          (new ShelfUnitItem())->where(['pack_id'=>$pack_info['id']])->delete(); 
           $upShelf = [
              'shelf_unit' => $shelf_unit_id,
              'wxapp_id'=> $pack_info['wxapp_id'],
              'user_id' =>$pack_info['member_id'],
              'express_num' =>$pack_info['express_num'],
              'pack_id' => $id,
            ];
        }else{
           $Inpack = new Inpack();
           $inpackinfo = $Inpack->where(['id'=> $id,'is_delete'=>0])->find();
           if($inpackinfo){
               (new ShelfUnitItem())->where(['pack_id'=>$inpackinfo['order_sn']])->whereOr('express_num',$inpackinfo['t_order_sn'])->delete();
               $upShelf = [
                  'shelf_unit' => $shelf_unit_id,
                  'wxapp_id'=> $inpackinfo['wxapp_id'],
                  'user_id' =>$inpackinfo['member_id'],
                  'express_num' =>$inpackinfo['t_order_sn'],
                  'pack_id' => $inpackinfo['order_sn'],
                ];
           }else{
               return $this->renderError('包裹不存在');
           }
        }

        
        $res = (new ShelfUnitItem())->post($upShelf);
        if (!$res){
            return $this->renderError('包裹操作失败');
        }
        return $this->renderSuccess('包裹操作成功');
    }
    
      // 分拣员 - 分拣下架
    public function checkDownShelf(){
      // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
            return $this->renderError('角色权限非法');
      }
      $id = $this->postData('id')[0];
      // 查询包裹所在货位
      $pack =  (new Package());
      $packdata = $pack->where(['id'=>$id])->find();
  
      if(!empty($packdata)){
            $res = $shelfUnitItem = (new ShelfUnitItem())->where(['pack_id'=>$id])->delete();
            if(!$res){
                  return $this->renderError('包裹操作失败');
            }
           Logistics::add2($id,'包裹已拣货下架完毕,等待打包员进行打包',$clerk['clerk_id']); 
      }else{
          $inpackData = (new Inpack())->where('id',$id)->find();
     
          if(!empty($inpackData)){
            $res = $shelfUnitItem = (new ShelfUnitItem())->where(['pack_id'=>$inpackData['order_sn']])->whereOr('express_num',$inpackData['t_order_sn'])->delete();
            if(!$res){
                  return $this->renderError('包裹操作失败');
            }
          }
      }
      return $this->renderSuccess('下架成功');
    }
    
    // 拣货详情
    public function loadingpackdetails(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $packModel = (new Inpack());
        $id = $this->postData('id')?$this->postData('id')[0]:1;
        //完成集运单价格的计算；
        // getpackfree($id);
        
        $data = $packModel ->with(['member','inpackimage.file','wvimages.file','consumableslog.consumables'])->find($id);
        // dump($data->toArray());die;
        $InpackService = new InpackService();//包装服务 
        $data['service'] = $InpackService->with('service')->where('inpack_id',$id)->select();
        $pack =  (new Package());
        $shelf = new ShelfUnitItem();
        $data['packs']= $pack->where('inpack_id',$id)->where('is_delete',0)->with(['packageimage.file','packitem','shelfunititem.shelfunit.shelf'])->order(['is_scan asc','shelf_id desc'])->select();
        $data['countpack'] =count($data['packs']);
        $data['countnoscanpack'] =$pack->where('inpack_id',$id)->where('is_scan',1)->where('is_delete',0)->count();
        // 获取物品详情
        $data['shop'] = '';

    
        if ($data['address_id']){
            $data['address'] = (new UserAddress())->find($data['address_id']);
        }
         
        if ($data['line_id']){
            $data['line'] = (new Line())->field('name')->find($data['line_id']);
        }
        $data['total'] =round($data['free'] +  $data['other_free'] +  $data['pack_free'],2);
        $data['weight'] = number_format($pack->where('inpack_id',$id)->where('is_delete',0)->sum('weight'), 2, '.', '');
        return $this->renderSuccess($data);
    }

    // 分拣员 - 查询包裹，并下架
    public function checkPackInStorage(){
      // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      $code = $this->postData('code')[0];
   
    //   $map[] = ['status','in',[2,6,7]];
      $map[] = ['is_delete','=',0];
      $map[] = ['storage_id','=',$clerk['shop_id']];
    //   $map[] = ['is_take','=',2];
      $map[] = ['express_num','=',$code]; 
      $res = (new Package())->setQuery($map)->with('storage')->find();
      if (!$res){
        $Inpack = (new Inpack());
        $ress = $Inpack->where(['t_order_sn'=> $code,'is_delete'=>0])->where(['shop_id'=>$clerk['shop_id']])->find();
        if($ress){
            $ress['express_num'] = $code;
            return $this->renderSuccess($ress);
        }
        return $this->renderError('包裹未查询到');
      }
      return $this->renderSuccess($res);
    }
    
    
    /**
     * 该方法将扫描到的包裹状态设置为is_scan =2 已扫描状态，方便仓管核对
     * 2022年5月26日
     */
    public function checkPackScan(){
      
      // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      
      $code = $this->postData('code')[0];
      //特殊处理京东单号
      if(stristr($code,'JD')){
         $code = explode('-',$code)[0];
      }
      $jid = $this->postData('jid')[0];
      if(empty($jid)){
         return $this->renderError('集运单id不存在');  
      }
      $inpack= new Inpack();
      $packageData = $inpack->where('id',$jid)->find(); 
      if(empty($packageData)){
         return $this->renderError('集运单不存在');  
      }
    //   $arrayPackid = explode(',',$packageData['pack_ids']);
      $packid = (new Package())->where('express_num',$code)->where('is_delete',0)->find();
      if (!$packid){
        return $this->renderError('未查询到此快递单');
      }
      if($packid['inpack_id']==$jid){
           $res =(new Package())->where('express_num',$code)->update(['is_scan'=>2,'scan_time'=>getTime()]);
           return $this->renderSuccess($code);
      }else{
           return $this->renderError('此单号不在此集运单中');
      }
    }
    
    /***
     * 当前用户角色
     * 仓管员功能
     * parem: $id
    */
    public function role(){
         // 当前用户信息
        $userInfo = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if(!$userInfo){
            $role_name = '普通用户';
            $role_type = 0;
        }else{
            // dump($userInfo['clerk_type']);die;
            // $clerkType = explode(',',$userInfo['clerk_type']);
            if($userInfo['clerk_type'] ==10 ){
                $role_name = '仓库长';
                $role_type = 10;
            }else{
                $role_name = '仓管员';
                $role_type = 20;
            }
        }
         $userRole['role_name'] = $role_name;
         $userRole['role_type'] = $role_type;
         $this->userRole = $userRole;
         return $this->renderSuccess(compact('userRole'));
    }
    
    /***
     * 集运单状态变更
     * 仓管员封箱功能
     * parem: $id
     * param：$status
     */
    public function changeOrderStatus($id,$status){
        $inpack= new Inpack();
        $count = (new Package())->where('inpack_id',$id)->where('is_scan',1)->count();
        if($count>0){
            return $this->renderError('还有包裹未扫描');
        }
        $res = $inpack->where('id',$id)->update(['status' => $status,'pick_time' => getTime(),'print_status_jhd'=>1]);
        if($res){
        //进行通知打包员
        $clerkdd = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerkdd){
            return $this->renderError('角色权限非法');
        } 
        
         $packData = $inpack::detail($id);
         $clerk = (new Clerk())->where('shop_id',$packData['storage_id'])->where(['send_status'=>0,'is_delete' => 0])->select();
        //通知用户付款
        $noticesetting = SettingModel::getItem('notice');
        // dump($noticesetting);die;
       
        Logistics::addrfidLog($packData['order_sn'],$noticesetting['check']['describe'],getTime(),$clerkdd['clerk_id']);
        // 清理包裹跟货架的关系
        $packlist = (new Package())->where('inpack_id',$id)->where('is_delete',0)->select();
        log_write("打包完成订单ID：".$id);
        foreach ($packlist as $v){
           $result = (new ShelfUnitItem())->where('pack_id',$v['id'])->select();
           if(count($result)>0){
               log_write("统计出货位的数量：".count($result));
               foreach ($result as &$vv){
                  $shelf_unit_id = $vv['shelf_unit_id'];
                  $vv->delete();  //删除货位信息
                  //如果此货位不为空，则还有包裹在上面，就不请客
                  $ress = (new ShelfUnitItem())->where('shelf_unit_id',$shelf_unit_id)->find();
                  if(empty($ress)){
                      log_write("清空此货位ID：".$shelf_unit_id);
                      //清理用户跟货位的归属关系
                      (new shelfunit())->where('shelf_unit_id',$shelf_unit_id)->update(['user_id'=>0]);
                  }else{
                      log_write("不清空此货位ID：".$shelf_unit_id);
                  }
                  
               }
           } 
        }
        return $this->renderSuccess("包裹已封箱");
        }
    }
    
    //点击切换包裹扫描状态
    public function changeStatus(){
        $express_num = input('express_num');
        $scan = input('scan');
        if(!$express_num){
             return $this->renderError('参数错误');
        }
        $scan = $scan==1?2:1;
        $res =(new Package())->where('express_num',$express_num)->update(['is_scan'=>$scan,'scan_time'=>getTime()]);
        return $this->renderSuccess("操作成功");
    }

    // 分拣员 - 分拣上架
    public function checkUpShelf(){
        $id = $this->postData('id')[0];
        $shelf_id = $this->postData('shelf_id')[0];
        $length = $this->postData('length')[0];
        $width = $this->postData('width')[0];
        $height = $this->postData('height')[0];
        $weight = $this->postData('weight')[0];
        $pack_info = (new Package())->field('id,status,express_num,member_id')->find($id);
        if ($pack_info['status']!=2){
            return $this->renderError('包裹状态错误');
        }

        $shelf = (new ShelfUnit())->find($shelf_id);
        if (!$shelf){
            return $this->renderError('货架单元不存在');
        }
        $upShelf = [
          'shelf_unit' => $shelf_id,
          'created_time' => getTime(),
          'express_num' => $pack_info['express_num'],
          'user_id' => $pack_info['member_id'],
          'pack_id' => $id,
        ];
        $res = (new ShelfUnitItem())->post($upShelf);
        if (!$res){
            return $this->renderError('包裹操作失败');
        }
        $update['status'] = 3;
        $update['length'] = $length;
        $update['width'] = $width;
        $update['height'] = $height;
        $update['weight'] = $weight;
        $up = (new Package())->where(['id'=>$id])->update($update);
        Logistics::add2($id,'包裹已拣货查验',$clerk['clerk_id']);
        if (!$up){
          return $this->renderError('包裹操作失败');
        }
        return $this->renderSuccess('包裹已入货架');
    }
    
    // 分拣员 - 查询用户的包裹所在的货架id
    public function searchuserShelf(){
        $settingdata  = SettingModel::getItem('store');
        $user_id = $this->postData('user_id')[0];
        if($settingdata['usercode_mode']['is_show']==1){
            $userinfo = (new UserModel)->where(['user_code'=>$user_id,'is_delete'=>0])->find();
            $user_id = $userinfo['user_id'];
        }else{
            $userinfo = UserModel::detail($user_id);
        }
      
        $shelfunititem = (new ShelfUnitItem())->where('user_id',$user_id)->where('shelf_unit_id','>',0)->order('created_time desc')->find();
        $shelfunit = (new ShelfUnit())->where('shelf_unit_id',$shelfunititem['shelf_unit_id'])->find();
        $shelf = (new Shelf())->where('id',$shelfunit['shelf_id'])->find();
        if(!empty($shelfunititem)){
            $data = [
                'shelf_unit_id'=>$shelfunititem['shelf_unit_id'],
                'shelf_unit'=> $shelfunit,
                'shelf'=> $shelf
            ];
           return $this->renderSuccess($data,'包裹已入货架',''); 
        }
        //   dump($shelf['shelf_unit_id']);die;
        return $this->renderError('请扫码货架码');
    }
    
 // 查询用户的专属货位，如果没有则自动分配一个
    public function getOrAllocateUserShelf(){
        $settingdata = SettingModel::getItem('store');
        $user_id = $this->postData('user_id')[0];
        $wxapp_id = \request()->get('wxapp_id');
        $real_user_id = null;
        
        // 如果提供了用户ID或编号，获取用户信息
        if(!empty($user_id)){
            // 根据用户编号或用户ID获取用户信息
            if($settingdata['usercode_mode']['is_show']==1){
                $userinfo = (new UserModel)->where(['user_code'=>$user_id,'is_delete'=>0])->find();
                if(!empty($userinfo)){
                    $real_user_id = $userinfo['user_id'];
                }
            }else{
                $userinfo = UserModel::detail($user_id);
                if(!empty($userinfo)){
                    $real_user_id = $userinfo['user_id'];
                }
            }
        }
        
        // 如果有用户ID，查询用户的专属货位（基于ShelfUnit表的user_id字段）
        if(!empty($real_user_id)){
            $userShelfUnit = $this->getUserBindShelfUnits($real_user_id, $wxapp_id);
            
            if(!empty($userShelfUnit)){
                // 找到专属货位，返回信息
                $shelf = (new Shelf())->where('id',$userShelfUnit['shelf_id'])->find();
                $data = [
                    'shelf_unit_id' => $userShelfUnit['shelf_unit_id'],
                    'shelf_unit' => $userShelfUnit,
                    'shelf' => $shelf
                ];
                return $this->renderSuccess($data, '查询到专属货位');
            }
        }
        
        // 没有专属货位或没有用户ID，使用fenpeihuowei的逻辑来分配
        $keepersetting = SettingModel::getItem('keeper', $wxapp_id);
        $selectedShelfUnitId = null;
        
        // 使用和fenpeihuowei相同的分配逻辑
        if(!empty($real_user_id)){
            // 如果有用户ID，但没有专属货位，看后台是否来决定是否给用户自动分配
            if(isset($keepersetting['shopkeeper']['is_auto_setshelfuser']) && $keepersetting['shopkeeper']['is_auto_setshelfuser']==1){
                //如果没有专属货位，就查询出空货位并给他分配一个，并绑定货位跟用户
                $emptyShelfUnits = $this->getEmptyShelfUnits($wxapp_id, 1);
                if(empty($emptyShelfUnits)){
                    return $this->renderError('没有可用的空货位，请添加更多货位');
                }
                $selectedShelfUnitId = $emptyShelfUnits[0]['shelf_unit_id'];
                (new ShelfUnit())->where('shelf_unit_id', $selectedShelfUnitId)->update([
                    'user_id' => $real_user_id
                ]);
            }else{
                //如果没有归属货位，并且也没有需要自动分配货位，就先查询下该用户是否有其他包裹有在货位上的，放在相同货位 
                $userShelfUnits = $this->getUserOtherPackagesShelfUnits($real_user_id, $wxapp_id, 1);
                if(!empty($userShelfUnits) && count($userShelfUnits)>0){
                    // 找到用户其他包裹的货位，优先分配
                    $selectedShelfUnitId = $userShelfUnits[0]['shelf_unit_id'];
                }
                // 找到包裹数量最少的货位分配
                if(empty($selectedShelfUnitId)){
                    $leastPackagesShelfUnits = $this->getLeastPackagesShelfUnits($wxapp_id, 1);
                    if(!empty($leastPackagesShelfUnits) && count($leastPackagesShelfUnits)>0){
                        $selectedShelfUnitId = $leastPackagesShelfUnits[0]['shelf_unit_id'];
                    }
                }
            }
        }else{
            // 如果没有用户ID，查询无主货架，随机分配一个无主货架
            $emptyShelfUnits = $this->getNouserShelfUnits($wxapp_id);
            if(!empty($emptyShelfUnits)){
                $selectedShelfUnitId = $emptyShelfUnits['shelf_unit_id'];
            }else{
                //如果没有无主货架，找到包裹数量最少的货位分配
                $leastPackagesShelfUnits = $this->getLeastPackagesShelfUnits($wxapp_id, 1);
                if(!empty($leastPackagesShelfUnits) && count($leastPackagesShelfUnits)>0){
                    $selectedShelfUnitId = $leastPackagesShelfUnits[0]['shelf_unit_id'];
                }
            }
        }
        
        if(empty($selectedShelfUnitId)){
            return $this->renderError('没有可用的货位');
        }
        
        // 查询分配后的货位信息
        $allocatedShelfUnit = (new ShelfUnit())->where('shelf_unit_id', $selectedShelfUnitId)->find();
        if(empty($allocatedShelfUnit)){
            return $this->renderError('货位不存在');
        }
        
        $shelf = (new Shelf())->where('id', $allocatedShelfUnit['shelf_id'])->find();
        
        $data = [
            'shelf_unit_id' => $selectedShelfUnitId,
            'shelf_unit' => $allocatedShelfUnit,
            'shelf' => $shelf
        ];
        
        $msg = !empty($real_user_id) ? '已为用户分配专属货位' : '已获取货位';
        return $this->renderSuccess($data, $msg);
    }

    public function createdOrderByScan(){
       // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      $code = $this->postData('code')[0];
      $package = (new Package())->where(['express_num'=>$code])->find();
      if (!$package){
          return $this->renderError('包裹不存在');
      }
      if ($package['status']!=7){
         return $this->renderError('该包裹未处在已拣货状态');
      }
      $order = [
        'order_sn' => 'P'.createSn(),
        'pack_ids' => $package['id'],
        'num' => 1,
        'opration_name' => $clerk['real_name'],
        'opration_id' => $clerk['user_id'],
      ];
      // 创建预发货单
      $res = (new SendPreOrder())->post($order);
      (new Package())->where(['id'=>$package['id']])->update(['status'=>8]);
      Logistics::add2($package['id'],'包裹已打包封箱,等待集中发货',$clerk['clerk_id']);
      $map['storage_id'] = $clerk['shop_id'];
      (new Inpack())->CheckISPack($map); 
      if (!$res){
        return $this->renderError('打包封箱失败');
      }
      return $this->renderSuccess('打包封箱完成');
    }

    // 仓库打包员 封箱
    public function createdOrder(){
         // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
         $id = $this->postData('id')[0];
         if(empty($id)){
            return $this->renderError('包裹封箱参数错误');
         }
         $package = (new Inpack())->find($id);
         if (!$package) 
             return $this->renderError('包裹封箱参数错误');
         $check = (new Inpack())->checkPack($package);
         if (!$check){
             return $this->renderError('包裹状态错误,请检查包裹是否分拣完成');
         }   
         $order = [
           'order_sn' => 'P'.createSn(),
           'pack_ids' => $package['pack_ids'],
           'num' => count(explode(',',$package['pack_ids'])),
           'opration_name' => $clerk['real_name'],
           'opration_id' => $clerk['user_id'],
         ];
         $packIds = explode(',',$package['pack_ids']);
         // 创建预发货单
         $res = (new SendPreOrder())->post($order);
         (new Package())->whereIn('id',$packIds)->update(['status'=>8]);
         (new Inpack())->where('id',$id)->update(['status'=>5]);
         foreach($packIds as $v){
             Logistics::add2($v,'包裹已打包封箱,等待集中发货',$clerk['clerk_id']);
         } 
         if (!$res){
           return $this->renderError('打包封箱失败');
         }
         return $this->renderSuccess('打包封箱完成');
    }

    // 上一个 入库单号
    public function lastEnterPack(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $data = (new Package())->where(['storage_id'=>$clerk['shop_id']])->whereIn('status',[2,3,4,5,6,7,8,9,10,11])->field('id,express_num')->order("entering_warehouse_time DESC")->find();
        return $this->renderSuccess($data);
    }
}
