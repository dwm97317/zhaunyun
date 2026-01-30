<?php

namespace app\common\service\message\trace;

use app\common\service\message\Basics;
use app\common\model\User;
use app\common\model\Setting as SettingModel;

/**
 * 消息通知服务 [物流派送中]
 */
class Delivery extends Basics
{
    /**
     * 参数列表
     * @var array
     */
    protected $param = [];

    /**
     * 发送消息通知
     * @param array $param
     * @return mixed|void
     * @throws \think\Exception
     */
    public function send($param)
    {
        $this->param = $param;
        $inpack = $this->param['inpack'];
        $wxappId = $inpack['wxapp_id'];
        
        // 获取订阅消息配置 (需在 yoshop_setting 中配置 'trace' => ['delivery'=>...])
        $setting = SettingModel::getItem('submsg', $wxappId);
        $template = isset($setting['trace']['delivery']) ? $setting['trace']['delivery'] : [];
        
        if (empty($template['template_id'])) {
            return false;
        }
        
        $isSub = isset($this->param['is_sub_order']) && $this->param['is_sub_order'];
        $msg = isset($this->param['msg']) ? $this->param['msg'] : '快件正在派送中';
        
        if ($isSub && isset($this->param['item'])) {
            $item = $this->param['item'];
            // 在备注中增加子单号标识
            $msg = "包裹[{$item['t_order_sn']}]正在派送。 " . $msg;
        }
        
        // 发送订阅消息
        return $this->sendWxSubMsg($wxappId, [
            'touser' => $this->getOpenidByUserId($inpack['member_id']),
            'template_id' => $template['template_id'],
            // 假设集运单详情页
            'page' => "pages/sharing/order/detail/detail?id={$inpack['id']}", 
            'data' => [
                // 1. 运单号
                $template['keywords'][0] => ['value' => $inpack['t_order_sn']],
                // 2. 状态
                $template['keywords'][1] => ['value' => '正在派送'],
                // 3. 详情
                $template['keywords'][2] => ['value' => $msg],
            ]
        ]);
    }

    private function getOpenidByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('open_id');
    }
}
