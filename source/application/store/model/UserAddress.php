<?php

namespace app\store\model;
use think\Model;
use app\common\model\UserAddress as UserAddressModel;
use app\store\model\Setting as SettingModel;
use think\Session;
/**
 * 用户收货地址模型
 * Class UserAddress
 * @package app\store\model
 */
class UserAddress extends UserAddressModel
{
    
    /**
     * 获取列表记录
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($user_id)
    {
        return $this
            ->where('address_type',0)
            ->where('user_id',$user_id)
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }
    
    
     /**
     * 获取列表记录
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getDsList()
    {
        return $this
            ->where('address_type',2)//获取代收点的地址
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }
    
    
    /**
     * 获取列表记录
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAllList($user_id,$query)
    {
        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        return $this
            ->where('address_type',2)//获取代收点的地址
            ->whereOr('user_id',$user_id)
            // ->group('address_type',desc)//获取代收点的地址
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }
    
    /**
     * 获取列表记录
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAll($query)
    {
        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        return $this
            // ->where('address_type',2)//获取代收点的地址
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }
    
        /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {
        if (isset($query) && !empty($query)) {
            $this->where('country|name', 'like', '%' . trim($query) . '%');
        }

    }
    
    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }
    
    
        /**
     * 表单验证
     * @param $data
     * @param string $scene
     * @return bool
     */
    private function validateForm($data)
    {

        if(!$data['phone']){
            $this->error = '手机号不能为空';
            return false;  
        }
        return true;
    }
    
      /**
     * 编辑记录
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
    
        if (!$this->validateForm($data)) {
            return false;
        }
        // dump($data);die;
        return $this->allowField(true)->save($data) !== false;
    }
    
     /**
     * 删除
     * @return false|int
     */
    public function remove()
    {
        $res = $this->delete(); 
          if(!$res){
            $this->error = '删除失败';
            return false;   
          }
        return true;   
    }
    
    /**
     * 详情
     * @return false|int
     */
    public static function detail($id)
    {
         return self::find($id);
    }
    
    /**
     * 隐藏手机号
     * @return 
     * @throws \think\exception\DbException
     */
    public function getPhoneAttr($value){
        if(empty($value)){
            return '';
        }
        $store = Session::get('yoshop_store');
        $setting = SettingModel::getItem('adminstyle');
        if($setting['is_address_secret']==1 && $store['user']['is_super']==0){
            return hide_mobile($value);
        }else{
            return $value;
        }
    }
}
