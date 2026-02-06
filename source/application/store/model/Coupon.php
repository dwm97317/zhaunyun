<?php

namespace app\store\model;

use app\common\model\Coupon as CouponModel;
use app\store\model\CouponGoods;
/**
 * 优惠券模型
 * Class Coupon
 * @package app\store\model
 */
class Coupon extends CouponModel
{
    /**
     * 获取优惠券列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }
    
        /**
     * 验证优惠券是否可领取
     * @return bool
     */
    public function checkReceive()
    {
        if ($this['total_num'] > -1 && $this['receive_num'] >= $this['total_num']) {
            $this->error = '优惠券已发完';
            return false;
        }
        if ($this['expire_type'] == 20 && ($this->getData('end_time') + 86400) < time()) {
            $this->error = '优惠券已过期';
            return false;
        }
        return true;
    }

    
        
    /**
     * 获取所有优惠券列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAllList()
    {
         return $this->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])->select();
    }
    

    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        if ($data['expire_type'] == '20') {
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
        }
        $this->allowField(true)->save($data);
        if(isset($data['line_ids'])){
            foreach ($data['line_ids'] as $v){
               (new CouponGoods())->add([
                    'coupon_id'=>$this->coupon_id,
                    'goods_id'=>$v,
                    'type'=>10
                ]); 
            }
        }
        return true;
    }
    
    
    /**
     * 更新记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        if ($data['expire_type'] == '20') {
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
        }
        (new CouponGoods())->where('coupon_id',$this->coupon_id)->delete();
        if(isset($data['line_ids'])){
            foreach ($data['line_ids'] as $v){
               (new CouponGoods())->add([
                    'coupon_id'=>$this->coupon_id,
                    'goods_id'=>$v,
                    'type'=>10
                ]); 
            }
        }
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 删除记录 (软删除)
     * @return bool|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]) !== false;
    }
    
    /**
     * 累计已领取数量
     * @return int|true
     * @throws \think\Exception
     */
    public function setIncReceiveNum()
    {
        return $this->setInc('receive_num');
    }

}
