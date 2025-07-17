<?php
namespace app\api\model;

use app\common\model\Line as LineModel;
use app\api\model\UserAddress;
/**
 * 线路模型
 * Class Express
 * @package app\api\model
 */
class Line extends LineModel
{
 
    public function getList($where){
        return $this->where($where)->order('sort DESC')->paginate(15);
    }
    
    public function getListAll(){
      return $this
        ->with('image')
        ->order('sort','created_time desc')
        ->select();
    }

    public function getLine($where){
      return $this->where($where)->field('id,name')->order('sort DESC')->paginate(600);
    }
    
    public function getLineplus($query){
      $where = [];
       if(!empty($query['line_category'])){
           $where['line_category'] = $query['line_category'];
       } 
     
       if(!empty($query['address_id'])){
           $data =  (new UserAddress())->where('address_id',$query['address_id'])->find();
          
           if($data['country_id']){
               return $this->where('FIND_IN_SET(:ids,countrys)', ['ids' => $data['country_id']])->where($where)->where('status',1)->field('id,name')->order('sort DESC')->paginate(600);
           }
       }
       return $this->field('id,name')->where($where)->order('sort DESC')->where('status',1)->paginate(600);
    }
    
    public function getLineForShop($param){
       $data =  (new UserAddress())->where('address_id',$param['address_id'])->find();
        $where['status'] = 1;
       if($data['country_id']){
          
           if(isset($param['shops'])){
              return $this->where('FIND_IN_SET(:ids,countrys)', ['ids' => $data['country_id']])
                        ->where($where)
                        ->where(function($query) use($param){
                              $query->where('shop_id',0)->whereOr('shop_id',$param['shops']);
                         })
                       ->field('id,name')
                       ->order('sort DESC')
                       ->paginate(600); 
           }else{
            //   dump(3434);die;
               return $this->where('FIND_IN_SET(:ids,countrys)', ['ids' => $data['country_id']])
                       ->where($where)
                       ->field('id,name')
                       ->order('sort DESC')
                       ->paginate(600);
           }
       }
       
       if(isset($param['shops'])){
           return $this->where($where)
                        ->where(function($query) use($param){
                              $query->where('shop_id',0)->whereOr('shop_id',$param['shops']);
                         })
                       ->field('id,name')
                       ->order('sort DESC')
                       ->paginate(600); 
       }
      return $this->field('id,name')->order('sort DESC')->paginate(600);
    }
    
    // 推荐路线
    public function goodsLine(){
        return $this
        ->with('image')
        ->where(['status'=>1,'is_recommend'=>1])
        ->field('id,name,tariff,goods_limit,image_id,limitationofdelivery,line_special')
        ->select();
    }
    
     public function country(){
        return $this->belongsTo('Country','country_id');
    }
    
    public function upload(){
        return $this->belongsTo('UploadFile','image_id');
    }

}