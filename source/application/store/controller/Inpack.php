<?php
namespace app\store\controller;

use app\common\service\OrderBatchPrinter;
use app\common\service\AsyncTaskQueue;

/**
 * 集运订单批量操作控制器
 * Class Inpack
 * @package app\store\controller
 */
class Inpack extends Controller
{
    /**
     * 批量打印云面单
     * @return array
     */
    public function orderbatchprinter()
    {
        $params = $this->request->post();
        $orderIds = isset($params['order_ids']) ? (array)$params['order_ids'] : [];
        $ditchId = isset($params['ditch_id']) ? $params['ditch_id'] : 0;
        $async = isset($params['async']) ? ($params['async'] === 'true' || $params['async'] === true || $params['async'] === 1) : false;
        
        if (empty($orderIds) || !$ditchId) {
            return $this->renderError('参数错误');
        }

        $printOptions = [
            'label' => isset($params['label']) ? $params['label'] : 60,
            'print_all' => 1,  // 批量打印强制打印全部包裹（母单+子单）
            'async' => $async,
            'priority' => isset($params['priority']) ? $params['priority'] : 5,
            'waybill_no' => isset($params['waybill_no']) ? $params['waybill_no'] : ''
        ];

        $result = OrderBatchPrinter::print($orderIds, $ditchId, $printOptions);
        
        return $this->renderSuccess('操作成功', '', $result);
    }

    /**
     * 异步任务队列管理（查询状态）
     * @return array
     */
    public function asynctaskqueue()
    {
        $params = $this->request->get();
        $action = isset($params['action']) ? $params['action'] : '';
        $taskId = isset($params['task_id']) ? $params['task_id'] : 0;

        if ($action === 'getTaskStatus' && $taskId) {
            $status = AsyncTaskQueue::getTaskStatus($taskId);
            if ($status) {
                return $this->renderSuccess('success', '', $status);
            }
            return $this->renderError('任务不存在');
        }

        return $this->renderError('无效请求');
    }

    /**
     * 批量打印多渠道订单（自动渠道检测）
     * 
     * POST /store/inpack/batchPrint
     * 请求体: {
     *   'order_ids': ['id1', 'id2', 'id3']
     * }
     * 
     * @return array 打印结果
     */
    public function batchPrint()
    {
        try {
            // 获取请求参数
            $params = $this->request->post();
            $orderIds = isset($params['order_ids']) ? (array)$params['order_ids'] : [];

            // 验证参数
            if (empty($orderIds)) {
                return $this->renderError('未选择订单');
            }

            // 验证订单ID格式（必须是数字或字符串）
            $validOrderIds = [];
            foreach ($orderIds as $orderId) {
                if (!empty($orderId)) {
                    $validOrderIds[] = $orderId;
                }
            }

            if (empty($validOrderIds)) {
                return $this->renderError('订单ID无效');
            }

            // 调用批量打印服务
            $result = OrderBatchPrinter::batchPrintMultiChannel($validOrderIds);

            // 返回结果
            if ($result['success']) {
                return $this->renderSuccess('批量打印完成', '', $result);
            } else {
                return $this->renderSuccess('批量打印完成（部分失败）', '', $result);
            }

        } catch (\Exception $e) {
            // 异常处理
            return $this->renderError('批量打印异常: ' . $e->getMessage());
        }
    }
}
