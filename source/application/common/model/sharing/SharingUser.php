<?php
namespace app\common\model\sharing;
use app\common\model\BaseModel;
/**
 * 团长模型
 * */
class SharingUser extends BaseModel{
    protected $name = 'sharing_user';
    
    /**
     * 获取团长信息
     * @param $userId
     * @param array $with
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($userId)
    {
        return self::get($userId);
    }
    
    public function setDelete(){
        return $this->delete();
    }

} 