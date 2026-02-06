<?php

namespace app\store\model;

use app\common\model\CouponGoods as CouponGoodsModel;

/**
 * 优惠券集运线路关联模型
 * Class Coupon
 * @package app\store\model
 */
class CouponGoods extends CouponGoodsModel
{
    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }


}
