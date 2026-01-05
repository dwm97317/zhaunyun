<?php
namespace app\store\controller\package;

use app\store\controller\Controller;
use app\store\model\Package;
use app\store\model\PackageItem;
use app\store\model\Category;
use app\store\model\Express;
use app\store\model\Line;
use app\store\model\PackageService;
use app\store\model\Countries;
use app\store\model\Shelf;
use app\store\model\ShelfUnit;
use app\store\model\ShelfUnitItem;
use app\store\model\store\Shop as ShopModel;
use app\store\model\User;
use app\store\model\Inpack;
use app\store\model\UserAddress;
use app\store\model\Comment;
use app\api\model\Logistics;
use app\store\model\PackageImage;
use think\Db;
use app\store\model\Barcode;
use app\common\service\Message;
use app\common\model\setting;
use app\store\model\InpackService as InpackServiceModel;
use app\store\model\user\UserMark as UserMarkModel;
use app\store\model\Batch;
use app\store\model\store\shop\Clerk;
use app\store\model\PackageClaim;
/**
 * 商家用户控制器
 * Class StoreUser
 * @package app\store\controller
 */
class Index extends Controller
{
    /**
     * 用户列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){
   
        $list = [];
        $category=[];
        $packageModel = new Package();
        $Category = new Category();
        $map = \request()->param();
        // dump($map);die;
        $adminstyle = Setting::detail('adminstyle')['values'];
        $map['limitnum'] = isset($map['limitnum'])?$map['limitnum']:(isset($adminstyle['pageno'])?$adminstyle['pageno']['package']:15);
              
        $list = $packageModel->getList($map);

        $shelf =   (new Shelf())->getList(['ware_no' => $this->store['user']['shop_id']]);
        
        $countweight = '+∞';
        if(isset($map['search']) || isset($map['likesearch']) || isset($map['express_num'])){
            $countweight = $packageModel->getListSum($map);
        }
        
        $shopList = ShopModel::getAllList(['wxapp_id'=> $this->getWxappId()]);

        $batchlist = (new Batch())->getAllwaitList([]);
        $line = (new Line())->getList([]);
        $packageService = (new PackageService())->getList([]);
        //获取设置
        $set = Setting::detail('store')['values'];
        
        $type = 'all';
   
        $topcategory = $Category->getListTop($name=null)->toArray()['data'];
   
        if(!empty($topcategory)){
           empty($map['top_id']) && $map['top_id'] = '';
           $category = $Category->getListTopChild($map['top_id'])->toArray()['data']; 
        }
        $storeAddress =(new UserAddress())->getDsList();
        $packlist = []; 
        $packlists = '';
        $i = 0;
        if(!empty($map['express_num'])){
            $express_num = str_replace("\r\n","\n",trim($map['express_num']));
            $express_num = explode("\n",$express_num);
            
            foreach ($express_num as $val){
                $result = $packageModel->where(['express_num'=>$val,'is_delete'=>0])->find();
                if(empty($result)){
                    $packlist[$i] = $val;
                    $i += 1;
                }
            }
            $packlists = implode(',',$packlist);
        }
        return $this->fetch('index', compact('i','packlists','list','shopList','line','packageService','type','storeAddress','category','topcategory','set','countweight','batchlist','adminstyle','shelf'));
    }
    
    // 下载导入失败的数据
    public function downloadErrorData(){
        // 引入excel插件
        vendor('PHPExcel.PHPExcel');
        
        // 获取POST数据 - 优先从$_POST直接获取，避免框架的自动处理
        $errorDataJson = '';
        if(isset($_POST['errorData'])){
            $errorDataJson = $_POST['errorData'];
        } elseif(input('?post.errorData')){
            $errorDataJson = input('post.errorData');
        } else {
            $post = request()->param();
            if(isset($post['errorData'])){
                $errorDataJson = $post['errorData'];
            }
        }
        
        if(empty($errorDataJson)){
            return $this->renderError('没有接收到失败数据');
        }
        
        // 如果是数组，直接使用
        if(is_array($errorDataJson)){
            $errorData = $errorDataJson;
        } 
        // 如果是字符串，解析JSON
        elseif(is_string($errorDataJson)){
            // 先尝试直接解析
            $errorData = @json_decode($errorDataJson, true);
            $jsonError = json_last_error();
            
            // 如果解析失败，尝试处理转义问题
            if($jsonError !== JSON_ERROR_NONE){
                // 方法1: 去除转义字符
                $decoded1 = stripslashes($errorDataJson);
                $errorData = @json_decode($decoded1, true);
                $jsonError = json_last_error();
                
                // 方法2: 如果还是失败，尝试处理HTML实体
                if($jsonError !== JSON_ERROR_NONE){
                    $decoded2 = html_entity_decode($errorDataJson, ENT_QUOTES | ENT_HTML401, 'UTF-8');
                    $errorData = @json_decode($decoded2, true);
                    $jsonError = json_last_error();
                }
                
                // 方法3: 如果还是失败，尝试去除首尾空白和可能的BOM
                if($jsonError !== JSON_ERROR_NONE){
                    $decoded3 = trim($errorDataJson);
                    $decoded3 = preg_replace('/^\xEF\xBB\xBF/', '', $decoded3); // 去除BOM
                    $errorData = @json_decode($decoded3, true);
                    $jsonError = json_last_error();
                }
            }
            
            // 如果仍然失败，返回详细错误信息
            if($jsonError !== JSON_ERROR_NONE){
                $errorMsg = 'JSON解析失败: ' . json_last_error_msg();
                $errorMsg .= ' | 错误代码: ' . $jsonError;
                $errorMsg .= ' | 数据长度: ' . strlen($errorDataJson);
                // 显示数据的前100个字符用于调试
                $preview = mb_substr($errorDataJson, 0, 100, 'UTF-8');
                $errorMsg .= ' | 数据预览: ' . $preview;
                return $this->renderError($errorMsg);
            }
        } else {
            return $this->renderError('数据格式错误，期望字符串或数组，实际类型: ' . gettype($errorDataJson));
        }
        
        // 验证数据格式
        if(!is_array($errorData) || empty($errorData)){
            return $this->renderError('数据格式错误或为空，期望非空数组');
        }
        
        // 获取系统设置，判断显示用户编号还是用户ID
        $set = Setting::detail('store')['values'];
        $isShowUserCode = isset($set['usercode_mode']['is_show']) && $set['usercode_mode']['is_show'] == 1;
        $userFieldLabel = $isShowUserCode ? '用户编号' : '用户ID';
        
        // 创建Excel对象
        $objPHPExcel = new \PHPExcel();
        
        // 设置表头（包含错误信息列）
        $headers = [
            '快递单号',
            $userFieldLabel,
            '唛头',
            '物流名称',
            '仓库名称',
            '包裹重量',
            '长',
            '宽',
            '高',
            '体积',
            '物品名称',
            '物品数量',
            '错误原因'
        ];
        
        // 设置表头
        $objPHPExcel->setActiveSheetIndex(0);
        $column = 'A';
        foreach ($headers as $header) {
            $objPHPExcel->getActiveSheet()->setCellValue($column . '1', $header);
            $column++;
        }
        
        // 设置样式
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . chr(64 + count($headers)) . '1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . chr(64 + count($headers)) . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . chr(64 + count($headers)) . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . chr(64 + count($headers)) . '1')->getFill()->getStartColor()->setRGB('FFE6E6');
        
        // 设置列宽
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20); // 快递单号
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15); // 用户编号/用户ID
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15); // 唛头
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15); // 物流名称
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15); // 仓库名称
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12); // 包裹重量
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10); // 长
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10); // 宽
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10); // 高
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10); // 体积
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15); // 物品名称
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(12); // 物品数量
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(50); // 错误原因
        
        // 填充数据
        $row = 2;
        foreach($errorData as $item){
            $originalData = isset($item['originalData']) ? $item['originalData'] : (isset($item['data']) ? $item['data'] : []);
            $error = isset($item['error']) ? $item['error'] : '未知错误';
            
            // 根据系统设置获取用户字段
            $userField = '';
            if($isShowUserCode){
                // 显示用户编号
                if(isset($originalData['用户编号'])){
                    $userField = $originalData['用户编号'];
                } elseif(isset($item['data']['user_code'])){
                    $userField = $item['data']['user_code'];
                }
            } else {
                // 显示用户ID
                if(isset($originalData['用户ID'])){
                    $userField = $originalData['用户ID'];
                } elseif(isset($item['data']['member_id'])){
                    $userField = $item['data']['member_id'];
                }
            }
            
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, isset($originalData['快递单号']) ? $originalData['快递单号'] : (isset($item['data']['express_num']) ? $item['data']['express_num'] : ''));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $userField);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, isset($originalData['唛头']) ? $originalData['唛头'] : (isset($item['data']['usermark']) ? $item['data']['usermark'] : ''));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, isset($originalData['物流名称']) ? $originalData['物流名称'] : (isset($item['data']['express_name']) ? $item['data']['express_name'] : ''));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, isset($originalData['仓库名称']) ? $originalData['仓库名称'] : (isset($item['data']['storage_name']) ? $item['data']['storage_name'] : ''));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, isset($originalData['包裹重量']) ? $originalData['包裹重量'] : (isset($item['data']['weight']) ? $item['data']['weight'] : ''));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, isset($originalData['长']) ? $originalData['长'] : (isset($item['data']['length']) ? $item['data']['length'] : ''));
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, isset($originalData['宽']) ? $originalData['宽'] : (isset($item['data']['width']) ? $item['data']['width'] : ''));
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, isset($originalData['高']) ? $originalData['高'] : (isset($item['data']['height']) ? $item['data']['height'] : ''));
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, isset($originalData['体积']) ? $originalData['体积'] : (isset($item['data']['volume']) ? $item['data']['volume'] : ''));
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, isset($originalData['物品名称']) ? $originalData['物品名称'] : (isset($item['data']['class_name']) ? $item['data']['class_name'] : ''));
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, isset($originalData['物品数量']) ? $originalData['物品数量'] : (isset($item['data']['product_num']) ? $item['data']['product_num'] : ''));
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $error);
            
            // 错误原因列设置为红色
            $objPHPExcel->getActiveSheet()->getStyle('M' . $row)->getFont()->getColor()->setRGB('FF0000');
            
            $row++;
        }
        
        // 设置工作表名称
        $objPHPExcel->getActiveSheet()->setTitle('导入失败数据');
        
        // 设置文件名
        $filename = "导入失败数据_" . date('YmdHis') . "_" . rand(1000, 9999) . ".xlsx";
        
        // 确保excel目录存在
        $excelDir = 'excel/';
        if (!is_dir($excelDir)) {
            mkdir($excelDir, 0755, true);
        }
        
        // 输出Excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($excelDir . $filename);
        
        return $this->renderSuccess("导出成功", '', [
            "file_name" => "https://" . $_SERVER["HTTP_HOST"] . "/" . $excelDir . $filename,
        ]);
    }
    
    // 下载导入模板
    public function downloadTemplate(){
        // 引入excel插件
        vendor('PHPExcel.PHPExcel');
        
        // 获取系统设置
        $set = Setting::detail('store')['values'];
        $isShowUserCode = isset($set['usercode_mode']['is_show']) && $set['usercode_mode']['is_show'] == 1;
        
        // 创建Excel对象
        $objPHPExcel = new \PHPExcel();
        
        // 设置表头
        $headers = [
            '快递单号',
            $isShowUserCode ? '用户编号' : '用户ID',
            '唛头',
            '物流名称',
            '仓库名称',
            '包裹重量',
            '长',
            '宽',
            '高',
            '体积',
            '物品名称',
            '物品数量',
        ];
        
        // 设置表头
        $objPHPExcel->setActiveSheetIndex(0);
        $column = 'A';
        foreach ($headers as $header) {
            $objPHPExcel->getActiveSheet()->setCellValue($column . '1', $header);
            $column++;
        }
        
        // 设置样式
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . chr(64 + count($headers)) . '1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . chr(64 + count($headers)) . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // 设置列宽
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(12);
        
        // 设置工作表名称
        $objPHPExcel->getActiveSheet()->setTitle('批量导入模板');
        
        // 设置文件名
        $filename = "小思集运批量导入模板_" . ($isShowUserCode ? '用户编号' : '用户ID') . ".xlsx";
        
        // 输出Excel文件
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    /**
     * 用户列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function userindex(){
   
        $list = [];
        $category=[];
        $packageModel = new Package();
        $Category = new Category();
        $map = \request()->param();
        // dump($map);die;
        $adminstyle = Setting::detail('adminstyle')['values'];
        $map['limitnum'] = isset($map['limitnum'])?$map['limitnum']:(isset($adminstyle['pageno'])?$adminstyle['pageno']['package']:15);
              
        $list = $packageModel->getList($map);

        $shelf =   (new Shelf())->getList(['ware_no' => $this->store['user']['shop_id']]);
        
        $countweight = '+∞';
        if(isset($map['search']) || isset($map['likesearch']) || isset($map['express_num'])){
            $countweight = $packageModel->getListSum($map);
        }
        
        $shopList = ShopModel::getAllList(['wxapp_id'=> $this->getWxappId()]);

        $batchlist = (new Batch())->getAllwaitList([]);
        $line = (new Line())->getList([]);
        $packageService = (new PackageService())->getList([]);
        //获取设置
        $set = Setting::detail('store')['values'];
        
        $type = 'all';
   
        $topcategory = $Category->getListTop($name=null)->toArray()['data'];
   
        if(!empty($topcategory)){
           empty($map['top_id']) && $map['top_id'] = '';
           $category = $Category->getListTopChild($map['top_id'])->toArray()['data']; 
        }
        $storeAddress =(new UserAddress())->getDsList();
        $packlist = []; 
        $packlists = '';
        $i = 0;
        if(!empty($map['express_num'])){
            $express_num = str_replace("\r\n","\n",trim($map['express_num']));
            $express_num = explode("\n",$express_num);
            
            foreach ($express_num as $val){
                $result = $packageModel->where(['express_num'=>$val,'is_delete'=>0])->find();
                if(empty($result)){
                    $packlist[$i] = $val;
                    $i += 1;
                }
            }
            $packlists = implode(',',$packlist);
        }
        return $this->fetch('index', compact('i','packlists','list','shopList','line','packageService','type','storeAddress','category','topcategory','set','countweight','batchlist','adminstyle','shelf'));
    }
    
    
    //退货单包裹列表
    public function returned(){
        $list = [];$category=[];
        $packageModel = new Package();
        $Category = new Category();
        $map1 = ['is_take'=>4 ];
        $map2 = \request()->param();
        $map = array_merge($map1,$map2);
        $list = $packageModel->getList($map);
        // dump($list->toARray());die;
        $countweight = $packageModel->getListSum($map);
        $shopList = ShopModel::getAllList();
        $line = (new Line())->getList([]);
        $packageService = (new PackageService())->getList([]);
        $status = [1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        $topcategory = $Category->getListTop($name=null)->toArray()['data'];
        if(!empty($topcategory)){
           empty($map2['top_id']) && $map2['top_id'] = '';
           $category = $Category->getListTopChild($map2['top_id'])->toArray()['data']; 
        }
        $type = 'errors';
        $storeAddress =(new UserAddress())->getDsList();
        $set = Setting::detail('store')['values'];

        $packlist = []; 
        $packlists = '';
        $i = 0;
        if(!empty($map['express_num'])){
            $express_num = str_replace("\r\n","\n",trim($map['express_num']));
            $express_num = explode("\n",$express_num);
            
            foreach ($express_num as $val){
                $result = $packageModel->where(['express_num'=>$val,'is_delete'=>0])->find();
                if(empty($result)){
                    $packlist[$i] = $val;
                    $i += 1;
                }
            }
            $packlists = implode(',',$packlist);
        }
        return $this->fetch('returned', compact('i','packlists','list','shopList','title','line','packageService','category','topcategory','type','storeAddress','set','countweight'));
    }
    
    /**
     * 待认领任务列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function claim(){
        $packageModel = new PackageClaim();
        $map = \request()->param();
        $list = $packageModel->getList($map);
        // dump($list->toArray());die;
        $shopList = ShopModel::getAllList();
        return $this->fetch('claim', compact('list','shopList'));
    }
    
    /**
     * 删除待认领任务列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function deleteclaim(){
        $packageModel = new PackageClaim();
        $map = \request()->param();
        if($packageModel->where('id',$map['id'])->delete()){
             return $this->renderSuccess("删除成功");
        }
        return $this->renderError('删除失败');
    }
    
    /**
     * 待认领任务列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function doclaim(){
        $packageModel = new PackageClaim();
        $map = \request()->param();
        
        $detail = $packageModel->where('id',$map['id'])->find();
        if(empty($detail)){
            return $this->renderError('认领任务不存在');
        }
        if($map['status']==1){
            (new Package())->where('id',$detail['package_id'])->update([
                'member_id'=>$detail['user_id'],
                'is_take'=>2,
                'updated_time'=>getTime()
            ]);
        }else{
            $res=(new Package())->where('id',$detail['package_id'])->update([
                'member_id'=>0,
                'is_take'=>1,
                'updated_time'=>getTime()
            ]);
        }
        $detail->save([
            'status'=>$map['status'],
            'clerk_remark'=>$map['remark'],
            'clerk_name'=>$this->store['user']['user_name'],
            'clerk_time'=>time(),
        ]);
        return $this->renderSuccess("处理成功");
    }
    
    //获取包裹的统计数据
    public function gettotal(){
        $map = \request()->param();
        $Category = new Category();
        $map['limitnum'] = isset($map['limitnum'])?$map['limitnum']:(isset($adminstyle['pageno'])?$adminstyle['pageno']['package']:15);
        $topcategory = $Category->getListTop($name=null)->toArray()['data'];
   
        if(!empty($topcategory)){
           empty($map['top_id']) && $map['top_id'] = '';
           $category = $Category->getListTopChild($map['top_id'])->toArray()['data']; 
        }
        $packageModel = new Package();
        $datatotal = $packageModel->getDataTotal($map);    
        return $this->renderSuccess("获取成功","",$datatotal);
    }
    
    
    //动态改变类目
    public function changecategory(){
        $Category = new Category();
        $data = $this->request->param();
        $category = $Category->getListTopChild($data['categoryid'])->toArray()['data'];
        return $this->renderSuccess("获取成功","",$category);
    }
    
    //新增包裹子包裹
    public function  addpackageitem(){
        $param = $this->request->param();
        $PackageItem = new PackageItem;
        if(!empty($param['package']['one_price']) && !empty($param['package']['product_num'])){
            $param['package']['all_price'] = $param['package']['one_price'] * $param['package']['product_num'];
        }
        $param['package']['wxapp_id'] = $this->getWxappId();
        $param['package']['express_num'] = $param['data']['express_num'];
        $param['package']['order_id'] = $param['data']['id'];
        if($PackageItem->save($param['package'])){
             return $this->renderSuccess("添加成功");
        }
        return $this->renderError("添加失败");
    }
    
    
    //编辑包裹的子包裹
    public function  edieditpackageitemt(){
        $param = $this->request->param();
        $PackageItem = new PackageItem;
        $model = $PackageItem->details($param['id']);
        if (!$this->request->isAjax()){
            return $this->fetch('editpackageitem', compact('model'));
        }
        if($model->save($param['package'])){
            return $this->renderSuccess("更新成功");
        }
    }
    
     //删除包裹的子包裹
    public function  deletepackageitem(){
        $param = $this->request->param();
        $PackageItem = new PackageItem;
        $model = $PackageItem->details($param['id']);
        if(!empty($model) && $model->delete()){
            return $this->renderSuccess("删除成功");
        }
        return $this->renderError("删除失败");
    }
    
    //包裹回收站    
    public function deletepack(){
        $packageModel = new Package();
        $Category = new Category();
        $map1 = ['is_delete'=>1];
        $map2 = \request()->param();
        $map = array_merge($map1,$map2);
   
        $list = $packageModel->getdeleteList($map);
        $countweight = '+∞';
        // $countweight = $packageModel->getListSum($map);
        $line = (new Line())->getListAll([]);
        $shopList = ShopModel::getAllList();
        $packageService = (new PackageService())->getList([]);
        $storeAddress =(new UserAddress())->getDsList();
        $category=[];
        $topcategory = $Category->getListTop($name=null)->toArray()['data'];
        if(!empty($topcategory)){
           empty($map2['top_id']) && $map2['top_id'] = '';
           $category = $Category->getListTopChild($map2['top_id'])->toArray()['data']; 
        }
        $type = 'deletepack';
        $set = Setting::detail('store')['values'];
        $packlist = []; 
        $packlists = '';
        $i = 0;
        if(!empty($map['express_num'])){
            $express_num = str_replace("\r\n","\n",trim($map['express_num']));
            $express_num = explode("\n",$express_num);
            
            foreach ($express_num as $val){
                $result = $packageModel->where(['express_num'=>$val,'is_delete'=>0])->find();
                if(empty($result)){
                    $packlist[$i] = $val;
                    $i += 1;
                }
            }
            $packlists = implode(',',$packlist);
        }
        return $this->fetch('index', compact('i','packlists','list','shopList','title','line','packageService','type','storeAddress','category','topcategory','set','countweight'));
    } 
     
    //deleteall 批量删除包裹
    public function deleteall(){
      $param = \request()->param();
      $packageModel = new Package();
      $idsArr = explode(',',$param['selectId']);

      foreach($idsArr as $key =>$val ){
           $pack[$key] = $packageModel->where('id',$val)->update(['is_delete' => 1]);
      }
      $newArr = array_unique($pack);
      if(count($newArr)==1 && $newArr[0] == 1){
           return $this->renderSuccess('操作成功');
      }
      return $this->renderError('操作失败');
    }
    
    //查询包裹的物流信息
    public function getlog(){
        $packageModel = new Package();
        $param = $this->request->param();
        $data = $packageModel->getlog($param);
        return $this->renderSuccess('操作成功','',compact('data'));
    }
    
    //代客预报
    public function adminreport(){
        $packageModel = new Package();
        $countryList = (new Countries())->getListAll();
        $set = Setting::detail('store')['values'];
        $shopList = ShopModel::getAllList();
        $expressList = Express::getAll();
        $list = [];
        if (!$this->request->isAjax()){
            return $this->fetch('adminreport', compact('shopList','expressList','countryList','set'));
        }
        $param = $this->request->param();
        $express_num = str_replace("\r\n","\n",trim($param['data']['express_num']));
        $express_num = explode("\n",$express_num);
        $data = [];
        $User = new User;
        $member = '';
        // dump($param);die;
        if(!empty($param['data']['user_code'])){
            $member = $User->where(['user_code'=>$param['data']['user_code'],'is_delete'=>0])->find();
        }
        if(!empty($param['data']['user_id'])){
            $member = $User->where(['user_id'=>$param['data']['user_id'],'is_delete'=>0])->find();
        }
        // dump($member);die;
        foreach ($express_num as $key => $value){
            $result = $packageModel->where(['express_num'=>$value,'is_delete'=>0])->find();
            if(!empty($result)){
                
                !empty($member) && $result->save([
                    'member_id'=>!empty($result['member_id'])?$result['member_id']:$member['user_id'],
                    'is_take'=>2,
                    'usermark'=>isset($param['data']['mark'])?$param['data']['mark']:'',
                    'country_id'=>$param['data']['country_id'],
                    'storage_id'=>$param['data']['shop_id'],
                    'remark'=>$param['data']['remark'],
                    'updated_time'=>getTime()
                    ]);
                continue;
            }
            $data['express_num'] = trim($value);
            $data['order_sn'] = createSn();
            $data['is_take'] = !empty($member)?2:1;
            $data['member_id'] = !empty($member)?$member['user_id']:'';
            $data['country_id'] = $param['data']['country_id'];
            $data['storage_id'] = $param['data']['shop_id'];
            $data['usermark'] = isset($param['data']['mark'])?$param['data']['mark']:'';
            $data['remark'] = $param['data']['remark'];
            $data['created_time'] = getTime();
            $data['updated_time'] = getTime();
            $data['wxapp_id'] = $this->getWxappId();
            
            $package_id = $packageModel->insertGetId($data);
            if(!empty($param['data']['goods_name'])){
                foreach ($param['data']['goods_name'] as $k=> $v){
                        $class = [
                        'width'=> $param['data']['width'][$k], 
                        'height'=> $param['data']['height'][$k],   
                        'length'=> $param['data']['length'][$k],   
                        'weight'=> $param['data']['weight'][$k],
                        // 'all_weight'=> (!empty($param['data']['unit_weight'][$k])?$param['data']['unit_weight'][$k]:0)*$param['data']['product_num'][$k],
                        'net_weight'=> !empty($param['data']['net_weight'][$k])?$param['data']['net_weight'][$k]:0,
                        'product_num'=> $param['data']['product_num'][$k],   
                        'one_price'=> !empty($param['data']['one_price'][$k])?$param['data']['one_price'][$k]:0,   
                        'goods_name'=> !empty($param['data']['goods_name'][$k])?$param['data']['goods_name'][$k]:'',
                        'class_name_en'=> !empty($param['data']['class_name_en'][$k])?$param['data']['class_name_en'][$k]:'',   
                        'goods_name_jp'=> !empty($param['data']['goods_name_jp'][$k])?$param['data']['goods_name_jp'][$k]:'',   
                        'spec'=> !empty($param['data']['spec'][$k])?$param['data']['spec'][$k]:'',
                        'brand'=> !empty($param['data']['brand'][$k])?$param['data']['brand'][$k]:'',   
                        'volumeweight'=> !empty($param['data']['volumeweight'][$k])?$param['data']['volumeweight'][$k]:0,
                        'volume'=>(!empty($param['data']['width'][$k])?$param['data']['width'][$k]:0)*(!empty($param['data']['height'][$k])?$param['data']['height'][$k]:0)*(!empty($param['data']['length'][$k])?$param['data']['length'][$k]:0)/1000000,
                    ];
                    
                    $packageModel->doClassIdstwo($class,$data['express_num'],$package_id,$this->getWxappId());
                }
            }
        }
        // // if(count($data)>0 && $packageModel->insertAll($data)){
        // //     return $this->renderSuccess('预报成功');
        // // }
        // if(count($data)==0 && count($express_num)>0){
        //     return $this->renderSuccess('认领成功');
        // }
        return $this->renderSuccess('预报成功');
    }
    
    
     //backall 批量还原包裹
    public function backall(){
      $param = \request()->param();
      $packageModel = new Package();
      $idsArr = explode(',',$param['selectId']);
      foreach($idsArr as $key =>$val ){
           $pack[$key] = $packageModel->where('id',$val)->update(['is_delete' => 0]);
      }
      $newArr = array_unique($pack);
      if(count($newArr)==1 && $newArr[0] == 1){
           return $this->renderSuccess('操作成功');
      }
      return $this->renderError('操作失败');
    }
    
    //问题件批量还原成正常件
    public function backtoNormalall(){
      $param = \request()->param();
      $packageModel = new Package();
      $idsArr = explode(',',$param['selectId']);
      foreach($idsArr as $key =>$val ){
           // 先查询包裹信息，检查是否有入库时间
           $package = $packageModel->where('id',$val)->find();
           if($package){
               // 如果有入库时间，设置为已入库(status=2)，否则设置为未入库(status=1)
               $status = !empty($package['entering_warehouse_time']) ? 2 : 1;
               $pack[$key] = $packageModel->where('id',$val)->update(['status' => $status]);
           }else{
               $pack[$key] = 0;
           }
      }
      $newArr = array_unique($pack);
      if(count($newArr)==1 && $newArr[0] == 1){
           return $this->renderSuccess('操作成功');
      }
      return $this->renderError('操作失败');
    }
    
    public function seachuserAddress(){
        $packageModel = new Package();
        $UserAddress = new UserAddress();
        $map = \request()->param();
        $packidArr = explode(',',$map['selectIds']);
        if(count($packidArr)>0){
            $pack = $packageModel->detail($packidArr[0]);
            if($pack['member_id'] == 0 || empty($pack['member_id'])){
               return $this->renderError('包裹未被领取或用户信息有误'); 
            }
            $useraddress = $UserAddress->getList($pack['member_id']);
            return $this->renderSuccess('操作成功','',$useraddress);
        }
        return $this->renderError('请选择单号');
    }
    
    
    // 代替用户申请打包
    public function uninpack(){
        $list = [];
        $packageModel = new Package();
        $Category = new Category();
        $map1 = ['is_take'=>2,'status'=>[2,3,4],'category_id' =>null];
        $map2 = \request()->param();
        $map = array_merge($map1,$map2);
       
        $list = $packageModel->getUnpackList($map);
        //   dump($packageModel->getLastsql());die;
        $countweight = '+∞';
        if(isset($map['search']) || isset($map['likesearch']) || isset($map['express_num'])){
            $countweight = $packageModel->getListSum($map);
        }
        $shopList = ShopModel::getAllList();
        $line = (new Line())->getListAll([]);
        $packageService = (new PackageService())->getListAll();
        $batchlist = (new Batch())->getAllwaitList([]);
        $set = Setting::detail('store')['values'];
        $type = 'uninpack';
        $topcategory = $Category->getListTop($name=null)->toArray()['data'];
        if(!empty($topcategory)){
           empty($map2['top_id']) && $map2['top_id'] = '';
           $category = $Category->getListTopChild($map2['top_id'])->toArray()['data']; 
        }
        //获取代收点的
        $storeAddress =(new UserAddress())->getAllDsList();
   
        $packlist = []; 
        $packlists = '';
        $i = 0;
        if(!empty($map['express_num'])){
            $express_num = str_replace("\r\n","\n",trim($map['express_num']));
            $express_num = explode("\n",$express_num);
            
            foreach ($express_num as $val){
                $result = $packageModel->where(['express_num'=>$val,'is_delete'=>0])->find();
                if(empty($result)){
                    $packlist[$i] = $val;
                    $i += 1;
                }
            }
            $packlists = implode(',',$packlist);
        }
        return $this->fetch('index', compact('i','packlists','list','shopList','title','line','packageService','type','storeAddress','topcategory','category','set','countweight','batchlist'));
    }
    
    public function setErrors(){
        $packageModel = new Package();
        $ids = $this->postData('selectIds')[0];
        $remark = $this->postData('error')['remark'];
        $idsArr = explode(',',$ids);
        $update['remark'] = $remark;
        $update['status'] = -1;
        
        $res = $packageModel->where('id','in',$idsArr)->update($update);
        // dump($packageModel->getLastsql());die;
        if ($res){
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError('操作失败');
    } 
    
    // // 申请打包
    // public function inpacks(){
    //     $list = [];
    //     $packageModel = new Package();
    //     $map1 = ['is_take'=>2,'status'=>[5,6,7,8,9,10,11]];
    //     $map2 = \request()->param();
    //     $map = array_merge($map1,$map2);
    //     $list = $packageModel->getList($map);
    //     $shopList = ShopModel::getAllList();
    //     $line = (new Line())->getList([]);
    //     $packageService = (new PackageService())->getList([]);
    //     $status = [1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
    //     $statusTotal = [];
    //     foreach($status as $key => $val){
    //         $map_where['status'] = $key;
    //         $map_where['is_take'] = 2;
    //         $statusTotal[$key] = $packageModel->where($map_where)->count();
    //     } 
    //     $type = 'applypacks';
    //     return $this->fetch('index', compact('list','shopList','title','line','packageService','statusTotal','type'));
    // }
    
    // 异常单     
    public function errors(){
        $list = [];$category=[];
        $packageModel = new Package();
        $Category = new Category();
        $map1 = ['is_take'=>2,'status'=>[-1]];
        $map2 = \request()->param();
        $map = array_merge($map1,$map2);
        $list = $packageModel->getList($map);
        $countweight = $packageModel->getListSum($map);
        $shopList = ShopModel::getAllList();
        $line = (new Line())->getList([]);
        $packageService = (new PackageService())->getList([]);
        $status = [1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        $topcategory = $Category->getListTop($name=null)->toArray()['data'];
        if(!empty($topcategory)){
           empty($map2['top_id']) && $map2['top_id'] = '';
           $category = $Category->getListTopChild($map2['top_id'])->toArray()['data']; 
        }
        $type = 'errors';
        $storeAddress =(new UserAddress())->getDsList();
        $set = Setting::detail('store')['values'];

        $packlist = []; 
        $packlists = '';
        $i = 0;
        if(!empty($map['express_num'])){
            $express_num = str_replace("\r\n","\n",trim($map['express_num']));
            $express_num = explode("\n",$express_num);
            
            foreach ($express_num as $val){
                $result = $packageModel->where(['express_num'=>$val,'is_delete'=>0])->find();
                if(empty($result)){
                    $packlist[$i] = $val;
                    $i += 1;
                }
            }
            $packlists = implode(',',$packlist);
        }
        return $this->fetch('index', compact('i','packlists','list','shopList','title','line','packageService','category','topcategory','type','storeAddress','set','countweight'));
    }
    
    
    //展示功能
    public function nouser(){
        $list = [];
        $packageModel = new Package();
        $map1 = ['statu' =>2,'is_take'=>1];
        $map2 = \request()->param();
        $map = array_merge($map1,$map2);
        $adminstyle = Setting::detail('adminstyle')['values'];
        $map['limitnum'] = isset($map['limitnum'])?$map['limitnum']:(isset($adminstyle['pageno'])?$adminstyle['pageno']['package']:15);
        $list = $packageModel->getList($map);
        $shopList = ShopModel::getAllList();
        return $this->fetch('nouser', compact('list','shopList','adminstyle'));
    }
    
    /**
     * 预约集运单
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function appointment(){
        $list = [];
        $packageModel = new Package();
        $map1 = ['statu' =>1,'is_take'=>2,'source'=>7];
        $map2 = \request()->param();
        $map = array_merge($map1,$map2);
        $list = $packageModel->getYList($map);
        $shopList = ShopModel::getAllList();
        return $this->fetch('appointment', compact('list','shopList'));
    }


    /*
    * 编辑包裹功能
    * 2022年12月8日
    */
    public function edit($id)
    {
        if(empty($id)){
            return;
        }
        $post = input();
        $map = ['id'=>$id];
        if (isset($post['search'])){
            $map['search'] = $post['search'];
        }
        //获取包裹物品类目
        $model = new PackageItem();
        $list = $model->getList($map);
        
        $packageModel = new Package();
        $detail = $packageModel->detail($id);
            //   dump($detail);die;
        // $shopShelf = ShopModel::getQueList(['storage_id'=>$detail['storage_id']]);
        // dump($shopShelf->toArray());die;
        $shopList = ShopModel::getListName();
        if($this->store['user']['shop_id']>0){
            $shopList = (new ShopModel())->whereIn('shop_id',$this->store['user']['shop_id'])->select();
        }
        //获取到国家信息
        $detail['country'] = (new Countries())->where('id',$detail['country_id'])->find();
        $countryList = (new Countries())->getListAll();
        //获取到用户信息
        $detail['user'] = (new User())->where('user_id',$detail['member_id'])->find();
        //获取到仓库信息
        $detail['storage'] = (new ShopModel())->where('shop_id',$detail['storage_id'])->find();
        $expressList = Express::getAll();
        $set = Setting::detail('store')['values'];
        
        //物品类目
        $category = (new Category())->getAll()['tree'];
        // dump($category);die;
        // 货架功能
        $shelfitem=[];
        $shelf = (new Shelf())->where('ware_no',$detail['storage_id'])->select();
        if(isset($shelf) && count($shelf)>0){
            if(isset($detail['shelfunititem'])){
                $shelfid = $detail['shelfunititem']['shelfunit']['shelf']['id'];
                $shelfitem = (new ShelfUnit())->where('shelf_id',$shelfid)->select();
            }
        }
        // dump($detail->toArray());die;
        return $this->fetch('edit', compact('list','detail','shopList','shelf','shelfitem','category','countryList','expressList','set'));
    }
    
    
    /**
     * 后台确认入库
     * 后台【包裹管理】【后台录入】
     * @param $id
     * @return list
     * @throws \think\Exception
     */
    public function enter(){
        $id =  \request()->get('id');
        $data = '';
        $shelfitem=[];
        if ($id){
            $data = (new Package())->detail($id);
            // dump($data->toArray());die;
            $packageItem = (new PackageItem())->where(['order_id'=>$data['id']])->select();
            if ($packageItem){
                 $data['shop_class'] = array_column($packageItem->toArray(),'class_name');
            }
            $country = (new Countries())->field('title')->find($data['country_id']);
            $data['country'] = $country['title'];
            //货架数据
            $shelf = (new Shelf())->where('ware_no',$data['storage_id'])->select();
            if(isset($shelf) && count($shelf)>0){
                if(isset($data['shelfunititem'])){
                    $shelfid = $data['shelfunititem']['shelfunit']['shelf']['id'];
                    $shelfitem = (new ShelfUnit())->where('shelf_id',$shelfid)->select();
                }
            }
        }
        $countryList = (new Countries())->getListAll();
        $set = Setting::detail('store')['values'];
        $category = (new Category())->getAll()['tree'];
        if(!empty($category)){
            foreach ($category as $key){
                if(!isset($key['child'])){
                     return $this->renderError('分类设置有误,请设置两级菜单，不可单独一级菜单'); 
                }
             }
        }
        $shopList = ShopModel::getAllList();
        $expressList = Express::getAll();

        $list = [];
        if (!$this->request->isAjax()){
            return $this->fetch('enter', compact('data','list','shopList','shelf','shelfitem','category','expressList','countryList','set'));
        }
    }
    
