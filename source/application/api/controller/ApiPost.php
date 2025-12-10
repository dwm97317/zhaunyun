<?php
namespace app\api\controller;
use app\api\model\Banner;
use app\api\model\store\Shop;
use app\api\model\Article as ArticleModel;
use app\api\model\dealer\Setting;
use app\api\model\Line;
use app\api\model\Bank;
use app\common\model\Setting as CommonSetting;
use app\store\model\user\UserLine;
use app\common\model\Wxapp as WxappModel;
use app\api\service\trackApi\TrackApi;
use app\api\model\BannerLog;
use app\common\library\wechat\WxPay;
use app\common\library\payment\HantePay\hantePay;
use think\Hook;
use app\common\model\UploadFile;
use  app\api\model\PackageService;
use app\api\model\article\Category as CategoryModel;
use app\api\model\Country;
use app\api\model\Wxapp;
use app\api\model\Package;
use app\api\model\PackageImage;
use app\store\controller\Upload;
use app\common\library\storage\Driver as StorageDriver;
use app\store\model\Setting as SettingModel;
use think\Request;
use app\common\library\AITool\BaiduOcr;
use app\api\model\User as UserModel;
use app\common\model\AiLog;
use app\common\service\Message;
use app\common\model\LogisticsTrack;
use app\common\model\Logistics;
use app\common\service\package\Printer;
use app\api\model\Shelf;
use app\api\model\ShelfUnit;
use app\api\model\ShelfUnitItem;
use app\common\model\PackageImage as CommonPackageImage;

/**
 * 页面控制器
 * Class Index
 * @package app\api\controller
 */
