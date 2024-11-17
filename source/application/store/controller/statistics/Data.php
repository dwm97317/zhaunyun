<?php

namespace app\store\controller\statistics;

use app\store\controller\Controller;
use app\store\service\statistics\Data as StatisticsDataService;
use app\store\model\Inpack;

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
    //   dump($Inpack->getLastsql());die;
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