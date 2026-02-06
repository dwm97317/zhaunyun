<?php

namespace app\api\controller;

use app\api\controller\Controller;
use app\api\model\Blindbox as BlindboxModel;
use app\api\model\BlindboxWall as BlindboxWallModel;
use app\common\model\Setting;
use app\api\model\Setting as SettingModel;
use app\api\model\Package as PackageModel;
use app\api\model\UserCoupon as UserCouponModel;
use app\api\model\BlindboxLog as BlindboxLogModel;

/**
 * 盲盒计划管理
 * Class Blindbox
 * @package app\store\controller\market
 */
class Blindbox extends Controller
{
    /**
     * 盲盒分享墙
     * @param $coupon_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function blindboxwall()
    {
        $BlindboxWallModel = new BlindboxWallModel();
        $list = $BlindboxWallModel->getList();
        return $this->renderSuccess(compact('list'));
    }
    
    /**
     * 盲盒日志
     * @param $coupon_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function blindboxwlog()
    {
        $BlindboxLogModel = new BlindboxLogModel();
        $list = $BlindboxLogModel->getList();
        return $this->renderSuccess(compact('list'));
    }
    
    /**
     * 盲盒抽奖
     * @param $coupon_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function Lottery(){
        $params = $this->request->param();
        $user = $this->getUser();
        if($user['blindbox']==0){
            return $this->renderError('暂无抽盲盒机会，快去下单或拉好友一起吧！');
        }
        //获取可以抽奖的物品
        $prizes = (new BlindboxModel())->getAllList()->toArray();
        
        if (empty($prizes)) {
            return $this->renderError('暂无可用盲盒！');
        }
        //减少用户抽盲盒次数
        $user->setDec ('blindbox',1);   
        // 4. 执行抽奖算法
        $result = $this->runLotteryAlgorithm($prizes);
        
        if($result['win']==true){
            if($result['prize']['type']==10){
                (new UserCouponModel())->receive($user,$result['prize']['coupon_id']);
                //记录抽中结果
                (new BlindboxLogModel())->add([
                    'user_id'=>$user['user_id'],   
                    'blindbox_id'=>$result['prize']['id'],
                    'content'=>'抽中'.$result['prize']['coupon']['name'],
                ]);
            }
            if($result['prize']['type']==20){
                $this->recordWinning($result['prize'],$user);
                 //记录抽中结果
                (new BlindboxLogModel())->add([
                    'user_id'=>$user['user_id'],   
                    'blindbox_id'=>$result['prize']['id'],
                    'content'=>'抽中'.$result['prize']['goods_desc'],
                ]);
            }
            //减少盲盒抽库存
                (new BlindboxModel())->where('id',$result['prize']['id'])->find()->setDec ('goods_inventory',1);
            return $this->renderSuccess($result['prize']);
        }
        return $this->renderError($result['message']);
    }
    
    // 记录中奖结果
    public function recordWinning($result,$userId) {
        $PackageModel = new PackageModel();
        $data = [
            'order_sn' => createSn(),
            'express_num'=>createMhsn(),
            'is_take'=>2,
            'status'=>2,
            'pack_type'=>0,
            'member_id'=>$userId['user_id'],
            'created_time' => getTime(),
            'updated_time' => getTime(),
            'wxapp_id' => $userId['wxapp_id'],
            'admin_remark'=>$result['goods_name'],
            'source'=>10,
        ];
        $PackageModel->insert($data);
    }
    
    
    //实现抽盲盒
    public function runLotteryAlgorithm($prizes) {
    // 计算总概率(可能小于1，剩余为不中奖概率)
        $totalProbability = array_sum(array_column($prizes, 'probability'));
        $random = (mt_rand() / mt_getrandmax())*100; // 生成0-1的随机数
      
        if ($random > $totalProbability) {
           return ['win' => false, 'message' => '很遗憾，未抽中任何盲盒！'];
        }
        
        // 概率区间算法
        $rangeStart = 0;
        foreach ($prizes as $prize) {
            $rangeEnd = $rangeStart + $prize['probability'];
            if ($random >= $rangeStart && $random < $rangeEnd) {
                return ['win' => true,'prize'=>$prize];
            }
            $rangeStart = $rangeEnd;
        }
        
        // 理论上不会执行到这里
        return ['win' => false, 'message' => '系统异常！'];
    }
    
    /**
     * 发布分享
     * @param
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function createblindboxwall()
    {
        $params = $this->request->param();
        $params['user_id'] = $this->getUser()->user_id;
        $Model = new BlindboxWallModel();
        // dump($params);die;
        return $this->renderSuccess($Model->addWall($params));
    }

    /**
     * 发放设置
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function setting()
    {
        if (!$this->request->isAjax()) {
            $list = $this->model->getList();
            $values = SettingModel::getItem('blindbox');
            return $this->fetch('setting', ['values' => $values,'list'=>$list]);
        }
        $model = new SettingModel;
        if ($model->edit('blindbox', $this->postData('blindbox'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

}