<?php
namespace app\common\model\sharing;
use app\common\model\BaseModel;
/**
 * 拼团用户模型
 * */
class SharingTrUser extends BaseModel{
    protected $name = 'sharing_tr_user';
    
    /**
     * 获取拼团用户信息
     * @param $id
     * @param array $with
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($id, $with = [])
    {
        return self::get($id, $with);
    }
    
    /**
     * 删除拼团用户
     * @return bool
     */
    public function setDelete(){
        return $this->delete();
    }

}
