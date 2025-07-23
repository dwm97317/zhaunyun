<?php

namespace app\api\controller;

use app\api\model\user\Grade as GradeModel;
use app\common\enum\user\grade\log\ChangeType as ChangeTypeEnum;
use app\api\model\user\GradeLog as GradeLogModel;

/**
 * 会员VIP列表
 * Class nav
 * @package app\api\controller
 */
class Grade extends Controller
{
    /**
     * 等级列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function list()
    {
        $list = GradeModel::getUsableList();
        return $this->renderSuccess(compact('list'));
    }
    
    /**
     * 等级列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function gradepointlist()
    {
        $list = GradeModel::getUsablePointList();
        return $this->renderSuccess(compact('list'));
    }
    
        
    /**
     * 等级列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function gradedetail($grade_id)
    {
        $detail = GradeModel::detail($grade_id);
        return $this->renderSuccess(compact('detail'));
    }
    
    
    /**
     * 兑换会员等级
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function exchangeGrade($grade_id)
    {
       $userInfo = $this->getUser();
       $detail = GradeModel::detail($grade_id);
       if($detail['use_point'] > $userInfo['points']){
           return $this->renderError('积分不足');  
       }
        // 修改用户VIP等级，累计时长
        if($userInfo['grade_id']==0 || $userInfo['grade_time']==0){
            $userInfo->setInc('grade_time', (time() + $detail['effective_time']*86400));
        }else{
            $userInfo->setInc('grade_time',$detail['effective_time']*86400);
        }
        $userInfo->save(['grade_id'=>$grade_id]);
         // 记录会员等级变更记录
        $GradeLogModel = new GradeLogModel;
        $grade = [
            'user_id'=>$userInfo['user_id'],  
            'old_grade_id'=>$userInfo['grade_id'], 
            'new_grade_id'=>$grade_id,
            'wxapp_id' => $userInfo['wxapp_id'],
            'change_type' => ChangeTypeEnum::PAY_UPGRADE,
        ];
        $GradeLogModel->save($grade);
        $userInfo->setDecPoints($detail['use_point'],'兑换会员'.$detail['name']);
        return $this->renderSuccess('兑换成功');   
    }
    
    
           
}