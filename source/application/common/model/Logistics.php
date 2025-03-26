<?php
namespace app\common\model;
use app\store\model\Inpack;
use app\common\model\LogisticsTrack;
use app\common\library\Ditch\BaiShunDa\bsdexp;
use app\common\library\Ditch\Jlfba\jlfba;
use app\common\library\Ditch\kingtrans;
use app\common\library\Ditch\Hualei;
use app\common\library\Ditch\Xzhcms5;
use app\common\library\Ditch\Aolian;
use app\common\library\Ditch\Yidida;
/**
 * 包裹日志模型
 * Class OrderAddress
 * @package app\common\model
 */
class Logistics extends BaseModel
{
    protected $name = 'logistics';
    protected $updateTime = false;




    // 状态映射  状态 1 待查验 2 待支付 3 待发货 4 拣货中 5 已打包  6已发货 7 已到货 8 已完成  9已取消
    public $map = [
     -1=>'问题件', 1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'
    ];
    
    public $maps = [1=>'问题件', 1=>'待查验',2=>'待支付',3=>'待发货',4=>'拣货中',5=>'已打包',6=>'已发货',7=>'已到货',8=>'已完成',9=>'已取消'];
    
    public static function addLog($id,$desc,$creatime){
        $id = (new Inpack())->where('order_sn',$id)->find();
        $model = new static;
        return $model->insert([
            'order_sn' => $id['order_sn'],
            // 'express_num' => $id['t_order_sn'],
            'status' => $id['status'],
            'status_cn' => $model->maps[$id['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> $id['t_order_sn'],
            'created_time' => $creatime,
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$id['wxapp_id'],
        ]);
    }
    
    //仓管端操作产生的日志记录函数
    public static function addrfidLog($id,$desc,$creatime,$clerk_id){
        $id = (new Inpack())->where('order_sn',$id)->find();
        $model = new static;
        return $model->insert([
            'order_sn' => $id['order_sn'],
            'operate_id'=> $clerk_id,
            'status' => $id['status'],
            'status_cn' => $model->maps[$id['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> $id['t_order_sn'],
            'created_time' => $creatime,
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$id['wxapp_id'],
        ]);
    }
    
    public static function add2($id,$desc,$clerk_id){
//  dump($id);die;
        $id = (new Package())->find($id);
            
        $model = new static;
        return $model->insert([
            'order_sn' => $id['order_sn'],
            'express_num' => $id['express_num'],
            'status' => $id['status'],
            'status_cn' => $model->map[$id['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'created_time' => getTime(),
            'operate_id'=> $clerk_id,
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$id['wxapp_id'],
        ]);
    }
    
    public static function add($id,$desc){
//  dump($id);die;
        $id = (new Package())->find($id);
            
        $model = new static;
        return $model->insert([
            'order_sn' => $id['order_sn'],
            'express_num' => $id['express_num'],
            'status' => $id['status'],
            'status_cn' => $model->map[$id['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$id['wxapp_id'],
        ]);
    }
    //用户提交打包的日志
    public static function addLogPack($id,$order_sn,$desc){
        $ids = (new Package())->find($id);
 
        $model = new static;
        return $model->insert([
            'order_sn' => $order_sn,
            'express_num' => $ids['express_num'],
            'status' => $ids['status'],
            'status_cn' => $model->map[$ids['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$ids['wxapp_id'],
        ]);
    }
    
    public static function addInpackLogsPlus($id,$desc,$time){
    
    $Inpack = (new Inpack())->where('order_sn',$id)->find();
    $model = new static;
    return $model->insert([
        'order_sn' => $Inpack['order_sn'],
        // 'express_num' => $Inpack['t_order_sn'],
        'status' => $Inpack['status'],
        'status_cn' => $model->maps[$Inpack['status']],
        'logistics_describe' => $desc?$desc:'包裹状态更新',
        'logistics_sn'=> '',
        'created_time' => $time,
        'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$Inpack['wxapp_id'],
    ]);
    }
      
     public static function addInpackLogs($id,$desc){
        
        $Inpack = (new Inpack())->where('order_sn',$id)->find();
        $model = new static;
        return $model->insert([
            'order_sn' => $Inpack['order_sn'],
            // 'express_num' => $Inpack['t_order_sn'],
            'status' => $Inpack['status'],
            'status_cn' => $model->maps[$Inpack['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> '',
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$Inpack['wxapp_id'],
        ]);
    }
    
    public static function addbatchLogs($id,$status_cn,$desc){
        
        $Inpack = (new Inpack())->where('order_sn',$id)->find();
        $model = new static;
        return $model->insert([
            'order_sn' => $Inpack['order_sn'],
            // 'express_num' => $Inpack['t_order_sn'],
            'status' => $Inpack['status'],
            'status_cn' => $status_cn,
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> '',
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$Inpack['wxapp_id'],
        ]);
    }
    
    public static function addbatchLogsforpackage($id,$status_cn,$desc){
        
        $package = (new package())->where('id',$id)->find();
        $model = new static;
        return $model->insert([
            'order_sn' => $package['order_sn'],
            'express_num' => $package['express_num'],
            'status' => $package['status'],
            'status_cn' => $status_cn,
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> '',
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$package['wxapp_id'],
        ]);
    }
    
     public static function addInpackLog($id,$desc,$t_order_sn){
        
        $Inpack = (new Inpack())->where('order_sn',$id)->find();

        $model = new static;
        return $model->insert([
            'order_sn' => $Inpack['order_sn'],
            // 'express_num' => $Inpack['t_order_sn'],
            'status' => $Inpack['status'],
            'status_cn' => $model->maps[$Inpack['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> $t_order_sn,
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$Inpack['wxapp_id'],
        ]);
    }
    
    public static function inpackstatus($id,$desc,$t_order_sn,$status){
        
        $Inpack = (new Inpack())->where('order_sn',$id)->find();

        $model = new static;
        return $model->insert([
            'order_sn' => $Inpack['order_sn'],
            // 'express_num' => $Inpack['t_order_sn'],
            'status' => $status,
            'status_cn' => $model->maps[$status],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> $t_order_sn,
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$Inpack['wxapp_id'],
        ]);
    }
    
    
    
    
      //集运单到货的日志
      public static function addInpackGetLog($id,$desc,$t_order_sn){
        
        $Inpack = (new Inpack())->where('id',$id)->find();
        $model = new static;
        return $model->insert([
            'order_sn' => $Inpack['order_sn'],
            // 'express_num' => $Inpack['t_order_sn'],
            'status' => $Inpack['status'],
            'status_cn' => $model->maps[$Inpack['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> $t_order_sn,
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$Inpack['wxapp_id'],
        ]);
    }
    
          //集运单到货的日志
      public static function addInpackGetLog2($id,$desc,$t_order_sn,$clerk_id){
        
        $Inpack = (new Inpack())->where('id',$id)->find();
        $model = new static;
        return $model->insert([
            'order_sn' => $Inpack['order_sn'],
            // 'express_num' => $Inpack['t_order_sn'],
            'status' => $Inpack['status'],
            'status_cn' => $model->maps[$Inpack['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> $t_order_sn,
            'created_time' => getTime(),
            'operate_id'=> $clerk_id,
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$Inpack['wxapp_id'],
        ]);
    }
    
     public static function updateOrderSn($packnum,$order_sn){
        return (new Logistics())->where('express_num',$packnum)->update(["order_sn" =>$order_sn ]);
    }
    
    /**
     * 删除记录
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        return $this->delete();
    }
    
    public function details($id){
        return $this->find($id);
    }
    
    /**
     * 查询渠道商的物流轨迹
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function searchLog($params,$express){
        if($params['ditch_no']==10001){
            $jlfba =  new jlfba(['key'=>$params['app_key'],'token'=>$params['app_token']]);
            $result = $jlfba->query($express);
        }
        //百顺达
        if($params['ditch_no']==10002){
            $bsdexp =  new bsdexp(['key'=>$params['app_key'],'token'=>$params['app_token']]);
            $result = $bsdexp->query($express);
        }
        //K5
        if($params['ditch_no']==10003){
            $kingtrans =  new kingtrans(['key'=>$params['app_key'],'token'=>$params['app_token'],'apiurl'=>$params['api_url']]);
            $result = $kingtrans->query($express);
        }
        //华磊api
        if($params['ditch_no']==10004){
            $Hualei =  new Hualei(['key'=>$params['app_key'],'token'=>$params['app_token'],'apiurl'=>$params['api_url']]);
            $result = $Hualei->query($express);
        }
        
        //星泰api
        if($params['ditch_no']==10005){
            $Xzhcms5 =  new Xzhcms5(['key'=>$params['app_key'],'token'=>$params['app_token'],'apiurl'=>$params['api_url']]);
            $result = $Xzhcms5->query($express);
        }
        
        //澳联
        if($params['ditch_no']==10006){
            $Aolian =  new Aolian(['key'=>$params['app_key'],'token'=>$params['app_token'],'apiurl'=>$params['api_url']]);
            $result = $Aolian->query($express);
        }
        
        //易抵达
        if($params['ditch_no']==10007){
            $Yidida =  new Yidida(['key'=>$params['app_key'],'token'=>$params['app_token'],'apiurl'=>$params['api_url']]);
            $result = $Yidida->query($express);
        }
        return $result;
    }
    
    

}
