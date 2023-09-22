<?php
namespace app\common\model;
use app\common\service\Message as MessageService;
use app\common\enum\OrderType as OrderTypeEnum;
/**
 * 包裹模型
 * Class OrderAddress
 * @package app\common\model
 */
class Package extends BaseModel
{
    protected $name = 'package';
    protected $updateTime = false;
    public function getWxappId(){
        return self::$wxapp_id;
    }
    /**
     * 确认入库发送消息通知
     * @param $orderList
     * @return bool
     */
    public function sendEnterMessage($orderList)
    {
        // 发送消息通知
    
        foreach ($orderList as $item) {
        
            MessageService::send('order.enter', [
                'order' => $item,
                'order_type' => OrderTypeEnum::MASTER,
            ]);
           
        }
           
        return true;
    }
    
    // /**
    //  * 获取包裹单号的货架
    //  * @param $orderList
    //  * @return bool
    //  */
    // public function getShelfNo($pack_id){
        
        
    // }
    
    
     /**
     * 关联包裹图片表
     * @return \think\model\relation\HasMany
     */
    public function packageimage()
    {
        return $this->hasMany('PackageImage')->order(['id' => 'asc']);
    }
    
     /**
     * 关联包裹图片表
     * @return \think\model\relation\HasMany
     */
    public function packagexpress()
    {
        return $this->hasOne('PackageImage')->order(['id' => 'asc']);
    }
    
     /**
     * 关联包裹图片表
     * @return \think\model\relation\HasMany
     */
    public function shelfunititem()
    {
        return $this->hasOne('ShelfUnitItem','pack_id','id');
    }

   
}