    /**
     * 后台手动后台录入
     * 后台【包裹管理】【后台录入】
     * @param $id
     * @return list
     * @throws \think\Exception
     */
    public function add(){
        $id =  \request()->get('id');
        $data = '';
        $shelfitem=[];
        if ($id){
            $data = (new Package())->detail($id);
            // dump($data->toArray());die;
            $packageItem = (new PackageItem())->where(['order_id'=>$data['id']])->select();
            if ($packageItem){
                 $data['shop_class'] = array_column($packageItem->toArray(),'class_name');
            }
            $country = (new Countries())->field('title')->find($data['country_id']);
            $data['country'] = $country['title'];
            //货架数据
            $shelf = (new Shelf())->where('ware_no',$data['storage_id'])->select();
            if(isset($shelf) && count($shelf)>0){
                if(isset($data['shelfunititem'])){
                    $shelfid = $data['shelfunititem']['shelfunit']['shelf']['id'];
                    $shelfitem = (new ShelfUnit())->where('shelf_id',$shelfid)->select();
                }
            }
        }
        $countryList = (new Countries())->getListAll();
        $printsetting = Setting::detail('printer')['values'];  //打印机设置
        $adminsetting = Setting::detail('adminstyle')['values'];  //电脑端设置
        $set = Setting::detail('store')['values'];
        $category = (new Category())->getAll()['tree'];
        if(!empty($category)){
            foreach ($category as $key){
                if(!isset($key['child'])){
                     return $this->renderError('分类设置有误,请设置两级菜单，不可单独一级菜单'); 
                }
             }
        }
        $shopList = ShopModel::getAllList();
        if($this->store['user']['shop_id']>0){
            $shopList = (new ShopModel())->where('shop_id','in',$this->store['user']['shop_id'])->select();
        }
        // dump($this->store);die;
        $expressList = Express::getAll();
        $shelf = [];
        if(count($shopList)>0){
            //货架数据
            // dump(43567);die;
            $shelf = (new Shelf())->where('ware_no',$shopList[0]['shop_id'])->select();
        }
        
        //   dump($shelf);die;
        if(isset($shelf) && count($shelf)>0){
            if(isset($detail['shelfunititem'])){
                $shelfid = $detail['shelfunititem']['shelfunit']['shelf']['id'];
                $shelfitem = (new ShelfUnit())->where('shelf_id',$shelfid)->select();
            }
        }
        
        $list = [];
        if (!$this->request->isAjax()){
            // dump($data);die;
            return $this->fetch('add', compact('data','list','shopList','shelf','shelfitem','category','expressList','countryList','set','printsetting','adminsetting'));
        }
    }
    
