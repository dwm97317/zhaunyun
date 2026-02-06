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
            'print_all' => isset($params['print_all']) ? $params['print_all'] : 0,
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
}
