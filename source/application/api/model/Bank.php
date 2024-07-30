<?php

namespace app\api\model;

use app\common\model\Bank as BankModel;

class Bank extends BankModel
{

   public function getList($query=[]){
        return $this->setListQueryWhere($query)
        ->paginate(10,false,[
            'query'=>\request()->request()
        ]);
    }

    public function setListQueryWhere($query){
      isset($query['bank_type']) && $this->where('bank_type',$query['bank_type']);
      return $this;
    }

}