    // 更新为已丢弃
    public function updateTaker(){
        $model = new Package();
        $id =  \request()->param('id');
        if (!$model->where(['id'=>$id])->update(['is_take'=>3,'updated_time'=>getTime()])) {
            return $this->renderError($model->getError() ?: '操作失败');
        }
        return $this->renderSuccess('操作成功');
    }
    
   
    /**
     * 后台录入包裹
     * 2022年11月5日
    */
    public function uodatepackStatus(){
        $model = new Package();
        if (!$model->uodatepackStatus($this->postData('data'))) {
            return $this->renderError($model->getError() ?: '操作失败');
        }
        return $this->renderSuccess('操作成功');  
    }
    
    // 手动录入库保存
    public function post(){
        $model = new Package();
        if (!$model->post($this->postData('data'))) {
            return $this->renderError($model->getError() ?: '操作失败');
        }
        
        return $this->renderSuccess('操作成功');  
    }
    
    // 文件导入上传页面
    public function import(){
        return $this->fetch('import');
    }
    
    
    //电脑端代替用户打包
    public function inpack(){
        $ids = $this->postData('selectIds')[0];
        $line_id = $this->postData('inpack')['line_id'];
        $address_id = $this->postData('inpack')['address_id'];
        $pack_ids = isset($this->postData('inpack')['id'])?$this->postData('inpack')['id']:'';
        $remark = $this->postData('remark')[0];
        $line = (new Line())->find($line_id);
        //物流模板设置
        $noticesetting = setting::getItem('notice');
        $storesetting = setting::getItem('store');
        if (!$ids){
            return $this->renderError('请选择要打包的包裹');
        }
        
        $idsArr = explode(',',$ids);
        $pack = (new Package())->whereIn('id',$idsArr)->select();
        $weight = (new Package())->whereIn('id',$idsArr)->sum('weight');
        $volumn = (new Package())->whereIn('id',$idsArr)->sum('volume');
        // 计算体积重
        $volumnweight = $volumn/$line['volumeweight']*1000000;
        if($line['volumeweight_type']==20){
            $volumnweight = round(($allWeigth + ($volumn*1000000/$line['volumeweight'] - $allWeigth)*$line['bubble_weight']/100),2);
        }
        
        if (!$pack || count($pack) !== count($idsArr)){
            return $this->renderError('打包包裹数据错误');
        }
        $status = array_unique(array_column($pack->toArray(),'status'));
        //dump($status);die;
        if (count($status)==1 && in_array($status[0], [1,7,8,9,10,11])){
            return $this->renderError('请选择可以打包的包裹');             
        }
        $pack_member = array_unique(array_column($pack->toArray(),'member_id'));
       
        if (count($pack_member)!=1){
             return $this->renderError('请选择同一用户包裹进行打包');
        }

        
        if($address_id=='-1'){
            $address = (new UserAddress())->where(['user_id'=>$pack_member[0]])->find();
            if(!$address){
                return $this->renderError('该用户没有默认地址<br><a target="_blank" href="index.php?s=/store/user/address">[前往地址设置]</a>');
            }
            $address_id = $address['address_id']; 
        }else{
            $address = (new UserAddress())->where(['address_id'=>$address_id])->find();
        }
        
        $userinfo = (new User())->where('user_id',$pack_member[0])->find();
       
        
        if (!$line){
            return $this->renderError('线路不存在,请重新选择');
        }
        // 获取审核设置
        $adminstyle = setting::getItem('adminstyle', (new Package())->getWxappId());
        $is_verify_free = isset($adminstyle['is_verify_free']) ? $adminstyle['is_verify_free'] : 0;
        // 如果is_verify_free==1需要审核，则is_doublecheck=0（未审核）；否则is_doublecheck=1（已审核/无需审核）
        $is_doublecheck = $is_verify_free == 1 ? 0 : 1;
        
        // 创建包裹订单
        $inpackOrder = [
          'order_sn' =>createSn(),
          'remark' =>$remark,
          'pack_ids' => $ids,
          'pack_services_id' => !empty($pack_ids)?$pack_ids:'',
          'storage_id' => $pack[0]['storage_id'],
          'address_id' => $address_id,
          'free' => 0,
          'weight' => $weight,
          'cale_weight' =>$weight,
          'line_weight'=>$weight,
          'pay_type'=> !empty($userinfo)?$userinfo['paytype']:0,
          'volume' => $volumnweight, //体积重
          'pack_free' => 0,
          'other_free' =>0,
          'member_id' => $pack_member[0],
          'country_id' => $address['country_id'],
          'created_time' => getTime(),
          'updated_time' => getTime(),
          'status' => 1,
          'source' => 1,
          'wxapp_id' => (new Package())->getWxappId(),
          'line_id' => $line_id,
          'is_doublecheck' => $is_doublecheck,
        ];
        //  dump($inpackOrder);die;
        $user_id = $pack_member[0];
        if($storesetting['usercode_mode']['is_show']==1){
           $member =  (new User())->where('user_id',$pack_member[0])->find();
           $user_id = $member['user_code'];
        }
        // dump($storesetting['orderno']['default']);die;
        $createSnfistword = $storesetting['createSnfistword'];
        $xuhao = ((new Inpack())->where(['member_id'=>$pack_member[0],'is_delete'=>0])->count()) + 1;
        $shopname = ShopModel::detail($pack[0]['storage_id']);     
        $orderno = createNewOrderSn($storesetting['orderno']['default'],$xuhao,$createSnfistword,$user_id,$shopname['shop_alias_name'],$address['country_id']);
        $inpackOrder['order_sn'] = $orderno;
        
        
        $inpack = (new Inpack())->insertGetId($inpackOrder); 
        $inpackdate = (new Inpack())->where('id',$inpack)->find();
        //处理包装服务
        if(!empty($pack_ids)){
            (new InpackServiceModel())->doservice($inpack,$pack_ids);
        }
        
        $res = (new Package())->whereIn('id',$idsArr)->update(['inpack_id'=>$inpack,'status'=>5,'line_id'=>$line_id,'pack_service'=>$pack_ids,'address_id'=>$address_id,'updated_time'=>getTime()]);
        //更新包裹的物流信息
        foreach ($idsArr as $key => $val){
            $packnum[$key] = (new Package())->where('id',$val)->value('express_num');
        }
        //修改包裹的记录
        foreach ($packnum as $ky => $vl){
            Logistics::updateOrderSn($vl,$inpackdate['order_sn']);
        }
         if($noticesetting['packageit']['is_enable']==1){
             Logistics::addInpackLogs($inpackdate['order_sn'],$noticesetting['packageit']['describe']);
        }
        //是否计算运费
        $settingdata  = setting::getItem('adminstyle',$inpackOrder['wxapp_id']);
        if(isset($settingdata) && $settingdata['is_auto_free']==1){
            getpackfree($inpack,[]);   
        }
        if (!$res){
            return $this->renderError('打包包裹提交失败');
        }
        return $this->renderSuccess('打包包裹提交成功');
    }
    

