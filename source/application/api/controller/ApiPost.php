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
         
        
        if(!empty($result)){
            $result->save([
                'status'=>2,
                'storage_id'=>$param['shop_id'],
                'weight'=>$param['weight'],
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
        return $this->renderSuccess("预报成功");
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
}
