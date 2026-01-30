<?php

namespace app\common\service\trace;

use app\common\model\Inpack;
use app\common\model\InpackItem;
use app\common\service\Message;
use think\Log;

/**
 * 京东物流回调处理服务
 */
class JdService
{
    /**
     * 处理轨迹推送
     * @param array $data
     * @return array
     */
    public static function handleCallback($data)
    {
        // 兼容单条或多条推送 (虽通常 JD 是单条，但为了健壮性)
        // 假设标准结构: { waybillCode, opeTitle, opeTime, opeCode ... }
        
        // 如果是数组列表
        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as $item) {
                self::processSingleTrace($item);
            }
        } else {
            self::processSingleTrace($data);
        }

        return ['code' => 200, 'message' => 'success'];
    }

    private static function processSingleTrace($trace)
    {
        $waybillNo = isset($trace['waybillCode']) ? $trace['waybillCode'] : '';
        $opeTitle  = isset($trace['opeTitle']) ? $trace['opeTitle'] : '';
        $opeTime   = isset($trace['opeTime']) ? $trace['opeTime'] : date('Y-m-d H:i:s');
        $opeCode   = isset($trace['opeCode']) ? $trace['opeCode'] : ''; // 可能是数字也可能是字符串

        if (empty($waybillNo)) {
            return;
        }

        Log::info("JD Trace Callback: No={$waybillNo} Code={$opeCode} Msg={$opeTitle}");

        // 统一业务状态 (映射到 SF 的标准: 44派送, 80签收, 50揽收)
        $mappedCode = self::mapStatus($opeCode, $opeTitle);
        
        // 如果无法映射业务状态，更新 raw code 也可以，但我们主要关注派送和签收
        if (!$mappedCode) {
             // 记录一下，但不强行改变业务逻辑，除非为了显示
             // 暂时用 0 或其他标识? 还是直接存 opeCode?
             // 为了前端显示 Badges，最好存统一的 Standard Code。
             // 如果不匹配，就存原始的 code 吧，前端再根据情况显示(或者不显示Badge)
             $mappedCode = $opeCode;
        }

        // 1. 尝试匹配主单
        $master = Inpack::where('t_order_sn', $waybillNo)->find();
        if ($master) {
            self::updateMasterStatus($master, $mappedCode, $opeTime, $opeTitle);
            return;
        }

        // 2. 尝试匹配子单
        $sub = InpackItem::where('t_order_sn', $waybillNo)->find();
        if ($sub) {
            self::updateSubStatus($sub, $mappedCode, $opeTime, $opeTitle);
            return;
        }
        
        Log::info("JD Trace Callback: Waybill {$waybillNo} not found in DB.");
    }

    private static function updateMasterStatus($inpack, $code, $time, $msg)
    {
        // 更新DB
        if ($inpack['last_trace_code'] != $code) {
            $inpack->save([
                'last_trace_code' => $code,
                'last_trace_time' => $time
            ]);
        }

        $semantic = self::getSemanticStatus($code);

        // 派送通知
        if ($semantic === 'delivering' && $inpack['is_push_delivered'] == 0) {
            $inpack->save(['is_push_delivered' => 1]);
            Message::send('trace.delivery', [
                'inpack' => $inpack,
                'msg'    => $msg,
                'is_sub_order' => false
            ]);
        }
        // 签收通知
        elseif ($semantic === 'signed' && $inpack['is_push_signed'] == 0) {
             $inpack->save(['is_push_signed' => 1]);
             Message::send('trace.signed', [
                'inpack' => $inpack,
                'time'   => $time,
                'is_sub_order' => false
            ]);
        }
    }

    private static function updateSubStatus($item, $code, $time, $msg)
    {
        if ($item['last_trace_code'] != $code) {
            $item->save([
                'last_trace_code' => $code,
                'last_trace_time' => $time
            ]);
        }

        $semantic = self::getSemanticStatus($code);
        $inpack = Inpack::get($item['inpack_id']);
        if (!$inpack) return;

        if ($semantic === 'delivering' && $item['is_push_delivered'] == 0) {
            $item->save(['is_push_delivered' => 1]);
            Message::send('trace.delivery', [
                'inpack' => $inpack,
                'item'   => $item,
                'msg'    => $msg,
                'is_sub_order' => true
            ]);
        }
        elseif ($semantic === 'signed' && $item['is_push_signed'] == 0) {
             $item->save(['is_push_signed' => 1]);
             Message::send('trace.signed', [
                'inpack' => $inpack,
                'item'   => $item,
                'time'   => $time,
                'is_sub_order' => true
            ]);
        }
    }

    /**
     * 将京东的状态映射为我们系统的标准状态码
     * Standard: 50(揽收), 44(派送), 80(签收)
     */
    private static function mapStatus($jdCode, $jdDesc)
    {
        // 逻辑优先匹配 opeCode，如果 opeCode 不明确，则匹配 opeTitle 关键字
        // 假设 JD Code: 揽收=10/11, 派送=40/41, 签收=80 (仅举例，需根据实际情况)
        // 更稳妥的是匹配关键字
        
        if (strpos($jdDesc, '派件') !== false || strpos($jdDesc, '派送') !== false) {
            return '44';
        }
        if (strpos($jdDesc, '签收') !== false || strpos($jdDesc, '妥投') !== false || strpos($jdDesc, '取件') !== false && strpos($jdDesc, '客户') !== false) {
            return '80';
        }
        if (strpos($jdDesc, '揽收') !== false || strpos($jdDesc, '揽件') !== false) {
            return '50';
        }
        
        return null;
    }
    
    // 语义解析
    private static function getSemanticStatus($code)
    {
        if ($code == '44') return 'delivering';
        if ($code == '80') return 'signed';
        return 'other';
    }
}