    //  计算线路费用
    // public function computeLinePrice($pack,$line,$pack_ids){

    //     $free_rule = json_decode($line['free_rule'],true);  //线路规则
    //     $price = 0; // 总运费
    //     $allWeigth = 0; //总重量
    //     $caleWeigth = 0; //计费重量
    //     $volumn = 0;   //体积重
    //     $pack_free = 0; //包装费
    //     switch ($line['free_mode']) {
    //         //阶梯计费
    //         case '1':
    //             foreach ($pack as $v){
    //                 //计算体积重，按6000规则
    //                 $weigthV = round(($v['length']*$v['width']*$v['height'])/6000,2);
    //                 // 取两者中较重者 
    //                 $oWeigth = $weigthV > $v['weight']?$weigthV:$v['weight'];
             
    //                 foreach ($free_rule as $k => $val) {
    //                   if ($oWeigth >= $val['weight'][0]){
    //                      if (isset($val['weight'][1]) && $oWeigth<$val['weight'][1]){
    //                          $predict = [
    //                              'price' => $oWeigth*$val['weight_price'],
    //                          ];
    //                          continue;   
    //                      }
    //                   }
    //               }
                   
    //               if (!isset($predict['price'])){
    //                   return $this->renderError('线路价格无法预估,请更换线路'); 
    //               }
                   
