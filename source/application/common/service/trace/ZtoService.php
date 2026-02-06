<?php

namespace app\common\service\trace;

use app\common\model\Inpack;
use app\common\model\InpackItem;
use app\common\service\Message;
use think\Log;
use app\api\model\Setting as SettingModel;

/**
 * 中通快递回调处理服务 (ZTO)
 */
class ZtoService
{
    /**
     * 处理轨迹推送
     * @param array $params (POST parameters: data, msg_type, data_digest, company_id)
     * @return array
     */
    public static function handleCallback($params)
    {
        // 1. 简单校验
        if (empty($params['data'])) {
            return ['result' => 'false', 'message' => 'Empty Data', 'status' => false];
        }

        $dataJson = $params['data'];
        $traces = json_decode($dataJson, true);

        if (empty($traces) || !is_array($traces)) {
            Log::error("[ZTO] Parse Error: " . $dataJson);
            return ['result' => 'false', 'message' => 'Json Parse Error', 'status' => false];
        }

        // 兼容带 result 层的结构 (根据文档 zto.merchant.waybill.track.query 返回结构)
        $list = $traces;
        if (isset($traces['result']) && is_array($traces['result'])) {
            $list = $traces['result'];
        }

        // 如果是个单一对象（关联数组），则包裹成数组
        if (isset($list['billCode']) || isset($list['bill_code'])) {
            $list = [$list];
        }

        // 2. 遍历处理每一条轨迹
        foreach ($list as $traceItem) {
            self::processSingleTrace($traceItem);
        }

        // 3. 返回成功
        return [
            'result' => 'success', 
            'message' => '成功', 
            'status' => true,
            'statusCode' => '200'
        ];
    }

    private static function processSingleTrace($trace)
    {
        $waybillNo = isset($trace['billCode']) ? $trace['billCode'] : '';
        // 兼容不同的字段名: scanType / action
        $scanType  = isset($trace['scanType']) ? $trace['scanType'] : (isset($trace['action']) ? $trace['action'] : ''); 
        $desc      = isset($trace['desc']) ? $trace['desc'] : '';
        // 兼容不同的字段名: scanDate / actionTime
        $scanDate  = isset($trace['scanDate']) ? $trace['scanDate'] : (isset($trace['actionTime']) ? $trace['actionTime'] : date('Y-m-d H:i:s'));
        
        if (empty($waybillNo)) {
            return;
        }

        // 映射状态码
        // 50:揽收, 44:派送, 80:签收
        $code = self::mapStatus($scanType, $desc);

        if (!$code) {
            return;
        }

        // 1. 尝试匹配母单
        $master = Inpack::where('t_order_sn', $waybillNo)->find();
        
        if ($master) {
            self::updateStatus($master, false, $code, $scanDate, $desc);
            return;
        }

        // 2. 尝试匹配子单
        $sub = InpackItem::where('t_order_sn', $waybillNo)->find();
        
        if ($sub) {
            self::updateStatus($sub, true, $code, $scanDate, $desc);
            return;
        }
    }

    /**
     * 更新状态并发送通知
     * @param \think\Model $orderModel Inpack 或 InpackItem 实例
     * @param bool $isSub 是否子单
     * @param string $code 状态码
     * @param string $time 时间
     * @param string $msg 描述
     */
    private static function updateStatus($orderModel, $isSub, $code, $time, $msg)
    {
        $currentCode = $orderModel['last_trace_code'];
        // 状态保护：已签收(80)不回退
        if ($currentCode == '80' && $code != '80') {
            return; 
        }

        // 更新数据库
        if ($currentCode != $code) {
            $orderModel->save([
                'last_trace_code' => $code
            ]);
        }

        // 获取主单信息（用于发消息）
        if ($isSub) {
            $inpack = Inpack::get($orderModel['inpack_id']);
        } else {
            $inpack = $orderModel;
        }
        
        if (!$inpack) return;

        // 发送通知
        // 派送通知
        if ($code == '44' && $orderModel['is_push_delivered'] == 0) {
            $orderModel->save(['is_push_delivered' => 1]);
            
            $params = [
                'inpack' => $inpack,
                'msg'    => $msg,
                'is_sub_order' => $isSub
            ];
            if ($isSub) $params['item'] = $orderModel;

            Message::send('trace.delivery', $params);
            Log::info("[ZTO Push] Sending Delivery Msg for {$orderModel['t_order_sn']}");
        }
        // 签收通知
        elseif ($code == '80' && $orderModel['is_push_signed'] == 0) {
            $orderModel->save(['is_push_signed' => 1]);

             $params = [
                'inpack' => $inpack,
                'time'   => $time,
                'is_sub_order' => $isSub
            ];
            if ($isSub) $params['item'] = $orderModel;

            Message::send('trace.signed', $params);
            Log::info("[ZTO Push] Sending Signed Msg for {$orderModel['t_order_sn']}");
        }
    }

    private static function mapStatus($scanType, $desc)
    {
        $type = strtoupper(strval($scanType));
        
        // --- 签收类 ---
        if (in_array($type, ['签收', 'SIGNED', '5'])) {
            return '80';
        }

        // --- 派送类 ---
        // 派件, DELIVERY(上门), ARRIVAL(入柜-通常视为待取/派送中)
        // 4 (派件)
        if (in_array($type, ['派件', 'DELIVERY', 'ARRIVAL', '4'])) {
            return '44';
        }

        // --- 揽收类 ---
        // 收件, 1
        if (in_array($type, ['收件', 'GOT', '1'])) {
            return '50';
        }
        
        // --- 关键字兜底 ---
        if (strpos($desc, '签收') !== false) return '80';
        if (strpos($desc, '派送') !== false || strpos($desc, '派件') !== false) return '44';
        if (strpos($desc, '揽收') !== false || strpos($desc, '收件') !== false) return '50';

        return null;
    }
}
