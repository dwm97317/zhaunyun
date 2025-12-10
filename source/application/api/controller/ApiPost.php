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
    
    
            ]);

    
    
     /**
     * 请求接口并保存昨天入库的包裹数据
     * @return array
     * @throws \think\exception\DbException
     */
    public function saveInWarehousePackages()
    {
        // 固定请求接口URL
        $apiUrl = 'https://transport.box0018.cn/index.php?s=/api/api_Post/getTodayInWarehousePackages&wxapp_id=10028';
      
        // 请求接口获取数据
        $response = curl($apiUrl);
        if (empty($response)) {
            return $this->renderError('请求接口失败，无法获取数据');
        }
        
        // 解析JSON数据
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->renderError('接口返回数据格式错误：' . json_last_error_msg());
        }
        
        // 验证返回数据格式
        if (empty($result['code']) || $result['code'] != 1) {
            return $this->renderError('接口返回错误：' . ($result['msg'] ?? '未知错误'));
        }
        
        if (empty($result['data']['list']) || !is_array($result['data']['list'])) {
            return $this->renderError('接口返回数据为空或格式不正确');
        }
        
        $dataList = $result['data']['list'];
        $successCount = 0;
        $failCount = 0;
        $errors = [];
        
        // 开始事务
        Db::startTrans();
        try {
            foreach ($dataList as $index => $item) {
                // 验证必要字段
                if (empty($item['express_num'])) {
                    $errors[] = "第" . ($index + 1) . "条数据：包裹单号不能为空";
                    $failCount++;
                    continue;
                }
                
                // 检查包裹是否已存在
                $existingPackage = (new Package())
                    ->where('express_num', $item['express_num'])
                    ->where('is_delete', 0)
                    ->find();
                
                if (!empty($existingPackage)) {
                    // 如果包裹已存在，更新入库时间
                    $packageId = $existingPackage['id'];
                    if (!empty($item['entering_warehouse_time'])) {
                        $existingPackage->save([
                            'entering_warehouse_time' => $item['entering_warehouse_time']
                        ]);
                    }
                } else {
                    // 创建新包裹
                    $packageData = [
                        'order_sn' => createSn(),
                        'express_num' => $item['express_num'],
                        'status' => 2, // 已入库
                        'source' => 9, // 外部接口导入
                        'entering_warehouse_time' => !empty($item['entering_warehouse_time']) 
                            ? $item['entering_warehouse_time'] 
                            : getTime(),
                        'is_take' => 1, // 未认领
                        'created_time' => getTime(),
                        'updated_time' => getTime()
                    ];
                    
                    $packageId = (new Package())->saveData($packageData);
                    if (!$packageId) {
                        $errors[] = "第" . ($index + 1) . "条数据：保存包裹失败";
                        $failCount++;
                        continue;
                    }
                }
                
                // 保存图片
                if (!empty($item['image_urls']) && is_array($item['image_urls'])) {
                    foreach ($item['image_urls'] as $imageUrl) {
                        if (empty($imageUrl)) {
                            continue;
                        }
                        
                        // 解析图片URL
                        $urlInfo = parse_url($imageUrl);
                        if (empty($urlInfo['host']) || empty($urlInfo['path'])) {
                            continue;
                        }
                        
                        // 提取文件URL和文件名
                        $fileUrl = $urlInfo['scheme'] . '://' . $urlInfo['host'];
                        $filePath = trim($urlInfo['path'], '/');
                        $fileName = basename($filePath);
                        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                        
                        // 检查图片是否已存在（通过file_url和file_name查找UploadFile）
                        $existingFile = UploadFile::where('file_url', $fileUrl)
                            ->where('file_name', $fileName)
                            ->find();
                        
                        $fileId = null;
                        if (!empty($existingFile)) {
                            // 如果文件已存在，复用文件ID
                            $fileId = $existingFile['file_id'];
                        } else {
                            // 创建UploadFile记录
                            $uploadFileData = [
                                'storage' => 'qiniu', // 或其他云存储类型，根据实际情况调整
                                'file_url' => $fileUrl,
                                'file_name' => $fileName,
                                'file_type' => 'image',
                                'extension' => $extension ?: 'jpg',
                                'file_size' => 0, // 远程图片无法获取大小
                                'wxapp_id' => $this->wxapp_id
                            ];
                            
                            $uploadFile = new UploadFile();
                            $uploadFile->addImage($uploadFileData);
                            $fileId = $uploadFile['file_id'];
                        }
                        
                        // 检查该包裹是否已有此图片
                        if ($fileId) {
                            $existingImage = CommonPackageImage::where('package_id', $packageId)
                                ->where('image_id', $fileId)
                                ->find();
                            
                            if (empty($existingImage)) {
                                // 创建PackageImage记录
                                $imageData = [
                                    'package_id' => $packageId,
                                    'image_id' => $fileId,
                                    'wxapp_id' => $this->wxapp_id,
                                    'create_time' => time()
                                ];
                                (new CommonPackageImage())->save($imageData);
                            }
                        }
                    }
                }
                
                $successCount++;
            }
            
            Db::commit();
            
            return $this->renderSuccess([
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'errors' => $errors
            ], "成功保存 {$successCount} 条，失败 {$failCount} 条");
            
        } catch (\Exception $e) {
            Db::rollback();
            return $this->renderError('保存失败：' . $e->getMessage());
        }
    }
}