    //               $price += $predict['price']; // 累加价格
    //               $allWeigth += $v['weight']; // 累加重量
    //               $caleWeigth += $oWeigth; // 累加计费重量 
    //               $volumn += $weigthV; // 累加体积重
    //               $free = $predict['price']; // 更新运费
    //               // 计算包装费用
    //               $packServiesSum = Db::name('package_services')->whereIn('id',explode(',',$pack_ids))->sum('price');
    //               $pack_free = $packServiesSum;
    //             //   (new Package())->where(['id'=>$v['id']])->update($up);
    //             }
    //             break;
    //         //首续重计费   
    //         case '2':
    //             //多个包裹循环计算
    //             foreach ($pack as $v){
    //                 //计算体积重，按6000规则
    //                 $weigthV = round(($v['length']*$v['width']*$v['height'])/6000,2);
    //                 // 取两者中较重者 
    //                 $oWeigth = $weigthV > $v['weight']?$weigthV:$v['weight'];
    //                 $caleWeigth += $oWeigth; // 累加计费重量 
    //                 $volumn += $weigthV; // 累加体积重
    //                 // 计算包装费用
    //                 $packServiesSum = Db::name('package_services')->whereIn('id',explode(',',$pack_ids))->sum('price');
    //                 $pack_free = $packServiesSum;
                    
