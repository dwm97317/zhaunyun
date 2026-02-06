<?php

namespace app\common\service\message\trace;

use app\common\service\message\Basics;
use app\common\model\User;
use app\common\model\Setting as SettingModel;

/**
 * 消息通知服务 [物流已签收]
 */
class Signed extends Basics
{
    protected $param = [];

    public function send($param)
    {
        $this->param = $param;
        $inpack = $this->param['inpack'];
        $wxappId = $inpack['wxapp_id'];
        
        $setting = SettingModel::getItem('submsg', $wxappId);
        $template = isset($setting['trace']['signed']) ? $setting['trace']['signed'] : [];
        
        if (empty($template['template_id'])) {
            return false;
        }

        $isSub = isset($this->param['is_sub_order']) && $this->param['is_sub_order'];
        $statusStr = '已签收';
        
        if ($isSub && isset($this->param['item'])) {
            $item = $this->param['item'];
            // 状态描述也可以带上单号
            $statusStr = "包裹[{$item['t_order_sn']}]已签收";
        }

        return $this->sendWxSubMsg($wxappId, [
            'touser' => $this->getOpenidByUserId($inpack['member_id']),
            'template_id' => $template['template_id'],
            'page' => "pages/sharing/order/detail/detail?id={$inpack['id']}",
            'data' => [
                $template['keywords'][0] => ['value' => $inpack['t_order_sn']],
                $template['keywords'][1] => ['value' => $statusStr],
                $template['keywords'][2] => ['value' => isset($this->param['time']) ? $this->param['time'] : date('Y-m-d H:i:s')],
            ]
        ]);
    }

    private function getOpenidByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('open_id');
    }
}