class ApiPost extends Controller
{
    private $config;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        // 存储配置信息
        $this->config = SettingModel::getItem('storage',$this->wxapp_id);
    }
    
    /**
     * 17track的Hook
     * Class Passport
     * @package app\api\controller
     */
    public function Webhook17Track(){
        $param = $this->request->param();
        return LogisticsTrack::addhookLog($param);
    }
    
    /**
     * 预报
     * Class Passport
     * @package app\api\controller
     */
    public function newreportpack(){
        $param = $this->request->param();
    
        if(!isset($param['app_key']) || empty($param['app_key'])){
            return $this->renderError("TOKEN不能为空");
        }
        if(!isset($param['tracking_number']) || empty($param['tracking_number'])){
            return $this->renderError("快递单号不能为空");
        }
        if(!isset($param['equipment_no']) || empty($param['equipment_no'])){
            return $this->renderError("仓库号不能为空");
        }
        $Wxapp = new Wxapp;
        $Package = new Package;
        $detail = $Wxapp::detail($param['wxapp_id']);
        if($detail['token'] != $param['app_key']){
            return $this->renderError("TOKEN错误");
        }
       
        
        $result = $Package->where(['express_num'=>$param['tracking_number'],'is_delete'=>0])->find();
        // dump($_FILES);die;
        if(!empty($_FILES['file']['name'])){
            $StorageDriver = new StorageDriver($this->config);
            // dump($this->config);die;
            $file = Request::instance()->file();
            // 设置上传文件的信息
            $StorageDriver->setUploadFile('file');
            // 上传图片
            if (!$StorageDriver->upload()) {
                return json(['code' => 0, 'msg' => '图片上传失败' . $StorageDriver->getError()]);
            }
    
            // 图片上传路径
            $fileName = $StorageDriver->getFileName();
            // 图片信息
            $fileInfo = $StorageDriver->getFileInfo();
            // 添加文件库记录
            $uploadFile = $this->addUploadFile($group_id = -1, $fileName, $fileInfo, 'image');
            // dump();die;$uploadFile['file_id']
        }
         
        
        if(!empty($result)){
            $result->save([
                'status'=>2,
                'storage_id'=>$param['equipment_no'],
                'weight'=>$param['package_weight'],
                'length'=>isset($param['length'])?$param['length']:$result['length'],
                'width'=>isset($param['width'])?$param['width']:$result['width'],
                'height'=>isset($param['height'])?$param['height']:$result['height'],
                'entering_warehouse_time'=>getTime()
            ]);
            
            if(isset($uploadFile)){
                $imgdata = [
                    'package_id'=> $result['id'],
                    'image_id'=>$uploadFile['file_id'],
                    'wxapp_id'=>$param['wxapp_id'],
                    'create_time'=>time()
                ];
                (new PackageImage())->save($imgdata);
            }
            $tplmsgsetting = SettingModel::getItem('tplMsg',$param['wxapp_id']);
            if($tplmsgsetting['is_oldtps']==1){
              //发送旧版本订阅消息以及模板消息
              $sub = (new Package())->sendEnterMessage([$result]);
            }else{
              //发送新版本订阅消息以及模板消息
              Message::send('package.inwarehouse',$result);
            }
            return $this->renderSuccess("更新成功");
        }
        $data = [
            'order_sn'=> createSn(),
            'express_num'=>$param['tracking_number'],
            'status'=>2,
            'storage_id'=>$param['equipment_no'],
            'wxapp_id'=>$param['wxapp_id'],
            'weight'=>$param['package_weight'],
            'source'=>9,
            'entering_warehouse_time'=>getTime()
        ];
        $id = $Package->saveData($data);
        if(isset($uploadFile)){
            $imgdata = [
                'package_id'=> $id,
                'image_id'=>$uploadFile['file_id'],
                'wxapp_id'=>$param['wxapp_id'],
                'create_time'=>time()
            ];
            (new PackageImage())->save($imgdata);
        }
        return $this->renderSuccess("预报成功");
    }
    
    /**
     * 预报
     * Class Passport
     * @package app\api\controller
     */
    public function reportpack(){
        $param = $this->request->param();
    
        if(!isset($param['token']) || empty($param['token'])){
            return $this->renderError("TOKEN不能为空");
        }
        if(!isset($param['expressnum']) || empty($param['expressnum'])){
            return $this->renderError("快递单号不能为空");
        }
        if(!isset($param['shop_id']) || empty($param['shop_id'])){
            return $this->renderError("仓库号不能为空");
        }
        $Wxapp = new Wxapp;
        $Package = new Package;
        $detail = $Wxapp::detail($param['wxapp_id']);
        if($detail['token'] != $param['token']){
            return $this->renderError("TOKEN错误");
        }
       
        
        $result = $Package->where(['express_num'=>$param['expressnum'],'is_delete'=>0])->find();
        if(!empty($_FILES['file']['name'])){
            $StorageDriver = new StorageDriver($this->config);
            // dump($this->config);die;
            $file = Request::instance()->file();
            // 设置上传文件的信息
            $StorageDriver->setUploadFile('file');
            // 上传图片
            if (!$StorageDriver->upload()) {
                return json(['code' => 0, 'msg' => '图片上传失败' . $StorageDriver->getError()]);
            }
    
            // 图片上传路径
            $fileName = $StorageDriver->getFileName();
            // 图片信息
            $fileInfo = $StorageDriver->getFileInfo();
            // 添加文件库记录
            $uploadFile = $this->addUploadFile($group_id = -1, $fileName, $fileInfo, 'image');
            // dump();die;$uploadFile['file_id']
        }
         
        $storesetting = SettingModel::getItem('store',$param['wxapp_id']);
        if(!empty($result)){
            $result->save([
                'status'=>2,
                'storage_id'=>$param['shop_id'],
                'weight'=>$param['weight'],
                'length'=>isset($param['length'])?$param['length']:$result['length'],
                'width'=>isset($param['width'])?$param['width']:$result['width'],
                'height'=>isset($param['height'])?$param['height']:$result['height'],
                'entering_warehouse_time'=>getTime()
            ]);
            
            if(isset($uploadFile)){
                $imgdata = [
                    'package_id'=> $result['id'],
                    'image_id'=>$uploadFile['file_id'],
                    'wxapp_id'=>$param['wxapp_id'],
                    'create_time'=>time()
                ];
                (new PackageImage())->save($imgdata);
            }
            $tplmsgsetting = SettingModel::getItem('tplMsg',$param['wxapp_id']);
            
            if($tplmsgsetting['is_oldtps']==1){
              //发送旧版本订阅消息以及模板消息
              $sub = (new Package())->sendEnterMessage([$result]);
            }else{
              //发送新版本订阅消息以及模板消息
              Message::send('package.inwarehouse',$result);
            }
            //分配货位
            
            if(isset($storesetting['is_auto_shelf']) && $storesetting['is_auto_shelf']==1){
                $this->fenpeihuowei($result['member_id'],$result['id'],$param['expressnum'],$result['wxapp_id']);
            }
            return $this->renderSuccess("更新成功");
        }
        $data = [
            'order_sn'=> createSn(),
            'express_num'=>$param['expressnum'],
            'status'=>2,
            'storage_id'=>$param['shop_id'],
            'wxapp_id'=>$param['wxapp_id'],
            'weight'=>$param['weight'],
            'source'=>9,
            'entering_warehouse_time'=>getTime()
        ];
        $id = $Package->saveData($data);
        if(isset($uploadFile)){
            $imgdata = [
                'package_id'=> $id,
                'image_id'=>$uploadFile['file_id'],
                'wxapp_id'=>$param['wxapp_id'],
                'create_time'=>time()
            ];
            (new PackageImage())->save($imgdata);
        }
        if(isset($storesetting['is_auto_shelf']) && $storesetting['is_auto_shelf']==1){
            $this->fenpeihuowei(0,$id,$param['expressnum'],$param['wxapp_id']);
        }
      
        return $this->renderSuccess("预报成功");
    }
    
    
    
    
     //分配货位
    public function fenpeihuowei($member_id,$pack_id,$express_num,$wxapp_id){
        $keepersetting = SettingModel::getItem('keeper',$wxapp_id);

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
     * 电子秤二号
     * Class Passport
     * @package app\api\controller
     */
    public function reportpacktwo(){
        $param = $this->request->param();
        $wxapp_id = $param['wxapp_id'];
        $param = $param[0];
        if(!isset($param['ticketsNum']) || empty($param['ticketsNum'])){
            return $this->renderError("快递单号不能为空");
        }
        if(!isset($param['weight']) || empty($param['weight'])){
            return $this->renderError("包裹重量不能为空");
        }
        $Package = new Package;
        $result = $Package->where(['express_num'=>$param['ticketsNum'],'is_delete'=>0])->find();
        if(!empty($result)){
            $result->save([
                'status'=>2,
                // 'storage_id'=>$param['shop_id'],
                'weight'=>$param['weight'],
                'length'=>$param['length'],
                'height'=>$param['height'],
                'width'=>$param['width'],
                'volume'=>$param['volume'],
                'entering_warehouse_time'=>getTime()
            ]);
            $tplmsgsetting = SettingModel::getItem('tplMsg',$param['wxapp_id']);
            if($tplmsgsetting['is_oldtps']==1){
              //发送旧版本订阅消息以及模板消息
              $sub = (new Package())->sendEnterMessage([$result]);
            }else{
              //发送新版本订阅消息以及模板消息
              Message::send('package.inwarehouse',$result);
            }
            return ['result'=>"true",'message'=>"入库成功"];
        }
        $data = [
            'order_sn'=> createSn(),
            'express_num'=>$param['ticketsNum'],
            'status'=>2,
            // 'storage_id'=>$param['shop_id'],
            'wxapp_id'=>$wxapp_id,
            'weight'=>$param['weight'],
            'length'=>$param['length'],
            'height'=>$param['height'],
            'width'=>$param['width'],
            'source'=>9,
            'entering_warehouse_time'=>getTime()
        ];
        $id = $Package->saveData($data);
        return ['result'=>"true",'message'=>"入库成功"];
    }

    /**
     * 图片上传接口
     * @return array
     * @throws \think\Exception
     */
    public function image()
    {
        // 实例化存储驱动
        
        $param = $this->request->param();
        // $data =  explode ( ',' ,  $param['file'] );  //截取data:image/png;base64, 这个逗号后的字符
        $data =  base64_decode($param['file']);
        // dump($data);die;
        $path = 'uploads/'.time().rand(10000,99999).'.jpg';
        file_put_contents($path,$data);
        // 设置上传文件的信息
        $this->config = SettingModel::getItem('storage',$param['wxapp_id']);
        $StorageDriver = new StorageDriver($this->config);
        //   dump($path);die;
        $StorageDriver->setUploadFileByReal($path);
        // dump(34);die;
        // 上传图片
        if (!$StorageDriver->put()) {
            return json(['code' => 0, 'msg' => '图片上传失败' . $StorageDriver->getError()]);
        }
        // dump(34);die;
        // 设置上传文件的信息
        // 图片上传路径
        $fileName = $StorageDriver->getFileName();
        // 图片信息
        $fileInfo = $StorageDriver->getFileInfo();
        // 添加文件库记录
        $uploadFile = $this->addUploadFiles($fileName, $fileInfo, 'image');
        $Package = new Package;
        $result = $Package->where(['express_num'=>$param['ticketsNum'],'is_delete'=>0])->find();
        if(isset($uploadFile) && !empty($result)){
            $imgdata = [
                'package_id'=> $result['id'],
                'image_id'=>$uploadFile['file_id'],
                'wxapp_id'=>$param['wxapp_id'],
                'create_time'=>time()
            ];
            (new PackageImage())->save($imgdata);
        }
        // 图片上传成功
        return json(['isOK' => 1]);
    }

    /**
     * 添加文件库上传记录
     * @param $fileName
     * @param $fileInfo
     * @param $fileType
     * @return UploadFile
     */
    private function addUploadFiles($fileName, $fileInfo, $fileType)
    {
        // 存储引擎
        $storage = $this->config['default'];

        // 存储域名
        $fileUrl = isset($this->config['engine'][$storage]['domain'])
            ? $this->config['engine'][$storage]['domain'] : '';
        // 添加文件库记录
                
        $model = new UploadFile;
        $model->addImage([
            'storage' => $storage,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
            'file_size' => $fileInfo['size'],
            'file_type' => $fileType,
            'extension' => pathinfo($fileInfo['name'], PATHINFO_EXTENSION),
            'is_user' => 0,
            'wxapp_id'=>$this->wxapp_id
        ]);
      
        return $model;
    }
/**
     * 添加文件库上传记录
     * @param $group_id
     * @param $fileName
     * @param $fileInfo
     * @param $fileType
     * @return UploadFile
     */
    private function addUploadFile($group_id, $fileName, $fileInfo, $fileType)
    {
        // 存储引擎
        $storage = $this->config['default'];
        // 存储域名
        $fileUrl = isset($this->config['engine'][$storage]['domain'])
            ? $this->config['engine'][$storage]['domain'] : '';
        // 添加文件库记录
        $model = new UploadFile;
        $model->add([
            'group_id' => $group_id > 0 ? (int)$group_id : 0,
            'storage' => $storage,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
            'file_size' => $fileInfo['size'],
            'file_type' => $fileType,
            'extension' => pathinfo($fileInfo['name'], PATHINFO_EXTENSION),
        ]);
        return $model;
    }
    
        /**
     * 预报
     * Class Passport
     * @package app\api\controller
     */
    public function reportpackToBaidu(){
        $param = $this->request->param();
        $setting = SettingModel::getItem('aiidentify',$this->wxapp_id);
        if($setting['is_enable']==0){
            return $this->renderError("尚未开启智能AI识别功能，请更改API");
        }
        $BaiduOcr = new BaiduOcr($setting);
        if(!isset($param['token']) || empty($param['token'])){
            return $this->renderError("TOKEN不能为空");
        }
        if(!isset($param['expressnum']) || empty($param['expressnum'])){
            return $this->renderError("快递单号不能为空");
        }
        if(!isset($param['shop_id']) || empty($param['shop_id'])){
            return $this->renderError("仓库号不能为空");
        }
        $Wxapp = new Wxapp;
        $Package = new Package;
        $UserModel = new UserModel;
        $AiLog = new AiLog;
        $detail = $Wxapp::detail($param['wxapp_id']);
        if($detail['token'] != $param['token']){
            return $this->renderError("TOKEN错误");
        }
       
        $userinfo = [];
        $result = $Package->where(['express_num'=>$param['expressnum'],'is_delete'=>0])->find();
        if(!empty($_FILES['file']['name'])){
            $StorageDriver = new StorageDriver($this->config);
            $file = Request::instance()->file();
            //调用百度文字识别接口
            // 设置上传文件的信息
            $StorageDriver->setUploadFile('file');
            // 上传图片
            if (!$StorageDriver->upload()) {
                return json(['code' => 0, 'msg' => '图片上传失败' . $StorageDriver->getError()]);
            }
    
            // 图片上传路径
            $fileName = $StorageDriver->getFileName();
            // 图片信息
            $fileInfo = $StorageDriver->getFileInfo();
            // 添加文件库记录
            $uploadFile = $this->addUploadFile($group_id = -1, $fileName, $fileInfo, 'image');
            //使用百度AI识别结果
            if(!empty($result) && $result['is_take']==1){
                $ailogresult = $BaiduOcr->generalBasic($uploadFile->file_url.'/'.$uploadFile->file_name);
                $AiLog->add([
                    'user_id'=>$ailogresult['user_id'],
                    'content'=>$ailogresult['words'],
                    'wxapp_id'=>$this->wxapp_id,
                ]);
                $detail->setDec('baiduai',1); 
            }
            
            $storesetting = SettingModel::getItem('store',$this->wxapp_id);
            
            //  dump($user_id);die;
            if(!empty($ailogresult['user_id'])){
                if($storesetting['usercode_mode']['is_show']==1){
                    $userinfo = $UserModel::detail(['user_code'=>$ailogresult['user_id']]);
                }else{
                    $userinfo = $UserModel::detail($ailogresult['user_id']);
                }
            }  
            //查询是否存在该用户id，不存在则跳过；存在就将包裹跟用户id绑定；
        }
           
        //当包裹已经预报时
        if(!empty($result)){
            $result->save([
                'status'=>2,
                'member_id'=>!empty($userinfo)?$userinfo['user_id']:$result['member_id'],
                'storage_id'=>$param['shop_id'],
                'weight'=>$param['weight'],
                'is_take'=>!empty($userinfo)?2:$result['is_take'],
                'entering_warehouse_time'=>getTime()
            ]);
            
            if(isset($uploadFile)){
                $imgdata = [
                    'package_id'=> $result['id'],
                    'image_id'=>$uploadFile['file_id'],
                    'wxapp_id'=>$param['wxapp_id'],
                    'create_time'=>time()
                ];
                (new PackageImage())->save($imgdata);
            }
            $tplmsgsetting = SettingModel::getItem('tplMsg',$param['wxapp_id']);
            if($tplmsgsetting['is_oldtps']==1){
              //发送旧版本订阅消息以及模板消息
              $sub = (new Package())->sendEnterMessage([$result]);
            }else{
              //发送新版本订阅消息以及模板消息
              Message::send('package.inwarehouse',$result);
            }
            return $this->renderSuccess("更新成功");
        }
         //当包裹没有预报时
        $data = [
            'order_sn'=> createSn(),
            'express_num'=>$param['expressnum'],
            'status'=>2,
            'storage_id'=>$param['shop_id'],
            'wxapp_id'=>$param['wxapp_id'],
            'weight'=>$param['weight'],
            'source'=>9,
            'member_id'=>!empty($userinfo)?$userinfo['user_id']:'',
            'is_take'=>!empty($userinfo)?2:1,
            'entering_warehouse_time'=>getTime()
        ];
        $id = $Package->saveData($data);
        // dump($id.'-'.$uploadFile);die;
        if(isset($uploadFile)){
            $imgdata = [
                'package_id'=> $id,
                'image_id'=>$uploadFile['file_id'],
                'wxapp_id'=>$param['wxapp_id'],
                'create_time'=>time()
            ];
            (new PackageImage())->save($imgdata);
        }
        return $this->renderSuccess("预报成功");
    }
    
    
     /**
     * 获取昨天入库的所有包裹以及包裹图片信息
     * @return array
     * @throws \think\exception\DbException
     */
    public function getTodayInWarehousePackages()
    {
        // 获取昨天的开始和结束时间
        $yesterdayStart = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $yesterdayEnd = date('Y-m-d 23:59:59', strtotime('-1 day'));
        
        // 查询昨天入库的包裹
        $packages = (new Package())
            ->where('is_delete', 0)
            ->where('entering_warehouse_time', 'between', [$yesterdayStart, $yesterdayEnd])
            ->field('id, express_num, entering_warehouse_time, weight')
            ->order('entering_warehouse_time', 'desc')
            ->select();
        
        $result = [];
        foreach ($packages as $package) {
            // 获取包裹的图片信息（使用with关联并bind字段）
            $images = CommonPackageImage::where('package_id', $package['id'])
                ->with(['file'])
                ->select();
            
            $imageUrls = [];
            foreach ($images as $image) {
                // 由于file关联使用了bind，file_path、file_name、file_url会直接绑定到image对象上
                if (!empty($image['file_path'])) {
                    // 优先使用file_path（完整路径）
                    $imageUrls[] = $image['file_path'];
                } elseif (!empty($image['file_url']) && !empty($image['file_name'])) {
                    // 如果没有file_path，则手动拼接
                    $imageUrls[] = rtrim($image['file_url'], '/') . '/' . $image['file_name'];
                }
            }
            
            $result[] = [
                'express_num' => $package['express_num'], // 包裹单号
                'entering_warehouse_time' => $package['entering_warehouse_time'], // 入库时间
                'weight' => isset($package['weight']) ? $package['weight'] : 0, // 包裹重量
                'image_urls' => $imageUrls // 包裹照片的url数组
            ];
        }
        
        return $this->renderSuccess([
            'list' => $result,
            'count' => count($result)
        ]);
    }
}
