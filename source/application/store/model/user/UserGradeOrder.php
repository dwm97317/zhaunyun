<?php

namespace app\store\model\user;

use app\common\model\user\UserGradeOrder as UserGradeOrderModel;

use app\store\model\User as UserModel;

/**
 * 用户会员等级模型
 * Class Grade
 * @package app\store\model\user
 */
class UserGradeOrder extends UserGradeOrderModel
{
    /**
     * 获取列表记录
     * @param string $grade_id 会员等级ID
     * @param string $nickName 会员昵称
     * @param string $user_id 用户ID
     * @param string $user_code 用户编号
     * @param string $start_date 创建开始日期
     * @param string $end_date 创建结束日期
     * @param string $effect_start_date 生效开始日期
     * @param string $effect_end_date 生效结束日期
     * @param string $is_expired 是否过期
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($grade_id = '', $nickName = '', $user_id = '', $user_code = '', $start_date = '', $end_date = '', $effect_start_date = '', $effect_end_date = '', $is_expired = '')
    {
        $query = $this->alias('order')->with(['user','grade'])
                ->join('user u', 'order.user_id = u.user_id',"LEFT");
        
        // 会员等级筛选
        if (!empty($grade_id)) {
            $query->where('order.grade_id', '=', $grade_id);
        }
        
        // 会员昵称筛选
        if (!empty($nickName)) {
            $query->where('u.nickName', 'like', '%' . $nickName . '%');
        }
        
        // 用户ID筛选
        if (!empty($user_id)) {
            $query->where('u.user_id', '=', '%' . $user_id . '%');
        }
        
        // 用户编号筛选
        if (!empty($user_code)) {
           $query->where('u.user_code', 'like', '%' . $user_code . '%');
        }
        
        // 创建日期范围筛选
        if (!empty($start_date)) {
            $query->where('order.create_time', '>=', strtotime($start_date . ' 00:00:00'));
        }
        if (!empty($end_date)) {
            $query->where('order.create_time', '<=', strtotime($end_date . ' 23:59:59'));
        }
        
        // 生效日期范围筛选
        if (!empty($effect_start_date)) {
            $query->where('u.grade_time', '>=', strtotime($effect_start_date . ' 00:00:00'));
        }
        if (!empty($effect_end_date)) {
            $query->where('u.grade_time', '<=', strtotime($effect_end_date . ' 23:59:59'));
        }
        
        // 是否过期筛选
        if ($is_expired !== '') {
            if ($is_expired == '1') {
                // 已过期：到期时间小于当前时间且到期时间不为空
                $query->where('grade_time', '<', time())
                      ->where('grade_time', '<>', '');
            } elseif ($is_expired == '0') {
                // 未过期：到期时间大于当前时间或到期时间为空
                $query->where(function($q) {
                    $q->where('grade_time', '>', time())
                      ->whereOr('grade_time', '=', '')
                      ->whereOr('grade_time', 'null');
                });
            }
        }
        
        return $query->where('order.is_pay',1)
            ->field('order.*,u.user_id,u.user_code,u.nickName,u.grade_time')
            ->order(['order.create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }
}