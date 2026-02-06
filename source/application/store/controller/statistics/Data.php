<?php

namespace app\store\controller\statistics;

use app\store\controller\Controller;
use app\store\service\statistics\Data as StatisticsDataService;
use app\store\model\Inpack;
use think\Db;
use app\store\model\store\shop\Clerk;
use app\store\model\User as UserModel;
use app\store\model\Package as PackageModel;
/**
 * 数据概况
 * Class Data
 * @package app\store\controller\statistics
 */
class Data extends Controller
{
    /* @var $statisticsDataService StatisticsDataService 数据概况服务类 */
    private $statisticsDataService;

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
        $this->statisticsDataService = new StatisticsDataService;
    }
    
    /**
 * 统计用户首次入库时间
 * @return mixed
 * @throws \think\Exception
 */
public function firstenter()
{ 
    $param = $this->request->param();
    $startTime = input('start_time', date('Y-m-01'));
    $endTime = input('end_time', date('Y-m-d'));
    $UserModel = new UserModel;
    $Clerk = new Clerk;
    $PackageModel = new PackageModel;
    $where = [];
    
    // 如果有客服筛选条件
    if(!empty($param['service_id'])){
        $where['u.service_id'] = $param['service_id'];
    }
    
    // 获取有权限的客服列表
    $servicelist = $Clerk->where('clerk_authority','like','%is_myuser%')
                        ->where('clerk_authority','like','%is_myuserpackage%')
                        ->where('is_delete',0)
                        ->select();
    
    // 步骤1: 获取在时间范围内首次入库的客户
    $firstEnterQuery = $PackageModel
        ->field('member_id, MIN(entering_warehouse_time) AS first_enter_time')
        ->where('entering_warehouse_time', 'between', [$startTime, $endTime])
        ->where('is_delete',0)
        ->where('wxapp_id',10001)
        ->group('member_id');
    
    // 步骤2: 统计这些客户在时间范围内的总包裹数
    $list = $UserModel
        ->alias('u')
        ->join([$firstEnterQuery->buildSql() => 'fc'], 'u.user_id = fc.member_id')
        ->join('package ep', 'fc.member_id = ep.member_id AND ep.entering_warehouse_time BETWEEN :start AND :end')
        ->where($where)  // 添加客服筛选条件
        ->where('ep.is_delete',0)
        ->bind(['start' => $startTime, 'end' => $endTime])
        ->field('u.user_id, u.nickName, u.mobile, u.service_id, fc.first_enter_time, COUNT(ep.express_num) AS total_packages')
        ->group('u.user_id, u.nickName, fc.first_enter_time')
        ->order('total_packages desc')
        ->select();
    
    // 获取所有客服信息用于显示客服名称
    $clerkList = $Clerk->column('real_name', 'clerk_id');
    foreach($list as &$item){
        $item['service_name'] = $clerkList[$item['service_id']] ?? '无归属客服';
    }
    
    return $this->fetch('firstenter',compact('list','servicelist'));  
}
    /**
     * 数据统计主页
     * @return mixed
     * @throws \think\Exception
     */
    public function index()
    {
        return $this->fetch('index', [
            // 数据概况
            'survey' => $this->statisticsDataService->getSurveyData(),
            // 近七日交易走势
            'echarts7days' => $this->statisticsDataService->getTransactionTrend(),
            // 商品销售榜
            'goodsRanking' => $this->statisticsDataService->getGoodsRanking(),
            // 用户消费榜
            'userExpendRanking' => $this->statisticsDataService->geUserExpendRanking(),
        ]);
    }
    
    public function datascreen(){
        $wxapp_id = $this->getWxappId();
        $url = base_url()."datascreen/?wxapp_id=".$wxapp_id;
        return $this->fetch('datascreen',compact('url'));
    }
    
    //集运类目排行榜    
    public function category(){
      return $this->fetch('category', ['categoryRanking' => $this->statisticsDataService->getCategoryRanking()]); 
    }
    
    //国家排行榜    
    public function country(){
      return $this->fetch('country', ['countryRanking' => $this->statisticsDataService->geOrderCountryRanking()]); 
    }

     //渠道排行榜    
    public function ditch(){
      return $this->fetch('ditch', ['ditchRanking' => $this->statisticsDataService->geOrderDitchRanking()]); 
    }
    
    //集运订单排行榜    
    public function inpackorder(){
      $Inpack = new Inpack;
      $param = $this->request->param();
      $start = date("Y-m-1",time());
      $end = date("Y-m-d",time()+86400);
      if(isset($param['start_time']) && isset($param['end_time'])){
          $start = $param['start_time'];
          $end = date("Y-m-d",strtotime($param['end_time'])+86400);
      }
    $orderinpack = [
        0=> [
            'mouth'=>1,
            'pay_type'=> '月结',
            'totalprice'=>$Inpack->whereBetween('created_time',[$start,$end])->where('pay_type',2)->where('is_delete',0)->SUM("free+pack_free+other_free"),
            'haspay'=>$Inpack->whereBetween('created_time',[$start,$end])->where('pay_type',2)->where('is_pay',1)->where('is_delete',0)->SUM("real_payment"),
            'ordernum'=>$Inpack->whereBetween('created_time',[$start,$end])->where('pay_type',2)->where('is_delete',0)->count(),
            'customnum'=>$Inpack->whereBetween('created_time',[$start,$end])->where('pay_type',2)->where('is_delete',0)->count('DISTINCT member_id'),
        ],
        1=>[
            'mouth'=>1,
            'pay_type'=> '货到付款',
            'totalprice'=>$Inpack->whereBetween('created_time',[$start,$end])->where('pay_type',1)->where('is_delete',0)->SUM("free+pack_free+other_free"),
            'haspay'=>$Inpack->whereBetween('created_time',[$start,$end])->where('pay_type',1)->where('is_pay',1)->where('is_delete',0)->SUM("real_payment"),
            'ordernum'=>$Inpack->whereBetween('created_time',[$start,$end])->where('pay_type',1)->where('is_delete',0)->count(),
            'customnum'=>$Inpack->whereBetween('created_time',[$start,$end])->where('pay_type',1)->where('is_delete',0)->count('DISTINCT member_id'),
        ]
      ]; 
      return $this->fetch('inpackorder', [
          'ditchRanking' => $orderinpack]
      ); 
    }
    

    /**
     * 数据概况API
     * @param null $startDate
     * @param null $endDate
     * @return array
     * @throws \think\Exception
     */
    public function survey($startDate = null, $endDate = null)
    {
        return $this->renderSuccess('', '',
            $this->statisticsDataService->getSurveyData($startDate, $endDate));
    }

}