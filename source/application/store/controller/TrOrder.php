<?php
namespace app\store\controller;
use app\api\model\Logistics;
use app\store\model\Inpack;
use app\store\model\InpackItem;
use app\store\model\InpackDetail;
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
use app\common\model\InpackImage;
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
use app\common\library\Ditch\Hualei;
use app\store\model\Countries;
use app\store\model\LineService;
use app\store\model\user\PointsLog as PointsLogModel;
use think\Db;
use Mpdf\Mpdf;
use app\common\library\Ditch\Zto;
use app\common\library\Ditch\Sf;

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
     * 查看子订单详情
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function InpackItemdetail(){
        $param = $this->request->param();
        $InpackItem = new InpackItem();
        $detail = $InpackItem->details($param['id']);
        return $this->renderSuccess('添加成功','',compact('detail'));
    }
    
    /**
     * 修改子订单
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function editInpackItem(){
        $param = $this->request->param();
        $InpackItem = new InpackItem();
        if($InpackItem->editItem($param['inpack'])){
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($InpackItem->getError() ?: '修改失败');
    }
    
    /**
     * 订单支付审核列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function payment_audit(){
        return $this->getPaymentAuditList('订单支付审核列表', 'payment_audit');
    }
    
    
    /**
     * 复制订单
     * @return array
     * @throws \think\exception\DbException
     */
    public function copyOrder(){
        $id = $this->request->param('id');
        if(empty($id)){
            return $this->renderError('订单ID不能为空');
        }
       
        $model = new Inpack();
        // 先检查订单是否存在
        $originalOrder = $model->where('id', $id)->find();
        if(empty($originalOrder)){
            return $this->renderError('原订单不存在');
        }
        
        // 开始事务
        Db::startTrans();
        try {
            // 直接从数据库获取原始数据（使用Db查询避免访问器问题）
            $orderData = Db::name('inpack')->where('id', $id)->find();
            if(empty($orderData)){
                throw new \Exception('获取订单数据失败');
            }
            
            // 排除不需要复制的字段
            $excludeFields = ['id', 'order_sn', 't_order_sn', 't2_order_sn', 'pay_order', 'pay_time', 
                            'created_time', 'updated_time', 'unpack_time', 'shoprk_time', 'receipt_time'];
            
            // 准备新订单数据
            $newOrderData = [];
            foreach($orderData as $key => $value){
                if(!in_array($key, $excludeFields)){
                    // 跳过对象和null值（某些字段可能是null）
                    if(is_object($value)){
                        continue;
                    }
                    $newOrderData[$key] = $value;
                }
            }
            
            // 获取系统设置
            $storeSetting = Setting::detail('store')['values'];
            
            // 根据系统设置生成订单号（优先使用自定义规则）
            $newOrderSn =createSn();
            if(isset($storeSetting['orderno']['default']) && !empty($storeSetting['orderno']['default'])){
                // 优先使用自定义订单号生成规则
                $user_id = $orderData['member_id'];
                // 如果使用用户编号模式
                if(isset($storeSetting['usercode_mode']['is_show']) && $storeSetting['usercode_mode']['is_show'] == 1){
                    $userModel = new User();
                    $member = $userModel->where('user_id', $orderData['member_id'])->find();
                    if($member && !empty($member['user_code'])){
                        $user_id = $member['user_code'];
                    }
                }
                // 计算序号（该用户的订单数量+1）
                $xuhao = $model->where(['member_id' => $orderData['member_id'], 'is_delete' => 0])->count() + 1;
                // 获取仓库简称
                $shop_alias_name = 'XS';
                if(!empty($orderData['storage_id'])){
                    $shop = ShopModel::detail($orderData['storage_id']);
                    if($shop && !empty($shop['shop_alias_name'])){
                        $shop_alias_name = $shop['shop_alias_name'];
                    }
                }
                $createSnfistword = isset($storeSetting['createSnfistword']) ? $storeSetting['createSnfistword'] : 'XS';
                $newOrderSn = createNewOrderSn(
                    $storeSetting['orderno']['default'], 
                    $xuhao, 
                    $createSnfistword, 
                    $user_id, 
                    $shop_alias_name, 
                    $orderData['country_id']
                );
            }
            
            // 设置新订单的特殊字段
            $newOrderData['order_sn'] = $newOrderSn; // 使用系统设置规则生成新订单号
            $newOrderData['parent_id'] = $id; // 保存母单ID
            $newOrderData['status'] = 1; // 重置为待查验状态
            $newOrderData['is_pay'] = 2; // 重置为未支付
            $newOrderData['pay_time'] = null; // 清空支付时间
            $newOrderData['created_time'] = getTime(); // 设置创建时间
            $newOrderData['updated_time'] = getTime(); // 设置更新时间
            $newOrderData['unpack_time'] = null; // 清空打包时间
            $newOrderData['shoprk_time'] = null; // 清空入库时间
            $newOrderData['receipt_time'] = null; // 清空收货时间
            $newOrderData['real_payment'] = 0; // 重置实付金额
            $newOrderData['inpack_type'] = 0; 
            $newOrderData['pack_ids'] = ''; // 清空包裹ID
            // 清空批次ID和拼团ID（如果存在且大于0）
            if(isset($newOrderData['batch_id']) && $newOrderData['batch_id'] > 0){
                $newOrderData['batch_id'] = 0;
            }
            if(isset($newOrderData['share_id']) && $newOrderData['share_id'] > 0){
                $newOrderData['share_id'] = 0;
            }
            
            // 确保所有必需字段都有值
            if(!isset($newOrderData['wxapp_id']) || empty($newOrderData['wxapp_id'])){
                $newOrderData['wxapp_id'] = isset($orderData['wxapp_id']) ? $orderData['wxapp_id'] : 0;
            }
            
            // 插入新订单
            $newOrderId = $model->insertGetId($newOrderData);
            if(!$newOrderId){
                throw new \Exception('创建新订单失败');
            }
            
            // 提交事务
            Db::commit();
            return $this->renderSuccess('订单复制成功，新订单号：' . $newOrderData['order_sn']);
            
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $this->renderError('复制订单失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取待审核订单列表
     * @param string $title
     * @param string $dataType
     * @return mixed
     */
    private function getPaymentAuditList($title, $dataType)
    {
        // 订单列表
        $model = new Inpack;
        $Line = new Line;
        $Clerk = new Clerk;
        $Track = new Track;
        $set = Setting::detail('store')['values'];
        $userclient = Setting::detail('userclient')['values'];
        $adminstyle = Setting::detail('adminstyle')['values'];
        $params = $this->request->param();
        if(!isset($params['limitnum'])){
            $params['limitnum'] = isset($adminstyle['pageno'])?$adminstyle['pageno']['inpack']:15;
        }
        $list = $model->getPaymentAuditList($dataType, $params);
        
        $tracklist = $Track->getAllList();
        $servicelist = $Clerk->where('clerk_authority','like','%is_myuser%')->where('clerk_authority','like','%is_myuserpackage%')->where('is_delete',0)->select();
        $pintuanlist = (new SharingOrder())->getAllList();
        $batchlist = (new Batch())->getAllwaitList([]);
        $shopList = ShopModel::getAllList();
        $lineList = $Line->getListAll();
        
        if(isset($adminstyle['pageno']['inpacktype']) && $adminstyle['pageno']['inpacktype']==20){
          return $this->fetch('newindex', compact('adminstyle','list','dataType','set','pintuanlist','shopList','lineList','servicelist','userclient','batchlist','tracklist'));  
        }
        return $this->fetch('index', compact('adminstyle','list','dataType','set','pintuanlist','shopList','lineList','servicelist','userclient','batchlist','tracklist'));
    }
    
    /**
     * 批量设置订单支付状态
     * @return array
     * @throws \think\exception\DbException
     */
    public function batchPayStatus(){
        $params = $this->request->param();
        if(empty($params['selectIds'])){
            return $this->renderError('请选择订单');
        }
        $payStatus = $this->request->param('pay_status');
        if(empty($payStatus)){
            return $this->renderError('请选择支付状态');
        }
        // 确保是单个值，不是数组
        if(is_array($payStatus)){
            $payStatus = $payStatus[0] ?? '';
        }
        $payStatus = (int)$payStatus;
        // 验证支付状态值
        if(!in_array($payStatus, [1, 2, 3])){
            return $this->renderError('支付状态值不正确');
        }
        
        // 获取支付方式
        $payType = $this->request->param('pay_type');
        // 确保是单个值，不是数组
        if(is_array($payType)){
            $payType = $payType[0] ?? '';
        }
        if($payType !== ''){
            $payType = (int)$payType;
            // 验证支付方式值
            if(!in_array($payType, [0, 1, 2, 3, 4, 5, 6])){
                return $this->renderError('支付方式值不正确');
            }
        }
        
        $idsArr = is_array($params['selectIds']) ? $params['selectIds'] : explode(',', $params['selectIds']);
        $model = new Inpack();
        
        $updateData = ['is_pay' => $payStatus];
        // 如果设置了支付方式，则更新
        if($payType !== ''){
            $updateData['is_pay_type'] = $payType;
        }
        // 如果设置为已支付，更新支付时间
        if($payStatus == 1){
            $updateData['pay_time'] = getTime();
        }
        
        $successCount = 0;
        $failCount = 0;
        foreach ($idsArr as $id){
            $order = $model->where(['id' => $id])->find();
            if($order){
                $result = $model->where('id', $id)->update($updateData);
                if($result){
                    $successCount++;
                } else {
                    $failCount++;
                }
            } else {
                $failCount++;
            }
        }
        
        if($successCount > 0){
            $msg = "成功设置 {$successCount} 个订单的支付状态";
            if($failCount > 0){
                $msg .= "，{$failCount} 个订单设置失败";
            }
            return $this->renderSuccess($msg);
        } else {
            return $this->renderError('设置失败，请检查订单是否存在');
        }
    }
    
    /**
     * 删除子订单
     * @param $delivery_id
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function deleteInpackItem($id)
    {
        $model = new InpackItem();
        if (!$model->deletes($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
    
    
            
    /**
     * 添加订单申报
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function addInpackDetail(){
        $param = $this->request->param();
        $InpackDetail = new InpackDetail();
        if($InpackDetail->addItem($param['inpack'])){
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($InpackDetail->getError() ?: '添加失败');
    }
    
    /**
     * 查看订单申报详情
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function Inpackdetaildetail(){
        $param = $this->request->param();
        $InpackDetail = new InpackDetail();
        $detail = $InpackDetail::detail($param['id']);
        return $this->renderSuccess('添加成功','',compact('detail'));
    }
    
    
    /**
     * 修改订单申报
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function editInpackDetail(){
        $param = $this->request->param();
        $InpackDetail = new InpackDetail();
        if($InpackDetail->editItem($param['inpack'])){
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($InpackDetail->getError() ?: '修改失败');
    }
    
    
    /**
     * 删除订单申报
     * @param $delivery_id
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function deleteInpackDetail($id)
    {
        $model = new InpackDetail();
        if (!$model->deletes($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
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
        $userclient =  Setting::detail('userclient')['values'];
        foreach ($list as &$value) {
            $value['num'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->count();
            $value['sonnum'] =  (new InpackItem())->where(['inpack_id'=>$value['id']])->count();
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
           if ($dataType=='payed'){
                $value['down_shelf'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->where('status',7)->count();
                $value['inpack'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->where('status',8)->count();
           }
        }

        return $this->fetch('index', compact('list','dataType','set','userclient'));
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
        $userclient =  Setting::detail('userclient')['values'];
        foreach ($list as &$value) {
            $value['num'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->count();
            $value['sonnum'] =  (new InpackItem())->where(['inpack_id'=>$value['id']])->count();
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
        }

        return $this->fetch('index', compact('list','dataType','set','lineList','userclient'));
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
        
        $storesetting = SettingModel::getItem('store',$this->getWxappId());
        if($storesetting['usercode_mode']['is_show']!=0){
              $member = UserModel::detail($detail['member_id']);
              $detail['member_id'] = $member['user_code'];
           }
        
        $address = (new UserAddress())->where(['address_id'=>$detail['address_id']])->find();
        // dump($address);die;
        $xuhao = ((new Inpack())->where(['member_id'=>$detail['member_id'],'is_delete'=>0])->count()) + 1;
        $batch = createNewOrderSn($settingDate['orderno']['default'],$xuhao,$settingDate['orderno']['first_title'],$detail['member_id'],$shopname['shop_alias_name'],$address['country_id']);
        return $this->renderSuccess('获取成功','',$batch);
    }

    
     /**
     * 渠道列表
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function getProductList(){
        $param = $this->request->param();
        
        // 使用缓存获取渠道配置
        $ditchdetail = \app\common\service\DitchCache::getConfig($param['ditch_no']);
        if (!$ditchdetail) {
            return $this->renderError('渠道配置不存在');
        }
      
        if($ditchdetail['ditch_no']==10004){
            if(!empty($ditchdetail['product_json'])){
                // 1. 将 &quot; 替换为双引号
                $data_str = html_entity_decode($ditchdetail['product_json']);
                // 3. 将字符串转换为 PHP 数组或对象
                $data_array = json_decode($data_str,true); // true 表示转换为数组，false 表示转换为对象
                return $this->renderSuccess('获取成功','', $data_array); 
            }
            $Hualei =  new Hualei(['key'=>$ditchdetail['app_key'],'token'=>$ditchdetail['app_token'],'apiurl'=>$ditchdetail['api_url']]);
            return $this->renderSuccess('获取成功','', $Hualei->getProductList()); 
        }
        
        
        
        return $this->renderError("获取失败");
    }
    


    /**
     * 推送至渠道商系统
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function sendtoqudaoshang()
    {   
        $param = $this->request->param();
        
        // 🔍 调试日志：记录方法调用
        \app\common\service\PrintLogger::info('推送到渠道商', '开始执行 sendtoqudaoshang', [
            'params' => $param,
            'has_id' => isset($param['id']),
            'id_value' => isset($param['id']) ? $param['id'] : 'NOT_SET'
        ]);
        
        // 检查必需参数
        if (!isset($param['id']) || empty($param['id'])) {
            \app\common\service\PrintLogger::error('推送到渠道商', '缺少订单ID参数', [
                'params' => $param
            ]);
            return $this->renderError('订单ID不能为空');
        }
        
        if (!isset($param['ditch_id']) || empty($param['ditch_id'])) {
            \app\common\service\PrintLogger::error('推送到渠道商', '缺少渠道ID参数', [
                'params' => $param
            ]);
            return $this->renderError('渠道ID不能为空');
        }
        
        $Inpack =new Inpack;
        $DitchModel = new DitchModel();
        $settingDate = SettingModel::getItem('adminstyle',$this->getWxappId());
        
        \app\common\service\PrintLogger::info('推送到渠道商', '准备加载订单详情', [
            'order_id' => $param['id']
        ]);
        
        try {
            $detail = Inpack::details($param['id']);
            
            if (!$detail) {
                \app\common\service\PrintLogger::error('推送到渠道商', '订单不存在', [
                    'order_id' => $param['id']
                ]);
                return $this->renderError('订单不存在');
            }
        } catch (\Exception $e) {
            \app\common\service\PrintLogger::error('推送到渠道商', '加载订单详情失败', [
                'order_id' => $param['id'],
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->renderError('加载订单详情失败：' . $e->getMessage());
        }
        
        // 🔍 调试日志：记录订单详情
        \app\common\service\PrintLogger::info('推送到渠道商', '订单详情已加载', [
            'order_id' => $param['id'],
            'order_sn' => $detail['order_sn'] ?? '',
            'storage_id' => $detail['storage_id'] ?? ''
        ]);
        
        $shopname = ShopModel::detail($detail['storage_id']);
        $address = (new UserAddress())->where(['address_id'=>$detail['address_id']])->find();
        
        // 使用缓存获取渠道配置
        $ditchdetail = \app\common\service\DitchCache::getConfig($param['ditch_id']);
        if (!$ditchdetail) {
            \app\common\service\PrintLogger::error('推送到渠道商', '渠道配置不存在', [
                'ditch_id' => $param['ditch_id']
            ]);
            return $this->renderError('渠道配置不存在');
        }
        
        // 🔍 调试日志：记录渠道信息
        \app\common\service\PrintLogger::info('推送到渠道商', '渠道配置已加载', [
            'ditch_id' => $param['ditch_id'],
            'ditch_no' => $ditchdetail['ditch_no'],
            'ditch_name' => $ditchdetail['ditch_name'] ?? '',
            'ditch_type' => $ditchdetail['ditch_type'] ?? 0
        ]);
        
        $countrydetail = (new Countries())->where('id',$detail['address']['country_id'])->find();
        $result = [];

        if($ditchdetail['ditch_no']==10004){
            $orderInvoiceParam = [];
            $orderVolumeParam = [];
            $i = 0;
            $j = 0;
            if(count($detail['inpackdetail'])>0){
                foreach ($detail['inpackdetail'] as $key=>$value){
                    $orderInvoiceParam[$i]['invoice_amount'] =  $value['total_free'];
                    $orderInvoiceParam[$i]['invoice_pcs'] =  $value['unit_num'];
                    $orderInvoiceParam[$i]['invoice_title'] =  $value['goods_name_en'];
                    $orderInvoiceParam[$i]['sku'] =  $value['goods_name'];
                    $orderInvoiceParam[$i]['sku_code'] =  $value['distribution'];
                    $orderInvoiceParam[$i]['hs_code'] =  $value['customs_code'];
                    $orderInvoiceParam[$i]['invoice_weight'] =  $value['unit_weight'];
                    $i +=1;
                }
            }


            if(count($detail['packageitems'])>0){
                foreach ($detail['packageitems'] as $key=>$value){
                  $orderVolumeParam[$j]['volume_height'] = $value['height'];
                  $orderVolumeParam[$j]['volume_length'] = $value['length'];
                  $orderVolumeParam[$j]['volume_width'] = $value['width'];
                  $orderVolumeParam[$j]['volume_weight'] = $value['weight'];
                  $j +=1;
                }
            }else{
                $orderVolumeParam[$j]['volume_height'] =  $detail['height'];
                $orderVolumeParam[$j]['volume_length'] =  $detail['length'];
                $orderVolumeParam[$j]['volume_width'] =  $detail['width'];
                $orderVolumeParam[$j]['volume_weight'] =  $detail['cale_weight'];
            }

            $data = [
                "buyerid"=>"",
                "order_piece"=> 1,//件数，小包默认1，快递需真实填写
                "consignee_mobile"=>$detail['address']['phone'],
                "trade_type"=>"ZYXT",
                "consignee_name"=> $detail['address']['name'],
                "consignee_address"=>$detail['address']['detail'],
                "consignee_telephone"=>$detail['address']['phone'],
                "country"=>$countrydetail['code'],//收件国家二字代码，必填
                "consignee_state"=>$detail['address']['province'],
                "consignee_city"=>$detail['address']['city'],
                "consignee_suburb"=>$detail['address']['region'],
                "consignee_postcode"=>$detail['address']['code'],
                "consignee_streetno"=>$detail['address']['street'],
                "consignee_doorno"=>$detail['address']['door'],
                "customer_id"=>$ditchdetail['app_key'],
                "customer_userid"=>$ditchdetail['app_key'],
                "order_customerinvoicecode"=>$detail['order_sn'],
                "product_id"=>$param['product_id'],
                "weight"=>$detail['cale_weight'],
                "order_insurance"=>$detail['insure_free'],
                "cargo_type"=>"P",
                "orderInvoiceParam"=>$orderInvoiceParam,
                "orderVolumeParam"=>$orderVolumeParam
            ];
            $Hualei =  new Hualei(['key'=>$ditchdetail['app_key'],'token'=>$ditchdetail['app_token'],'apiurl'=>$ditchdetail['api_url']]);
            $result = $Hualei->createOrderApi($data);
            if($result['ack']==true){
                $detail->pushSuccessComplete($param['id'], [
                    't_order_sn' => $result['tracking_number'],
                    't_name'     => $ditchdetail['ditch_name'],
                    't_number'   => $ditchdetail['ditch_id'],
                    'transfer'   => 0
                ]);
            }
        }
            
        if($ditchdetail['ditch_no']==10009 || (isset($ditchdetail['ditch_type']) && in_array((int)$ditchdetail['ditch_type'], [2, 3], true))){
            $storage = ($shopname && is_object($shopname)) ? $shopname->toArray() : [];
            $region = isset($storage['region']) && is_array($storage['region']) ? $storage['region'] : [];
            
            // 优先检查渠道配置中的自定义发货地址 (sender_json)
            $customSender = [];
            if (!empty($ditchdetail['sender_json'])) { // 假设DB字段叫 sender_json 或者 product_json 里的某个key，这里先假设有个扩展字段
                 $decodedSender = json_decode(html_entity_decode($ditchdetail['sender_json']), true);
                 if (is_array($decodedSender) && !empty($decodedSender['province'])) {
                     $customSender = $decodedSender;
                 }
            }

            // 构造基础地址
            $senderProvince = '上海';
            $senderCity = '上海市';
            $senderDistrict = '青浦区';
            $senderAddress = '默认地址';
            $senderName = '集运仓';
            $senderPhone = '13800138000';

            // 1. 尝试从渠道配置获取 (Highest Priority)
            // 优先检查独立字段，其次检查 JSON
            if (!empty($ditchdetail['sender_province'])) {
                $senderProvince = $ditchdetail['sender_province'];
                $senderCity     = $ditchdetail['sender_city'];
                $senderDistrict = $ditchdetail['sender_district'];
                $senderAddress  = $ditchdetail['sender_address'];
                $senderName     = $ditchdetail['sender_name'] ?: $senderName;
                $senderPhone    = $ditchdetail['sender_phone'] ?: $senderPhone;
            } elseif (!empty($customSender)) {
                $senderProvince = isset($customSender['province']) ? $customSender['province'] : $senderProvince;
                $senderCity     = isset($customSender['city'])     ? $customSender['city']     : $senderCity;
                $senderDistrict = isset($customSender['district']) ? $customSender['district'] : $senderDistrict;
                $senderAddress  = isset($customSender['address'])  ? $customSender['address']  : $senderAddress;
                if (!empty($customSender['name'])) $senderName = $customSender['name'];
                if (!empty($customSender['phone'])) $senderPhone = $customSender['phone'];
            }
            // 2. 尝试从仓库获取 (Fallback)
            elseif (!empty($region) && !empty($region['province'])) {
                $senderProvince = $region['province'];
                $senderCity     = isset($region['city']) ? $region['city'] : $region['city'];
                $senderDistrict = isset($region['region']) ? $region['region'] : $region['region'];
                $senderAddress  = isset($storage['address']) && $storage['address'] ? $storage['address'] : $senderAddress;
                // 仓库联系人
                $senderName = isset($storage['linkman']) && $storage['linkman'] ? $storage['linkman'] : $senderName;
                $senderPhone = isset($storage['phone']) && $storage['phone'] ? $storage['phone'] : $senderPhone;
            }
            
            $commonData = [
                'consignee_name'     => isset($detail['address']['name']) ? $detail['address']['name'] : '',
                'consignee_mobile'   => isset($detail['address']['phone']) ? $detail['address']['phone'] : '',
                'consignee_telephone'=> isset($detail['address']['phone']) ? $detail['address']['phone'] : '',
                'consignee_address'  => isset($detail['address']['detail']) ? $detail['address']['detail'] : '',
                'consignee_state'    => isset($detail['address']['province']) ? $detail['address']['province'] : '',
                'consignee_city'     => isset($detail['address']['city']) ? $detail['address']['city'] : '',
                'consignee_suburb'   => isset($detail['address']['region']) ? $detail['address']['region'] : '',
                'consignee_postcode' => isset($detail['address']['code']) ? $detail['address']['code'] : '',
                'country'            => $countrydetail['code'],
                'sender_name'        => $senderName,
                'sender_phone'       => $senderPhone,
                'sender_mobile'      => $senderPhone,
                'sender_province'    => $senderProvince,
                'sender_city'        => $senderCity,
                'sender_district'    => $senderDistrict,
                'sender_address'     => $senderAddress,
            ];

            // --- 快递管家 (ZTO Manager) 功能升级 2026-01-30 ---
            $pushConfigStr = !empty($ditchdetail['push_config_json']) ? html_entity_decode($ditchdetail['push_config_json']) : '';
            $pushConfig = !empty($pushConfigStr) ? json_decode($pushConfigStr, true) : [];
            
            // 1. goodsPath (图片 URL)
            $goodsPath = '';
            if (!empty($detail['inpackimage']) && count($detail['inpackimage']) > 0) {
                $firstImage = $detail['inpackimage'][0];
                if (isset($firstImage['file']['file_path'])) {
                    $goodsPath = $firstImage['file']['file_path'];
                }
            }
            $commonData['goodsPath'] = $goodsPath;

            // 2. skuPropertiesName (包裹单号拼接)
            if (isset($pushConfig['enableSkuPropertiesName']) && $pushConfig['enableSkuPropertiesName']) {
                $packageNos = [];
                if (isset($detail['packagelist'])) {
                    foreach ($detail['packagelist'] as $pkg) {
                        if (!empty($pkg['express_num'])) $packageNos[] = $pkg['express_num'];
                    }
                }
                $skuName = implode(',', $packageNos);
                $commonData['skuPropertiesName'] = mb_substr($skuName, 0, 200);
            }

            // 3. goodsTitle (商品标题规则匹配)
            if (isset($pushConfig['enableGoodsTitle']) && $pushConfig['enableGoodsTitle'] && !empty($pushConfig['goodsTitleRules'])) {
                $rules = $pushConfig['goodsTitleRules'];
                usort($rules, function($a, $b) {
                    return (isset($a['priority']) ? $a['priority'] : 99) - (isset($b['priority']) ? $b['priority'] : 99);
                });
                
                $matchedTitle = '';
                foreach ($rules as $rule) {
                    if (isset($rule['status']) && (int)$rule['status'] === 1) {
                         $matchedTitle = $rule['title'];
                         break;
                    }
                }
                if (!empty($matchedTitle)) {
                    $commonData['goodsTitle'] = $matchedTitle;
                }
            }

            // 4. payDate (支付时间 yyyy-MM-dd)
            if (isset($pushConfig['enablePayDate']) && $pushConfig['enablePayDate'] && !empty($detail['pay_time'])) {
                 $payTime = is_numeric($detail['pay_time']) ? $detail['pay_time'] : strtotime($detail['pay_time']);
                 if ($payTime > 0) {
                     $commonData['payDate'] = date('Y-m-d H:i:s', $payTime);
                 }
            }

            // 5. buyerMessage (留言截取 500 字符)
            // 总是传递原始留言，由 Zto.php 决定是否使用积木配置覆盖
            $commonData['buyerMessage'] = mb_substr($detail['remark'], 0, 500);

            // 6. sellerMessage (后台备注截取 500 字符)
            // 优先使用 inpack 表的 remark (如果区分的话) 或其他备注字段
            $sellerRemark = isset($detail['admin_remark']) ? $detail['admin_remark'] : '';
            $commonData['sellerMessage'] = mb_substr($sellerRemark, 0, 500);
            
            // 构造 MessageBuilder 所需的完整上下文数据 (Full Context)
            $goodsNames = [];
            $goodsList = [];
            if (isset($detail['inpackdetail']) && is_array($detail['inpackdetail'])) {
                foreach ($detail['inpackdetail'] as $item) {
                    $goodsNames[] = isset($item['goods_name']) ? $item['goods_name'] : '';
                    $goodsList[] = [
                        'goodsTitle' => isset($item['goods_name']) ? $item['goods_name'] : '',
                        'sku' => isset($item['goods_name']) ? $item['goods_name'] : '',
                        'qty' => isset($item['unit_num']) ? $item['unit_num'] : 1,
                        'price' => isset($item['total_free']) ? $item['total_free'] : 0,
                    ];
                }
            }
            
            // 获取服务列表
            $serviceNames = [];
            try {
                // 手动加载服务关联 (因为 details 默认可能不包含)
                if (isset($detail['id'])) {
                     $services = \app\store\model\InpackService::where('inpack_id', $detail['id'])
                        ->with(['service'])
                        ->select();
                     foreach ($services as $svc) {
                         if (isset($svc['service']['name'])) {
                             $serviceNames[] = $svc['service']['name'];
                         }
                     }
                }
            } catch (\Exception $e) {
                // 忽略错误，防止阻断流程
            }

            // 支付状态映射
            $payStatusMap = [
                1 => '已支付',
                2 => '未支付',
                3 => '待审核',
            ];
            $payStatusText = isset($payStatusMap[$detail['is_pay']]) ? $payStatusMap[$detail['is_pay']] : '未知';

            $fullContext = [
                'create_time' => isset($detail['create_time']) ? $detail['create_time'] : 0,
                'pay_time' => isset($detail['pay_time']) ? $detail['pay_time'] : 0,
                'goodsName' => implode(', ', array_filter($goodsNames)),
                'buyer_remark' => isset($detail['remark']) ? $detail['remark'] : '',
                'admin_remark' => isset($detail['admin_remark']) ? $detail['admin_remark'] : '',
                'items' => $goodsList,
                // Receiver Shortcuts
                'receiverName' => isset($detail['address']['name']) ? $detail['address']['name'] : '',
                'receiverMobile' => isset($detail['address']['phone']) ? $detail['address']['phone'] : '',
                'receiverAddress' => isset($detail['address']['detail']) ? $detail['address']['detail'] : '',
                // Enhanced Fields 2026-01-30
                'volume_weight' => isset($detail['volume']) ? $detail['volume'] : 0, // 体积重 (Assuming 'volume' field)
                'chargeable_weight' => isset($detail['cale_weight']) ? $detail['cale_weight'] : 0, // 计费重量
                'warehouse_name' => isset($detail['storage']['shop_name']) ? $detail['storage']['shop_name'] : '', // 寄送仓库
                'pay_status' => $payStatusText, // 支付状态
                'apply_time' => isset($detail['create_time']) ? $detail['create_time'] : 0, // 申请打包时间 (Use create_time)
                'sub_order_count' => isset($detail['inpackdetail']) ? count($detail['inpackdetail']) : 0, // 子订单数量
                'service_items' => implode(', ', $serviceNames), // 打包服务项目
            ];
            
            $ztoConfig = [
                'key'    => $ditchdetail['app_key'],
                'token'  => $ditchdetail['app_token'],
                'apiurl' => isset($ditchdetail['api_url']) ? $ditchdetail['api_url'] : '',
                'ditch_type' => isset($ditchdetail['ditch_type']) ? $ditchdetail['ditch_type'] : 1,
                'shop_key' => isset($ditchdetail['shop_key']) ? $ditchdetail['shop_key'] : '',
                'push_config_json' => isset($ditchdetail['push_config_json']) ? $ditchdetail['push_config_json'] : '',
            ];
            
            $extraData = [];
            if (!empty($ditchdetail['account_id'])) {
                $extraData['accountId'] = $ditchdetail['account_id'];
                $extraData['accountPassword'] = isset($ditchdetail['account_password']) && $ditchdetail['account_password'] !== '' ? $ditchdetail['account_password'] : 'ZTO123';
            } elseif (!empty($ditchdetail['customer_code'])) {
                // 如果 account_id 为空但 customer_code有值，尝试将其作为 accountId 使用 (兼容某些配置习惯)
                $extraData['accountId'] = $ditchdetail['customer_code'];
                $extraData['accountPassword'] = 'ZTO123'; // 默认密码或需要额外配置
                $ztoConfig['customer_code'] = $ditchdetail['customer_code'];
            }
            if (!empty($ditchdetail['use_timestamp'])) {
                $ztoConfig['use_timestamp'] = 1;
            }
            $Zto = new Zto($ztoConfig);
            
            // 获取箱子列表
            $boxes = isset($detail['packageitems']) ? $detail['packageitems'] : [];
            $isMultiBox = count($boxes) > 0;
            
            if (!$isMultiBox) {
                // === 单包裹模式 ===
                $data = array_merge($commonData, $extraData, $fullContext, [
                    'partnerOrderCode'   => $detail['order_sn'],
                    'order_customerinvoicecode' => $detail['order_sn'],
                    'order_sn'           => $detail['order_sn'],
                    'weight'             => $detail['cale_weight'],
                    'quantity'           => 1,
                ]);
                
                $result = $Zto->createOrder($data);
                if (isset($result['ack']) && $result['ack'] === 'true') {
                    $detail->pushSuccessComplete($param['id'], [
                        't_order_sn' => isset($result['tracking_number']) ? $result['tracking_number'] : '',
                        't_name'     => $ditchdetail['ditch_name'],
                        't_number'   => $ditchdetail['ditch_id'],
                        'transfer'   => 0
                    ]);
                }
            } else {
                // === 多包裹独立下单模式 ===
                $resultsLog = [];
                $hasError = false;
                $firstWaybillNo = '';
                $subTrackingNumbers = []; // 收集子单号
                
                foreach ($boxes as $index => $box) {
                    $subOrderSn = $detail['order_sn'] . '_' . $box['id'];
                    $boxWeight = isset($box['weight']) && $box['weight'] > 0 ? $box['weight'] : 1;
                    
                    $data = array_merge($commonData, $extraData, $fullContext, [
                        'partnerOrderCode'   => $subOrderSn,
                        'order_customerinvoicecode' => $subOrderSn,
                        'order_sn'           => $subOrderSn,
                        'weight'             => $boxWeight,
                        'quantity'           => 1,
                    ]);
                    
                    $res = $Zto->createOrder($data);
                    
                    if (isset($res['ack']) && $res['ack'] === 'true') {
                        $tn = isset($res['tracking_number']) ? $res['tracking_number'] : '';
                        if (is_object($box)) {
                            $box->save(['t_order_sn' => $tn]);
                        }
                        if ($index === 0) {
                            $firstWaybillNo = $tn;
                             // 仅保存第一个作为显示，不覆盖
                            $detail->pushSuccessComplete($param['id'], [
                                't_order_sn' => $tn,
                                't_name'     => $ditchdetail['ditch_name'],
                                't_number'   => $ditchdetail['ditch_id'],
                                'transfer'   => 0
                            ]);
                        }
                        $resultsLog[] = "箱" . ($index+1) . "成功";
                        $subTrackingNumbers[] = ['id' => $box['id'], 'tn' => $tn];
                    } else {
                        $hasError = true;
                        $resultsLog[] = "箱" . ($index+1) . "失败: " . (isset($res['message']) ? $res['message'] : '');
                    }
                }
                
                 $result = [
                    'ack' => $hasError ? 'false' : 'true',
                    'message' => implode('; ', $resultsLog),
                    'tracking_number' => $firstWaybillNo,
                    'sub_tracking_numbers' => $subTrackingNumbers
                ];
            }
        }
        
        // 顺丰快递下单逻辑（type=4）
        if($ditchdetail['ditch_no']==10010 || (isset($ditchdetail['ditch_type']) && (int)$ditchdetail['ditch_type'] === 4)){
            $storage = ($shopname && is_object($shopname)) ? $shopname->toArray() : [];
            $region = isset($storage['region']) && is_array($storage['region']) ? $storage['region'] : [];
            
            // 构造基础地址与联系人信息 (共用)
            $commonData = [
                'consignee_name'     => isset($detail['address']['name']) ? $detail['address']['name'] : '',
                'consignee_mobile'   => isset($detail['address']['phone']) ? $detail['address']['phone'] : '',
                'consignee_telephone'=> isset($detail['address']['phone']) ? $detail['address']['phone'] : '',
                'consignee_address'  => isset($detail['address']['detail']) ? $detail['address']['detail'] : '',
                'consignee_state'    => isset($detail['address']['province']) ? $detail['address']['province'] : '',
                'consignee_city'     => isset($detail['address']['city']) ? $detail['address']['city'] : '',
                'consignee_suburb'   => isset($detail['address']['region']) ? $detail['address']['region'] : '',
                'consignee_postcode' => isset($detail['address']['code']) ? $detail['address']['code'] : '',
                'country'            => $countrydetail['code'],
                'sender_name'        => isset($storage['linkman']) && $storage['linkman'] ? $storage['linkman'] : '集运仓',
                'sender_phone'       => isset($storage['phone']) && $storage['phone'] ? $storage['phone'] : '13800138000',
                'sender_mobile'      => isset($storage['phone']) && $storage['phone'] ? $storage['phone'] : '13800138000',
                'sender_province'    => isset($region['province']) ? $region['province'] : '上海',
                'sender_city'        => isset($region['city']) ? $region['city'] : '上海市',
                'sender_district'    => isset($region['region']) ? $region['region'] : '青浦区',
                'sender_address'     => isset($storage['address']) && $storage['address'] ? $storage['address'] : '默认地址',
            ];

            $sfConfig = [
                'key'    => $ditchdetail['app_key'],
                'token'  => $ditchdetail['app_token'],
                'apiurl' => isset($ditchdetail['api_url']) ? $ditchdetail['api_url'] : '',
                'customer_code' => isset($ditchdetail['customer_code']) ? $ditchdetail['customer_code'] : '',
                'sf_express_type' => isset($ditchdetail['sf_express_type']) ? (int)$ditchdetail['sf_express_type'] : 1,
            ];
            $Sf = new Sf($sfConfig);
            
            // 获取箱子列表
            $boxes = isset($detail['packageitems']) ? $detail['packageitems'] : [];
            $isMultiBox = count($boxes) > 0;
            
            if (!$isMultiBox) {
                // === 单包裹模式 (兼容原有逻辑) ===
                $data = array_merge($commonData, [
                    'partnerOrderCode'   => $detail['order_sn'],
                    'order_sn'           => $detail['order_sn'],
                    'weight'             => $detail['cale_weight'],
                    'quantity'           => 1,
                ]);
                
                $result = $Sf->createOrder($data);
                if (isset($result['ack']) && $result['ack'] === 'true') {
                    $detail->pushSuccessComplete($param['id'], [
                        't_order_sn' => isset($result['tracking_number']) ? $result['tracking_number'] : '',
                        't_name'     => $ditchdetail['ditch_name'],
                        't_number'   => $ditchdetail['ditch_id'],
                        'transfer'   => 0
                    ]);
                }
            } else {
                // === 多包裹 (子母单) 模式 ===
                $motherWaybillNo = '';
                $resultsLog = [];
                $hasError = false;
                $subTrackingNumbers = []; // 收集子单号
                
                foreach ($boxes as $index => $box) {
                    $isMother = ($index === 0);
                    // 构造唯一的子单号
                    $subOrderSn = $detail['order_sn'] . '_' . $box['id'];
                    $boxWeight = isset($box['weight']) && $box['weight'] > 0 ? $box['weight'] : 1;
                    
                    $data = array_merge($commonData, [
                        'partnerOrderCode'   => $subOrderSn,
                        'order_sn'           => $subOrderSn,
                        'weight'             => $boxWeight,
                        'quantity'           => 1,
                    ]);
                    
                    if ($isMother) {
                        $data['is_mother_child'] = 1;
                    } else {
                        $data['is_mother_child'] = 2;
                        $data['mother_waybill_no'] = $motherWaybillNo;
                    }
                    
                    $res = $Sf->createOrder($data);
                    
                    if (isset($res['ack']) && $res['ack'] === 'true') {
                        $tn = $res['tracking_number'];
                        // 保存到 InpackItem (子表)
                        if (is_object($box)) {
                            $box->save(['t_order_sn' => $tn]);
                        }
                        
                        $resultsLog[] = "箱" . ($index+1) . "成功";
                        $subTrackingNumbers[] = ['id' => $box['id'], 'tn' => $tn];
                        
                        // 如果是母单，更新主表并记录母单号
                        if ($isMother) {
                            $motherWaybillNo = $tn;
                            $detail->pushSuccessComplete($param['id'], [
                                't_order_sn' => $tn,
                                't_name'     => $ditchdetail['ditch_name'],
                                't_number'   => $ditchdetail['ditch_id'],
                                'transfer'   => 0
                            ]);
                        }
                    } else {
                        $hasError = true;
                        $errMsg = isset($res['message']) ? $res['message'] : '未知错误';
                        $resultsLog[] = "箱" . ($index+1) . "失败: " . $errMsg;
                        
                        if ($isMother) {
                             $resultsLog[] = "母单失败，中止后续推送";
                             break;
                        }
                    }
                }
                
                // 汇总结果
                $result = [
                    'ack' => $hasError ? 'false' : 'true',
                    'message' => implode('; ', $resultsLog),
                    'tracking_number' => $motherWaybillNo,
                    'sub_tracking_numbers' => $subTrackingNumbers
                ];
            }
            } elseif (isset($ditchdetail['ditch_type']) && (int)$ditchdetail['ditch_type'] == 5) {
                // === 京东物流 (JD) ===
                $this->writeJdOrderLog("========================================");
                $this->writeJdOrderLog(">>> 京东快递下单流程开始");
                $this->writeJdOrderLog("========================================");
                $this->writeJdOrderLog("包裹信息: inpack_id={$detail['id']}, order_sn={$detail['order_sn']}");
                
                // 获取仓库信息作为发件人
                $storage = ($shopname && is_object($shopname)) ? $shopname->toArray() : [];
                $region = isset($storage['region']) && is_array($storage['region']) ? $storage['region'] : [];
                
                $this->writeJdOrderLog("仓库信息: " . json_encode([
                    'linkman' => isset($storage['linkman']) ? $storage['linkman'] : '未设置',
                    'phone' => isset($storage['phone']) ? $storage['phone'] : '未设置',
                    'address' => isset($storage['address']) ? $storage['address'] : '未设置',
                    'region' => $region
                ], JSON_UNESCAPED_UNICODE));
                
                // 解析京东多包裹打单配置
                $jdMultiboxConfig = [];
                if (!empty($ditchdetail['jd_multibox_config'])) {
                    $decoded = json_decode(html_entity_decode($ditchdetail['jd_multibox_config']), true);
                    if (is_array($decoded)) {
                        $jdMultiboxConfig = $decoded;
                    }
                }
                $multiboxEnabled = isset($jdMultiboxConfig['enabled']) && $jdMultiboxConfig['enabled'];
                
                $this->writeJdOrderLog("多包裹配置: " . json_encode([
                    'raw_config' => $ditchdetail['jd_multibox_config'],
                    'parsed_config' => $jdMultiboxConfig,
                    'multibox_enabled' => $multiboxEnabled
                ], JSON_UNESCAPED_UNICODE));
                
                // 构造基础地址与联系人信息（使用仓库信息作为发件人）
                $commonData = [
                    'consignee_name'     => isset($detail['address']['name']) ? $detail['address']['name'] : '',
                    'consignee_mobile'   => isset($detail['address']['phone']) ? $detail['address']['phone'] : '',
                    'consignee_telephone'=> isset($detail['address']['phone']) ? $detail['address']['phone'] : '',
                    'consignee_address'  => isset($detail['address']['detail']) ? $detail['address']['detail'] : '',
                    'consignee_state'    => isset($detail['address']['province']) ? $detail['address']['province'] : '',
                    'consignee_city'     => isset($detail['address']['city']) ? $detail['address']['city'] : '',
                    'consignee_suburb'   => isset($detail['address']['region']) ? $detail['address']['region'] : '',
                    'consignee_postcode' => isset($detail['address']['code']) ? $detail['address']['code'] : '',
                    'country'            => $countrydetail['code'],
                    'sender_name'        => isset($storage['linkman']) && $storage['linkman'] ? $storage['linkman'] : '集运仓',
                    'sender_phone'       => isset($storage['phone']) && $storage['phone'] ? $storage['phone'] : '13800138000',
                    'sender_mobile'      => isset($storage['phone']) && $storage['phone'] ? $storage['phone'] : '13800138000',
                    'sender_province'    => isset($region['province']) ? $region['province'] : '上海',
                    'sender_city'        => isset($region['city']) ? $region['city'] : '上海市',
                    'sender_district'    => isset($region['region']) ? $region['region'] : '青浦区',
                    'sender_address'     => isset($storage['address']) && $storage['address'] ? $storage['address'] : '默认地址',
                    // 京东特有字段
                    'name'               => isset($detail['address']['name']) ? $detail['address']['name'] : '',
                    'phone'              => isset($detail['address']['phone']) ? $detail['address']['phone'] : '',
                    'detail'             => isset($detail['address']['detail']) ? $detail['address']['detail'] : '',
                    'province'           => isset($detail['address']['province']) ? $detail['address']['province'] : '',
                    'city'               => isset($detail['address']['city']) ? $detail['address']['city'] : '',
                    'region'             => isset($detail['address']['region']) ? $detail['address']['region'] : '',
                ];
                
                $jdConfig = [
                    'app_key'       => $ditchdetail['app_key'],
                    'app_secret'    => $ditchdetail['app_token'],
                    'access_token'  => $ditchdetail['print_url'], // 复用字段
                    'customer_code' => $ditchdetail['customer_code'],
                    'api_url'       => isset($ditchdetail['api_url']) ? $ditchdetail['api_url'] : '',
                ];
                
                $this->writeJdOrderLog("SDK 配置: " . json_encode([
                    'app_key' => substr($ditchdetail['app_key'], 0, 8) . '***',
                    'customer_code' => $ditchdetail['customer_code'],
                    'api_url' => $jdConfig['api_url']
                ], JSON_UNESCAPED_UNICODE));
                
                $Jd = new \app\common\library\Ditch\Jd($jdConfig);

                // 京东产品编码 (从 product_json 字段读取，或者默认京东标快)
                $productCode = isset($ditchdetail['product_json']) && !empty($ditchdetail['product_json']) 
                               ? $ditchdetail['product_json'] 
                               : 'ed-m-0001';
                
                $this->writeJdOrderLog("产品编码: {$productCode}");

                $boxes = isset($detail['packageitems']) ? $detail['packageitems'] : [];
                $isMultiBox = count($boxes) > 0;
                
                $this->writeJdOrderLog("包裹信息: " . json_encode([
                    'box_count' => count($boxes),
                    'is_multi_box' => $isMultiBox
                ], JSON_UNESCAPED_UNICODE));

                if (!$isMultiBox) {
                    // === 单包裹 ===
                    $this->writeJdOrderLog("--- 单包裹下单模式 ---");
                    
                    $data = array_merge($commonData, [
                        'order_sn'     => $detail['order_sn'],
                        'weight'       => $detail['cale_weight'],
                        'product_code' => $productCode,
                        'quantity'     => 1
                    ]);
                    
                    $this->writeJdOrderLog("下单参数: " . json_encode([
                        'order_sn' => $data['order_sn'],
                        'weight' => $data['weight'],
                        'product_code' => $data['product_code'],
                        'quantity' => $data['quantity'],
                        'consignee_name' => $data['consignee_name'],
                        'consignee_mobile' => $data['consignee_mobile'],
                        'consignee_address' => $data['consignee_address'],
                        'sender_name' => $data['sender_name'],
                        'sender_phone' => $data['sender_phone']
                    ], JSON_UNESCAPED_UNICODE));
                    
                    $this->writeJdOrderLog("调用 SDK createOrder()...");
                    $res = $Jd->createOrder($data);
                    
                    $this->writeJdOrderLog("API 响应: " . json_encode($res, JSON_UNESCAPED_UNICODE));
                    
                    if ($res['code'] == 1) {
                        $tn = $res['data']['waybillCode'];
                        $this->writeJdOrderLog("✅ 下单成功: waybill={$tn}");
                        
                        $detail->pushSuccessComplete($param['id'], [
                            't_order_sn' => $tn,
                            't_name'     => $ditchdetail['ditch_name'],
                            't_number'   => $ditchdetail['ditch_id'],
                            'transfer'   => 0
                        ]);
                        $this->writeJdOrderLog("✅ 运单号及承运商信息已保存到订单");
                        
                        $result = [
                            'ack' => 'true',
                            'tracking_number' => $tn,
                            'message' => '推送成功'
                        ];
                    } else {
                        $this->writeJdOrderLog("❌ 下单失败: " . $res['msg']);
                        $result = [
                            'ack' => 'false', 
                            'message' => 'JD Error: ' . $res['msg']
                        ];
                    }
                } else {
                    // === 多包裹 ===
                    if ($multiboxEnabled) {
                        // 多包裹独立打单模式：为每个包裹生成独立的运单号
                        $this->writeJdOrderLog("--- 多包裹独立打单模式 ---");
                        $this->writeJdOrderLog("包裹数量: " . count($boxes));
                        
                        $motherWaybillNo = '';
                        $resultsLog = [];
                        $hasError = false;
                        $subTrackingNumbers = [];

                        foreach ($boxes as $index => $box) {
                             $isMother = ($index === 0);
                             // 使用子包裹ID生成唯一订单号
                             $boxId = is_object($box) ? $box->id : $box['id'];
                             $subOrderSn = $detail['order_sn'] . '_' . $boxId;
                             $boxWeight = isset($box['weight']) && $box['weight'] > 0 ? $box['weight'] : 1;
                             
                             $this->writeJdOrderLog("--- 处理包裹 #" . ($index + 1) . " ---");
                             $this->writeJdOrderLog("包裹信息: box_id={$boxId}, is_mother={$isMother}, weight={$boxWeight}");
                             
                             $data = array_merge($commonData, [
                                'order_sn'     => $subOrderSn,
                                'weight'       => $boxWeight,
                                'product_code' => $productCode,
                                'quantity'     => 1
                            ]);
                            
                            $this->writeJdOrderLog("下单参数: " . json_encode([
                                'sub_order_sn' => $subOrderSn,
                                'weight' => $boxWeight,
                                'product_code' => $productCode,
                                'quantity' => 1
                            ], JSON_UNESCAPED_UNICODE));
                            
                            $this->writeJdOrderLog("调用 SDK createOrder()...");
                            $res = $Jd->createOrder($data);
                            
                            $this->writeJdOrderLog("API 响应: " . json_encode($res, JSON_UNESCAPED_UNICODE));
                            
                            if ($res['code'] == 1) {
                                $tn = $res['data']['waybillCode'];
                                $this->writeJdOrderLog("✅ 包裹 #{$index} 下单成功: waybill={$tn}");
                                
                                // 更新子包裹运单号（使用正确的字段名）
                                if (is_object($box)) {
                                    $box->save(['t_order_sn' => $tn]);
                                    $this->writeJdOrderLog("✅ 子包裹运单号已保存: box_id={$boxId}");
                                }
                                
                                $subTrackingNumbers[] = ['id' => $boxId, 'tn' => $tn];
                                $resultsLog[] = "箱" . ($index+1) . "成功";
                                
                                if ($isMother) {
                                    $motherWaybillNo = $tn;
                                    $detail->save([
                                        't_order_sn' => $tn,
                                        't_name' => $ditchdetail['ditch_name'],
                                        't_number' => $ditchdetail['ditch_id'],
                                        'transfer' => 0
                                    ]);
                                    $this->writeJdOrderLog("✅ 母单运单号及承运商信息已保存: {$tn}");
                                }
                            } else {
                                $hasError = true;
                                $errMsg = $res['msg'];
                                $this->writeJdOrderLog("❌ 包裹 #{$index} 下单失败: {$errMsg}");
                                $resultsLog[] = "箱" . ($index+1) . "失败: " . $errMsg;
                                
                                if ($isMother) {
                                    $this->writeJdOrderLog("❌ 母单失败，中止后续包裹下单");
                                    $resultsLog[] = "母单失败，中止"; 
                                    break;
                                }
                            }
                        }
                        
                        $this->writeJdOrderLog("多包裹独立打单完成: " . json_encode([
                            'has_error' => $hasError,
                            'mother_waybill' => $motherWaybillNo,
                            'sub_count' => count($subTrackingNumbers),
                            'results' => $resultsLog
                        ], JSON_UNESCAPED_UNICODE));
                        
                        $result = [
                            'ack' => $hasError ? 'false' : 'true',
                            'message' => implode('; ', $resultsLog),
                            'tracking_number' => $motherWaybillNo,
                            'sub_tracking_numbers' => $subTrackingNumbers
                        ];
                    } else {
                        // 子母件模式：一次下单，生成母单和子单
                        $this->writeJdOrderLog("--- 子母件模式 ---");
                        $this->writeJdOrderLog("包裹数量: " . count($boxes));
                        
                        // 计算总重量
                        $totalWeight = 0;
                        foreach ($boxes as $box) {
                            $boxWeight = isset($box['weight']) && $box['weight'] > 0 ? $box['weight'] : 1;
                            $totalWeight += $boxWeight;
                        }
                        
                        // 使用订单总重量（如果有的话）
                        if (isset($detail['cale_weight']) && $detail['cale_weight'] > 0) {
                            $totalWeight = $detail['cale_weight'];
                        }
                        
                        $this->writeJdOrderLog("重量计算: 总重量={$totalWeight}kg");
                        
                        // 下单参数：标记为母单
                        $data = array_merge($commonData, [
                            'order_sn'           => $detail['order_sn'],
                            'weight'             => $totalWeight,
                            'product_code'       => $productCode,
                            'quantity'           => count($boxes),
                            'is_mother_child'    => 1,  // 标记为母单
                        ]);
                        
                        $this->writeJdOrderLog("下单参数: " . json_encode([
                            'order_sn' => $data['order_sn'],
                            'weight' => $data['weight'],
                            'product_code' => $data['product_code'],
                            'quantity' => $data['quantity'],
                            'is_mother_child' => $data['is_mother_child'],
                            'consignee_name' => $data['consignee_name'],
                            'consignee_mobile' => $data['consignee_mobile'],
                            'sender_name' => $data['sender_name']
                        ], JSON_UNESCAPED_UNICODE));
                        
                        $this->writeJdOrderLog("调用 SDK createOrder()...");
                        $res = $Jd->createOrder($data);
                        
                        $this->writeJdOrderLog("API 响应: " . json_encode($res, JSON_UNESCAPED_UNICODE));
                        
                        if ($res['code'] == 1) {
                            $motherWaybillNo = $res['data']['waybillCode'];
                            $this->writeJdOrderLog("✅ 子母件下单成功: mother_waybill={$motherWaybillNo}");
                            
                            $detail->pushSuccessComplete($param['id'], [
                                't_order_sn' => $motherWaybillNo,
                                't_name'     => $ditchdetail['ditch_name'],
                                't_number'   => $ditchdetail['ditch_id'],
                                'transfer'   => 0
                            ]);
                            $this->writeJdOrderLog("✅ 母单运单号及承运商信息已保存");
                            
                            // 获取子单号列表（如果API返回）
                            $subWaybills = [];
                            if (isset($res['data']['subWaybillCodes']) && is_array($res['data']['subWaybillCodes'])) {
                                $subWaybills = $res['data']['subWaybillCodes'];
                                $this->writeJdOrderLog("API 返回子单号: " . json_encode($subWaybills, JSON_UNESCAPED_UNICODE));
                            } else {
                                $this->writeJdOrderLog("⚠️ API 未返回子单号列表");
                            }
                            
                            // 更新各个包裹的运单号
                            $subTrackingNumbers = [];
                            foreach ($boxes as $index => $box) {
                                $boxId = is_object($box) ? $box->id : $box['id'];
                                
                                if ($index === 0) {
                                    // 第一个包裹使用母单号
                                    $tn = $motherWaybillNo;
                                    $this->writeJdOrderLog("包裹 #{$index} (box_id={$boxId}): 使用母单号 {$tn}");
                                } else {
                                    // 其他包裹使用子单号（如果有）
                                    $tn = isset($subWaybills[$index - 1]) ? $subWaybills[$index - 1] : $motherWaybillNo;
                                    $this->writeJdOrderLog("包裹 #{$index} (box_id={$boxId}): 使用子单号 {$tn}");
                                }
                                
                                // 保存子包裹运单号（使用正确的字段名）
                                if (is_object($box)) {
                                    $box->save(['t_order_sn' => $tn]);
                                    $this->writeJdOrderLog("✅ 子包裹运单号已保存: box_id={$boxId}");
                                }
                                $subTrackingNumbers[] = ['id' => $boxId, 'tn' => $tn];
                            }
                            
                            $this->writeJdOrderLog("子母件下单完成: " . json_encode([
                                'mother_waybill' => $motherWaybillNo,
                                'sub_count' => count($subTrackingNumbers),
                                'sub_tracking_numbers' => $subTrackingNumbers
                            ], JSON_UNESCAPED_UNICODE));
                            
                            $result = [
                                'ack' => 'true',
                                'tracking_number' => $motherWaybillNo,
                                'sub_tracking_numbers' => $subTrackingNumbers,
                                'message' => '推送成功（子母件）'
                            ];
                        } else {
                            $this->writeJdOrderLog("❌ 子母件下单失败: " . $res['msg']);
                            $result = [
                                'ack' => 'false',
                                'message' => 'JD Error: ' . $res['msg']
                            ];
                        }
                    }
                }
                
                $this->writeJdOrderLog("========================================");
                $this->writeJdOrderLog("<<< 京东快递下单流程结束");
                $this->writeJdOrderLog("最终结果: " . json_encode($result, JSON_UNESCAPED_UNICODE));
                $this->writeJdOrderLog("========================================");
            }
        
        // 清除缓冲区，防止之前的输出（如BOM头、Notice警告）破坏JSON格式
        if (ob_get_level() > 0) {
            ob_clean();
        }
        return $this->renderSuccess('获取成功','',$result);
    }
    
    public function package($id){
         // 订单详情
        $params =$this->request->param(); 
        $where = [];
        $Package = new Package();
        $storesetting = SettingModel::getItem('store',$this->getWxappId());
        !empty($params['search']) && $where['express_num'] = $params['search'];
        !empty($params['is_scan']) && $where['is_scan'] = $params['is_scan'];
        $list = $Package->with("packageimage.file")->where($where)->where('inpack_id',$id)->order('is_scan asc')->select();
       
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
        if ($detail['status']>=2){
            $detail['total'] = $detail['free']+$detail['pack_free']+$detail['other_free'];
        }
         $packagelist= (new Package())->where("inpack_id",$detail['id'])->select();
        foreach($packagelist as $key => $value){
          $packageItems[$key] = (new PackageItem())->where("order_id",$value['id'])->find();
          if(!empty($packageItems[$key])){
              $packageItem[$key] = $packageItems[$key];
          }
        }
        $packageService = (new PackageService())->getList([]);
        $detail['service'] = (new InpackService())->with('service')->where('inpack_id',$id)->select();
        //获取订单日志记录
        $detail['log'] = (new Logistics())->where('order_sn',$detail['order_sn'])->select();
        //获取到用户信息
        $detail['user'] = (new UserModel())->where('user_id',$detail['member_id'])->find();
        //获取到仓库信息
        $detail['storage'] = (new ShopModel())->where('shop_id',$detail['storage_id'])->find();
        //获取子订单记录
        $detail['sonitem'] = (new InpackItem())->where('inpack_id',$detail['id'])->select();
        $set = Setting::detail('store')['values'];
        $userclient =  Setting::getItem('userclient',$detail['wxapp_id']);
        return $this->fetch('orderdetail', compact(
            'detail','line','packageItem','packageService','set','userclient'
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
           $_up = [
             'status' => $status
           ];
           
           $status_map = [
               5 => '8',
               6 => '9',
               7 => '10',
               8 => '11',
               9 => '2'
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
           if($status==9){
                $_up['status'] = 1;
           }
           $model->where(['id'=>$v])->update($_up);
           (new Package())->where('inpack_id',$order['id'])->update(['status'=>$status_map[$status]]);
           if(strpos($noticesetting['dosend']['describe'],'code')){
                 $dosend = str_ireplace('{code}', $order['t_order_sn'], $noticesetting['dosend']['describe']);
            }else{
                 $dosend = $noticesetting['dosend']['describe'];
            }
           $status_remark = [
               5=> "待发货状态，修改发货单号",
               6 => $dosend,
               7 => $noticesetting['reach']['describe'],
               8 => $noticesetting['take']['describe'],
               9 => "订单回退，重新打包",
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
           //处理积分赠送
           //6、发送积分
            $setting = SettingModel::getItem('points',$order['wxapp_id']);
            $giftpoint = 0;
            // dump($setting);die;
            if($setting['is_open']==1 && $setting['is_logistics_gift']==1){
                if($setting['is_logistics_area']==20 && $userData['grade_id']>0){
                    $giftpoint = floor($order['real_payment']*$setting['logistics_gift_ratio']/100);
                }else if($setting['is_logistics_area']==10){
                    $giftpoint = floor($order['real_payment']*$setting['logistics_gift_ratio']/100);
                }
            }
            
            if($giftpoint>0 && $status==8){
                $userData->setInc('points',$giftpoint);
                // 新增积分变动记录
                PointsLogModel::add([
                    'user_id' => $order['member_id'],
                    'value' => $giftpoint,
                    'type' => 1,
                    'describe' => "订单".$order['order_sn']."赠送积分".$giftpoint,
                    'remark' => "积分来自集运订单:".$order['order_sn'],
                ]);
            }
            
           }    
       return $this->renderSuccess('更新成功');
    }
    
    
    /**
     * 批量发送支付的模板消息
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function sendpaymess(){
       $params = $this->request->param();
       if(count($params['selectIds'])==0){
           return $this->renderError('请选择订单');
       }
       $idsArr = $params['selectIds'];
       $model = new Inpack();
       
       foreach ($idsArr as $v){
           $order =  $model->where(['id'=>$v])->find();
           $userData = (new User())->where('user_id',$order['member_id'])->find();
           
           // 获取费用审核设置，判断是否需要审核后才发送支付通知
           $adminstyle = SettingModel::getItem('adminstyle', $order['wxapp_id']);
           $is_verify_free = isset($adminstyle['is_verify_free']) ? $adminstyle['is_verify_free'] : 0;
           $canSendPayOrder = true; // 是否可以发送支付通知
           
           // 如果开启了费用审核，需要检查是否已审核
           if($is_verify_free == 1) {
               $is_doublecheck = isset($order['is_doublecheck']) ? $order['is_doublecheck'] : 0;
               $canSendPayOrder = ($is_doublecheck == 1); // 只有已审核才能发送
           }
           
           // 只有满足条件时才发送支付通知
           if($canSendPayOrder) {
               //处理模板消息
               $data['id'] = $order['id'];
               $data['order_sn'] = $order['order_sn'];
               $data['member_id'] = $order['member_id'];
               $data['free'] = $order['free'] + $order['pack_free'] + $order['other_free'] + $order['insure_free'] ;
               $data['weight'] = $order['cale_weight'];
               $data['wxapp_id'] = $order['wxapp_id'];
               Message::send('package.payorder',$data);
           }
       }    
        return $this->renderSuccess('发送成功');
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
    
    /**
     * 全部订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function alluserlist()
    {
        return $this->getList('全部订单列表', "all");
    }
    
    /**
     * 全部订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function getTroderList()
    {
        $params = $this->request->param();
        $dataType = 'all';
        $model = (new Inpack());
        $adminstyle = Setting::detail('adminstyle')['values'];
        if(!isset($params['limitnum'])){
            $params['limitnum'] = isset($adminstyle['pageno'])?$adminstyle['pageno']['inpack']:15;
        }
        $list = $model->getList($dataType, $params);
        return json([
        'code' => 0,
        'msg' => '',
        'count' => count($list),
        'data' => $list
    ]);
    }
    
    public function edit($id){
        $line = (new Line())->getListAll([]);
        // 订单详情
        $detail = Inpack::details($id);
        // dump($id);die;
        $detail['total'] = $detail['free']+$detail['pack_free']+$detail['other_free'];
        $set = Setting::detail('store')['values'];
        $is_auto_free = 0;
        if($set['is_auto_free']==1){
            $is_auto_free = 1;
        }
        // 获取审核设置
        $adminstyle = Setting::getItem('adminstyle', $detail['wxapp_id']);
        $is_verify_free = isset($adminstyle['is_verify_free']) ? $adminstyle['is_verify_free'] : 0;
        return $this->fetch('detail', compact('detail','line','set','is_auto_free','is_verify_free'));
    }
    
    
    /**
     * 点击编辑集运单，修改保存的函数
     * 2022年11月5日 增加图片增删功能
    */
    public function modify_save(){
       $model = (new Inpack());
       if ($model->edit($this->postData('data'))){
             return $this->renderSuccess('操作成功', 'javascript:window.location.href = document.referrer');
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
       $payprice = $inpackdata['free'] + $inpackdata['pack_free'] + $inpackdata['other_free'] + $inpackdata['insure_free'] - $inpackdata['user_coupon_money'];
       return  $this->renderSuccess('操作成功','',$result=['price' =>  $payprice ,'balance' =>$userdata['balance']]);
    }
    
     //使用现金支付支付集运单费用
    public function cashforprice(){
        $data = $this->request->param();
        $model = new Inpack();
        $Package = new Package();
        $user =  new UserModel;
        $inpackdata = $model::details($data['id']);
        $userdata = User::detail($data['user_id']);
 
        $payprice = $inpackdata['free'] + $inpackdata['pack_free'] + $inpackdata['other_free']  + $inpackdata['insure_free'] - $inpackdata['user_coupon_money'];
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
        $Package->where('inpack_id',$data['id'])->update(['status'=>6,'is_pay'=>1]);
        //更新支付后的物流轨迹
        $noticesetting =  Setting::detail('notice')['values'];
        if($noticesetting['ispay']['is_enable']==1){
            Logistics::addLog($inpackdata['order_sn'],$noticesetting['ispay']['describe'],date("Y-m-d H:i:s",time()));
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
        $Package = new Package;
        $inpackdata = $model::details($data['id']);
        $userdata = User::detail($data['user_id']);
        
        $payprice = $inpackdata['free'] + $inpackdata['pack_free'] + $inpackdata['other_free'] + $inpackdata['insure_free'];
      
        if(($userdata['balance'] < $payprice) || $payprice==0){
            return $this->renderError('用户余额不足');
        }
        
        if($inpackdata['is_pay']==1){
            return $this->renderError('订单已支付，请勿重复支付');
        }
           
        //扣除余额，并产生一天用户的消费记录；减少用户余额；
        $res = $user->banlanceUpdate('remove',$data['user_id'],$payprice,date("Y-m-d H:i:s").',集运单'.$inpackdata['order_sn'].'消费余额'.$payprice);
        if(!$res){
            return $this->renderError($user->getError() ?: '操作失败');
        }
      
        //累计消费金额
        $userdata->setIncPayMoney($payprice);
        //修改集运单状态的支付状态
        $this->dealerData(['amount'=>$payprice,'order_id'=>$data['id']],$userdata);
        if($inpackdata['status']==2){
            $inpackdata->where('id',$data['id'])->update(['real_payment'=>$payprice,'status'=>3,'is_pay'=>1,'is_pay_type'=>0,'pay_time'=>date('Y-m-d H:i:s',time())]);
        }else{
            $inpackdata->where('id',$data['id'])->update(['real_payment'=>$payprice,'is_pay'=>1,'is_pay_type'=>0,'pay_time'=>date('Y-m-d H:i:s',time())]);
        }
        $Package->where('inpack_id',$data['id'])->update(['status'=>6,'is_pay'=>1]);
        //更新支付后的物流轨迹
        $noticesetting =  Setting::detail('notice')['values'];
        if($noticesetting['ispay']['is_enable']==1){
            Logistics::addLog($inpackdata['order_sn'],$noticesetting['ispay']['describe'],date("Y-m-d H:i:s",time()));
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
    
    //查询包裹的物流信息
    public function getlog(){
        $Inpack = new Inpack();
        $param = $this->request->param();
        $data = $Inpack->getlog($param);
        return $this->renderSuccess('操作成功','',compact('data'));
    }
    
    //保存总货值
    public function savegoodsvalue(){
        $Inpack = new Inpack();
        $param = $this->request->param();
        $model = $Inpack::details($param['order_id']);
        $model->save(['total_goods_value'=>$param['goods_value']]);
        return $this->renderSuccess('保存成功');
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
        $data = $this->postData('delivery');
        if ($model->modify($data)){
            // 保存分箱单号
            if(isset($data['sonitem']) && is_array($data['sonitem'])){
                foreach($data['sonitem'] as $itemId => $itemData){
                    if(isset($itemData['t_order_sn'])){
                         (new InpackItem())->save(
                             ['t_order_sn' => $itemData['t_order_sn']],
                             ['id' => $itemId]
                         );
                    }
                }
            }
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
    
    
    /**
     * 修改打印状态
     * @param $id
     * @return array
     * @throws \think\exception\DbException
     */
    public function updatePrintStatus($id)
    {
        $model = Inpack::detail($id);
        if ($model->save(['print_status_jhd'=>1])) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }
    
    
    
    //问题件删除
    public function orderdelete($id){
        $model = Inpack::details($id);
        (new Package())->where('inpack_id',$model['id'])->update(['is_delete' => 1]);
        if ($model->removedelete($id)) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }
    
    // 取消订单
    public function cancelorder($id){
        $model = Inpack::details($id);
        
        // 开启事务
        Db::startTrans();
        try {
            // 判断该订单是否已支付 且 实际付款金额>0
            if ($model['is_pay'] == 1 && $model['real_payment'] > 0) {
                // 退款流程：将支付金额退还到用户余额
                $remark = '集运订单' . $model['order_sn'] . '的支付退款';
                (new User())->banlanceUpdate('add', $model['member_id'], $model['real_payment'], $remark);
            }
            
            // 更新包裹状态：回退到待打包状态
            (new Package())->where('inpack_id', $model['id'])->update(['status' => 2, 'inpack_id' => 0, 'is_scan' => 1]);
            
            // 删除订单（标记为已删除）
            if ($model->removedelete($id)) {
                Db::commit();
                return $this->renderSuccess('取消成功');
            }
            
            Db::rollback();
            return $this->renderError($model->getError() ?: '取消失败');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->renderError('取消失败：' . $e->getMessage());
        }
    }
    
    
     //集运单删除
    public function delete($id){
        $model = Inpack::details($id);
        (new Package())->where('inpack_id',$model['id'])->update(['is_delete' => 1]);
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
        // 修复userclient配置缺失address导致视图报错的问题
        if (!isset($userclient['address'])) {
            $userclient['address'] = [
                'reciveaddress_setting' => [
                    'is_identitycard' => '0',
                    'is_clearancecode' => '0',
                    'is_province' => '1',
                    'is_city' => '1',
                    'is_street' => '1',
                    'is_door' => '1',
                    'is_detail' => '1',
                    'is_code' => '1',
                    'is_email' => '0'
                ]
            ];
        }
        $adminstyle = Setting::detail('adminstyle')['values'];
        // 获取费用审核设置
        $is_verify_free = isset($adminstyle['is_verify_free']) ? $adminstyle['is_verify_free'] : 0;
        $params = $this->request->param();
        if(!isset($params['limitnum'])){
            $params['limitnum'] = isset($adminstyle['pageno'])?$adminstyle['pageno']['inpack']:15;
        }
        $list = $model->getList($dataType, $params);
        // dump($list->toArray());die;
        $tracklist = $Track->getAllList();
        $servicelist = $Clerk->where('clerk_authority','like','%is_myuser%')->where('clerk_authority','like','%is_myuserpackage%')->where('is_delete',0)->select();
        $pintuanlist = (new SharingOrder())->getAllList();
        $batchlist = (new Batch())->getAllwaitList([]);
        $shopList = ShopModel::getAllList();
        $lineList = $Line->getListAll();
        
        // 订单类型数量统计（仅在all页面统计）
        $inpackTypeCount = [];
        if($dataType == 'all') {
            $baseWhere = ['is_delete' => 0];
            $inpackTypeCount = [
                'all' => (new Inpack)->where($baseWhere)->count(),
                'type_1' => (new Inpack)->where($baseWhere)->where('inpack_type', 1)->count(), // 拼团
                'type_2' => (new Inpack)->where($baseWhere)->where('inpack_type', 2)->count(), // 直邮
                'type_3' => (new Inpack)->where($baseWhere)->where('inpack_type', 'in', [0, 3])->count(), // 拼邮（包含0和3）
            ];
        }
        
        if(isset($adminstyle['pageno']['inpacktype']) && $adminstyle['pageno']['inpacktype']==20){
          return $this->fetch('newindex', compact('adminstyle','list','dataType','set','pintuanlist','shopList','lineList','servicelist','userclient','batchlist','tracklist','is_verify_free','inpackTypeCount'));  
        }
        return $this->fetch('index', compact('adminstyle','list','dataType','set','pintuanlist','shopList','lineList','servicelist','userclient','batchlist','tracklist','is_verify_free','inpackTypeCount'));
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
        $userclient =  Setting::detail('userclient')['values'];
        $shopList = ShopModel::getAllList();
        foreach ($list as &$value) {
            $value['num'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->count();
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
           if ($dataType=='payed'){
                $value['down_shelf'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->where('status',7)->count();
                $value['inpack'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->where('status',8)->count();
           }
        }

        return $this->fetch('index', compact('list','dataType','set','pintuanlist','shopList','lineList','userclient'));
    }
    
    /**
     * 批量将集运订单加入到批次中
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function changeLine(){
        $Inpack = new Inpack;
        $Line = new Line();
        $param = $this->request->param();
        $arr = $param['selectId'];
        foreach ($arr as $key =>$val){
            $Inpack->where('id',$val)->update(['line_id'=>$param['line_id']]);
        }
        return $this->renderSuccess('修改路线成功');
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
        $userclient =  Setting::detail('userclient')['values'];
        foreach ($list as &$value) {
            $value['num'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->count();
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
           if ($dataType=='payed'){
                $value['down_shelf'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->where('status',7)->count();
                $value['inpack'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->where('status',8)->count();
           }
        }

        return $this->fetch('index', compact('list','dataType','set','pintuanlist','shopList','lineList','userclient'));
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
        $userclient =  Setting::detail('userclient')['values'];
        foreach ($list as &$value) {
            $value['num'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->count();
            $value['sonnum'] =  (new InpackItem())->where(['inpack_id'=>$value['id']])->count();
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
           if ($dataType=='payed'){
                $value['down_shelf'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->where('status',7)->count();
                $value['inpack'] = (new Package())->where('inpack_id',$value['id'])->where('is_delete',0)->where('status',8)->count();
           }
        }

        return $this->fetch('index', compact('list','dataType','set','userclient'));
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
              $update['status'] = 2;
              $update['inpack_id'] = 0;
              $res =  (new Package())->where('id',input('id'))->update($update);
              if ($res){
                   return $this->renderSuccess('修改成功');
              }
              return $this->renderError($model->getError() ?: '修改失败');
          }
          
          
          //批量移出集运单
          $ids= input("post.selectId/a");  //需要去除的包裹id；
          $item =input("post.selectItem"); // 集运单编号
          $update['status'] = 2;
          $update['inpack_id'] = 0;
          foreach($ids as $key => $val){
             (new Package())->where('id',$val)->update($update);    
          } 
          return $this->renderSuccess('修改成功');
    }
    
    // 添加快递进入集运单 
    public function add(){
        $order_id = $this->getData('id');
        $Inpack = new Inpack();
        $model = $Inpack::details($order_id);

        if (!$this->request->isAjax()){
            // 查询该用户待打包的包裹列表
            $pending_packages = $this->getPendingPackages($model['member_id']);
            return $this->fetch('appendchild', compact('model', 'pending_packages'));
        }
    
        if ($Inpack->appendData($this->postData('delivery'))) {
            return $this->renderSuccess('修改成功','javascript:history.back(1)');
        }
        return $this->renderError($Inpack->getError() ?: '修改失败');
    }
    
    
    /**
     * 审核订单
     * @param $id
     * @return array
     * @throws \think\exception\DbException
     */
    public function auditOrder($id)
    {
        $model = Inpack::details($id);
        if (!$model) {
            return $this->renderError('订单不存在');
        }
        
        $auditStatus = $this->request->param('audit_status');
        $auditRemark = $this->request->param('audit_remark', '');
        
        if ($auditStatus == '1') {
            // 审核通过 - 调用现金支付接口
            return $this->auditPass($model, $auditRemark);
        } else {
            // 审核不通过 - 修改订单支付状态为未支付
            return $this->auditReject($model, $auditRemark);
        }
    }
    
    /**
     * 审核通过处理
     * @param $model
     * @param $remark
     * @return array
     */
    private function auditPass($model, $remark)
    {
        try {
            // 更新订单备注（包含审核信息）
            $auditInfo = '【审核通过】' . date('Y-m-d H:i:s') . ' ' . $remark;
            $newRemark = $model['remark'] ? $model['remark'] . "\n" . $auditInfo : $auditInfo;
            
            $model->save([
                'remark' => $newRemark
            ]);
            
            // 调用现金支付接口
            $result = $this->callCashPayment($model);
            
            if ($result['code'] == 1) {
                return $this->renderSuccess('审核通过，现金支付成功');
            } else {
                return $this->renderError('审核通过，但现金支付失败：' . $result['msg']);
            }
        } catch (\Exception $e) {
            return $this->renderError('审核失败：' . $e->getMessage());
        }
    }
    
    /**
     * 审核不通过处理
     * @param $model
     * @param $remark
     * @return array
     */
    private function auditReject($model, $remark)
    {
        try {
            // 更新订单备注（包含审核信息），修改支付状态为未支付
            $auditInfo = '【审核不通过】' . date('Y-m-d H:i:s') . ' ' . $remark;
            $newRemark = $model['remark'] ? $model['remark'] . "\n" . $auditInfo : $auditInfo;
            
            $model->save([
                'remark' => $newRemark,
                'is_pay' => 2  // 2表示未支付
            ]);
            
            return $this->renderSuccess('审核不通过，订单状态已更新为未支付');
        } catch (\Exception $e) {
            return $this->renderError('审核失败：' . $e->getMessage());
        }
    }
    
    /**
     * 调用现金支付接口
     * @param $model
     * @return array
     */
    private function callCashPayment($model)
    {
        try {
            $Package = new Package();
            $user = new UserModel();
            $inpackdata = $model;
            $userdata = User::detail($model['member_id']);

            $payprice = $inpackdata['free'] + $inpackdata['pack_free'] + $inpackdata['other_free'] + $inpackdata['insure_free'] - $inpackdata['user_coupon_money'];
            if($payprice == 0){
                return [
                    'code' => 0,
                    'msg' => '订单金额为0，请先设置订单金额'
                ];
            }
            
            //扣除余额，并产生一天用户的消费记录；减少用户余额；
            $res = $user->logUpdate(0, $model['member_id'], $payprice, date("Y-m-d H:i:s").',集运单'.$inpackdata['order_sn'].'使用现金支付'.$payprice.'（现金支付不改变用户余额）');
            if(!$res){
                return [
                    'code' => 0,
                    'msg' => $user->getError() ?: '操作失败'
                ];
            }
                  
            //累计消费金额
            $userdata->setIncPayMoney($payprice);
            $this->dealerData(['amount'=>$payprice,'order_id'=>$model['id']], $userdata);
            
            //修改集运单状态和支付状态
            if($inpackdata['status'] == 2){
                $inpackdata->where('id', $model['id'])->update(['real_payment'=>$payprice,'status'=>3,'is_pay'=>1,'is_pay_type'=>5,'pay_time'=>date('Y-m-d H:i:s',time())]);
            }else{
                $inpackdata->where('id', $model['id'])->update(['real_payment'=>$payprice,'is_pay'=>1,'is_pay_type'=>5,'pay_time'=>date('Y-m-d H:i:s',time())]);
            }
            $Package->where('inpack_id', $model['id'])->update(['status'=>6,'is_pay'=>1]);
            
            //更新支付后的物流轨迹
            $noticesetting = Setting::detail('notice')['values'];
            if($noticesetting['ispay']['is_enable'] == 1){
                Logistics::addLog($inpackdata['order_sn'], $noticesetting['ispay']['describe'], date("Y-m-d H:i:s",time()));
            }
            
            return [
                'code' => 1,
                'msg' => '现金支付成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取用户待打包的包裹列表
     */
    private function getPendingPackages($member_id) {
        if (empty($member_id)) {
            return [];
        }
        
        $Package = new Package();
        return $Package->alias('p')
            ->field('p.id, p.express_num, p.weight, p.length, p.width, p.height, p.entering_warehouse_time, p.remark, p.usermark')
            ->where('p.member_id', $member_id)
            ->where('p.status','in',[2]) // 待打包状态
            ->where('p.is_delete', 0)
            ->where('p.inpack_id', 0) // 未分配到任何集运单
            ->order('p.entering_warehouse_time', 'desc')
            ->select()
            ->toArray();
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
        $weigthV = $pakdata['volume'];
        if (isset($data['boxes']) && !empty($data['boxes'])) {
            $boxes = json_decode(html_entity_decode($data['boxes']),true);
            foreach ($boxes as $v){
                // 计算体检重
                if(!empty($v['length']) && !empty($v['width']) && !empty($v['height']) && $line['volumeweight_type']==20){
                    $weigthV = round(($data['weight'] + (($v['length']*$v['width']*$v['height'])/$line['volumeweight'] - $data['weight'])*$line['bubble_weight']/100),2);
                }
                if(!empty($v['length']) && !empty($v['width']) && !empty($v['height']) && $line['volumeweight_type']==10){
                    $weigthV = round($v['length']*$v['width']*$v['height']/$line['volumeweight'],2);
                }
            }
        }
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
                //first_equity  second_equity equity
                
                if(!empty($suer['grade']) && $suer['grade']['status']==1){
                    $countorder = (new Inpack())->where('member_id',$suer['user_id'])->where('is_delete',0)->where('is_pay',1)->count();
               
                    if($countorder==0){
                        $value['discount'] = $suer['grade']['first_equity']*0.1;
                    }
                    if($countorder==1){
                        $value['discount'] = $suer['grade']['second_equity']*0.1;
                    }
                    if($countorder>1){
                        $value['discount'] = $suer['grade']['equity']*0.1;
                    }
                    if($value['discount']==0){
                        $value['discount'] = 1;
                    }
                }
        }else{
            $value['discount'] =1;
        }
        !isset($data['weight']) && $data['weight']=0;
        //根据是否重量取整
        if($line['weight_integer']==1 && $line['line_type']==0){
            $data['weight'] = ceil($data['weight']);
        }
       
        //根据是否体积重取整
        if($line['weightvol_integer']==1){
            $weigthV = ceil($weigthV);
        }
        // 取两者中 较重者 
          
        $oWeigth = ($weigthV >= ($data['weight']*$line['volumeweight_weight'])) ? $weigthV:$data['weight'];
       
        if($line['line_type']==1){
            $oWeigth = $data['weight'];
        }
       
        //关税和增值服务费用
        //计算所有的箱子的超长超重费；
        $boxes = [];
        if (isset($data['boxes']) && !empty($data['boxes'])) {
            $boxes = json_decode(html_entity_decode($data['boxes']),true);
            $otherfree = ( new LineService())->getserviceFree($oWeigth,$pakdata['country_id'],$line['line_category'],$pakdata['address']['code'],$boxes,$line['services_require'],$pakdata['total_goods_value']);
        }else{
            $otherfree = 0;
        }
     
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
                    'price' => ($reprice+ $free_rule[0]['weight_price']*$free_rule[0]['weight'][0])*$value['discount'],
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
                      'price' => ($v['first_price']+ $ww*$v['next_price'])*$value['discount'],
                      'rule' => $v,
                      'service' =>0,
                    ];
                    // dump($value['discount']);die;
               }
       
                break;
            case '3':
                $free_rule = json_decode($line['free_rule'],true);
               foreach ($free_rule as $k => $v) {
                   if ($oWeigth >= $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<=$v['weight'][1]){
                          $lines['predict'] = [
                              'weight' => $oWeigth,
                              'price' => $v['weight_price']*$value['discount'],
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
                              'price' => (floatval($v['weight_price']) * $ww)*$value['discount'],
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
                          $lines['sortprice'] =($vv['first_price']+ $ww*$vv['next_price'])*$value['discount'];
                          $lines['predict'] = [
                              'weight' => $oWeigth,
                              'price' => number_format(($vv['first_price']+ $ww*$vv['next_price'])*$value['discount'],2),
                              'rule' => $vv,
                              'service' =>0,
                          ]; 
                  }else{
                      $lines['sortprice'] = $vv['first_price'];
                      $lines['predict'] = [
                              'weight' => $oWeigth,
                              'price' => number_format(($vv['first_price'])*$value['discount'],2),
                              'rule' => $vv,
                              'service' =>0,
                          ]; 
                  }
                }
                
                if($vv['type']=="2"){
           
                       if ($oWeigth >= $vv['weight'][0]){
                          if (isset($vv['weight'][1]) && $oWeigth<=$vv['weight'][1]){
                              $lines['sortprice'] =floatval($vv['weight_price'])*$value['discount'] ;
                              $lines['predict'] = [
                                  'weight' => $oWeigth,
                                  'price' => number_format((floatval($vv['weight_price']))*$value['discount'],2),
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
                          $lines['sortprice'] =(floatval($vv['weight_price']) *$ww)*$value['discount'] ;
                          $lines['predict'] = [
                              'weight' => $oWeigth,
                              'price' => number_format((floatval($vv['weight_price']) * $ww)*$value['discount'],2),
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
                                  $lines['sortprice'] =($v['first_price']+ $ww*$v['next_price'])*$value['discount'];
                                  $lines['predict'] = [
                                      'weight' => $oWeigth,
                                      'price' => number_format(($v['first_price']+ $ww*$v['next_price'])*$value['discount'],2),
                                      'rule' => $v
                                  ]; 
                            }else{
                              $lines['sortprice'] = $v['first_price'];
                              $lines['predict'] = [
                                      'weight' => $oWeigth,
                                      'price' => number_format(($v['first_price'])*$value['discount'],2),
                                      'rule' => $v
                                  ]; 
                          }
                        }
               }
               break;
        }
        
        
        $PackageService = new PackageService(); 
        $pricethree = 0;
        $formatted = 0;   
        if (preg_match('/^-?\d{1,3}(,\d{3})*(\.\d+)?$/', $lines['predict']['price'])) {
            $floatValue = floatval(str_replace(',', '', $lines['predict']['price']));
            $formatted = number_format($floatValue, 2);
            $lines['predict']['price'] = $formatted;
        } 
       
        $pricetwo = str_replace(',','',$lines['predict']['price']);
        //   dump($lines['predict']['price']);
        if(count($pakdata['inpackservice'])>0){
          $servicelist = $pakdata['inpackservice'];
          foreach ($servicelist as $val){
              $servicedetail = $PackageService::detail($val['service_id']);
            //   dump($servicedetail);die;
              if($servicedetail['type']==0){
                  $lines['predict']['service'] = $lines['predict']['service']*$val['service_sum'] + $servicedetail['price'];
                  $pricethree = floatval($pricethree) + floatval($servicedetail['price']*$val['service_sum']);
              }
              
              if($servicedetail['type']==1){
                  $lines['predict']['service'] = floatval($pricetwo)*floatval($servicedetail['percentage'])/100 + floatval($lines['predict']['service']);
                  $pricethree = floatval($pricetwo)* floatval($servicedetail['percentage'])/100 + floatval($pricethree);
              }
            
          }
        }
        
       
        $settingdata  = SettingModel::getItem('adminstyle',$line['wxapp_id']);
        //不需要主动更新费用
        if($settingdata['is_editauto_free']==0){
          $lines['predict']['price'] = 0;
        }
        return $this->renderSuccess([
            'oWeigth'=>$oWeigth,
            'price'=>str_replace(',','',$lines['predict']['price']),
            'weightV'=>$weigthV,
            'packfree'=>$pricethree,
            'insure_free'=>$insure_free,
            'otherfree'=>$otherfree
        ]);
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
       $InpackImage = new InpackImage();
       $InpackItem = new InpackItem();
       $InpackDetail = new InpackDetail();
       $InpackService = new InpackService();
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
            $res = $model->where('id',$val)->update(['is_delete' => 1,'updated_time'=>getTime()]);
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
          $newpack['print_status_jhd'] = $newpack['print_status_jhd']['value'];
          $newpack['pay_type'] = $newpack['pay_type']['value'];

          $result = $model->insertGetId($newpack);
          if (!$result){
              return $this->renderSuccess('合并失败');
          }
          
          // 迁移包裹到新订单
          foreach ($idsArr as $va){
             $Package->where('inpack_id',$va)->update(['inpack_id'=>$result]); 
          }
          
          // 迁移订单图片到新订单（包括订单图片和重量/体积重实拍图）
          foreach ($idsArr as $va){
              // 迁移订单图片（image_type = 10）
              $InpackImage->where('inpack_id',$va)
                          ->where('image_type', 10)
                          ->update(['inpack_id'=>$result]);
              
              // 迁移重量/体积重实拍图（image_type = 20）
              $InpackImage->where('inpack_id',$va)
                          ->where('image_type', 20)
                          ->update(['inpack_id'=>$result]);
          }
          
          // 迁移订单明细（子订单/分箱清单）
          foreach ($idsArr as $va){
              $InpackItem->where('inpack_id',$va)
                         ->update(['inpack_id'=>$result]);
          }
          
          // 迁移申报信息（海关申报信息）
          foreach ($idsArr as $va){
              $InpackDetail->where('inpack_id',$va)
                           ->update(['inpack_id'=>$result]);
          }
          
          // 迁移服务项目（打包服务项目）
          foreach ($idsArr as $va){
              $InpackService->where('inpack_id',$va)
                            ->update(['inpack_id'=>$result]);
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
          
          $result = $Package->where('id','in',$ids)->update(['inpack_id'=>0]);
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
          $newpack['print_status_jhd'] = $detail['print_status_jhd']['value'];
          $newpack['pay_type'] = $detail['pay_type']['value'];
          
          $resultid = $model->insertGetId($newpack);
             
          $resultpack = $Package->where('id','in',$ids)->update(['inpack_id'=>$resultid,'updated_time'=>getTime()]);
          if ($resultpack){
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
       $res = $model->where('id','in',$idsArray)->update(['share_id'=>$pintuan_id,'inpack_type'=>1]);
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
       $label = $this->request->param('label');    
       $inpack = (new Inpack());
       $data = $inpack->getExpressData($id);
       if(!$data['order_sn']){
           return $this->renderError('转运单号为空');
       }
       $adminstyle = Setting::getItem('adminstyle',$data['wxapp_id']);
    //   dump($data->toArray());die;
       $data['setting'] = Setting::getItem('store',$data['wxapp_id']);
       if(!empty($data['member_id'])){
           $member  = UserModel::detail($data['member_id']);
           $data['name'] = $member['nickName'];
           if($data['setting']['usercode_mode']['is_show']==1){
              $data['member_id'] = $member['user_code'];
           }
           if($data['setting']['usercode_mode']['is_show']==2){
              $data['member_id'] = $data['usermark'];
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
        $data['total_free'] = $data['free'] + $data['pack_free'] + $data['insure_free']+$data['other_free'];
        $line_type_unit = [10=>'g',20=>'kg',30=>'lbs',40=>'cbm'];
        $data['line_type_unit'] = $line_type_unit[$data['line']['line_type_unit']];
        $dompdf = new Dompdf();
        if(count($data['packageitems'])==0){
            switch ($label) {
               case '10':
                   echo $this->label10($data);
                   break;
               case '20':
                   echo $this->label20($data);
                   break;
               case '30':
                   echo $this->label30($data);
                   break;
               case '40':
                   return $this->label40($data);
                   break;
               case '50':
                   //国际单号
                    if(!empty($data['t_order_sn'])){
                      $data['barcodet_order_sn'] = $generatorSVG->getBarcode($data['t_order_sn'], $generatorSVG::TYPE_CODE_128,$widthFactor =2, $totalHeight = 80); 
                   }else{
                        return $this->renderError('国际物流单号为空');
                   }
                   return $this->label50($data);
                   break;
               case '60':
                   echo $this->label60($data);
                   break;
                   
               default:
                    echo $this->label10($data);
                   break;
           }
        }else{
            for ($i = 0; $i < count($data['packageitems']); $i++) {
               $data['index'] = $i;
               switch ($label) {
                   case '10':
                       echo $this->label10($data);
                       break;
                   case '20':
                       echo $this->label20($data);
                       break;
                   case '30':
                        echo $this->label30($data);
                       break;
                   case '40':
                        return  $this->label40($data);
                       break;
                   case '50':
                       //国际单号
                    if(!empty($data['t_order_sn'])){
                      $data['barcodet_order_sn'] = $generatorSVG->getBarcode($data['t_order_sn'], $generatorSVG::TYPE_CODE_128,$widthFactor =2, $totalHeight = 80); 
                   }else{
                        return $this->renderError('国际物流单号为空');
                   }
                        echo $this->label50($data);
                        break;
                   case '60':
                        echo $this->label60($data);
                        break;
                   default:
                        echo $this->label10($data);
                       break;
               }
            }
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

  // 拣货单
    public function printpacklist(){
    $id = $this->request->param('id');    
       $label = $this->request->param('label');    
       $inpack = (new Inpack());
       $data = $inpack->getExpressData($id);
       if(!$data['order_sn']){
           return $this->renderError('转运单号为空');
       }
       $adminstyle = Setting::getItem('adminstyle',$data['wxapp_id']);
    //   dump($data->toArray());die;
       $data['setting'] = Setting::getItem('store',$data['wxapp_id']);
       if(!empty($data['member_id'])){
           $member  = UserModel::detail($data['member_id']);
           $data['name'] = $member['nickName'];
           if($data['setting']['usercode_mode']['is_show']==1){
              $data['member_id'] = $member['user_code'];
           }
           if($data['setting']['usercode_mode']['is_show']==2){
              $data['member_id'] = $data['usermark'];
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
        $data['total_free'] = $data['free'] + $data['pack_free'] + $data['insure_free']+$data['other_free'];
        $line_type_unit = [10=>'g',20=>'kg',30=>'lbs',40=>'cbm'];
        $data['line_type_unit'] = $line_type_unit[$data['line']['line_type_unit']];
        
    if(count($data['packagelist'])==0){
        $hll = '';
    }else{
        $hll = '';
        foreach ($data['packagelist'] as $key=>$value){
            $hll = $hll. '<tr><td class="font_m">'.($key + 1).'</td>
                <td class="font_m">'.$value['shelfunititem']['shelfunit']['shelf_unit_code'].'</td>
                <td class="font_m">'.$value['express_num'].'</td>
                <td class="font_m">'.$value['remark'].'</td>
                <td class="font_m">'.$value['num'].'</td>
                <td class="font_m">'.$data['weight'].'</td>
                <td class="font_m">'.$value['length'].'*'.$value['width'].'*'.$value['height'].'</td>
                <td class="font_m">'.$value['entering_warehouse_time'].'</td></tr>';
        }
    }
    $packservice = '';
    if(count($data['inpackservice'])>0){
        
        foreach ($data['inpackservice'] as $key=>$value){
            $packservice = $packservice.'  '. $value['service']['name'];
        }
    }
       
    echo $html = '<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }



    table {
        font: 12px "Microsoft YaHei", Verdana, arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    table.container {
        width: 100%;
        border-bottom: 0;
    }
    
    .conta {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .printdata tr{
        border: 1px solid #333;
    }
    
    .printdata td{
        border: 1px solid #333;
    }

    table td {
        padding: 2px;
    }

    table.nob {
        width: 100%;
    }

    table.nob td {
        border: 0;
    }

    table td.center {
        text-align: center;
    }

    table td.right {
        text-align: right;
    }

    table td.pl {
        padding-left: 5px;
        margin: 4px 0;
    }

    table td.br {
        border-right: 1px solid #333;
    }

    table.nobt,
    table td.nobt {
        border-top: 0;
    }

    table.nobb,
    table td.nobb {
        border-bottom: 0;
    }

    .font_s {
        font-size: 10px;
        -webkit-transform: scale(0.84, 0.84);
        *font-size: 10px;
    }

    .font_m {
        font-size: 14px;
        padding-left: 10px;
        text-align:center;
    }

    .font_l {
        font-size: 16px;
        font-weight: bold;
    }

    .font_xl {
        font-size: 18px;
        font-weight: bold;
    }

    .font_xxl {
        font-size: 28px;
        font-weight: bold;
    }

    .font_xxxl {
        font-size: 32px;
        font-weight: bold;
    }

    tbody tr:nth-child(2n){
        color: #000;
    }

    .country {
        font-size: 37px;
        padding: 0px;
        margin: 0px;
        font-weight: bold;
        width: 100px;
    }

    .barcode {
        text-align: center;
    }

    .barcode svg {
        width: 378px;
    }

    .font_12 {
        font-size: 12px;
        font-weight: bold;
    }

    .p-l-20 {
        padding-left: 20px;
    }

    .printdata {
        width: 190mm; /* 210mm总宽度减去左右各10mm边距 */
        height: auto;
        margin: 0 auto;
        border: 2px solid #000;
        padding: 10px;
        page-break-after: always;
    }

    .divider {
        height: 2px;
        background: #000;
        margin: 10px 0;
    }

    @page {
        size: A4;
        margin: 0;
    }

    @media print {
        body {
            width: 210mm;
            height: 297mm;
        }
        .printdata {
            border: none;
        }
    }
</style>

<div class="printdata">
    <table class="container" style="height: 50mm;">
        <tr>
            <td height="30mm" class="center">
                订单号
            </td>
            <td colspan="3" height="30mm" class="center">
                '.$data['order_sn'].'
            </td>
            <td rowspan="2" colspan="4" class="center">
               '.$data['barcode'].'
            </td>
        </tr>
        <tr>
            <td class="center">客户账号</td>
            <td colspan="3" class="center">'.$data['user']['nickName'].'('.$data['member_id'].')'.'</td>
        </tr>
        <tr>
            <td class="center">序号</td>
            <td class="center">货架货位</td>
            <td class="center">快递单号</td>
            <td class="center">备注</td>
            <td class="center">件数</td>
            <td class="center">重量</td>
            <td class="center">尺寸</td>
            <td class="center">添加时间</td>
        </tr>
        '.$hll.'
        <tr>
            <td class="center">包裹个数</td>
            <td colspan="3" class="center">'.count($data['packagelist']).'</td>
            <td class="center">总重量</td>
            <td colspan="3" class="center">'.count($data['packagelist']).'</td>
        </tr>
        <tr>
            <td class="center">运送方式</td>
            <td colspan="7" class="">'.$data['line']['name'].'</td>
        </tr>
        <tr>
            <td class="center">增值服务</td>
            <td colspan="7" class="">'.$packservice.'</td>
        </tr>
        <tr>
            <td class="center">客户备注</td>
            <td colspan="7" class="">'.$data['remark'].'</td>
        </tr>
    </table>
</div>';
}
    
 
    // 渲染标签模板B
    public function label40($data){
        if(!empty($data['t_number'])){
            // 使用缓存获取渠道配置
            $ditchdetail = \app\common\service\DitchCache::getConfig($data['t_number']);
            if($ditchdetail && $ditchdetail['ditch_no']==10004){
                $Hualei =  new Hualei([
                    'key'=>$ditchdetail['app_key'],
                    'token'=>$ditchdetail['app_token'],
                    'apiurl'=>$ditchdetail['api_url'],
                    'printurl'=>$ditchdetail['print_url']
                ]);
                $url = $Hualei->printlabel($data['t_order_id']);
                return $this->renderSuccess('获取成功',$url); 
            }
        }
        return $this->renderError("暂未开通");
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
    
    // 渲染标签模板B
    public function label50($data){
        
    if(count($data['packageitems'])==0){
		$jianshu = '<td class="font_xxl left">件數：1/1</td>';
    }else{
		$jianshu = '<td class="font_xxl left">件數：'.($data['index'] +1).'/'.count($data['packageitems']).'</td>';
    }
       
    return  $html = '<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    
    .printdata {
        width: 100mm;
        height: 100mm;
       
        border: 1px solid #000; /* 调试时可保留 */
        overflow: hidden; /* 防止内容溢出 */
    }
    
    table {
        width: 100%;
        font: 12px "Microsoft YaHei", Verdana, arial, sans-serif;
        border-collapse: collapse;
        margin:2mm;
    }
    .center{text-align:center;}
    .left{text-align:left;}
    .font_xl { font-size: 12px; }
    .font_xxl { font-size: 14px; font-weight: 600;}
    .font_xxxl { font-size: 20px;font-weight: bold }
    
    .barcode svg {
        width: 80mm;
        height: auto;
    }
    
    .divider {
        height: 1px;
        border-top: 1px solid #000;
        margin: 2mm 0;
    }
</style>
<div class="printdata">
    <!-- 条码区 -->
    <table>
        <tr>
            <td class="barcode center">'.$data['barcodet_order_sn'].'</td>
        </tr>
        <tr>
            <td class="center font_xxxl">'.$data['t_order_sn'].'</td>
        </tr>
    </table>
    
    <div class="divider"></div>
    
    <!-- 目的地 -->
    <table>
        <tr>
            <td class="font_xxxl">目的地：'.$data['address']['country'].'</td>
            <td class="font_xxxl">会员唛头：'.$data['member_id'].'</td>
        </tr>
    </table>
    
    <div class="divider"></div>
    
    <!-- 件数/渠道 -->
    <table>
        <tr>
            '.$jianshu.'
        </tr>
        <tr>
            <td class="font_xxl">路线渠道:'.$data['line']['name'].'</td>
        </tr>
    </table>
    
    <div class="divider"></div>
    
    <!-- 地址信息 -->
    <table>
        <tr>
            <td class="font_xxl">送货地址：'.$data['address']['province'].$data['address']['city'].$data['address']['detail'].'</td>
        </tr>
        <tr>
            <td class="font_xxl">收件人：'.$data['address']['name'].'</td>
        </tr>
        <tr>
            <td class="font_xxl">电话：'.$data['address']['phone'].'</td>
        </tr>
    </table>
    
    <!-- 打印时间 -->
    <table>
        <tr>
            <td class="font_xl" style="text-align: left;">
                打印时间：'.date("Y-m-d H:i:s").'
            </td>
        </tr>
    </table>
</div>
';
}
    
    // 渲染标签模板5
    public function label60($data){
        
    // 处理件数
    if(count($data['packageitems'])==0){
        $jianshu = '1/1';
        $actual_weight = $data['line_weight'] ?? $data['weight'] ?? 0;
        $chargeable_weight = $data['cale_weight'] ?? $actual_weight;
        $length = $data['length'] ?? 0;
        $width = $data['width'] ?? 0;
        $height = $data['height'] ?? 0;
    }else{
        $jianshu = ($data['index'] +1).'/'.count($data['packageitems']);
        $actual_weight = $data['packageitems'][$data['index']]['line_weight'] ?? $data['packageitems'][$data['index']]['weight'] ?? 0;
        $chargeable_weight = $data['cale_weight'] ?? $data['line_weight'] ?? $actual_weight;
        $length = $data['packageitems'][$data['index']]['length'] ?? 0;
        $width = $data['packageitems'][$data['index']]['width'] ?? 0;
        $height = $data['packageitems'][$data['index']]['height'] ?? 0;
    }
    
    // 获取系统名称（参考模板3）
    $system_name = !empty($data['setting']['name']) ? $data['setting']['name'] : '';
    
    // 格式化地址
    $full_address = '';
    if(!empty($data['address'])){
        $address_parts = [];
        if(!empty($data['address']['province'])) $address_parts[] = $data['address']['province'];
        if(!empty($data['address']['city'])) $address_parts[] = $data['address']['city'];
        if(!empty($data['address']['detail'])) $address_parts[] = $data['address']['detail'];
        $full_address = implode(' ', $address_parts);
    }
    
    // 生成条形码（优先使用国际单号，否则使用订单号）
    $barcode_number = !empty($data['t_order_sn']) ? $data['t_order_sn'] : $data['order_sn'];
    // 如果已有条形码且号码匹配，直接使用；否则生成新的
    if(!empty($data['barcode']) && !empty($data['order_sn']) && $barcode_number == $data['order_sn']){
        $barcode = $data['barcode'];
    }else{
        $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $barcodeSvg = $generatorSVG->getBarcode($barcode_number, $generatorSVG::TYPE_CODE_128, $widthFactor = 2, $totalHeight = 50);
        $barcode = preg_replace('/<\?xml[^>]*\?>\s*/is', '', $barcodeSvg);
    }
       
    return  $html = '<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    .printdata {
        width: 100mm;
        height: 100mm;
        padding: 3mm;
        font-family: "Microsoft YaHei", Arial, sans-serif;
        background: #f0f0f0;
        position: relative;
        overflow: hidden;
    }
    
    .label-row {
        margin-bottom: 3mm;
        font-size: 13px;
        line-height: 1.6;
        color: #000;
    }
    
    .label-title {
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 2mm;
        padding-bottom: 1mm;
    }
    
    .label-info {
        margin-bottom: 3mm;
    }
    
    .label-info-row {
        font-size: 11px;
        margin-bottom: 2mm;
        line-height: 1.4;
    }
    
    .member-info-row {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 3mm;
        line-height: 1.5;
    }
    
    .recipient-row {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 3mm;
        line-height: 1.5;
    }
    
    .line-info-row {
        font-size: 11px;
        margin-bottom: 2mm;
        line-height: 1.4;
    }
    
    .system-name-row {
        font-size: 11px;
        margin-bottom: 2mm;
        line-height: 1.4;
        text-align: center;
        border-top: 2px solid #000;
        padding-top: 1mm;
    }
    
    .barcode-container {
        text-align: center;
        margin-top: 4mm;
        padding: 2mm 0;
    }
    
    .barcode svg {
        width: 100%;
        height: auto;
        max-width: 75mm;
        margin: 0 auto;
    }
    
    .barcode-number {
        font-size: 12px;
        font-weight: bold;
        margin-top: 1mm;
        letter-spacing: 1px;
        font-family: "Courier New", monospace;
    }
    
    .info-row {
        font-size: 11px;
        margin-bottom: 2mm;
    }
</style>
<div class="printdata">
    <div class="label-info">
        <div class="member-info-row"><strong>会员ID:</strong> '.$data['member_id'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>件数:</strong> '.$jianshu.'</div>
    </div>
    
    <div class="label-info">
        <div class="recipient-row"><strong>收件人:</strong> '.($data['address']['name'] ?? '').'</div>
        <div class="label-info-row"><strong>电话:</strong> '.($data['address']['phone'] ?? '').'</div>
        <div class="label-info-row"><strong>送貨地址:</strong> '.$full_address.'</div>
    </div>
    
    <div class="label-info">
        <div class="system-name-row"><strong>'.$system_name.'</strong></div>
        <div class="line-info-row"><strong>线路渠道:</strong> '.($data['line']['name'] ?? '').'</div>
    </div>
    
    <div class="label-info">
        <div class="info-row"><strong>实重:</strong> '.number_format($actual_weight, 2).$data['line_type_unit'].' <strong>計費重:</strong> '.number_format($chargeable_weight, 2).$data['line_type_unit'].' <strong>尺寸:</strong> '.$length.'*'.$width.'*'.$height.'</div>
    </div>
    
    <div class="barcode-container">
        '.$barcode.'
        <div class="barcode-number">'.$barcode_number.'</div>
    </div>
</div>
';
}
    

    // 渲染标签模板B
    public function label30($data){
        
    if(count($data['packageitems'])==0){
        $hll = '<td class="font_m">重量：'.$data['line_weight'].$data['line_type_unit'].'</td>
		            <td class="font_m">尺寸：'.$data['length'].'*'.$data['width'].'*'.$data['height'].'</td>';
		$jianshu = '<td class="font_m">件數：1/1</td>';
    }else{
        $hll = '<td class="font_m">重量：'.$data['packageitems'][$data['index']]['line_weight'].$data['line_type_unit'].'</td>
        <td class="font_m">計費總重量：'.$data['line_weight'].$data['line_type_unit'].'</td>
		            <td class="font_m">尺寸：'.$data['packageitems'][$data['index']]['length'].'*'.$data['packageitems'][$data['index']]['width'].'*'.$data['packageitems'][$data['index']]['height'].'</td>';
		$jianshu = '<td class="font_m">件數：'.($data['index'] +1).'/'.count($data['packageitems']).'</td>';
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
		font-size: 14px;
		padding-left:10px;
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
	.p-l-20{
	    padding-left:20px;
	}
	.printdata:first-child{
	    margin-top:30px !important;
	}
	.printdata{
	    width:550px;
	    height:550px;
	    margin:30px 20px 20px 20px;
	    border:2px solid #000;
	}
	
</style>
<div style="padding:10px;">
<div class="printdata">
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

<div style="height:1px;border-top:2px solid #000;margin:10px 0px 10px 0px;"></div>
<table class="container" style="height:30px;">
	<tr>
		<td  height="55" class="font_xxxl conta">
		    <table class="nob">
		        <tr>
		            <td class="font_xxl conta">目的地：'.$data['address']['country'].'</td>
		            <td class="font_xxl p-l-20">會員ID：'.$data['member_id'].'</td>
		        </tr>
		    </table>
		</td>
	</tr>
</table>
<div style="height:1px;border-top:2px solid #000;margin:10px 0px 10px 0px;"></div>
<table class="container" style="height:30px;">
	<tr>
		<td  height="25" class="font_m">
		    <table class="nob">
		        <tr>
		            '. $jianshu.'
		        </tr>
		    </table>
		</td>
	</tr>
	<tr>
		<td  height="25" class="font_m">
		    <table class="nob">
		        <tr>
		            <td class="font_m">路線渠道：'.$data['line']['name'].'</td>
		        </tr>
		    </table>
		</td>
	</tr>
	<tr>
		<td  height="25" class="font_m">
		    <table class="nob">
		        <tr>
		            '.$hll.'
		        </tr>
		    </table>
		</td>
	</tr>
</table>
<div style="height:1px;border-top:2px solid #000;margin:10px 0px 10px 0px;"></div>
<table class="container" style="height:30px;">
	<tr>
		<td  height="25" class="font_m">
		    <table class="nob">
		        <tr>
		            <td class="font_m">送貨地址：'.$data['address']['province'].$data['address']['city'].$data['address']['detail'].'</td>
		        </tr>
		    </table>
		</td>
	</tr>
	<tr>
		<td  height="25" class="font_m">
		    <table class="nob">
		        <tr>
		            <td class="font_m">收件人：'.$data['address']['name'].'</td>
		        </tr>
		    </table>
		</td>
	</tr>
	<tr>
		<td  height="25" class="font_m">
		    <table class="nob">
		        <tr>
		            <td class="font_m">電話：'.$data['address']['phone'].'</td>
		        </tr>
		    </table>
		</td>
	</tr>
</table>
<div style="width：100%;height:1px;border-top:2px solid #000;margin:10px 0px 20px 0px;"></div>
<table class="container" style="height:30px;">
	<tr>
		<td  height="25" class="font_m">
		    <table class="nob">
		        <tr>
		            <td class="font_m">備註：'.$data['remark'].'</td>
		        </tr>
		        <tr>
		            <td class="font_m">打印時間：'.date("Y-m-d H:i:s",time()).'</td>
		        </tr>
		    </table>
		</td>
	</tr>
</table>
</div>
</div>
';
}
    
    
    // 渲染标签模板B
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
        		   Qty: '. count($data['packageitems']) .' pkgs
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
            src: url(assets/common/fonts/SimHei.ttf)
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
					<td width="60">'.count($data['packageitems']).'</td>
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
public function expressBillbatch() {
    try {
        $selectIds = $this->postData("selectIds");
        if (empty($selectIds)) {
            return $this->renderError("未选择任何订单");
        }

        // 如果selectIds是字符串，转换为数组
        if (is_string($selectIds)) {
            $selectIds = explode(',', $selectIds);
            $selectIds = array_filter($selectIds); // 过滤空值
        }
        
        if (empty($selectIds)) {
            return $this->renderError("未选择任何订单");
        }

        $inpack = new Inpack();
        $data = $inpack->getExpressBatchData($selectIds);
        if (empty($data)) {
            return $this->renderError("未找到订单数据");
        }

        $setting = Setting::getItem('store', $data[0]['wxapp_id']);
        $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $htmlArray = [];

        foreach ($data as $order) {
            $order['setting'] = $setting;
            // 生成条形码并移除XML声明（HTML中嵌入SVG时不需要XML声明）
            $barcodeSvg = $generatorSVG->getBarcode($order['order_sn'], $generatorSVG::TYPE_CODE_128, 2, 50);
            // 移除XML声明，保留纯SVG内容
            $order['barcode'] = preg_replace('/<\?xml[^>]*\?>\s*/is', '', $barcodeSvg);

            // 拼接收件人信息 - 确保UTF-8编码
            $recipientInfo = isset($order['address']['name']) ? $order['address']['name'] : '';
            if (!mb_check_encoding($recipientInfo, 'UTF-8')) {
                $recipientInfo = mb_convert_encoding($recipientInfo, 'UTF-8', 'auto');
            }
            if (isset($order['setting']['address_setting']['is_tel_code']) && $order['setting']['address_setting']['is_tel_code'] == 1) {
                $telCode = isset($order['address']['tel_code']) ? $order['address']['tel_code'] : '';
                if (!mb_check_encoding($telCode, 'UTF-8')) {
                    $telCode = mb_convert_encoding($telCode, 'UTF-8', 'auto');
                }
                $recipientInfo .= $telCode;
            }
            $phone = isset($order['address']['phone']) ? $order['address']['phone'] : '';
            $order['shoujianren'] = $recipientInfo . '  ' . $phone;

            // 拼接详细地址 - 确保UTF-8编码
            $addressFields = ['country', 'province', 'city', 'region', 'street', 'door'];
            $fullAddress = '';
            foreach ($addressFields as $field) {
                if (isset($order['setting']['address_setting']["is_$field"]) && $order['setting']['address_setting']["is_$field"] == 1) {
                    $fieldValue = isset($order['address'][$field]) ? $order['address'][$field] : '';
                    if (!mb_check_encoding($fieldValue, 'UTF-8')) {
                        $fieldValue = mb_convert_encoding($fieldValue, 'UTF-8', 'auto');
                    }
                    $fullAddress .= $fieldValue;
                }
            }
            $detail = isset($order['address']['detail']) ? $order['address']['detail'] : '';
            if (!mb_check_encoding($detail, 'UTF-8')) {
                $detail = mb_convert_encoding($detail, 'UTF-8', 'auto');
            }
            $order['address']['xiangxi'] = $fullAddress . $detail;

            // 确保所有字符串字段都是UTF-8编码
            foreach ($order as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $order[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
                } elseif (is_array($value)) {
                    array_walk_recursive($value, function(&$item) {
                        if (is_string($item) && !mb_check_encoding($item, 'UTF-8')) {
                            $item = mb_convert_encoding($item, 'UTF-8', 'auto');
                        }
                    });
                    $order[$key] = $value;
                }
            }

            // 渲染模板
            $htmlArray[] = $this->template20($order);
        }

        // 构建完整的HTML文档
        $htmlContent = implode('<hr style="page-break-after: always; border: none; margin: 0;">', $htmlArray);
        
        // 确保HTML内容本身是UTF-8编码
        if (!mb_check_encoding($htmlContent, 'UTF-8')) {
            $htmlContent = mb_convert_encoding($htmlContent, 'UTF-8', 'auto');
        }
        
        // 清理HTML中的旧字体定义（保持兼容）
        $htmlContent = preg_replace('/@font-face\s*\{[^}]*\}/is', '', $htmlContent);
        
        // 添加完整的HTML文档结构，包含meta charset
        // mPDF对中文支持很好，使用UTF-8即可
        $html = '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta charset="UTF-8" />
</head>
<body>
' . $htmlContent . '
</body>
</html>';
        
        // 使用mPDF生成PDF - 对中文支持很好
        // 检查mPDF类是否存在（支持 mPDF 6.x 和 7.x）
        $mpdfClass = null;
        $mpdfVersion = null;
        
        // 检查 mPDF 7.x (新命名空间)
        if (class_exists('\Mpdf\Mpdf', false)) {
            $mpdfClass = '\Mpdf\Mpdf';
            $mpdfVersion = 7;
        }
        // 检查 mPDF 6.x (旧命名空间)
        elseif (class_exists('mPDF', false)) {
            $mpdfClass = 'mPDF';
            $mpdfVersion = 6;
        } else {
            // 尝试自动加载 mPDF 7.x
            $mpdfPath = __DIR__ . '/../../../vendor/mpdf/mpdf/src/Mpdf.php';
            if (file_exists($mpdfPath)) {
                require_once $mpdfPath;
                if (class_exists('\Mpdf\Mpdf')) {
                    $mpdfClass = '\Mpdf\Mpdf';
                    $mpdfVersion = 7;
                }
            }
            // 尝试自动加载 mPDF 6.x
            if (!$mpdfClass) {
                $mpdfPath6 = __DIR__ . '/../../../vendor/mpdf/mpdf/mpdf.php';
                if (file_exists($mpdfPath6)) {
                    require_once $mpdfPath6;
                    if (class_exists('mPDF')) {
                        $mpdfClass = 'mPDF';
                        $mpdfVersion = 6;
                    }
                }
            }
        }
        
        // 如果仍然找不到类，返回错误
        if (!$mpdfClass) {
            return $this->renderError("mPDF库未安装。请按照以下步骤操作：<br/>1. 在项目根目录运行命令：composer require mpdf/mpdf:^7.0（或 ^6.1 如果PHP版本低于7.1）<br/>2. 安装完成后运行：composer dump-autoload<br/>3. 如果无法使用composer，请查看 install_mpdf.md 文件了解手动安装方法");
        }
        
        try {
            // mPDF配置
            $tempDir = sys_get_temp_dir() . '/mpdf';
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }
            
            // 获取mPDF字体目录（尝试多个可能的位置）
            $mpdfFontDirs = [];
            $possibleFontDirs = [
                __DIR__ . '/../../../vendor/mpdf/mpdf/ttfonts',
                __DIR__ . '/../../../vendor/mpdf/mpdf/src/Config/../../ttfonts',
            ];
            foreach ($possibleFontDirs as $dir) {
                $realDir = realpath($dir);
                if ($realDir && is_dir($realDir)) {
                    $mpdfFontDirs[] = $realDir;
                    break;
                }
            }
            
            // mPDF 7.x 配置格式
            if ($mpdfVersion == 7) {
                $config = [
                    'mode' => 'utf-8',
                    'format' => [100, 150], // 自定义尺寸：100mm x 150mm
                    'orientation' => 'P',
                    'margin_left' => 0,
                    'margin_right' => 0,
                    'margin_top' => 0,
                    'margin_bottom' => 0,
                    'margin_header' => 0,
                    'margin_footer' => 0,
                    'tempDir' => $tempDir,
                    'default_font' => 'dejavusans', // mPDF默认字体，支持中文
                    'autoScriptToLang' => true,
                    'autoLangToFont' => true,
                ];
                
                // 如果有字体目录，添加到配置
                if (!empty($mpdfFontDirs)) {
                    $config['fontDir'] = $mpdfFontDirs;
                }
                
                $mpdf = new \Mpdf\Mpdf($config);
            } else {
                // mPDF 6.x 配置格式（数组参数）
                // mPDF 6.x 的自定义尺寸格式：使用数组 [宽度, 高度]（单位：毫米）
                $customFormat = [100, 150]; // 100mm x 150mm
                
                $mpdf = new mPDF(
                    'utf-8',
                    $customFormat, // 自定义尺寸：100mm x 150mm
                    '',
                    '',
                    0, // margin_left
                    0, // margin_right
                    0, // margin_top
                    0, // margin_bottom
                    0, // margin_header
                    0, // margin_footer
                    'P' // orientation
                );
                
                // 设置临时目录
                $mpdf->tempDir = $tempDir;
                
                // mPDF 6.x 需要单独设置字体
                $mpdf->autoScriptToLang = true;
                $mpdf->autoLangToFont = true;
                if (!empty($mpdfFontDirs)) {
                    $mpdf->fontDir = $mpdfFontDirs;
                }
            }
            
            // 设置UTF-8编码
            mb_internal_encoding('UTF-8');
            
            // 写入HTML内容
            $mpdf->WriteHTML($html);
            
            // 确保excel目录存在
            $excelDir = WEB_PATH . DIRECTORY_SEPARATOR . 'excel';
            if (!is_dir($excelDir)) {
                if (!mkdir($excelDir, 0755, true)) {
                    return $this->renderError("无法创建PDF存储目录");
                }
            }

            // 保存PDF
            $filename = rand(100000, 999999) . '.pdf';
            $filePath = $excelDir . DIRECTORY_SEPARATOR . $filename;
            
            // 输出到文件（mPDF 7.x 使用字符串 'F' 表示保存到文件）
            $mpdf->Output($filePath, 'F');
            
            $pdfOutput = file_get_contents($filePath);
            if ($pdfOutput === false) {
                return $this->renderError("读取PDF文件失败");
            }
        } catch (\Exception $e) {
            // 如果mPDF不存在或出错，回退到dompdf
            return $this->renderError("PDF生成失败: " . $e->getMessage() . "。请先安装mPDF: composer require mpdf/mpdf");
        }

        $pdfUrl = base_url() . '/excel/' . $filename;
        return $this->renderSuccess('面单生成成功', '', ['url' => $pdfUrl]);
        
    } catch (\Exception $e) {
        return $this->renderError("生成面单失败: " . $e->getMessage());
    }
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
    //导出成csv或excel文档
     public function loaddingOutExcel(){
        //获取需要导出的数据列表
        $ids= input("post.selectId/a");
        $seach= input("post.seach/a");
        $format = input("post.format", "csv"); // 获取导出格式，默认为csv
        
        //1 待入库 2 已入库 3 已分拣上架  4 待打包  5 待支付  6 已支付 7 已分拣下架  8 已打包  9 已发货 10 已收货 11 已完成
        $map =[-1=>'问题件',1=>'待入库',2=>'已入库',3=>'已分拣上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        $status = [1=>'待查验',2=>'待支付',3=>'已支付','4'=>'已拣货','5'=>'已打包','6'=>'已发货','7'=>'已收货','8'=>'已完成','-1'=>'已取消'];
        if($ids){
           $data = (new Inpack())->with(['address','user'])->whereIn('id',$ids)->select()->each(function ($item, $key) use($map){
                    $item['t_name'] = (new Line())->where('id',$item['line_id'])->value('name');
                    $item['total_free'] = $item['free']+ $item['pack_free'] +$item['other_free'] +$item['insure_free'];
                    
                    //集运单包裹中的物品分类和价格
                    $packdata = (new Package())->where('inpack_id',$item['id'])->where('is_delete',0)->value('id');
                    $packClass = [];
                    $packprice = 0;

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
                    return $item;
                }); 
        }else{
            $where = [];
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
            $data =(new Inpack())->with(['address','user'])->where($where)->select()->each(function ($item, $key) use($map){
                    $item['t_name'] = (new Line())->where('id',$item['line_id'])->value('name');
                    $item['total_free'] = $item['free']+ $item['pack_free'] +$item['other_free'] +$item['insure_free'];
                    //判断是否有优惠折扣
                    $discountData = (new UserLine())->where(['user_id'=>$item["user"]['user_id'],'line_id'=>$item['line_id']])->find();
                    if($discountData){
                  
                        $item['discount'] = $discountData['discount'];
                    }else{
                        $item['discount'] = 1;
                    }
                                   
                    $item['discount_price'] = $item['discount'] * $item['free'];
                    $item['status_text'] = $map[$item['status']];
                    return $item;
                });
        }
        
        $setting = SettingModel::getItem('store',$data[0]['wxapp_id']);
        
        // 根据格式选择导出方式
        if($format == 'excel'){
            // 导出Excel格式
            return $this->exportToExcel($data, $setting, $status);
        }else{
            // 导出CSV格式
            return $this->exportToCsv($data, $setting, $status);
        }
     }
     
    /**
      * 导出为CSV格式
      */
     private function exportToCsv($data, $setting, $status){
        // 生成CSV文件名
        if($setting['usercode_mode']['is_show']==0){
            $filename = $data[0]['user']['user_id'].'-'. date("YmdHis") . ".csv";
        }else{
            $filename = $data[0]['user']['user_code'].'-'. date("YmdHis") . ".csv";
        }
        
        // 确保excel目录存在
        $csvDir = "excel/";
        if(!is_dir($csvDir)){
            mkdir($csvDir, 0755, true);
        }
        
        // 打开文件句柄
        $filePath = $csvDir . $filename;
        $fp = fopen($filePath, 'w');
        
        // 添加UTF-8 BOM，确保Excel正确识别中文
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // 写入表头
        $headers = [
            '序号', '集运线路', '平台订单号', '目的地', '重量', '标准价', 
            '商品品类', '商品价格', '用户ID', '姓名', '手机号', 
            '个人通关号码', '身份证', '地址', '邮编', '承运商', 
            '发货单号', '备注', '状态', '业务日期', '签收时间', '专属客服'
        ];
        fputcsv($fp, $headers);
        
        // 写入数据行
        for($i=0;$i<count($data);$i++){
            //根据setting的is_show来判断是显示user_code还是user_id
            $userIdValue = ($setting['usercode_mode']['is_show'] == 0) ? $data[$i]['user']['user_id'] : (isset($data[$i]['user']['user_code']) ? $data[$i]['user']['user_code'] : $data[$i]['user']['user_id']);
            
            $row = [
                $i+1, //序号
                isset($data[$i]['t_name']) ? $data[$i]['t_name'] : '', //集运路线
                isset($data[$i]['order_sn']) ? $data[$i]['order_sn'] : '', //平台订单号
                isset($data[$i]['address']['country']) ? $data[$i]['address']['country'] : '', //目的地
                isset($data[$i]['cale_weight']) && $data[$i]['cale_weight'] ? $data[$i]['cale_weight'] : (isset($data[$i]['weight']) ? $data[$i]['weight'] : ''), //重量
                isset($data[$i]['total_free']) ? $data[$i]['total_free'] : '', //标准价
                isset($data[$i]['packClass']) ? $data[$i]['packClass'] : '', //商品品类
                isset($data[$i]['packprice']) ? $data[$i]['packprice'] : 0, //商品价格
                $userIdValue, //用户ID
                isset($data[$i]['address']['name']) ? $data[$i]['address']['name'] : '', //姓名
                isset($data[$i]['address']['phone']) ? $data[$i]['address']['phone'] : '', //手机号
                isset($data[$i]['address']['clearancecode']) ? $data[$i]['address']['clearancecode'] : '', //个人通关号码
                isset($data[$i]['address']['identitycard']) ? $data[$i]['address']['identitycard'] : '', //身份证
                isset($data[$i]['address']['detail']) ? $data[$i]['address']['detail'] : '', //地址
                isset($data[$i]['address']['code']) ? $data[$i]['address']['code'] : '', //邮编
                isset($data[$i]['t_name']) ? $data[$i]['t_name'] : '', //承运商
                isset($data[$i]['t_order_sn']) ? $data[$i]['t_order_sn'] : '', //发货单号
                isset($data[$i]['remark']) ? $data[$i]['remark'] : '', //备注
                isset($status[$data[$i]['status']]) ? $status[$data[$i]['status']] : '', //状态
                isset($data[$i]['created_time']) ? $data[$i]['created_time'] : '', //业务日期
                isset($data[$i]['receipt_time']) ? $data[$i]['receipt_time'] : '', //签收时间
                isset($data[$i]['user']['user_id']) ? $data[$i]['user']['user_id'] : '' //专属客服
            ];
            fputcsv($fp, $row);
        }
        
        // 关闭文件句柄
        fclose($fp);
        
        return $this->renderSuccess("导出成功", [
            "file_name" => "https://".$_SERVER["HTTP_HOST"] . "/excel/" . $filename,
        ]);
     }
     
     
     /**
      * 导出为Excel格式
      */
     private function exportToExcel($data, $setting, $status){
        //引入excel插件
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        
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
         
        $objPHPExcel->getActiveSheet()->getStyle( 'A4:V4')->applyFromArray($style_Array);
        
        //第一行的样式 - 合并所有列并加粗放大
        $objPHPExcel->getActiveSheet()->setCellValue('A1',$setting['name'].'── 业务结算清单');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(24);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:V1');
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(36);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        //第二行的样式 - 合并所有列并加粗放大
        $objPHPExcel->getActiveSheet()->setCellValue('A2','致'.$data[0]['address']['name'].'  '.'导出日期：'.getTime());
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:V2');
        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(28);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
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
                   
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:V')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A4:V4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objPHPExcel->getActiveSheet()->getStyle('A:V')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

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
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+5),isset($data[$i]['packClass'])?$data[$i]['packClass']:'');//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+5),isset($data[$i]['packprice'])?$data[$i]['packprice']:0);//标准价 ***********
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

     
     /**导出批次里所有的集运订单中的包裹明细**/
    //导出成excel文档
     public function exportBatchInpackpackage(){
         //引入excel插件
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $Batch = new Batch;
        $Inpack = new Inpack;
        $param= $this->request->param();
        $inpackList = $Inpack->where('batch_id',$param['id'])->where('is_delete',0)->select(); //获取到所有此批次的集运订单
        $data = [];
        // dump($inpackList);die;
        foreach ($inpackList as $key => $value) {
            $result = (new Package())
                ->with(['storage', 'Member', 'categoryAttr', 'batch'])
                ->where('inpack_id', $value['id'])
                ->where('is_delete', 0)
                ->select();
            
            // 将查询结果合并到$data数组中
            $data = array_merge($data, $result->toArray());
        }
        //获取需要导出的数据列表
        if(count($data)==0){
            return $this->renderError('暂无订单或订单中无包裹'); 
        }
        // dump($data);die;
        
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
                    $packdata = (new Package())->where('inpack_id',$item['id'])->where('is_delete',0)->value('id');
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
     
    /**
     * 获取打印任务数据（统一接口）
     * 支持打印母单、子单、全部包裹
     * 用于订单列表打印增强功能
     */
    public function getPrintTask()
    {
        try {
            $id = $this->request->param('id');
            $waybillNo = $this->request->param('waybill_no', '');
            $label = $this->request->param('label', 60);
            $printAll = $this->request->param('print_all', 0);  // 新增：是否打印全部包裹
            
            // 📝 记录请求开始
            \app\common\service\PrintLogger::printTask('getPrintTask', '开始处理打印任务', [
                'order_id' => $id,
                'waybill_no' => $waybillNo,
                'print_all' => $printAll
            ]);
            
            if (empty($id)) {
                \app\common\service\PrintLogger::error('getPrintTask', '订单ID为空');
                return $this->renderError('订单ID不能为空');
            }
            
            // 获取订单数据
            $inpack = new Inpack();
            $data = $inpack->getExpressData($id);
            
            if (!$data) {
                \app\common\service\PrintLogger::error('getPrintTask', '订单不存在', ['order_id' => $id]);
                return $this->renderError('订单不存在');
            }
            
            \app\common\service\PrintLogger::info('getPrintTask', '订单数据加载成功', [
                'order_id' => $id,
                'order_sn' => $data['order_sn'] ?? ''
            ]);
            
            // 检查运单号（仅在非打印全部模式下需要）
            if (!$printAll) {
                // 优先使用传递的 waybill_no 参数（用于打印子单）
                // 如果没有传递，则使用订单的 t_order_sn（母单号）
                // 如果 t_order_sn 为空，尝试从子单列表获取第一个子单号作为母单号
                if (empty($waybillNo)) {
                    // 没有传递 waybill_no，使用订单的 t_order_sn
                    if (empty($data['t_order_sn'])) {
                        // t_order_sn 也为空，尝试从子单列表获取
                        if (!empty($data['packageitems']) && is_array($data['packageitems']) && count($data['packageitems']) > 0) {
                            // 第一个子单的运单号就是母单号
                            $firstItem = $data['packageitems'][0];
                            if (!empty($firstItem['t_order_sn'])) {
                                $data['t_order_sn'] = $firstItem['t_order_sn'];
                                $waybillNo = $firstItem['t_order_sn'];
                                \think\Log::info('getPrintTask - 从子单列表获取母单号: ' . $waybillNo);
                            } else {
                                return $this->renderError('国际物流单号为空');
                            }
                        } else {
                            return $this->renderError('国际物流单号为空');
                        }
                    } else {
                        // 使用订单的 t_order_sn
                        $waybillNo = $data['t_order_sn'];
                    }
                } else {
                    // 传递了 waybill_no，使用它（可能是母单号或子单号）
                    // 如果订单的 t_order_sn 为空，需要设置它（用于后续判断）
                    if (empty($data['t_order_sn'])) {
                        if (!empty($data['packageitems']) && is_array($data['packageitems']) && count($data['packageitems']) > 0) {
                            // 第一个子单的运单号就是母单号
                            $firstItem = $data['packageitems'][0];
                            if (!empty($firstItem['t_order_sn'])) {
                                $data['t_order_sn'] = $firstItem['t_order_sn'];
                                \think\Log::info('getPrintTask - 设置母单号: ' . $data['t_order_sn']);
                            }
                        }
                    }
                }
            }
            
            // 判断渠道类型
            // 通过 t_number 或 t_name 判断是否为顺丰
            $tNumber = isset($data['t_number']) ? $data['t_number'] : '';
            $tName = isset($data['t_name']) ? $data['t_name'] : '';
            
            // 📝 记录订单信息
            \app\common\service\PrintLogger::info('getPrintTask', '订单渠道信息', [
                'order_id' => $id,
                't_number' => $tNumber,
                't_name' => $tName,
                't_order_sn' => $data['t_order_sn']
            ]);
            
            // 查找对应的渠道配置
            $ditchModel = new \app\common\model\Ditch();
            $ditchConfig = null;
            
            // 优先通过 t_number 查找 (t_number 实际存储的是 ditch_id)
            if (!empty($tNumber)) {
                // 使用缓存获取渠道配置
                $ditchConfig = \app\common\service\DitchCache::getConfig($tNumber);
                
                // 如果没找到,尝试通过 ditch_no 查找
                if (!$ditchConfig) {
                    $ditchConfig = $ditchModel->where('ditch_no', $tNumber)->find();
                }
            }
            
            // 如果没找到,尝试通过名称模糊匹配
            if (!$ditchConfig && !empty($tName)) {
                $ditchConfig = $ditchModel->where('ditch_name', 'like', '%' . $tName . '%')->find();
            }
            
            // 📝 记录渠道配置查找结果
            if ($ditchConfig) {
                \app\common\service\PrintLogger::success('getPrintTask', '渠道配置查找成功', [
                    'ditch_id' => $ditchConfig['ditch_id'],
                    'ditch_name' => $ditchConfig['ditch_name'],
                    'ditch_type' => $ditchConfig['ditch_type']
                ]);
            } else {
                \app\common\service\PrintLogger::warning('getPrintTask', '未找到渠道配置', [
                    't_number' => $tNumber,
                    't_name' => $tName
                ]);
            }
            
            // 判断渠道类型
            // ditch_type: 1=普通渠道, 2=中通快递, 3=中通管家, 4=顺丰速运
            $isSf = false;
            $isZto = false;
            $isJd = false;
            
            if ($ditchConfig) {
                $ditchType = isset($ditchConfig['ditch_type']) ? (int)$ditchConfig['ditch_type'] : 1;
                $isSf = ($ditchType === 4) || stripos($ditchConfig['ditch_name'], '顺丰') !== false;
                $isZto = ($ditchType === 2 || $ditchType === 3) || stripos($ditchConfig['ditch_name'], '中通') !== false;
                $isJd = ($ditchType === 5) || stripos($ditchConfig['ditch_name'], '京东') !== false;
            } elseif (stripos($tName, '顺丰') !== false) {
                // 即使没找到配置,如果名称包含"顺丰",也尝试使用顺丰云打印
                $isSf = true;
                
                // 尝试获取默认的顺丰配置 (ditch_type=4)
                $ditchConfig = $ditchModel->where('ditch_type', 4)
                    ->order('ditch_id DESC')
                    ->find();
            } elseif (stripos($tName, '中通') !== false) {
                // 如果名称包含"中通",尝试使用中通云打印
                $isZto = true;
                
                // 尝试获取默认的中通配置 (ditch_type=2 或 3)
                $ditchConfig = $ditchModel->whereIn('ditch_type', [2, 3])
                    ->order('ditch_id DESC')
                    ->find();
            } elseif (stripos($tName, '京东') !== false) {
                // 如果名称包含"京东",尝试使用京东云打印
                $isJd = true;
                
                // 尝试获取默认的京东配置 (ditch_type=5)
                $ditchConfig = $ditchModel->where('ditch_type', 5)
                    ->order('ditch_id DESC')
                    ->find();
            }
            
            // 📝 记录渠道判断结果
            \app\common\service\PrintLogger::info('getPrintTask', '渠道类型判断', [
                'is_sf' => $isSf,
                'is_zto' => $isZto,
                'is_jd' => $isJd,
                'has_config' => !empty($ditchConfig)
            ]);
            
            if ($isZto && $ditchConfig) {
                // 使用中通云打印
                \app\common\service\PrintLogger::printTask('ZTO', '开始中通云打印', [
                    'order_id' => $id,
                    'ditch_id' => $ditchConfig['ditch_id'],
                    'ditch_name' => $ditchConfig['ditch_name'],
                    'print_all' => $printAll
                ]);
                
                // 转换数据库字段名到 Zto 类期望的配置键名
                $ditchArray = is_object($ditchConfig) ? $ditchConfig->toArray() : (array)$ditchConfig;
                $ztoConfig = [
                    'key' => isset($ditchArray['app_key']) ? $ditchArray['app_key'] : '',
                    'token' => isset($ditchArray['app_token']) ? $ditchArray['app_token'] : '',
                    'apiurl' => isset($ditchArray['api_url']) ? $ditchArray['api_url'] : '',
                    'customer_code' => isset($ditchArray['customer_code']) ? $ditchArray['customer_code'] : '',
                    'ditch_type' => isset($ditchArray['ditch_type']) ? (int)$ditchArray['ditch_type'] : 2,
                    'push_config_json' => isset($ditchArray['push_config_json']) ? $ditchArray['push_config_json'] : '',
                    // 发件人信息字段
                    'sender_name' => isset($ditchArray['sender_name']) ? $ditchArray['sender_name'] : '',
                    'sender_phone' => isset($ditchArray['sender_phone']) ? $ditchArray['sender_phone'] : '',
                    'sender_province' => isset($ditchArray['sender_province']) ? $ditchArray['sender_province'] : '',
                    'sender_city' => isset($ditchArray['sender_city']) ? $ditchArray['sender_city'] : '',
                    'sender_district' => isset($ditchArray['sender_district']) ? $ditchArray['sender_district'] : '',
                    'sender_address' => isset($ditchArray['sender_address']) ? $ditchArray['sender_address'] : '',
                ];
                
                $zto = new \app\common\library\Ditch\Zto($ztoConfig);
                
                // 根据参数判断打印模式
                if ($printAll) {
                    $printMode = 'all';
                    \think\Log::info('getPrintTask - 中通打印全部模式');
                } else {
                    $printMode = 'mother'; // 默认打印母单
                    
                    // 如果传递了 waybill_no，判断是否为子单
                    if (!empty($waybillNo) && !empty($data['t_order_sn'])) {
                        if ($waybillNo !== $data['t_order_sn']) {
                            $printMode = 'child';
                            \think\Log::info('getPrintTask - 中通检测到子单打印: ' . json_encode([
                                'waybill_no' => $waybillNo,
                                'mother_waybill' => $data['t_order_sn']
                            ], JSON_UNESCAPED_UNICODE));
                        }
                    }
                }
                
                // 调用中通云打印接口
                \app\common\service\PrintLogger::apiRequest('ZTO', '调用云打印接口', [
                    'print_mode' => $printMode,
                    'waybill_no' => $waybillNo
                ]);
                
                $result = $zto->cloudPrint($id, [
                    'print_mode' => $printMode,
                    'waybill_no' => $waybillNo,
                    'sellerMessage' => isset($data['seller_message']) ? $data['seller_message'] : (isset($data['remark']) ? $data['remark'] : '')
                ]);
                
                // 📝 记录调用结果
                if ($result === false) {
                    \app\common\service\PrintLogger::apiResponse('ZTO', false, [
                        'error' => $zto->getError()
                    ]);
                    return $this->renderError('获取中通云打印数据失败: ' . $zto->getError());
                }
                
                if (!$result['success']) {
                    \app\common\service\PrintLogger::apiResponse('ZTO', false, [
                        'message' => $result['message']
                    ]);
                    return $this->renderError('中通云打印失败: ' . $result['message']);
                }
                
                \app\common\service\PrintLogger::apiResponse('ZTO', true, [
                    'message' => $result['message'],
                    'has_data' => isset($result['data'])
                ]);
                
                // 返回中通云打印结果
                // 中通云打印返回的是打印成功/失败列表，前端需要根据这个结果显示
                return $this->renderSuccess('获取成功', null, [
                    'mode' => 'zto_cloud_print',
                    'data' => $result['data'],
                    'order_id' => $id,
                    'waybill_no' => $waybillNo ?: $data['t_order_sn'],
                    'print_all' => $printAll ? true : false
                ]);
                
            } elseif ($isJd && $ditchConfig) {
                // 使用京东云打印
                $this->writeJdDebugLog("========================================");
                $this->writeJdDebugLog(">>> getPrintTask - 京东云打印流程开始");
                $this->writeJdDebugLog("========================================");
                $this->writeJdDebugLog("请求参数: order_id={$id}, waybill_no={$waybillNo}, print_all=" . ($printAll ? 'true' : 'false'));
                
                \think\Log::info('getPrintTask - 使用京东云打印: ' . json_encode([
                    'order_id' => $id,
                    'ditch_id' => $ditchConfig['ditch_id'],
                    'ditch_name' => $ditchConfig['ditch_name'],
                    'waybill_no' => $waybillNo ?: $data['t_order_sn'],
                    'print_all' => $printAll
                ], JSON_UNESCAPED_UNICODE));

                // 转换数据库字段名到 Jd 类期望的配置键名
                $ditchArray = is_object($ditchConfig) ? $ditchConfig->toArray() : (array)$ditchConfig;
                $jdConfig = [
                    'app_key'       => isset($ditchArray['app_key']) ? $ditchArray['app_key'] : '',
                    'app_secret'    => isset($ditchArray['shop_key']) ? $ditchArray['shop_key'] : '',
                    'access_token'  => isset($ditchArray['print_url']) ? $ditchArray['print_url'] : '',
                    'customer_code' => isset($ditchArray['customer_code']) ? $ditchArray['customer_code'] : '',
                    'api_url'       => isset($ditchArray['api_url']) ? $ditchArray['api_url'] : '',
                ];
                
                $this->writeJdDebugLog("渠道配置信息:");
                $this->writeJdDebugLog("- ditch_id: " . $ditchConfig['ditch_id']);
                $this->writeJdDebugLog("- ditch_name: " . $ditchConfig['ditch_name']);
                $this->writeJdDebugLog("- app_key: " . substr($jdConfig['app_key'], 0, 8) . '***');
                $this->writeJdDebugLog("- customer_code: " . $jdConfig['customer_code']);
                $this->writeJdDebugLog("- api_url: " . $jdConfig['api_url']);

                $jd = new \app\common\library\Ditch\Jd($jdConfig);

                // 解析京东打印配置（jd_print_config）
                $jdPrintConfig = [];
                if (!empty($ditchArray['jd_print_config'])) {
                    $decoded = json_decode(html_entity_decode($ditchArray['jd_print_config']), true);
                    if (is_array($decoded)) {
                        $jdPrintConfig = $decoded;
                    }
                }
                
                // 解析京东多包裹打单配置（jd_multibox_config）
                $jdMultiboxConfig = [];
                if (!empty($ditchArray['jd_multibox_config'])) {
                    $decoded = json_decode(html_entity_decode($ditchArray['jd_multibox_config']), true);
                    if (is_array($decoded)) {
                        $jdMultiboxConfig = $decoded;
                    }
                }
                
                // 获取打印配置
                $orderType = isset($jdPrintConfig['orderType']) ? $jdPrintConfig['orderType'] : 'PRINT';
                $tempUrl = isset($jdPrintConfig['tempUrl']) ? $jdPrintConfig['tempUrl'] : '';
                $customTempUrl = isset($jdPrintConfig['customTempUrl']) ? $jdPrintConfig['customTempUrl'] : '';
                $printName = isset($jdPrintConfig['printName']) ? $jdPrintConfig['printName'] : '';
                
                // 获取多包裹打单配置
                $multiboxEnabled = isset($jdMultiboxConfig['enabled']) && $jdMultiboxConfig['enabled'];
                
                $this->writeJdDebugLog("打印配置解析:");
                $this->writeJdDebugLog("- orderType: {$orderType}");
                $this->writeJdDebugLog("- tempUrl: " . ($tempUrl ? $tempUrl : '(未设置)'));
                $this->writeJdDebugLog("- customTempUrl: " . ($customTempUrl ? $customTempUrl : '(未设置)'));
                $this->writeJdDebugLog("- printName: " . ($printName ? $printName : '(未设置)'));
                $this->writeJdDebugLog("- multiboxEnabled: " . ($multiboxEnabled ? 'true' : 'false'));
                
                // 记录打印配置
                \think\Log::info('getPrintTask - 京东打印配置: ' . json_encode([
                    'orderType' => $orderType,
                    'tempUrl' => $tempUrl,
                    'customTempUrl' => $customTempUrl,
                    'printName' => $printName,
                    'has_print_name' => !empty($printName),
                    'multibox_enabled' => $multiboxEnabled
                ], JSON_UNESCAPED_UNICODE));

                // 根据参数判断打印模式
                $waybillsToProcess = [];
                
                if ($printAll) {
                    // 打印全部模式：根据 multibox_enabled 配置决定打印策略
                    if ($multiboxEnabled) {
                        // 多包裹模式：打印所有独立的运单号
                        $this->writeJdDebugLog("打印模式: 打印全部（多包裹独立打单）");
                        \think\Log::info('getPrintTask - 京东打印全部模式（多包裹独立打单）');
                        
                        // 收集所有包裹的运单号（每个包裹都有独立的运单号）
                        if (!empty($data['packageitems']) && is_array($data['packageitems'])) {
                            $this->writeJdDebugLog("发现 " . count($data['packageitems']) . " 个子包裹");
                            foreach ($data['packageitems'] as $pkg) {
                                $childWaybillNo = isset($pkg['t_order_sn']) ? $pkg['t_order_sn'] : '';
                                
                                // 添加所有非空运单号
                                if (!empty($childWaybillNo)) {
                                    $waybillsToProcess[] = $childWaybillNo;
                                    $this->writeJdDebugLog("  - 子包裹运单号: {$childWaybillNo}");
                                }
                            }
                        }
                        
                        // 如果没有子单，使用订单的运单号
                        if (empty($waybillsToProcess) && !empty($data['t_order_sn'])) {
                            $waybillsToProcess[] = $data['t_order_sn'];
                            $this->writeJdDebugLog("无子包裹，使用主运单号: " . $data['t_order_sn']);
                        }
                    } else {
                        // 子母件模式：只打印母单和子单（母单号 + 不同的子单号）
                        $this->writeJdDebugLog("打印模式: 打印全部（子母件方式）");
                        \think\Log::info('getPrintTask - 京东打印全部模式（子母件方式）');
                        
                        // 添加母单号
                        if (!empty($data['t_order_sn'])) {
                            $waybillsToProcess[] = $data['t_order_sn'];
                            $this->writeJdDebugLog("添加母单号: " . $data['t_order_sn']);
                        }
                        
                        // 添加所有与母单号不同的子单号
                        if (!empty($data['packageitems']) && is_array($data['packageitems'])) {
                            $this->writeJdDebugLog("检查 " . count($data['packageitems']) . " 个子包裹");
                            foreach ($data['packageitems'] as $pkg) {
                                $childWaybillNo = isset($pkg['t_order_sn']) ? $pkg['t_order_sn'] : '';
                                
                                // 只添加与母单号不同的子单号
                                if (!empty($childWaybillNo) && $childWaybillNo !== $data['t_order_sn']) {
                                    $waybillsToProcess[] = $childWaybillNo;
                                    $this->writeJdDebugLog("  - 添加子单号: {$childWaybillNo}");
                                }
                            }
                        }
                    }
                } else {
                    // 单个打印模式：只打印指定的运单号
                    $singleWaybill = $waybillNo ?: $data['t_order_sn'];
                    $waybillsToProcess[] = $singleWaybill;
                    $this->writeJdDebugLog("打印模式: 单个打印，运单号: {$singleWaybill}");
                }
                
                $this->writeJdDebugLog("待处理运单号列表 (" . count($waybillsToProcess) . " 个): " . implode(', ', $waybillsToProcess));
                
                // 记录要处理的运单号列表
                \think\Log::info('getPrintTask - 京东运单号列表: ' . json_encode($waybillsToProcess, JSON_UNESCAPED_UNICODE));
                
                // 调用京东云打印接口获取打印数据
                $contents = [];  // 京东云打印组件要求的 contents 数组
                $errors = [];
                
                $this->writeJdDebugLog("开始调用京东云打印接口获取打印数据...");
                
                foreach ($waybillsToProcess as $index => $waybill) {
                    $this->writeJdDebugLog("--- 处理运单 #" . ($index + 1) . ": {$waybill} ---");
                    
                    // 使用缓存获取打印数据（传递地址数据用于检测地址变更）
                    $cacheKey = $waybill . '_' . $id;
                    // 准备地址数据用于缓存检测（转换为数组）
                    $addressData = null;
                    if (isset($data['address']) && $data['address']) {
                        $addressData = is_object($data['address']) ? $data['address']->toArray() : (array)$data['address'];
                    }
                    
                    $cacheHit = false;
                    $result = \app\common\service\JdCache::getPrintData($cacheKey, function() use ($jd, $id, $waybill) {
                        return $jd->jdcloudprint($id, $waybill);
                    }, $addressData, $cacheHit);
                    
                    // 移除内部的地址哈希字段
                    if (is_array($result) && isset($result['__address_hash__'])) {
                        unset($result['__address_hash__']);
                    }
                    
                    // 记录缓存命中状态
                    if ($cacheHit) {
                        $this->writeJdDebugLog("✅ 缓存命中，使用缓存数据");
                    } else {
                        $this->writeJdDebugLog("⚠️ 缓存未命中，使用新获取的数据");
                    }
                    
                    // 处理打印数据（无论是否缓存命中，处理逻辑相同）
                    if ($result['code'] == 1) {
                        // 处理 prePrintDatas 数组
                        if (isset($result['data']['prePrintDatas']) && is_array($result['data']['prePrintDatas'])) {
                            foreach ($result['data']['prePrintDatas'] as $prePrintData) {
                                // 检查单个运单的返回状态
                                if (isset($prePrintData['code']) && $prePrintData['code'] == 1) {
                                    $contentItem = [];
                                    
                                    // 如果启用了标准模板，使用指定的 tempUrl，否则使用配置的 tempUrl
                                    if (!empty($tempUrl)) {
                                        $contentItem['tempUrl'] = $tempUrl;
                                        $this->writeJdDebugLog("使用配置的标准模板: {$tempUrl}");
                                    } else {
                                        $contentItem['tempUrl'] = '';
                                        $this->writeJdDebugLog("未配置标准模板，使用空值");
                                    }
                                    
                                    // 打印数据（加密数据）- 从 perPrintData 字段获取
                                    $contentItem['printData'] = isset($prePrintData['perPrintData']) ? $prePrintData['perPrintData'] : '';
                                    $this->writeJdDebugLog("printData 长度: " . strlen($contentItem['printData']) . " 字符");
                                    
                                    // 如果启用了自定义模板URL，添加到 contentItem
                                    if (!empty($customTempUrl)) {
                                        $contentItem['customTempUrl'] = $customTempUrl;
                                        $this->writeJdDebugLog("添加自定义模板: {$customTempUrl}");
                                    }
                                    
                                    $this->writeJdDebugLog("准备添加 contentItem 到 contents 数组");
                                    $contents[] = $contentItem;
                                    $this->writeJdDebugLog("✅ contentItem 已添加，当前 contents 数量: " . count($contents));
                                    \think\Log::info('getPrintTask - 京东运单号 ' . $waybill . ' 打印数据获取成功');
                                } else {
                                    // 单个运单失败
                                    $errorMsg = isset($prePrintData['msg']) ? $prePrintData['msg'] : '未知错误';
                                    $errors[] = [
                                        'waybillNo' => $waybill,
                                        'error' => $errorMsg
                                    ];
                                    $this->writeJdDebugLog("❌ 运单 {$waybill} 打印数据获取失败: " . $errorMsg);
                                    \think\Log::error('getPrintTask - 京东运单号 ' . $waybill . ' 打印数据获取失败: ' . $errorMsg);
                                }
                            }
                        } else {
                            // 没有 prePrintDatas 数据
                            $errors[] = [
                                'waybillNo' => $waybill,
                                'error' => '返回数据中缺少 prePrintDatas'
                            ];
                            $this->writeJdDebugLog("❌ 运单 {$waybill} 打印数据获取失败: 返回数据中缺少 prePrintDatas");
                            \think\Log::error('getPrintTask - 京东运单号 ' . $waybill . ' 打印数据获取失败: 返回数据中缺少 prePrintDatas');
                        }
                    } else {
                        $errors[] = [
                            'waybillNo' => $waybill,
                            'error' => $result['msg']
                        ];
                        $this->writeJdDebugLog("❌ 运单 {$waybill} 打印数据获取失败: " . $result['msg']);
                        \think\Log::error('getPrintTask - 京东运单号 ' . $waybill . ' 打印数据获取失败: ' . $result['msg']);
                    }
                }
                
                $this->writeJdDebugLog("打印数据获取完成: 成功 " . count($contents) . " 个，失败 " . count($errors) . " 个");
                
                // 记录调用结果
                \think\Log::info('getPrintTask - 京东云打印调用结果: ' . json_encode([
                    'success_count' => count($contents),
                    'error_count' => count($errors),
                    'errors' => $errors
                ], JSON_UNESCAPED_UNICODE));
                
                if (empty($contents)) {
                    $errorMsg = '获取京东云打印数据失败';
                    if (!empty($errors)) {
                        $errorMsg .= ': ' . $errors[0]['error'];
                    }
                    $this->writeJdDebugLog("❌ 最终失败: {$errorMsg}");
                    $this->writeJdDebugLog("========================================\n");
                    return $this->renderError($errorMsg);
                }
                
                // 构造完整的京东云打印请求数据
                $jdPrintRequest = [
                    'orderType' => $orderType,
                    'version' => '2',
                    'parameters' => [
                        'printName' => $printName,
                        'contents' => $contents  // 多个面单的数组
                    ]
                ];
                
                $this->writeJdDebugLog("构造最终 printRequest:");
                $this->writeJdDebugLog("- orderType: {$orderType}");
                $this->writeJdDebugLog("- version: 2");
                $this->writeJdDebugLog("- printName: " . ($printName ? $printName : '(未设置)'));
                $this->writeJdDebugLog("- contents 数量: " . count($contents));
                
                // 发送报文到本地京东云打印组件
                $sendResult = $this->sendJdPrintMessage($jdPrintRequest, $printName, $ditchArray);
                if ($sendResult['success']) {
                    $this->writeJdDebugLog("✅ 报文已发送到本地打印组件");
                } else {
                    $this->writeJdDebugLog("⚠️ 报文发送失败: " . $sendResult['error']);
                }
                
                $this->writeJdDebugLog("✅ getPrintTask - 京东云打印流程完成");
                $this->writeJdDebugLog("========================================\n");
                
                // 返回京东云打印数据
                // 前端将通过 WebSocket 连接到本地京东云打印组件，发送打印任务
                return $this->renderSuccess('获取成功', null, [
                    'mode' => 'jd_cloud_print',
                    'printRequest' => $jdPrintRequest,  // 完整的打印请求数据
                    'printConfig' => [
                        'orderType' => $orderType,
                        'tempUrl' => $tempUrl,
                        'customTempUrl' => $customTempUrl,
                        'printName' => $printName,
                        'multiboxEnabled' => $multiboxEnabled
                    ],
                    'order_id' => $id,
                    'waybill_no' => $waybillNo ?: $data['t_order_sn'],
                    'print_all' => $printAll ? true : false,
                    'waybill_count' => count($contents),  // 打印的运单数量
                    'errors' => $errors,  // 包含错误信息（如果有）
                    'send_result' => $sendResult  // 发送结果
                ]);

            } elseif ($isSf && $ditchConfig) {
                // 使用顺丰云打印
                \think\Log::info('getPrintTask - 使用顺丰云打印: ' . json_encode([
                    'order_id' => $id,
                    'ditch_id' => $ditchConfig['ditch_id'],
                    'ditch_name' => $ditchConfig['ditch_name'],
                    'app_key' => isset($ditchConfig['app_key']) ? substr($ditchConfig['app_key'], 0, 5) . '***' : 'N/A',
                    'print_all' => $printAll
                ], JSON_UNESCAPED_UNICODE));
                
                // 转换数据库字段名到 Sf 类期望的配置键名
                $ditchArray = is_object($ditchConfig) ? $ditchConfig->toArray() : (array)$ditchConfig;
                $sfConfig = [
                    'key' => isset($ditchArray['app_key']) ? $ditchArray['app_key'] : '',
                    'token' => isset($ditchArray['app_token']) ? $ditchArray['app_token'] : '',
                    'apiurl' => isset($ditchArray['api_url']) ? $ditchArray['api_url'] : '',
                    'customer_code' => isset($ditchArray['customer_code']) ? $ditchArray['customer_code'] : '',
                    'sf_express_type' => isset($ditchArray['sf_express_type']) ? (int)$ditchArray['sf_express_type'] : 1,
                    'push_config_json' => isset($ditchArray['push_config_json']) ? $ditchArray['push_config_json'] : '',
                ];
                
                $sf = new \app\common\library\Ditch\Sf($sfConfig);
                
                // 根据参数判断打印模式
                if ($printAll) {
                    // 打印全部模式：母单 + 所有子单
                    $printMode = 'all';
                    \think\Log::info('getPrintTask - 打印全部模式');
                } else {
                    // 判断打印模式：根据 waybill_no 判断是母单还是子单
                    $printMode = 'mother'; // 默认打印母单
                    
                    // 如果传递了 waybill_no，判断是否为子单
                    if (!empty($waybillNo) && !empty($data['t_order_sn'])) {
                        // 如果 waybill_no 与母单号不同，说明是打印子单
                        if ($waybillNo !== $data['t_order_sn']) {
                            $printMode = 'child';
                            \think\Log::info('getPrintTask - 检测到子单打印: ' . json_encode([
                                'waybill_no' => $waybillNo,
                                'mother_waybill' => $data['t_order_sn']
                            ], JSON_UNESCAPED_UNICODE));
                        }
                    }
                }
                
                // 调用顺丰云打印接口获取面单
                // 注意：printlabelParsedData 方法内部会自动获取订单数据并处理 buyer_remark、seller_remark、sub_order_count
                $result = $sf->printlabelParsedData($id, [
                    'print_mode' => $printMode,  // 根据参数判断打印模式
                    'waybill_no' => $waybillNo  // 传递运单号，用于判断是母单还是子单（仅在非打印全部模式下使用）
                ]);
                
                // 记录调用结果
                \think\Log::info('getPrintTask - 顺丰云打印调用结果: ' . json_encode([
                    'success' => $result !== false,
                    'result_type' => is_string($result) ? 'string' : (is_array($result) ? 'array' : gettype($result)),
                    'has_contents' => is_array($result) && isset($result['contents']),
                    'error' => $result === false ? $sf->getError() : null
                ], JSON_UNESCAPED_UNICODE));
                
                if ($result === false) {
                    return $this->renderError('获取顺丰云打印数据失败: ' . $sf->getError());
                }
                
                // 判断返回类型
                if (is_string($result)) {
                    // 返回的是URL字符串(PDF或图片)
                    return $this->renderSuccess('获取成功', null, [
                        'mode' => 'pdf_url',
                        'url' => $result,
                        'order_id' => $id,
                        'waybill_no' => $waybillNo ?: $data['t_order_sn']
                    ]);
                } elseif (is_array($result) && (isset($result['requestID']) || isset($result['files']))) {
                    // 返回的是顺丰插件 SDK 数据结构
                    // 包含: requestID, accessToken, templateCode, documents, version, files
                    // 需要添加 partnerID 供前端 SCPPrint 实例化使用
                    
                    // 判断环境
                    $isSandbox = isset($ditchArray['api_url']) && strpos($ditchArray['api_url'], 'sbox') !== false;
                    
                    // 读取打印选项配置
                    $printOptions = [];
                    if (!empty($ditchArray['sf_print_options'])) {
                        $printOptions = json_decode($ditchArray['sf_print_options'], true);
                        if (!is_array($printOptions)) {
                            $printOptions = [];
                        }
                    }
                    
                    // 记录返回的数据结构
                    \think\Log::info('getPrintTask - 顺丰 SDK 数据结构: ' . json_encode([
                        'has_requestID' => isset($result['requestID']),
                        'has_files' => isset($result['files']),
                        'has_documents' => isset($result['documents']),
                        'files_count' => isset($result['files']) ? count($result['files']) : 0,
                        'env' => $isSandbox ? 'sbox' : 'pro',
                        'print_options' => $printOptions
                    ], JSON_UNESCAPED_UNICODE));
                    
                    return $this->renderSuccess('获取成功', null, [
                        'mode' => 'sf_plugin',
                        'data' => $result,
                        'partnerID' => isset($ditchConfig['app_key']) ? $ditchConfig['app_key'] : '',
                        'env' => $isSandbox ? 'sbox' : 'pro', // 传递环境标识
                        'printOptions' => $printOptions, // 传递打印选项配置
                        'order_id' => $id,
                        'waybill_no' => $waybillNo ?: $data['t_order_sn'],
                        'print_all' => $printAll ? true : false  // 标识是否为打印全部模式
                    ]);
                } else {
                    // 记录未识别的格式以便调试
                    \think\Log::error('getPrintTask - 未识别的返回格式: ' . json_encode([
                        'type' => gettype($result),
                        'is_array' => is_array($result),
                        'keys' => is_array($result) ? array_keys($result) : 'N/A'
                    ], JSON_UNESCAPED_UNICODE));
                    
                    return $this->renderError('未识别的顺丰云打印返回格式');
                }
            } else {
                // 记录使用原有方法的原因
                \think\Log::info('getPrintTask - 使用原有expressLabel方法: ' . json_encode([
                    'reason' => (!$isSf && !$isZto) ? '非顺丰/中通渠道' : '无渠道配置',
                    'is_sf' => $isSf,
                    'is_zto' => $isZto,
                    'has_config' => !empty($ditchConfig)
                ], JSON_UNESCAPED_UNICODE));
                
                // 其他渠道使用原有的expressLabel方法
                $printUrl = url('store/trOrder/expressLabel', [
                    'id' => $id,
                    'label' => $label
                ], true, true);
                
                // 确保URL格式正确(添加斜杠)
                if (strpos($printUrl, 'index.php?') !== false) {
                    $printUrl = str_replace('index.php?', '/index.php?', $printUrl);
                }
                
                return $this->renderSuccess('获取成功', null, [
                    'mode' => 'pdf_url',
                    'url' => $printUrl,
                    'order_id' => $id,
                    'waybill_no' => $waybillNo ?: $data['t_order_sn']
                ]);
            }
            
        } catch (\Exception $e) {
            return $this->renderError('获取打印数据失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取顺丰 OAuth AccessToken
     * 供前端 SDK 使用（如果需要）
     */
    public function getSfAccessToken()
    {
        try {
            $ditchId = $this->request->param('ditch_id');
            
            if (empty($ditchId)) {
                return $this->renderError('渠道ID不能为空');
            }
            
            // 获取渠道配置
            $ditchModel = new \app\common\model\Ditch();
            $ditchConfig = $ditchModel->where('ditch_id', $ditchId)->find();
            
            if (!$ditchConfig) {
                return $this->renderError('渠道配置不存在');
            }
            
            // 判断是否为沙箱环境
            $isSandbox = isset($ditchConfig['api_url']) && strpos($ditchConfig['api_url'], 'sbox') !== false;
            
            // 获取 OAuth token
            $partnerId = isset($ditchConfig['app_key']) ? $ditchConfig['app_key'] : '';
            $secret = isset($ditchConfig['app_token']) ? $ditchConfig['app_token'] : '';
            
            if (empty($partnerId) || empty($secret)) {
                return $this->renderError('渠道配置缺少必要参数');
            }
            
            $accessToken = \app\common\library\Sf\OAuth::getAccessToken($partnerId, $secret, $isSandbox);
            
            if ($accessToken === false) {
                return $this->renderError('获取 AccessToken 失败');
            }
            
            return $this->renderSuccess('获取成功', null, [
                'accessToken' => $accessToken,
                'expiresIn' => 7200 // 2小时
            ]);
            
        } catch (\Exception $e) {
            return $this->renderError('获取 AccessToken 失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 更新包裹打印状态
     * 用于订单列表打印增强功能 - 更新package表的打印状态
     */
    public function updatePackagePrintStatus()
    {
        try {
            $orderId = $this->request->param('order_id');
            $waybillNo = $this->request->param('waybill_no');
            
            if (empty($orderId) || empty($waybillNo)) {
                return $this->renderError('参数错误');
            }
            
            // 开始事务
            Db::startTrans();
            
            try {
                // 更新包裹打印状态
                // 注意: package 表中运单号字段是 express_num，不是 t_order_sn
                $result = Db::name('package')
                    ->where('inpack_id', $orderId)
                    ->where('express_num', $waybillNo)
                    ->update([
                        'print_status' => 1,
                        'print_time' => time(),
                        'print_count' => Db::raw('print_count + 1')
                    ]);
                
                Db::commit();
                
                if ($result) {
                    return $this->renderSuccess('状态更新成功');
                } else {
                    return $this->renderError('状态更新失败: 未找到匹配的包裹');
                }
                
            } catch (\Exception $e) {
                Db::rollback();
                return $this->renderError('数据库错误: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            return $this->renderError('系统错误: ' . $e->getMessage());
        }
    }

    /**
     * 清除京东云打印缓存
     * 清除 AccessToken、打印数据、打印机列表缓存
     */
    public function clearJdCache()
    {
        try {
            // 导入缓存服务
            $jdCacheClass = 'app\common\service\JdCache';
            
            if (!class_exists($jdCacheClass)) {
                return $this->renderError('京东缓存服务不可用');
            }
            
            // 清除缓存
            $cleared = 0;
            
            // 1. 清除 AccessToken 缓存
            try {
                $jdCacheClass::clear('token', 'jd_app_key');
                $cleared++;
            } catch (\Exception $e) {
                // 继续执行，不中断
            }
            
            // 2. 清除打印数据缓存
            try {
                $jdCacheClass::clear('print', 'all_waybills');
                $cleared++;
            } catch (\Exception $e) {
                // 继续执行
            }
            
            // 3. 清除打印机列表缓存
            try {
                $jdCacheClass::clear('printer', 'jd_app_key');
                $cleared++;
            } catch (\Exception $e) {
                // 继续执行
            }
            
            // 记录清除缓存操作
            $this->writeJdDebugLog("清除京东云打印缓存: 已清除 {$cleared} 项缓存");
            
            return $this->renderSuccess('京东云打印缓存已清除（' . $cleared . '项）');
            
        } catch (\Exception $e) {
            $this->writeJdDebugLog("清除缓存异常: " . $e->getMessage());
            return $this->renderError('清除缓存失败: ' . $e->getMessage());
        }
    }

    /**
     * 写入京东调试日志到 logs/jd/ 目录
     * @param string $message
     */
    private function writeJdDebugLog($message)
    {
        $logDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/logs/jd';
        
        // 确保目录存在
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/' . date('Ymd') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [TrOrder::getPrintTask] {$message}\n";
        
        @file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    /**
     * 写入京东订单下单调试日志到 logs/jd/ 目录
     * @param string $message
     */
    private function writeJdOrderLog($message)
    {
        // 日志开关：设置为 false 关闭日志
        $enableLog = false;
        
        if (!$enableLog) {
            return;
        }
        
        $logDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/logs/jd';
        
        // 确保目录存在
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/' . date('Ymd') . '.log';
        $timestamp = date('Y-m-d H:i:s.') . substr(microtime(), 2, 3);
        $logMessage = "[{$timestamp}] [TrOrder::sendtoqudaoshang] {$message}\n";
        
        @file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    /**
     * 发送打印报文到本地京东云打印组件
     * 通过 WebSocket 连接到本地打印服务
     * @param array $printRequest 打印请求数据
     * @param string $printName 打印机名称
     * @return array ['success' => bool, 'error' => string, 'response' => mixed]
     */
    private function sendJdPrintMessage($printRequest, $printName = '', $ditchConfig = [])
    {
        $this->writeJdDebugLog("开始发送报文到本地打印组件...");
        
        // WebSocket 地址解析优先级
        $wsUrl = null;
        
        // 优先级 1: 使用专门配置的打印组件地址
        if (!empty($ditchConfig['jd_print_component_url'])) {
            $wsUrl = $ditchConfig['jd_print_component_url'];
            $this->writeJdDebugLog("✅ 使用配置的打印组件地址: {$wsUrl}");
        }
        // 优先级 2: 检查 print_url 是否是有效的 WebSocket 地址
        elseif (!empty($ditchConfig['print_url'])) {
            $printUrl = $ditchConfig['print_url'];
            // 检查是否是有效的 WebSocket 地址（以 ws:// 或 wss:// 开头）
            if (strpos($printUrl, 'ws://') === 0 || strpos($printUrl, 'wss://') === 0) {
                $wsUrl = $printUrl;
                $this->writeJdDebugLog("✅ 使用 print_url 作为 WebSocket 地址: {$wsUrl}");
            } else {
                $this->writeJdDebugLog("⚠️ print_url 不是有效的 WebSocket 地址: {$printUrl}");
            }
        }
        
        // 优先级 3: 使用默认本地地址
        if (empty($wsUrl)) {
            $wsUrl = 'ws://127.0.0.1:9113';
            $this->writeJdDebugLog("✅ 使用默认本地 WebSocket 地址: {$wsUrl}");
        }
        
        // 构造发送给打印组件的报文
        $message = [
            'orderType' => isset($printRequest['orderType']) ? $printRequest['orderType'] : 'PRINT',
            'version' => '2',
            'parameters' => [
                'printName' => !empty($printName) ? $printName : '',
                'contents' => $printRequest['parameters']['contents']  // 直接使用 contents 数组
            ]
        ];
        
        $this->writeJdDebugLog("报文格式: " . $message['orderType']);
        $this->writeJdDebugLog("打印机: " . ($message['parameters']['printName'] ?: '(默认)'));
        $this->writeJdDebugLog("面单数量: " . count($message['parameters']['contents']));
        
        // 通过 WebSocket 发送到本地打印服务
        $this->writeJdDebugLog("尝试连接到打印服务: {$wsUrl}");
        
        try {
            // 使用 PHP 的 WebSocket 客户端库
            // 如果没有安装 WebSocket 库，使用 socket 直接连接
            $result = $this->sendViaWebSocket($wsUrl, $message);
            
            if ($result['success']) {
                $this->writeJdDebugLog("✅ 报文发送成功");
                return $result;
            } else {
                $this->writeJdDebugLog("⚠️ WebSocket 发送失败: " . $result['error']);
                return $result;
            }
            
        } catch (\Exception $e) {
            $this->writeJdDebugLog("❌ 异常: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response' => null
            ];
        }
    }

    /**
     * 通过 WebSocket 发送报文
     * @param string $wsUrl WebSocket 地址
     * @param array $message 报文数据
     * @return array ['success' => bool, 'error' => string, 'response' => mixed]
     */
    private function sendViaWebSocket($wsUrl, $message)
    {
        $this->writeJdDebugLog("使用 WebSocket 发送报文...");
        
        // 解析 WebSocket URL
        $parsed = parse_url($wsUrl);
        $host = $parsed['host'] ?? '127.0.0.1';
        $port = $parsed['port'] ?? 9113;
        
        $this->writeJdDebugLog("WebSocket 连接参数: host={$host}, port={$port}");
        
        try {
            // 创建 socket 连接
            $socket = @fsockopen($host, $port, $errno, $errstr, 3);
            
            if (!$socket) {
                $this->writeJdDebugLog("❌ Socket 连接失败: [{$errno}] {$errstr}");
                return [
                    'success' => false,
                    'error' => "Socket connection failed: [{$errno}] {$errstr}",
                    'response' => null
                ];
            }
            
            $this->writeJdDebugLog("✅ Socket 连接成功");
            
            // 构造 WebSocket 握手请求
            $key = base64_encode(random_bytes(16));
            $handshake = "GET / HTTP/1.1\r\n";
            $handshake .= "Host: {$host}:{$port}\r\n";
            $handshake .= "Upgrade: websocket\r\n";
            $handshake .= "Connection: Upgrade\r\n";
            $handshake .= "Sec-WebSocket-Key: {$key}\r\n";
            $handshake .= "Sec-WebSocket-Version: 13\r\n";
            $handshake .= "\r\n";
            
            $this->writeJdDebugLog("发送 WebSocket 握手请求...");
            fwrite($socket, $handshake);
            
            // 读取握手响应
            $response = '';
            while (!feof($socket)) {
                $line = fgets($socket, 1024);
                $response .= $line;
                if ($line === "\r\n") {
                    break;
                }
            }
            
            $this->writeJdDebugLog("握手响应: " . substr($response, 0, 100) . "...");
            
            // 验证握手响应
            if (strpos($response, '101') === false) {
                $this->writeJdDebugLog("❌ WebSocket 握手失败");
                fclose($socket);
                return [
                    'success' => false,
                    'error' => 'WebSocket handshake failed',
                    'response' => $response
                ];
            }
            
            $this->writeJdDebugLog("✅ WebSocket 握手成功");
            
            // 发送报文
            $payload = json_encode($message, JSON_UNESCAPED_UNICODE);
            $this->writeJdDebugLog("发送报文: " . $payload);
            
            $frame = $this->createWebSocketFrame($payload);
            fwrite($socket, $frame);
            
            $this->writeJdDebugLog("✅ 报文已发送");
            
            // 读取响应报文
            $responseData = '';
            stream_set_timeout($socket, 3);
            
            // 读取 WebSocket 帧数据
            $frameData = '';
            while (!feof($socket)) {
                $chunk = fread($socket, 1024);
                if ($chunk === false || $chunk === '') {
                    break;
                }
                $frameData .= $chunk;
            }
            
            fclose($socket);
            
            // 解析 WebSocket 帧
            if (!empty($frameData)) {
                $responseData = $this->parseWebSocketFrame($frameData);
                $this->writeJdDebugLog("✅ 收到响应: " . substr($responseData, 0, 200) . "...");
            } else {
                $this->writeJdDebugLog("⚠️ 未收到响应数据");
            }
            
            return [
                'success' => true,
                'error' => '',
                'response' => $responseData
            ];
            
        } catch (\Exception $e) {
            $this->writeJdDebugLog("❌ WebSocket 异常: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response' => null
            ];
        }
    }

    /**
     * 解析 WebSocket 数据帧
     * @param string $frameData WebSocket 帧数据
     * @return string 解析后的报文内容
     */
    private function parseWebSocketFrame($frameData)
    {
        if (empty($frameData)) {
            return '';
        }
        
        // 跳过第一个字节（FIN + opcode）
        $offset = 1;
        
        // 读取长度信息
        $lengthByte = ord($frameData[$offset]);
        $offset++;
        
        $isMasked = ($lengthByte & 0x80) !== 0;
        $length = $lengthByte & 0x7F;
        
        if ($length === 126) {
            $length = (ord($frameData[$offset]) << 8) | ord($frameData[$offset + 1]);
            $offset += 2;
        } elseif ($length === 127) {
            $length = 0;
            for ($i = 0; $i < 8; $i++) {
                $length = ($length << 8) | ord($frameData[$offset + $i]);
            }
            $offset += 8;
        }
        
        // 读取掩码（如果有）
        $mask = '';
        if ($isMasked) {
            $mask = substr($frameData, $offset, 4);
            $offset += 4;
        }
        
        // 提取报文内容
        $payload = substr($frameData, $offset, $length);
        
        // 解掩码
        if ($isMasked) {
            $unmasked = '';
            for ($i = 0; $i < $length; $i++) {
                $unmasked .= chr(ord($payload[$i]) ^ ord($mask[$i % 4]));
            }
            $payload = $unmasked;
        }
        
        return $payload;
    }

    /**
     * 创建 WebSocket 数据帧
     * @param string $payload 报文内容
     * @return string WebSocket 帧数据
     */
    private function createWebSocketFrame($payload)
    {
        $length = strlen($payload);
        
        // 构造帧头
        $frame = chr(0x81); // FIN + opcode (text frame)
        
        if ($length < 126) {
            $frame .= chr(0x80 | $length); // MASK + length
        } elseif ($length < 65536) {
            $frame .= chr(0xFE); // MASK + 126
            $frame .= chr($length >> 8);
            $frame .= chr($length & 0xFF);
        } else {
            $frame .= chr(0xFF); // MASK + 127
            $frame .= chr(0);
            $frame .= chr(0);
            $frame .= chr(0);
            $frame .= chr(0);
            $frame .= chr($length >> 24);
            $frame .= chr($length >> 16);
            $frame .= chr($length >> 8);
            $frame .= chr($length & 0xFF);
        }
        
        // 生成掩码
        $mask = '';
        for ($i = 0; $i < 4; $i++) {
            $mask .= chr(rand(0, 255));
        }
        $frame .= $mask;
        
        // 对报文进行掩码处理
        $masked = '';
        for ($i = 0; $i < $length; $i++) {
            $masked .= chr(ord($payload[$i]) ^ ord($mask[$i % 4]));
        }
        $frame .= $masked;
        
        return $frame;
    }
    
    /**
     * 获取渠道商列表（用于批量推送）
     * 
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    /**
     * 获取可推送的渠道商列表
     * 返回支持批量推送的渠道商类型：自有物流、中通快递、中通管家、顺丰速运、京东物流
     * 
     * @return \think\response\Json
     */
    public function getDitchList()
    {
        try {
            $ditchModel = new \app\store\model\Ditch();
            
            // 查询所有启用的渠道商（不筛选类型，让前端根据运输方式筛选）
            $list = $ditchModel
                ->where('status', '=', 0)
                ->where('wxapp_id', '=', $this->getWxappId())
                ->order('sort', 'asc')
                ->field('ditch_id, ditch_name, ditch_no, ditch_type')
                ->select();
            
            return $this->renderSuccess('获取成功', '', [
                'list' => $list
            ]);
            
        } catch (\Exception $e) {
            return $this->renderError('获取渠道商列表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 批量推送订单到渠道商
     * 
     * @return \think\response\Json
     */
    public function orderbatchpusher()
    {
        try {
            $params = $this->request->param();
            
            // 🔍 调试日志：记录接收到的参数
            \app\common\service\PrintLogger::info('批量推送', '接收参数', $params);
            
            // 验证参数
            if (empty($params['order_ids'])) {
                \app\common\service\PrintLogger::error('批量推送', '参数错误：订单ID为空');
                return $this->renderError('请选择要推送的订单');
            }
            
            if (empty($params['ditch_id'])) {
                \app\common\service\PrintLogger::error('批量推送', '参数错误：渠道商ID为空');
                return $this->renderError('请选择渠道商');
            }
            
            // 转换订单ID为数组
            $orderIds = is_array($params['order_ids']) 
                ? $params['order_ids'] 
                : explode(',', $params['order_ids']);
            
            $ditchId = (int)$params['ditch_id'];
            
            // 🔧 修复：正确处理 async 参数（字符串 "false" 应该转为 false）
            $async = false;
            if (isset($params['async'])) {
                if (is_bool($params['async'])) {
                    $async = $params['async'];
                } elseif (is_string($params['async'])) {
                    $async = ($params['async'] === 'true' || $params['async'] === '1');
                } else {
                    $async = (bool)$params['async'];
                }
            }
            
            // 🔍 调试日志：记录处理后的参数
            \app\common\service\PrintLogger::info('批量推送', '处理后参数', [
                'order_ids' => $orderIds,
                'ditch_id' => $ditchId,
                'async' => $async
            ]);
            
            // 额外参数
            $extraParams = [
                'async' => $async
            ];
            
            if (isset($params['priority'])) {
                $extraParams['priority'] = (int)$params['priority'];
            }
            
            if (isset($params['product_id'])) {
                $extraParams['product_id'] = $params['product_id'];
            }
            
            // 运输方式参数
            if (isset($params['transfer'])) {
                $extraParams['transfer'] = $params['transfer'];
            }
            
            // 🔍 调试日志：记录额外参数
            \app\common\service\PrintLogger::info('批量推送', '额外参数', $extraParams);
            
            // 调用批量推送服务
            \app\common\service\PrintLogger::info('批量推送', '开始调用 OrderBatchPusher::push');
            $result = \app\common\service\OrderBatchPusher::push(
                $orderIds,
                $ditchId,
                $extraParams
            );
            
            // 🔍 调试日志：记录推送结果
            \app\common\service\PrintLogger::info('批量推送', '推送结果', $result);
            
            if ($result['error_count'] > 0 && $result['success_count'] == 0) {
                // 全部失败
                \app\common\service\PrintLogger::error('批量推送', '全部失败', $result);
                return $this->renderError('批量推送失败', [
                    'data' => $result
                ]);
            } else {
                // 全部成功或部分成功
                \app\common\service\PrintLogger::success('批量推送', '推送完成', $result);
                return $this->renderSuccess($result, '批量推送完成');
            }
            
        } catch (\Exception $e) {
            // 🔍 调试日志：记录异常
            \app\common\service\PrintLogger::error('批量推送', '异常', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->renderError('批量推送异常: ' . $e->getMessage());
        }
    }
    
    /**
     * 异步任务队列操作
     * 
     * @return \think\response\Json
     */
    public function asynctaskqueue()
    {
        try {
            $action = $this->request->param('action');
            
            switch ($action) {
                case 'getTaskStatus':
                    $taskId = (int)$this->request->param('task_id');
                    if (empty($taskId)) {
                        return $this->renderError('任务ID不能为空');
                    }
                    
                    $status = \app\common\service\AsyncTaskQueue::getTaskStatus($taskId);
                    if ($status) {
                        return $this->renderSuccess($status);
                    } else {
                        return $this->renderError('任务不存在');
                    }
                    break;
                    
                default:
                    return $this->renderError('不支持的操作');
            }
            
        } catch (\Exception $e) {
            return $this->renderError('操作失败: ' . $e->getMessage());
        }
    }
}
