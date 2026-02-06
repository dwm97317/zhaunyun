<?php
namespace app\task\model;
use app\common\model\SiteSms as SiteSmsModel;
/**
 * 线路模型
 * Class Delivery
 * @package app\common\model
 */
class SiteSms extends SiteSmsModel
{
    public function add($data){
       // 表单验证
       if (!$this->onValidate($data)) return false;
       // 保存数据
       $data['created_time'] = getTime();
       $data['updated_time'] = getTime();
       $res = $this->allowField(true)->insertGetId($data);
       
       if ($res){
           return true;
       }
       return false;
    }
    
    public function onValidate($data){
      if (!isset($data['user_id']) || empty($data['user_id'])) {
         $this->error = '请选择用户';
         return false;
      }
      if (!isset($data['content']) || empty($data['content'])) {
        $this->error = '请输入内容';
        return false;
      }
      
      return true;
    }
    
}