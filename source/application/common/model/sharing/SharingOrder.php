<?php
namespace app\common\model\sharing;
use app\common\model\BaseModel;
use app\common\model\UploadFile;
/**
 * 拼团订单服务
 * */
class SharingOrder extends BaseModel{
    protected $name = 'sharing_tr_order';
    
    /**
     * 有效期-开始时间
     * @param $value
     * @return mixed
     */
    public function getCreateTimeAttr($value)
    {
        return ['text' => date('Y-m-d H:i:s', $value), 'value' => $value];
    }
    
    public function getStatusAttr($value){
        $map = [
           0 => '待审核',
           1 => '开团中',
           2 => '已完成',
           3 => '已解散',
           4 => '已发货',
           5 => '已完结',
        ];
        return ['text' => $map[$value] , 'value'=>$value];
    }

    public static function detail($id)
    {
        return self::get($id,['country','storage','address','line','shareImage']);
    }
    
    public function country(){
        return $this->belongsTo('app\common\model\Country','country_id');
    }
    
    public function storage(){
        return $this->belongsTo('app\common\model\store\Shop','storage_id');
    }
        public function User(){
        return $this->belongsTo('app\common\model\User','member_id');
    }
    public function line(){
        return $this->belongsTo('app\common\model\Line','line_id');
    }
    public function address(){
        return $this->belongsTo('app\common\model\UserAddress','address_id');
    }
    
    /**
     * 关联拼团二维码图片
     * @return \think\model\relation\HasOne
     */
    public function shareImage()
    {
        return $this->hasOne('app\common\model\UploadFile', 'file_id', 'share_image_id');
    }
} 