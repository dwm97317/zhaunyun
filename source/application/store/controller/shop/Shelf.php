<?php
namespace app\store\controller\shop;

use app\store\controller\Controller;

use app\store\model\User;
use app\store\model\store\Shop as ShopModel;
use app\store\model\store\shop\Clerk as ClerkModel;
use app\store\model\Shelf as ShelfModel;
use app\store\model\ShelfUnit as ShelfUnitModel;
use app\store\model\ShelfUnit;
use app\store\model\ShelfUnitItem;
use app\common\library\FileZip;
use app\common\model\setting;
use app\store\model\Package;
/**
 * 门店店员控制器
 * Class Clerk
 * @package app\store\controller\shop
 */
class Shelf extends Controller
{
    public function index(){
        $map['ware_no'] = $this->store['user']['shop_id'];
        $list = (new ShelfModel())->getList($map);
        foreach ($list as &$value) {
            $value['num'] = (new ShelfUnit())->where(['shelf_id'=>$value['id']])->count();
        }
        return $this->fetch('index', compact('list'));
    }
    
     /**
     * 查看货位下的包裹单号列表页面
     * @return mixed
     */
    public function viewShelfUnitPackages(){
        $shelf_unit_id = $this->request->param('shelf_unit_id');
        $status = $this->request->param('status', '');
        $set = Setting::detail('store')['values'];
        
        if (empty($shelf_unit_id)) {
            return $this->renderError('货位ID不能为空');
        }
        
        // 获取货位信息
        $shelfUnit = (new ShelfUnit())->with(['shelf'])->find($shelf_unit_id);
        
        if (!$shelfUnit) {
            return $this->renderError('货位不存在');
        }
        
        // 构建查询条件
        $shelfUnitItemModel = new ShelfUnitItem();
        $packageModel = new Package();
        
        // 如果设置了状态筛选，使用 JOIN 查询
        if ($status !== '') {
            // 获取包裹表名
            $packageTable = $packageModel->getTable();
            
            $shelfUnitItems = $shelfUnitItemModel
                ->alias('sui')
                ->join($packageTable . ' p', 'sui.pack_id = p.id')
                ->where('sui.shelf_unit_id', $shelf_unit_id)
                ->where('p.status', $status)
                ->field('sui.*')
                ->with(['user', 'shelfunit.shelf'])
                ->order('p.status desc')
                ->select();
        } else {
            // 没有状态筛选，直接查询
            $packageTable = $packageModel->getTable();
            $shelfUnitItems = $shelfUnitItemModel
                ->alias('sui')
                ->join($packageTable . ' p', 'sui.pack_id = p.id')
                ->where('sui.shelf_unit_id', $shelf_unit_id)
                ->field('sui.*')
                ->with(['user', 'shelfunit.shelf'])
                ->order('p.status desc')
                ->select();
        }
        
        // 组装数据
        $packageList = [];
        if ($shelfUnitItems) {
            foreach ($shelfUnitItems as $item) {
                $packageList[] = [
                    'express_num' => $item['package']['express_num'] ?? '-',
                    'user' => $item['user'] ?? null,
                    'created_time' => $item['created_time'] ?? '',
                    'package' => $item['package'] ?? null,
                ];
            }
        }
        
        return $this->fetch('view_shelf_unit_packages', compact('shelfUnit', 'packageList', 'set'));
    }
    
    /**
     * 批量从货位删除包裹
     * @return mixed
     */
    public function batchDeletePackagesFromShelf(){
        $pack_ids = $this->request->param()['selectIds'];
        if (empty($pack_ids) || !is_array($pack_ids)) {
            return $this->renderError('请选择要下架的包裹');
        }
        
        // 批量删除货位项记录
        $res = (new ShelfUnitItem())->where('pack_id', 'in', $pack_ids)->delete();
        
        if (!$res) {
            return $this->renderError('批量下架失败');
        }
        return $this->renderSuccess('批量下架成功');
    }
    
    /**
     * 从货位删除单个包裹
     * @return mixed
     */
    public function deletePackageFromShelf(){
        $pack_id = $this->request->param('pack_id');
        
        if (empty($pack_id)) {
            return $this->renderError('包裹ID不能为空');
        }
        
        // 删除货位项记录
        $res = (new ShelfUnitItem())->where(['pack_id' => $pack_id])->delete();
        
        if (!$res) {
            return $this->renderError('删除失败');
        }

        return $this->renderSuccess('下架成功');
    }
    
    /**
     * 新增货架
     */
    public function add(){
      $shopList = ShopModel::getAllList();
      if (!$this->request->isAjax()) {
        return $this->fetch('add',compact('shopList'));
      }
   
      // 新增记录
      $model = new ShelfModel();
      if ($model->add($this->postData('shelf'))) {
          return $this->renderSuccess('添加成功',url('/store/shop.shelf/index'));
      }
      return $this->renderError($model->getError() ?: '添加失败');
    }
    
