<?php
namespace app\api\model\sharing;
use app\common\model\sharing\SharingTrUser as SharingTrUserModel;

class SharingTrUser extends SharingTrUserModel {
    
    /**
     * 获取拼团用户列表
     * @param array $query
     * @return \think\Paginator
     */
    public function getList($query = []){
        return $this
            ->setWhere($query)
            ->with(['user', 'order'])
            ->order('create_time', 'desc')
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
    
    /**
     * 设置查询条件
     * @param array $query
     * @return $this
     */
    public function setWhere($query){
        if (isset($query['user_id']) && $query['user_id']){
            $this->where(['user_id' => $query['user_id']]);
        }
        if (isset($query['order_id']) && $query['order_id']){
            $this->where(['order_id' => $query['order_id']]);
        }
        if (isset($query['status']) && $query['status'] !== ''){
            $this->where(['status' => $query['status']]);
        }
        if (isset($query['member_id']) && $query['member_id']){
            $this->where(['member_id' => $query['member_id']]);
        }
        return $this;
    }
    
    /**
     * 加入拼团
     * @param array $data
     * @return bool
     */
    public function join($data){
        // 检查是否已加入
        if ($this->isJoined($data['user_id'], $data['order_id'])){
            $this->error = '您已经加入该拼团了';
            return false;
        }
        $data['wxapp_id'] = self::$wxapp_id;
        $data['create_time'] = time();
        $data['update_time'] = time();
        return $this->allowField(true)->save($data) !== false;
    }
    
    /**
     * 检查是否已加入拼团
     * @param int $userId
     * @param int $orderId
     * @return bool
     */
    public function isJoined($userId, $orderId){
        return $this->where([
            'user_id' => $userId,
            'order_id' => $orderId
        ])->find() ? true : false;
    }
    
    /**
     * 退出拼团
     * @param int $userId
     * @param int $orderId
     * @return bool
     */
    public function quit($userId, $orderId){
        $model = $this->where([
            'user_id' => $userId,
            'order_id' => $orderId
        ])->find();
        
        if (!$model){
            $this->error = '未找到拼团记录';
            return false;
        }
        
        return $model->delete();
    }
    
    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('app\api\model\User', 'user_id');
    }
    
    /**
     * 关联拼团订单
     * @return \think\model\relation\BelongsTo
     */
    public function order(){
        return $this->belongsTo('app\api\model\sharing\SharingOrder', 'order_id');
    }
    
}
