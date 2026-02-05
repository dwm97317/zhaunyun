<?php

namespace app\common\service;

use app\common\service\PrintLogger;
use app\store\model\Inpack;
use think\Db;

/**
 * 集运订单打印状态管理工具
 * 
 * 功能：管理集运订单（Inpack）的打印状态，让客户更直观地看到订单状态
 * 
 * 特性：
 * - 标记订单为"已批量打印"
 * - 记录打印时间
 * - 查询打印状态
 * - 批量更新打印状态
 * - 统一日志记录
 * 
 * 状态说明：
 * - print_status_jhd = 0: 未打印
 * - print_status_jhd = 1: 已打印（单个打印）
 * - print_status_jhd = 2: 已批量打印
 */
class InpackPrintStatus
{
    /**
     * 标记订单为已批量打印
     * 
     * @param int $orderId 订单ID
     * @param array $printInfo 打印信息（可选，预留扩展）
     * @return bool 是否成功
     */
    public static function markAsBatchPrinted($orderId, array $printInfo = [])
    {
        try {
            $inpack = Inpack::detail($orderId);
            if (!$inpack) {
                PrintLogger::error('集运订单打印状态', '订单不存在', ['order_id' => $orderId]);
                return false;
            }
            
            // 更新打印状态为"已批量打印"
            $updateData = [
                'print_status_jhd' => 2,  // 2 = 已批量打印
                'updated_time' => date('Y-m-d H:i:s')
            ];
            
            $result = $inpack->save($updateData);
            
            if ($result) {
                PrintLogger::success('集运订单打印状态', '标记为已批量打印', [
                    'order_id' => $orderId,
                    'order_sn' => $inpack['order_sn']
                ]);
                return true;
            } else {
                PrintLogger::error('集运订单打印状态', '更新失败', ['order_id' => $orderId]);
                return false;
            }
            
        } catch (\Exception $e) {
            PrintLogger::error('集运订单打印状态', '标记异常', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 批量标记订单为已批量打印
     * 
     * @param array $orderIds 订单ID数组
     * @param array $printInfo 打印信息（可选，预留扩展）
     * @return array 结果统计
     *   - success_count: 成功数量
     *   - error_count: 失败数量
     *   - total: 总数量
     */
    public static function batchMarkAsPrinted(array $orderIds, array $printInfo = [])
    {
        $successCount = 0;
        $errorCount = 0;
        
        PrintLogger::info('集运订单打印状态', '开始批量标记', [
            'total_orders' => count($orderIds)
        ]);
        
        foreach ($orderIds as $orderId) {
            if (self::markAsBatchPrinted($orderId, $printInfo)) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }
        
        PrintLogger::success('集运订单打印状态', '批量标记完成', [
            'total' => count($orderIds),
            'success' => $successCount,
            'error' => $errorCount
        ]);
        
        return [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total' => count($orderIds)
        ];
    }
    
    /**
     * 获取订单的打印状态
     * 
     * @param int $orderId 订单ID
     * @return array|null 打印状态信息
     *   - status: 状态码（0=未打印, 1=已打印, 2=已批量打印）
     *   - status_text: 状态文本
     *   - updated_time: 更新时间
     */
    public static function getPrintStatus($orderId)
    {
        try {
            $inpack = Inpack::detail($orderId);
            if (!$inpack) {
                return null;
            }
            
            $status = isset($inpack['print_status_jhd']) ? (int)$inpack['print_status_jhd'] : 0;
            $statusText = self::getStatusText($status);
            
            return [
                'order_id' => $orderId,
                'order_sn' => $inpack['order_sn'],
                'status' => $status,
                'status_text' => $statusText,
                'updated_time' => isset($inpack['updated_time']) ? $inpack['updated_time'] : null
            ];
            
        } catch (\Exception $e) {
            PrintLogger::error('集运订单打印状态', '查询异常', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * 批量获取订单的打印状态
     * 
     * @param array $orderIds 订单ID数组
     * @return array 打印状态列表
     */
    public static function batchGetPrintStatus(array $orderIds)
    {
        $results = [];
        
        foreach ($orderIds as $orderId) {
            $status = self::getPrintStatus($orderId);
            if ($status) {
                $results[] = $status;
            }
        }
        
        return $results;
    }
    
    /**
     * 获取状态文本
     * 
     * @param int $status 状态码
     * @return string 状态文本
     */
    public static function getStatusText($status)
    {
        $statusMap = [
            0 => '未打印',
            1 => '已打印',
            2 => '已批量打印'
        ];
        
        return isset($statusMap[$status]) ? $statusMap[$status] : '未知状态';
    }
    
    /**
     * 获取批量打印的订单列表
     * 
     * @param array $filters 过滤条件
     *   - start_time: 开始时间
     *   - end_time: 结束时间
     *   - limit: 限制数量（默认100）
     * @return array 订单列表
     */
    public static function getBatchPrintedOrders(array $filters = [])
    {
        try {
            $query = Inpack::where('print_status_jhd', 2)
                ->where('is_delete', 0);
            
            // 按时间范围过滤
            if (isset($filters['start_time'])) {
                $query->where('updated_time', '>=', $filters['start_time']);
            }
            if (isset($filters['end_time'])) {
                $query->where('updated_time', '<=', $filters['end_time']);
            }
            
            // 限制数量
            $limit = isset($filters['limit']) ? (int)$filters['limit'] : 100;
            
            $orders = $query->order('updated_time DESC')
                ->limit($limit)
                ->select();
            
            $results = [];
            foreach ($orders as $order) {
                $results[] = [
                    'order_id' => $order['id'],
                    'order_sn' => $order['order_sn'],
                    'status' => $order['print_status_jhd'],
                    'status_text' => self::getStatusText($order['print_status_jhd']),
                    'updated_time' => $order['updated_time']
                ];
            }
            
            PrintLogger::info('集运订单打印状态', '查询批量打印订单', [
                'filters' => $filters,
                'count' => count($results)
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            PrintLogger::error('集运订单打印状态', '查询异常', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * 重置打印状态（用于测试或重新打印）
     * 
     * @param int $orderId 订单ID
     * @return bool 是否成功
     */
    public static function resetPrintStatus($orderId)
    {
        try {
            $inpack = Inpack::detail($orderId);
            if (!$inpack) {
                PrintLogger::error('集运订单打印状态', '订单不存在', ['order_id' => $orderId]);
                return false;
            }
            
            $result = $inpack->save([
                'print_status_jhd' => 0,  // 0 = 未打印
                'updated_time' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                PrintLogger::success('集运订单打印状态', '重置打印状态', [
                    'order_id' => $orderId,
                    'order_sn' => $inpack['order_sn']
                ]);
                return true;
            } else {
                PrintLogger::error('集运订单打印状态', '重置失败', ['order_id' => $orderId]);
                return false;
            }
            
        } catch (\Exception $e) {
            PrintLogger::error('集运订单打印状态', '重置异常', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 获取打印状态统计
     * 
     * @param array $filters 过滤条件
     *   - start_time: 开始时间
     *   - end_time: 结束时间
     * @return array 统计信息
     */
    public static function getPrintStatistics(array $filters = [])
    {
        try {
            $query = Inpack::where('is_delete', 0);
            
            // 按时间范围过滤
            if (isset($filters['start_time'])) {
                $query->where('updated_time', '>=', $filters['start_time']);
            }
            if (isset($filters['end_time'])) {
                $query->where('updated_time', '<=', $filters['end_time']);
            }
            
            // 统计各状态数量
            $total = $query->count();
            $notPrinted = (clone $query)->where('print_status_jhd', 0)->count();
            $printed = (clone $query)->where('print_status_jhd', 1)->count();
            $batchPrinted = (clone $query)->where('print_status_jhd', 2)->count();
            
            $statistics = [
                'total' => $total,
                'not_printed' => $notPrinted,
                'printed' => $printed,
                'batch_printed' => $batchPrinted,
                'not_printed_percent' => $total > 0 ? round($notPrinted / $total * 100, 2) : 0,
                'printed_percent' => $total > 0 ? round($printed / $total * 100, 2) : 0,
                'batch_printed_percent' => $total > 0 ? round($batchPrinted / $total * 100, 2) : 0
            ];
            
            PrintLogger::info('集运订单打印状态', '统计打印状态', $statistics);
            
            return $statistics;
            
        } catch (\Exception $e) {
            PrintLogger::error('集运订单打印状态', '统计异常', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