    //             }
    //                 //累计重量进行计算运费
    //                 if($caleWeigth>$free_rule[0]['first_weight']){
    //                     $linenum = ($caleWeigth-$free_rule[0]['first_weight'])/$free_rule[0]['next_weight'];
    //                     //向上取整，不管0.1也取1
    //                     $price =$free_rule[0]['first_price']+ ceil($linenum)*$free_rule[0]['next_price'];
    //                     $allWeigth = $caleWeigth;
    //                     $caleWeigth = $free_rule[0]['first_weight'] + ceil($linenum)*$free_rule[0]['next_weight'] ;
    //                 }else{
    //                     $price += $free_rule[0]['first_price'];
    //                     $allWeigth = $caleWeigth;
    //                     $caleWeigth = 0.5;
    //                 }
                    
                    
    //             break;
    //         default:
    //             // code...
    //             break;
    //     }
               
    //     $data['allWeigth'] = $allWeigth; //此数值计算无误
    //     $data['free'] = $price;  //此数值计算无误
    //     $data['caleWeigth'] = $caleWeigth;//此数值计算无误
    //     $data['volumn'] = $volumn;  //此参数意义不大，可以忽略
    //     $data['pack_free'] = $pack_free; //包装费 此费用需要根据选择的包裹服务统计包装服务费用
    //     $data['other_free'] = $line['tariff'] + $line['service_route'];  //海关费用+渠道增值服务费
      
    //     return $data;
    // }

    /**
     * 批量修改包裹所属用户
     * 
     * */
    public function changeUser(){
        $ids = $this->postData('selectIds')[0];
        $user_id = $this->postData('package')['user_id'];
        $idsArr = explode(',',$ids);
        $res = (new Package())->whereIn("id",$idsArr)->update(['member_id'=>$user_id,'is_take'=>2,'updated_time'=>getTime()]);
        (new ShelfUnitItem())->whereIn("pack_id",$idsArr)->update(['user_id'=>$user_id]);
        if (!$res){
            return $this->renderError('修改提交失败');
        }
        return $this->renderSuccess('修改提交成功');
    }
    
    /**
     * 修改包裹所属用户
     * 
     * */
    public function changepackageuser(){
        $params = $this->request->param();
        $User = new User();
        $storesetting = setting::getItem('store');
        if($storesetting['usercode_mode']['is_show']==0){
            $userresult = $User::detail($params['user_id']);
            if($userresult){
                $user_id = $params['user_id'];
            }else{
                return $this->renderError('用户不存在');
            }
        }
        if($storesetting['usercode_mode']['is_show']==1){
            $userresult = $User->where('user_code|user_id',$params['user_id'])->where('is_delete',0)->find();
            if($userresult){
                $user_id = $userresult['user_id'];
            }else{
                return $this->renderError('用户不存在');
            }
        }
        
        $res = (new Package())->whereIn("id",$params['package_id'])->update(['member_id'=>$user_id,'is_take'=>2,'updated_time'=>getTime()]);
        if (!$res){
            return $this->renderError('修改提交失败');
        }
        return $this->renderSuccess('修改提交成功');
    }
    
    
    
    /**
     * 根据图片修改归属用户
     * 
     * */
    public function getPackageImages(){
        $ids = $this->request->param()['ids'];
        $idsArr = explode(',',$ids);
        $result = (new PackageImage())->with(['file','package'])->whereIn("package_id",$idsArr)->select();
        // dump($result->toArray());die;
        return $this->renderSuccess('修改提交成功','',$result);
    }
    
    
    // 批量修改货架
    public function changeShelf(){
        $ids = $this->postData('selectIds')[0];
        $shelf_unit_id = $this->postData('shelf')['shelf_unit'];
        $shop_id = $this->postData('shelf')['shop_id'];
        $idsArr = explode(',',$ids);
        $pack = (new Package())->whereIn('id',$idsArr)->select();
        if(!empty($shelf_unit_id)){
            $shelf = (new ShelfUnit())->find($shelf_unit_id);
            if (!$shelf){
                return $this->renderError('货架单元不存在');
            }
        }
        
        //如果仓库id有改变，则进行批量更新归属仓库
        if(!empty($shop_id)){
            (new Package())->whereIn('id',$idsArr)->update(['storage_id'=>$shop_id]);
        }
        
        foreach($pack as $v){
            if(!empty($shelf_unit_id)){
                // 删除原有货物存储位
                $shelfUnitItem =  (new ShelfUnitItem())->where(['pack_id'=>$v['id']])->find();
                if ($shelfUnitItem){
                    if (!$shelfUnitItem){
                        return $this->renderError('货位数据错误');
                    }
                    if ($shelfUnitItem['shelf_unit_id'] == $shelf_unit_id){
                        return $this->renderError('无效转移');
                    }
                    (new ShelfUnitItem())->where(['pack_id'=>$v['id']])->delete();
                }
                $upShelf = [
                  'shelf_unit' => $shelf_unit_id,
                  'express_num' => $v['express_num'],
                  'user_id' => $v['member_id'],
                  'created_time' => getTime(),
                  'pack_id' => $v['id'],
                ];
                $res = (new ShelfUnitItem())->postplus($upShelf);
            }
        }
        return $this->renderSuccess('批量修改包裹位置成功');
    }
    
    
    // 批量修改包裹类型
    public function changetype(){
        $ids = $this->postData('selectIds')[0];
        $params = $this->request->param()['package'];
 
        $idsArr = explode(',',$ids);
        $pack = (new Package())->whereIn('id',$idsArr)->select();
        foreach($pack as $v){
                $v->save(['shop_id'=>$params['shop_id'],'pack_type'=>$params['pack_type']]);
        }
        return $this->renderSuccess('批量修改包裹类型成功');
    }
    
    
    public function shelfDown(){
        $ids = $this->postData('id')[0];
        $model = (new ShelfUnitItem());
        if (!$model->shelfDown($ids)) {
            return $this->renderError($model->getError() ?: '操作失败');
        }
        return $this->renderSuccess('操作成功');  
    }
    
   // 文件导入处理
    public function importdo(){
       $post = request()->param();
       //物流模板设置
       $PackageItem = new PackageItem;
       $noticesetting = setting::getItem('notice');
       
       // 根据Excel实际内容判断是用户编号还是用户ID
       // 优先处理user_code（用户编号），如果存在则转换为member_id
       if (isset($post['user_code']) && !empty($post['user_code'])) {
           $user = (new User())->where('user_code', $post['user_code'])->where('is_delete', 0)->find();
           if (!$user) {
               $post['error'] = "用户编号不存在: " . $post['user_code'];
               return $this->renderError('导入错误','',$post);
           }
           $post['member_id'] = $user['user_id'];
           unset($post['user_code']);
       }
       // 如果Excel中直接提供了member_id（用户ID），则直接使用，不需要转换
       
       $field = [
          'express_num','member_id','express_name','storage_name','weight','usermark'
       ];
       $require_field = [
          'express_num','storage_name'
       ];
       // 检查excel 是否符合格式
       $postKey = array_keys($post);
       if (!$this->checkRequireField($require_field,$post)){
           $post['error'] = "必填参数未填,请检查文档数据";
           return $this->renderError('导入错误','',$post);
       }
       // 检查数据的可用性
       $res = $this->onCheckData($post);
     
       if (!$res['code']){
           $post['error'] = $res['msg'];
           return $this->renderError('导入错误','',$post);
       }
       //当检验结果为code=3，则跳过这条数据的插入，直接update即可
       if ($res['code']==3){
           $post['success'] = $res['msg'];
           return $this->renderSuccess('导入成功','',$post);
       }
       //查询包裹是否存在
       $packdata = (new Package())->where('express_num',$post['express_num'])->where('is_delete',0)->find();
       
       // 确定member_id：优先使用Excel中提供的，如果没有则使用原有包裹的
       $memberId = null;
       if(isset($post['member_id']) && !empty($post['member_id'])){
           $memberId = $post['member_id'];
       } elseif($packdata && !empty($packdata['member_id'])){
           $memberId = $packdata['member_id'];
       }
       
       $postData = [
            'order_sn' => $packdata ? $packdata['order_sn'] : createSn(),
            'status' => 2,
            'member_id' => $memberId,
            'member_name' => isset($res['data']['member']['nickName'])?$res['data']['member']['nickName']:($packdata ? $packdata['member_name'] : ''),
            'express_num' =>$post['express_num'],
            'express_name'=>isset($post['express_name'])?$post['express_name']:($packdata ? $packdata['express_name'] : '其他'),
            'storage_id' => isset($res['data']['storage']['shop_id'])?$res['data']['storage']['shop_id']:($packdata ? $packdata['storage_id'] : ''),
            'length'=> isset($post['length'])?$post['length']:($packdata ? $packdata['length'] : 0),
            'width'=> isset($post['width'])?$post['width']:($packdata ? $packdata['width'] : 0),
            'height'=> isset($post['height'])?$post['height']:($packdata ? $packdata['height'] : 0),
            'usermark'=> isset($post['usermark'])?$post['usermark']:($packdata ? $packdata['usermark'] : ''),
            // 'num'=>isset($post['num'])?$post['num']:$packdata['num'],
            'volume'=>isset($post['volume'])?$post['volume']:($packdata ? $packdata['volume'] : 0),
            'weight'=>isset($post['weight'])?$post['weight']:($packdata ? $packdata['weight'] : 0),
            'entering_warehouse_time'=> getTime(),
            'created_time'=> $packdata ? $packdata['created_time'] : getTime(),
            'updated_time'=> getTime(),
            'source'=>4,
            'wxapp_id' => (new Package())->getWxappId(),
            'is_take'=> !empty($memberId) ? 2 : 1
       ];
       
        
       if(!empty($postData['member_id']) && !empty($postData['usermark'])){
           $usermark = (new UserMarkModel())->where(['user_id'=>$postData['member_id'],'mark'=>$postData['usermark']])->find();
           if(empty($usermark)){
               (new UserMarkModel())->save([
                    'user_id'=>$postData['member_id'],
                    'mark'=>$postData['usermark'],
                    'wxapp_id'=>(new Package())->getWxappId(),
                    'create_time'=>time()
               ]);
           }
       }
       
       if(empty($packdata)){
            $res = (new Package())->insertGetId($postData);
            $postData['id'] = $res;
            (new Package())->sendEnterMessage([$postData]);
       }else{
            $packdata->save($postData);
            (new Package())->sendEnterMessage([$packdata->toArray()]);
           $res = $packdata['id'];
       }
       $PackageItem->save([
            'order_id'=>$res,
            'express_num'=>$postData['express_num'],
            'class_name'=>isset($post['class_name'])?$post['class_name']:'',
            'product_num'=>isset($post['product_num'])?$post['product_num']:$packdata['product_num'],
            'wxapp_id' => (new Package())->getWxappId(),
       ]);
       if (!$res){
           $post['error'] = '未知错误';
           return $this->renderError('导入错误','',$post);
       }
       if($noticesetting['enter']['is_enable']==1){
           Logistics::add($res,$noticesetting['enter']['describe']);
       }
      
       $post['success'] = '导入成功';
       return $this->renderSuccess('导入成功','',$post);
    }
    
