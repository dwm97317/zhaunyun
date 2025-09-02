<?php
namespace app\store\model;
use app\common\model\Currency as CurrencyModel;
use think\Db;

class Currency extends CurrencyModel
{
    public function getList($name){
            return $this
            ->where(function($query) use ($name) {
               $query->where('title','like','%'.$name.'%')
               ->whereor('code','like','%'.$name.'%');
            })
            ->paginate(300,false, [
                'query' => \request()->request()
            ]);
    }
    
    public function getListAll(){
           return $this
            ->order(["is_default"=>"desc",'sort'=>'asc'])
            ->paginate(300,false, [
                'query' => \request()->request()
            ]);
    }
    
    public function getListAllCountry(){
        return $this->select();
    }
    
     public function details($id){
        return $this->find($id);
    }
    
     public function add($data){
        // 表单验证
        if (!$this->onValidate($data)) return false;
        // 保存数据
        $result = $this->where('is_default',1)->find(); 
        if($result){
            $result->save(['is_default'=>0]);
        }
        $data['wxapp_id'] = self::$wxapp_id;
        if ($this->allowField(true)->save($data)) {
            return true;
        }
        return false;
    }
    
    public function copy(){
        $data = getFileData('assets/currencylist.json');
        $currency=[];
        foreach ($data as $key =>$value) {
            $currency[$key]['currency_name'] = $value['currency_name'];
            $currency[$key]['exchange_rate'] = $value['exchange_rate'];
            $currency[$key]['currency_en'] = $value['currency_en'];
            $currency[$key]['currency_symbol'] = $value['currency_symbol'];
            $currency[$key]['status'] =1;
            $currency[$key]['sort'] = $key+1;
            $currency[$key]['wxapp_id'] =self::$wxapp_id;
        }
        if ($this->insertAll($currency)) {
            return true;
        }
        return false;
    }

    public function edit($data)
    {
        // 表单验证
        if (!$this->onValiEdit($data)) return false;
        // 保存数据
        $result = $this->where('is_default',1)->find(); 
        if($result){
            $result->save(['is_default'=>0]);
        }
        return $this->allowField(true)->save($data);
    }
    
    public function deletes($id){
        return $this->find($id)->delete();
    }


    public function onValidate($data){
        if (!isset($data['currency_name']) || empty($data['currency_name'])) {
            $this->error = '请输入货币名称';
            return false;
        }
        if (!isset($data['currency_symbol']) || empty($data['currency_symbol'])) {
            $this->error = '请输入货币符号';
            return false;
        }
        if (!isset($data['currency_en']) || empty($data['currency_en'])) {
            $this->error = '请输入货币英文缩写';
            return false;
        }
        if (self::where(['currency_name'=>$data['currency_name']])->find()){
            $this->error = '该货币已存在';
            return false;
        }
        return true;
    }
    
    public function onValiEdit($data){
        if (!isset($data['currency_name']) || empty($data['currency_name'])) {
            $this->error = '请输入货币名称';
            return false;
        }
        if (!isset($data['currency_symbol']) || empty($data['currency_symbol'])) {
            $this->error = '请输入货币符号';
            return false;
        }
        if (!isset($data['currency_en']) || empty($data['currency_en'])) {
            $this->error = '请输入货币英文缩写';
            return false;
        }
        return true;
    }
}