    /**
     * 删除货架 
     */
    public function shelfdelete($id){
         $model = (new ShelfModel())->details($id);
         if (!$model->setDelete($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功',url('/store/shop.shelf/index'));
    } 
    
    
    public function edit($id){
        // 模板详情
        $model = (new ShelfModel())->details($id);
        if (!$this->request->isAjax()) {
            $shopList = ShopModel::getAllList();
            return $this->fetch('edit', compact('model','shopList'));
        }
        // 更新记录
        if ($model->edit($this->postData('shelf'))) {
            return $this->renderSuccess('更新成功', url('/store/shop.shelf/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    
    public function editshelfunit($shelf_unit_id){
        // 模板详情
        $model = (new ShelfUnitModel())->details($shelf_unit_id);
        if (!$this->request->isAjax()) {
            $shelfList = (new ShelfModel)->getAllList([]);
            return $this->fetch('shelfunitedit', compact('model','shelfList'));
        }
        // 更新记录
        if ($model->edit($this->postData('shelf'))) {
            return $this->renderSuccess('更新成功', url('/store/shop.shelf/datashelfunit'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    public function printshelfunit() {
        // 1. 获取选择的货位ID
        $selectIds = $this->postData("selectIds");
        if (empty($selectIds)) {
            throw new Exception("未选择任何货位");
        }
    
        // 2. 查询货位数据
        $ShelfUnitModel = new ShelfUnitModel();
        $data = $ShelfUnitModel->with(['user','shelf'])->where('shelf_unit_id','in',$selectIds)->select();
        if (count($data)==0) {
            throw new Exception("未找到货位数据");
        }
        require_once APP_PATH . '/common/library/phpqrcode/phpqrcode.php';
        $QRcode = new \QRcode();
        // 3. 生成条形码（调整为更适合标签的尺寸）
        $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorSVG();
       
        $htmlArray = [];
        $html = '';
        $setting = Setting::getItem('store',$data[0]['wxapp_id']);
        
        foreach ($data as $item) {
            $shelfNo = (string)$item['shelf_unit_no'];
            $item['barcode'] = $generatorSVG->getBarcode($item['shelf_unit_no'], $generatorSVG::TYPE_CODE_128, 2, 50);
            if($setting['usercode_mode']['is_show']==0){
                $item['user_id'] = $item['user']['user_code'];
            }
            $userDisplay = '';
            if (!empty($item['user']) || !empty($item['user_id'])) {
                $userDisplay = '<tr>
                    <td class="center font_xxxl">'.($item['user']['nickName'] ?? '').($item['user_id'] ?? '').'</td>
                </tr>';
            }
            if ($item['shelf'] && $item['shelf']['barcode_type']==10) {
                   // 生成二维码 - 使用 phpqrcode
                ob_start();
                $QRcode::png($item['shelf_unit_no'], null, QR_ECLEVEL_L, 8, 2);
                $qrCodeImage = ob_get_clean();
                $codeHtml = '<img src="data:image/png;base64,' . base64_encode($qrCodeImage) . '" />';
            } else {
                // 默认生成条形码
                $codeHtml = $generatorSVG->getBarcode($item['shelf_unit_no'], $generatorSVG::TYPE_CODE_128, 2, 50);
            }
            $item['barcode'] = $codeHtml;
            $html = $html. '<style>
                * {
                	margin: 0;
                	padding: 0;
                	font-family: ttt, sans-serif;
                	
                }
                .font_xl {
            		font-size: 18px;
            		font-weight: bold
            	}
                .font_xxxl {
                    font-size: 24px;
                    font-weight: bold;
                }
            	table {
            		margin-top: -1px;
            		border-collapse: collapse
            	}
            	table.nob{
            	    width:100%;
            	}
                table.nob td {
            		border: 0
            	}
            	table.container {
            		width: 375px;
            		height:200px;
            		border: 1px solid #000;
            		border-bottom: 0
            		text-align:center;
            		margin-top:10px;
            	}
            
            	table td {
            		border-top: 1px solid #000;
            		border-bottom: 1px solid #000
            	}
            	.center{text-align:center;}
            	.pt20{ padding-top:20px;}
            	.pb20{ padding-bottom:10px;}
            </style>
            <table class="container">
                <tr>
            		<td  class="center">
            			<table class="nob">
            				<tr center>
            					<td class="center pt20">'.$item['barcode'].'</td>
            				</tr>
            				<tr>
            					<td class="center font_xl pb20">'.$item['shelf_unit_no'].'</td>
            				</tr>
            			</table>
            		</td>
            	</tr>
               '.$userDisplay.'
            </table>';
        }
        return $html;
    }

    // 货位数据
    public function dataShelfUnit(){
       $set = Setting::detail('store')['values'];
       $params = $this->request->param();
       if(isset($params['user_id']) && $set['usercode_mode']['is_show']==0){
           $params['user_id'] = $params['user_id'];
       }
       if(isset($params['user_id']) && $set['usercode_mode']['is_show']==1){
           $params['user_code'] = $params['user_id'];
           unset($params['user_id']);
       }
       $params['ware_no'] = $this->store['user']['shop_id'];
       
       $data = (new ShelfUnit())->getAllList($params);
       return $this->fetch('datashelfunit', compact('data','set'));
    }
    
    public function reset(){
         $ids = $this->postData('selectId');
         $model = (new ShelfUnit()); 
         $res = $model->resetCode($ids);
         if ($res){
             return $this->renderSuccess('生成成功');
         }
         return $this->renderError('生成失败');
    }
    
    public function download(){
         $ids = $this->postData('selectId');
         $model = (new ShelfUnit()); 
         $list = $model->where('shelf_unit_id','in',$ids)->select();
         $res = FileZip::init($list);
         return $this->renderSuccess($res);
    }
}