   // 数据检查
    public function onCheckData($post){
        //物流模板设置
        $noticesetting = setting::getItem('notice');
        $data = [];
        
        // 根据Excel实际内容判断是用户编号还是用户ID
        // 优先处理user_code（用户编号），如果存在则转换为member_id
        if (isset($post['user_code']) && !empty($post['user_code'])) {
            $user = (new User())->where('user_code', $post['user_code'])->where('is_delete', 0)->find();
            if (!$user) {
                return ['code'=>0,'msg'=>'用户编号不存在: ' . $post['user_code']];
            }
            $post['member_id'] = $user['user_id'];
            unset($post['user_code']);
        }
        
        // 如果Excel中提供了member_id（用户ID），则验证用户是否存在
        if(isset($post['member_id']) && !empty($post['member_id'])){
            $member = (new User())->find($post['member_id']);
            if (!$member){
                return ['code'=>0,'msg'=>'用户不存在,请检查用户ID: ' . $post['member_id']];
            }
            if ($member['is_delete']==1){
                return ['code'=>0,'msg'=>'用户已被删除,请检查用户ID: ' . $post['member_id']];
            }
            $data['member'] = $member;
        }
        
        if(isset($post['weight'])){
            $data['weight'] = $post['weight'];
        }
        //检查仓库名称
        if(isset($post['storage_name'])){
            $storage = (new ShopModel())->where(['shop_name'=>$post['storage_name']])->find();
            if (!$storage){
                return ['code'=>0,'msg'=>'仓库不存在,请检查仓库名称'];
            }
             $data['storage'] = $storage;
        }
        
        if(isset($post['express_name'])){
            $express = (new Express())->where(['express_name'=>$post['express_name']])->find();
            if (!$express){
                 return ['code'=>0,'msg'=>'物流不存在,请检查物流名称'];
            }
            $data['express'] = $express;
        }
        
        if(isset($post['express_num'])){
            $sn = (new Package())->where(['express_num'=>$post['express_num']])->find();
           
            if(!$sn['storage_id']){
                 $update['storage_id'] = isset($data['storage']['shop_id'])?$data['storage']['shop_id']:'';
            }
            
            if ($sn && ($sn['status']==1 || $sn['status']==2 || $sn['status']==3)){
                // 如果Excel中提供了member_id，则更新绑定（即使包裹已有member_id也要更新）
                if(isset($post['member_id']) && !empty($post['member_id'])){
                    $update['member_id'] = $post['member_id'];
                    $update['member_name'] = isset($data['member']['nickName'])?$data['member']['nickName']:'';
                    $update['is_take'] = 2;
                } elseif(!$sn['member_id'] && isset($post['member_id'])){
                    // 如果包裹没有member_id，且Excel中提供了，则绑定
                    $update['member_id'] = $post['member_id'];
                    $update['member_name'] = isset($data['member']['nickName'])?$data['member']['nickName']:'';
                    $update['is_take'] = 2;
                } elseif(!$sn['member_id']){
                    // 如果包裹没有member_id，且Excel中也没有提供，则保持未绑定状态
                    $update['is_take'] = 1;
                }
                
                if($sn['status'] == 1){
                     $update['status'] = 2;
                }
                
                $update['weight'] = isset($post['weight'])?$post['weight']:0.000;
                $update['entering_warehouse_time'] = getTime();
                $update['express_name'] = isset($post['express_name'])?$post['express_name']:'其他';
                $res= (new Package())->where(['express_num'=>$post['express_num']])->update($update);
                $data = (new Package())->where(['express_num'=>$post['express_num']])->find();
                if($res){
                    if($noticesetting['enter']['is_enable']==1){
                        Logistics::add($data['id'],$noticesetting['enter']['describe']);
                    }
                    $sub =  (new Package())->sendEnterMessage([$data->toArray()]);
                    return ['code'=>3,'msg'=>'快递单号客户已预报,状态成功修改为已入库'];
                }
                $sub =  (new Package())->sendEnterMessage([$data->toArray()]);
                return ['code'=>3,'msg'=>'快递单号客户已预报,状态修改失败，请手动调整为入库'];
            }
            if ($sn && $sn['status']==2){
                return ['code'=>0,'msg'=>'快递单号已入库,请勿重复入库'];
            }
            
            // if ($sn['status']==2){
            //     return ['code'=>0,'msg'=>'快递单号已入库,请勿重复入库'];
            // }
            if($sn){
                $data['sn'] = $sn;
            }
        }else{
             return ['code'=>0,'msg'=>'快递单号不能为空'];
        }
        
        
        return ['code'=>1,'msg'=>'数据验证通过','data' =>$data];
    }
    
    // 检查必要字段是否为空
    public function checkRequireField($field,$post){
        foreach ($field as $v){
            if (!isset($post[$v]) || empty($post[$v])){
                 return false;
            }
        }          
        return true;
    }
    
