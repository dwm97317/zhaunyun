<?php
namespace app\common\model;

/**
 * 用户收货地址模型
 * Class UserAddress
 * @package app\common\model
 */
class UserAddress extends BaseModel
{
    protected $name = 'user_address';

    /**
     * 追加字段
     * @var array
     */
    protected $append = ['chineseregion'];
    
    public function countrydata(){
        return $this->belongsTo('app\common\model\Country','country_id','id');
    }
    
    /**
     * 地区名称（中文地址拼接）
     * @param $value
     * @param $data
     * @return string
     */
    public function getChineseRegionAttr($value, $data)
    {
        // 基础地址拼接，子类可以重写此方法以使用配置
        $detail = '';
        if (isset($data['country'])) {
            $detail = $data['country'];
        }
        if (isset($data['province'])) {
            $detail .= $data['province'];
        }
        if (isset($data['city'])) {
            $detail .= $data['city'];
        }
        if (isset($data['region'])) {
            $detail .= $data['region'];
        }
        if (isset($data['detail'])) {
            $detail .= $data['detail'];
        }
        return $detail;
    }

}
