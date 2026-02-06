<?php
namespace app\store\model\sharing;
use app\common\model\sharing\SharingTrUser as SharingTrUserModel;

class SharingTrUser extends SharingTrUserModel {
    
    /**
     * 获取拼团用户列表
     * @param array $query
     * @return \think\Paginator
     */
    public function getList($query = []){
        return $this->setListQueryWhere($query)
            ->order('create_time', 'desc')
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }
    
    /**
     * 设置查询条件
     * @param array $query
     * @return $this
     */
    public function setListQueryWhere($query){
        if (isset($query['user_id']) && $query['user_id']){
            $this->where(['user_id' => $query['user_id']]);
        }
        if (isset($query['order_id']) && $query['order_id']){
            $this->where(['order_id' => $query['order_id']]);
        }
        if (isset($query['status']) && $query['status'] !== ''){
            $this->where(['status' => $query['status']]);
        }
        return $this;
    }
    
        /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('app\store\model\User', 'user_id');
    }
    
    /**
     * 关联拼团订单
     * @return \think\model\relation\BelongsTo
     */
    public function order(){
        return $this->belongsTo('app\store\model\sharing\SharingOrder', 'order_id', 'order_id');
    }
    
}  

?>