    //包裹删除功能
    public function delete($id)
    {
        $model = new Package();
        if (!$model->setDelete($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
    
    // 包裹评论
    public function comment($id){
        $list = (new Comment())->getCommentById($id);
        return $this->fetch('comment', compact('list'));
    }
    
    public function commentdelete($id){
         // 店员详情
        $model = (new Comment())->details($id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

   /**
    * 编辑包裹信息
    * 保存修改的值
    * /store/package.index/edit
    */
    public function save($data){
        
        $list = [
            "id" => $data['id'],
            "storage_id" => $data['store_id'],
            "country_id" => $data['country'],
            'express_num'=>$data['express_num'],
            'express_id'=>$data['express_id'],
            "price" => $data['price'],
            'weight' => $data['weight'],
            'length' => $data['length'],
            'width' => $data['width'],
            'member_id'=>isset($data['user_id'])?$data['user_id']:0,
            'height' => $data['height'],
            "admin_remark" => $data['admin_remark'],
            'visit_free' => isset($data['visit_free'])?$data['visit_free']:0,
            'is_take' =>!empty($data['user_id'])?2:1,
            "updated_time" => getTime(),
            'class_ids'=> $data['class_ids']
        ];
        if(!empty($list['length']) && !empty($list['width']) && !empty($list['height'])){
            $list['volume'] = $list['length']*$list['width']*$list['height']/1000000;
        }
        
        $model = new Package();
        $PackageImage =  new PackageImage();
         
         //如果存在class_ids
         if(isset($data['class_ids']) && !empty($data['class_ids'])){
           if(!$model->doClassIds($data['class_ids'],$data['express_num'],$data['id'])){
                return $this->renderError('类目更新失败'); 
            }
         }
        //更新图片
        if(isset($data['images'])){
            (new $PackageImage)->where('package_id',$data['id'])->delete(); 
            foreach ($data['images'] as $key =>$val){
                 $result = (new $PackageImage)->where('package_id',$data['id'])->where('image_id',$val)->find();
                 if(!isset($result)){
                     $update['package_id'] = $data['id'];
                     $update['image_id'] = $val;
                     $update['wxapp_id'] = $model->getWxappId();
                     $update['create_time'] = strtotime(getTime());

                     $res= (new PackageImage())->save($update);
                     
                     if(!$res){
                          return $this->renderError('图片更新失败');
                     }
                 }
            }
        }else{
            (new $PackageImage)->where('package_id',$data['id'])->delete(); 
        }
        
        //更新包裹所在货架
        $packDetail = $model->detail($data['id']);
        // dump($data);die;
        if(isset($data['shelf_unit_id'])){
              $ress = (new ShelfUnitItem())->where('pack_id',$packDetail['id'])->find();
            // dump($data);die;
              $shelf_unit_data =[
              'shelf_unit_id' => $data['shelf_unit_id'],
              'pack_id' => $data['id'],
              'wxapp_id' => $model->getWxappId(),
              'created_time' => getTime(),
              'express_num' => $packDetail['express_num'] ,
              'user_id' =>$packDetail['member_id'],
             ];
             
             if($ress){
                 (new ShelfUnitItem())->where('pack_id',$packDetail['id'])->update(['shelf_unit_id' => $data['shelf_unit_id']]);
             }else{
                 (new ShelfUnitItem())->allowField(true)->save($shelf_unit_data);  
             }
        }
      
        if($model->setSave($list)){
            return $this->renderSuccess('操作成功','javascript:history.back(1)');
        };
         return $this->renderError('更新失败');
        
    }
    
    // 扫码入库  
    public function scan(){
        $shopList = ShopModel::getAllList();
        return $this->fetch('scan', compact('list','shopList'));
    }
    
    // 扫码出库  
    public function scanout(){
        $batchlist = (new Batch())->getAllwaitList();
        return $this->fetch('scanout', compact('list','batchlist'));
    }
    
    //扫码出库
    public function scanoutshop(){
        $code = request()->param('barcode');
        $batch_id = request()->param('batch_id');
        $data = (new Package())->alias('a')->field('a.id,a.storage_id,a.is_scan,a.wxapp_id,a.order_sn,u.nickName,a.member_id,s.shop_name,a.status as a_status,a.entering_warehouse_time,a.pack_free,a.source,a.is_take,a.free,a.express_num,a.express_name, a.length, a.width, a.height, a.weight,a.price,a.real_payment,a.remark,c.title')->join('user u', 'a.member_id = u.user_id',"LEFT")
            ->join('countries c', 'a.country_id = c.id',"LEFT")
            ->join('store_shop s', 'a.storage_id = s.shop_id',"LEFT")
            ->where(['express_num'=>$code])
            ->where('a.is_delete',0)
            ->find();
     
            if (empty($data)){
                // 入库标记为待认领
                $data['express_num'] = $code;
                $data['status'] = 2;
                $data['storage_id'] = 0;
                $data['is_take'] = 1;
                $data['err'] = '库中未查到到该包裹,请先入库';
                $data['opTime'] = getTime(); 
                $return = [
                   'success' =>false,
                   'data' => $data,
                ];
                 
                return $this->renderSuccess($data['err'],'',$return);
                die;
            }
            $param = ['is_scan'=> 2,'status'=>4,'updated_time'=>getTime()];
            if($batch_id>0){
                $param['status'] = 7;
                $param['batch_id'] = $batch_id;
            }
            $res = $data->save($param);
            if ($res){
               $data['err'] = '检测到包裹,包裹已标记出库';
               $data['is_scan'] = 2;
               $data['opTime'] = getTime(); 
               $return = ['success' =>true,'data' => $data];
               return $this->renderSuccess($data['err'],'',$return);
            }
    }
    
    // 扫码结果 并更新 状态
    public function scanResult(){
        $type = input('op');
        $form = input('form','scan');
        $code = request()->param('barcode');
        $shop_id = request()->param('shop_id');
        $data = (new Package())->alias('a')->field('a.id,a.storage_id,a.is_scan,a.wxapp_id,a.order_sn,u.nickName,a.member_id,s.shop_name,a.status as a_status,a.entering_warehouse_time,a.pack_free,a.source,a.is_take,a.free,a.express_num,a.express_name, a.length, a.width, a.height, a.weight,a.price,a.real_payment,a.remark,c.title')->join('user u', 'a.member_id = u.user_id',"LEFT")
            ->join('countries c', 'a.country_id = c.id',"LEFT")
            ->join('store_shop s', 'a.storage_id = s.shop_id',"LEFT")
            ->where(['express_num'=>$code])
            ->where('a.is_delete',0)
            ->find();
     
 
                if (empty($data)){
                    // 入库标记为待认领
                    $data['order_sn'] = createSn();
                    $data['express_num'] = $code;
                    $data['status'] = 2;
                    $data['storage_id'] = $shop_id;
                    $data['is_take'] = 1;
                    $data['source'] = 2;
                    $data['wxapp_id'] = (new Package())->getWxappId();
                    $data['entering_warehouse_time'] = getTime();
                    $data['created_time'] = getTime();
                    $data['updated_time'] = getTime();
                    $res = (new Package())->insert($data);
                    $data['err'] = '包裹库中未查到,已自动入库，标记为未认领';
                     $data['opTime'] = getTime(); 
                    $return = [
                       'success' =>false,
                       'data' => $data,
                    ];
                    return $this->renderSuccess($data['err'],'',$return);
                    die;
                }
                
                if ($data['a_status']!=1){
                    $err = '该包裹未处于待入库状态';
                    $data['err'] = $err;
                    $data['opTime'] = getTime(); 
                    $return = [
                       'success' =>false,
                       'data' => $data,
                    ];
                    return $this->renderSuccess($err,'',$return);
                    die;
                }
                $update['status'] = 2;
                $update['entering_warehouse_time'] = getTime();
                
                $res = $data->save($update);
    
                if ($res){
                    $data['err'] = '成功入库';
                    $data['opTime'] = getTime(); 
                    $return = [
                       'success' =>true,
                       'data' => $data,
                    ];
                   $data['entering_warehouse_time'] =$update['entering_warehouse_time'];
                   $sub =  (new Package())->sendEnterMessage([$data->toArray()]);
                    //  dump($sub);die;
                   return $this->renderSuccess($data['err'],'',$return);
                   die;
                }
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
        $map =[''=>'',-1=>'问题件',1=>'待入库',2=>'已入库',3=>'已分拣上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        
        if($ids){
           $data = (new Package())->with(['categoryAttr'])->whereIn('id',$ids)->select()->each(function ($item, $key) use($map){
                    $item["user"] = (new User())->where('user_id',$item['member_id'])->field('user_id,nickName,mobile')->find();
                    $item['status_text'] = $map[$item['status']];
                    $item['phone'] =(new UserAddress())->where('user_id',$item['member_id'])->find();
                    $item['volumeweight'] = $item['width']*$item['height']*$item['length']/6000;
                    return $item;
                }); 
        }else{
            // dump($seach);die;
            if(!empty($seach['search'])){
                 $where['member_id'] = $seach['search']; //用户id
            }
            if(!empty($seach['status'])){
                 $where['status'] = $seach['status'];    //包裹状态
            }
            if(!empty($seach['start_time']) && !empty($seach['end_time'])){
                //  $wheretime['start_time'] = $seach['start_time'];  //起始时间
                 $where['entering_warehouse_time']=['between',[$seach['start_time'],$seach['end_time']]];
            }
            if(!empty($seach['extract_shop_id'])){
                $where['storage_id'] = $seach['extract_shop_id'];  //仓库
            }
            if(!empty($seach['express_num'])){
                 $where['express_num'] = $seach['express_num'];  //快递单号
            }
            $where['is_delete'] = 0; 
            $data =(new Package())->where($where)->select()->each(function ($item, $key) use($map){
                    $item["user"] = (new User())->where('user_id',$item['member_id'])->field('user_id,nickName,mobile')->find();
                    $item['status_text'] = $map[$item['status']];
                
                    $item['phone'] =(new UserAddress())->where('user_id',$item['member_id'])->find();
                    
                    $item['volumeweight'] = $item['width']*$item['height']*$item['length']/6000;
                    return $item;
                });
            // dump((new Package())->getLastsql());die;
            
        }
        // dump($data->toArray());die;
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '包裹单号')
                ->setCellValue('B1', '用户编号')
                ->setCellValue('C1', '姓名')
                ->setCellValue('D1', '手机号')
                ->setCellValue('E1', '包裹状态')
                ->setCellValue('F1', '入库时间')
                ->setCellValue('G1', '重量')
                ->setCellValue('H1', '唛头')
                ->setCellValue('I1', '长度')
                ->setCellValue('J1', '宽度')
                ->setCellValue('K1', '高度')
                ->setCellValue('L1', '总重量')
                ->setCellValue('M1', '总体积')
                ->setCellValue('N1', '体积重')
                ->setCellValue('O1', '货品')
                ->setCellValue('P1', '数量')
                ->setCellValue('Q1', '创建时间')
                ;
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:Q')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:Q1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A:Q')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        
        //设置行高
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
        //设置颜色

        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('N')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('O')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('P')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('Q')->setWidth(30);
        // $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        for($i=0;$i<count($data);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$data[$i]['express_num'].' ');//ID
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$data[$i]['user']['user_id']);//标签码
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$data[$i]['user']['nickName']);//防伪码
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$data[$i]['phone']?($data[$i]['phone']['phone'].' '):($data[$i]['user']['mobile'].' '));//ID
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$data[$i]['status_text']);//标签码
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$data[$i]['entering_warehouse_time']);//ID
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+2),$data[$i]['weight']);//标签码
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+2),$data[$i]['usermark']);//防伪码
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+2),$data[$i]['length']);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+2),$data[$i]['width']);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($i+2),$data[$i]['height']);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.($i+2),$data[$i]['weight']);
            $objPHPExcel->getActiveSheet()->setCellValue('M'.($i+2),$data[$i]['volume']);
            $objPHPExcel->getActiveSheet()->setCellValue('N'.($i+2),$data[$i]['volumeweight']); //体积重
            $objPHPExcel->getActiveSheet()->setCellValue('O'.($i+2),isset($data[$i]['category_attr'][0])?$data[$i]['category_attr'][0]['class_name']:''); //货品
            $objPHPExcel->getActiveSheet()->setCellValue('P'.($i+2),isset($data[$i]['category_attr'][0])?$data[$i]['category_attr'][0]['product_num']:''); //数量
            $objPHPExcel->getActiveSheet()->setCellValue('Q'.($i+2),$data[$i]['created_time']);
        }
        //7.设置保存的Excel表格名称
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('包裹列表');
        //9.设置浏览器窗口下载表格
        $filename = "用户包裹"  . rand(10000, 99999) . ".xlsx";
        // $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $ov = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $ov->save("excel/" . $filename);
        return $this->renderSuccess("导出成功", [
            "file_name" => "https://".$_SERVER["HTTP_HOST"] . "/excel/" . $filename,
        ]);
     }
    
}

