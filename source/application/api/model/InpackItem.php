<?php

namespace app\api\model;

use app\common\model\InpackItem as InpackItemModel;
use app\api\model\Inpack;
/**
 * 集运单服务项目模型
 * Class GoodsImage
 * @package app\api\model
 */
class InpackItem extends InpackItemModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
        'update_time',
    ];
    

    /**
     * 处理包装服务
     * @var array
     */
    public function add($data){
        $data['wxapp_id'] = self::$wxapp_id;
        $data['create_time'] = time();
        $Inpackdetail = (new Inpack())->getDetails($data['inpack_id'],[]);
        if(!empty($data['width']) && !empty($data['length']) && !empty($data['height'])){
            $data['volume'] = $data['width']*$data['length']*$data['height']/1000000;
            $volume_weight = $data['width']*$data['length']*$data['height']/$Inpackdetail['line']['volumeweight'];
            if(!empty($data['weight'])){
                $data['cale_weight'] = $data['weight'] > $volume_weight?$data['weight']:$volume_weight;
            }
        }
        return $this->allowField(true)->insert($data);
    }

}
