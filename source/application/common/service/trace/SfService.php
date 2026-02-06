<?php

namespace app\common\service\trace;

use app\common\model\Inpack;
use app\common\model\InpackItem;
use app\common\service\Message;
use think\Log;

/**
 * 顺丰回调处理服务
 */
class SfService
{
    /**
     * 处理路由推送 (Route Push)
     * @param array $data 顺丰推送的JSON转换后的数组
     * @return array
     */
    public static function handleRouteCallback($data)
    {
        // 顺丰真实推送格式: {"requestId":"...","timestamp":"...","orderState":[{...}]}
        // 兼容旧格式: {"Body":{"WaybillRoute":[...]}}
        
        $routes = [];
        
        // 格式1: 新版路由推送 (orderState 数组)
        if (isset($data['orderState']) && is_array($data['orderState'])) {
            $routes = $data['orderState'];
        }
        // 格式2: 旧版路由推送 (Body.WaybillRoute)
        elseif (isset($data['Body']['WaybillRoute'])) {
             // 可能是单个对象或数组
             if (isset($data['Body']['WaybillRoute']['id'])) {
                 $routes[] = $data['Body']['WaybillRoute'];
             } else {
                 $routes = $data['Body']['WaybillRoute'];
             }
        }
        // 格式3: 简单结构
        elseif (isset($data['waybillNo'])) {
             $routes[] = $data;
        }

        foreach ($routes as $route) {
            self::processSingleRoute($route);
        }

        return ['head' => 'OK', 'code' => 'OK'];
    }

    private static function processSingleRoute($route)
    {
        // 兼容多种字段名
        // waybillNo (新版) 或 mailno (旧版)
        $waybillNo = '';
        if (isset($route['waybillNo'])) {
            $waybillNo = $route['waybillNo'];
        } elseif (isset($route['mailno'])) {
            $waybillNo = $route['mailno'];
        }
        
        // orderNo (新版) 或 orderid (旧版)
        $orderNo = '';
        if (isset($route['orderNo'])) {
            $orderNo = $route['orderNo'];
        } elseif (isset($route['orderid'])) {
            $orderNo = $route['orderid'];
        }
        
        // opCode 可能是 opCode 或 orderStateCode
        $opCode = '';
        if (isset($route['opCode'])) {
            $opCode = (string)$route['opCode'];
        } elseif (isset($route['orderStateCode'])) {
            $opCode = (string)$route['orderStateCode'];
        }
        
        // acceptTime 可能是 acceptTime, createTm, 或 lastTime
        $acceptTime = '';
        if (isset($route['acceptTime'])) {
            $acceptTime = $route['acceptTime'];
        } elseif (isset($route['createTm'])) {
            $acceptTime = $route['createTm'];
        } elseif (isset($route['lastTime']) && $route['lastTime']) {
            $acceptTime = $route['lastTime'];
        } else {
            $acceptTime = date('Y-m-d H:i:s');
        }
        
        // remark 可能是 remark 或 orderStateDesc
        $remark = '';
        if (isset($route['remark'])) {
            $remark = $route['remark'];
        } elseif (isset($route['orderStateDesc'])) {
            $remark = $route['orderStateDesc'];
        }
        
        if (empty($waybillNo) || empty($opCode)) {
            return;
        }
        
        Log::info("SF Trace Callback: No={$waybillNo} OrderNo={$orderNo} Op={$opCode} Desc={$remark}");

        // 1. 尝试匹配主单 (Master Order)
        $master = Inpack::where('t_order_sn', $waybillNo)->find();
        if ($master) {
            self::updateMasterStatus($master, $opCode, $acceptTime, $remark);
            return;
        }

        // 2. 尝试匹配子单 (Sub Order)
        $sub = InpackItem::where('t_order_sn', $waybillNo)->find();
        if ($sub) {
            self::updateSubStatus($sub, $opCode, $acceptTime, $remark);
            return;
        }
        
        Log::info("SF Trace Callback: Waybill {$waybillNo} not found in DB.");
    }

    /**
     * 更新主单状态
     */
    private static function updateMasterStatus($inpack, $code, $time, $msg)
    {
        // 更新最新状态
        if ($inpack['last_trace_code'] !== $code) {
             $inpack->save([
                'last_trace_code' => $code,
                'last_trace_time' => $time
            ]);
        }

        $status = self::parseStatus($code);
        
        // 派送通知
        if ($status === 'delivering' && $inpack['is_push_delivered'] == 0) {
            $inpack->save(['is_push_delivered' => 1]);
            Message::send('trace.delivery', [
                'inpack' => $inpack,
                'msg'    => $msg,
                'is_sub_order' => false
            ]);
        }
        // 签收通知
        elseif ($status === 'signed' && $inpack['is_push_signed'] == 0) {
             $inpack->save(['is_push_signed' => 1]);
             Message::send('trace.signed', [
                'inpack' => $inpack,
                'time'   => $time,
                'is_sub_order' => false
            ]);
        }
    }

    /**
     * 更新子单状态
     */
    private static function updateSubStatus($item, $code, $time, $msg)
    {
        // 更新子单表
        if ($item['last_trace_code'] !== $code) {
            $item->save([
                'last_trace_code' => $code,
                'last_trace_time' => $time
            ]);
        }

        $status = self::parseStatus($code);
        
        // 子单通知逻辑
        // 需要获取主单信息 (Inpack) 因为 wxapp_id 和 member_id 在主单上
        $inpack = Inpack::get($item['inpack_id']);
        if (!$inpack) return;

        // 派送通知
        if ($status === 'delivering' && $item['is_push_delivered'] == 0) {
            $item->save(['is_push_delivered' => 1]);
            Message::send('trace.delivery', [
                'inpack' => $inpack, // 传主单用于获取用户信息
                'item'   => $item,   // 传子单用于文案
                'msg'    => $msg,
                'is_sub_order' => true
            ]);
        }
        // 签收通知
        elseif ($status === 'signed' && $item['is_push_signed'] == 0) {
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
     * 状态码解析
     * 顺丰状态码格式: XX-XXXXX (如 04-40037, 05-40003, 44, 80)
     */
    private static function parseStatus($opCode)
    {
        $opCode = (string)$opCode;
        
        // 提取前缀（如果是 XX-XXXXX 格式）
        if (strpos($opCode, '-') !== false) {
            $prefix = substr($opCode, 0, 2);
        } else {
            $prefix = $opCode;
        }
        
        // 状态映射
        // 04-xxxxx: 下单/接收
        // 05-xxxxx: 已收件
        // 44: 派件中
        // 80: 已签收
        if ($prefix === '04') {
            return 'collected'; // 已接收/已揽收
        } elseif ($prefix === '05') {
            return 'collected'; // 已收件（也算揽收完成）
        } elseif ($prefix === '44' || $opCode === '44') {
            return 'delivering'; // 派件中
        } elseif ($prefix === '80' || $opCode === '80') {
            return 'signed'; // 已签收
        } elseif (in_array($opCode, ['50', '3036'])) {
            return 'collected'; // 兼容旧格式
        }
        
        return 'other';
    }
}
