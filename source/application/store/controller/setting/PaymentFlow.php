<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\common\model\Inpack;
use app\common\model\recharge\Order as RechargeOrder;
use app\common\model\Order;
use app\common\model\User;
use app\store\model\Setting;

/**
 * 支付流水
 * Class PaymentFlow
 * @package app\store\controller\setting
 */
class PaymentFlow extends Controller
{
    /**
     * 支付流水列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $param = $this->request->param();
        
        // 获取筛选条件
        $orderType = isset($param['order_type']) ? $param['order_type'] : ''; // 订单类型：inpack, recharge, shop
        $startDate = isset($param['start_date']) ? $param['start_date'] : '';
        $endDate = isset($param['end_date']) ? $param['end_date'] : '';
        $search = isset($param['search']) ? $param['search'] : ''; // 搜索订单号或用户信息
        
        // 汇总支付流水数据
        $list = $this->getPaymentFlowList($orderType, $startDate, $endDate, $search);
        
        // 统计信息（应用相同的筛选条件）
        $statistics = $this->getStatistics($orderType, $startDate, $endDate, $search);
        
        // 获取设置
        $set = Setting::detail('store')['values'];
        
        // 获取请求对象
        $request = $this->request;
        
        return $this->fetch('index', compact('list', 'orderType', 'startDate', 'endDate', 'search', 'statistics', 'set', 'request'));
    }
    
    /**
     * 获取支付流水列表
     * @param string $orderType 订单类型
     * @param string $startDate 开始日期
     * @param string $endDate 结束日期
     * @param string $search 搜索关键词
     * @return array
     */
    private function getPaymentFlowList($orderType = '', $startDate = '', $endDate = '', $search = '')
    {
        $list = [];
        
        // 集运订单支付流水
        if (empty($orderType) || $orderType == 'inpack') {
            $inpackList = $this->getInpackPayments($startDate, $endDate, $search);
            $list = array_merge($list, $inpackList);
        }
        
        // 充值订单支付流水
        if (empty($orderType) || $orderType == 'recharge') {
            $rechargeList = $this->getRechargePayments($startDate, $endDate, $search);
            $list = array_merge($list, $rechargeList);
        }
        
        // 商城订单支付流水
        if (empty($orderType) || $orderType == 'shop') {
            $shopList = $this->getShopOrderPayments($startDate, $endDate, $search);
            $list = array_merge($list, $shopList);
        }
        
        // 按支付时间倒序排序
        usort($list, function($a, $b) {
            $timeA = is_numeric($a['pay_time']) ? $a['pay_time'] : strtotime($a['pay_time']);
            $timeB = is_numeric($b['pay_time']) ? $b['pay_time'] : strtotime($b['pay_time']);
            return $timeB - $timeA;
        });
        
        // 分页处理（简单分页，实际可以使用更复杂的分页逻辑）
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = 20;
        $total = count($list);
        $list = array_slice($list, ($page - 1) * $limit, $limit);
        
        // 创建分页对象（简化版）
        $paginate = [
            'list' => $list,
            'total' => $total,
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => ceil($total / $limit)
        ];
        
        return $paginate;
    }
    
    /**
     * 获取集运订单支付流水
     */
    private function getInpackPayments($startDate = '', $endDate = '', $search = '')
    {
        $wxappId = Inpack::$wxapp_id;
        $model = new Inpack();
        $query = $model->alias('i')
            ->join('user u', 'u.user_id = i.member_id', 'left')
            ->where('i.is_pay', 1)
            ->where('i.is_delete', 0)
            ->where('i.pay_time', '>', 0)
            ->where('i.is_pay_type', 'in', [0, 1, 3, 4]) // 只统计特定支付类型：0后台操作 1微信 3汉特 4OMIPAY
            ->where('i.wxapp_id', $wxappId)
            ->field('i.id, i.order_sn as order_no, i.real_payment as pay_price, i.pay_time, i.is_pay_type, i.pay_order as transaction_id,
                     u.user_id, u.nickName, u.user_code, i.member_id')
            ->order('i.pay_time', 'desc');
        
        // 时间筛选
        if (!empty($startDate)) {
            $query->where('i.pay_time', '>=', strtotime($startDate));
        }
        if (!empty($endDate)) {
            $query->where('i.pay_time', '<=', strtotime($endDate . ' 23:59:59'));
        }
        
        // 搜索条件
        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('i.order_sn', 'like', "%{$search}%")
                      ->whereOr('u.nickName', 'like', "%{$search}%")
                      ->whereOr('u.user_code', 'like', "%{$search}%");
            });
        }
        
        $data = $query->select();
        $result = [];
        
        foreach ($data as $item) {
            // 处理模型对象，使用 getData() 获取原始数据，避免触发 getter
            if (is_object($item) && method_exists($item, 'getData')) {
                $itemData = $item->getData(); // 获取原始数据，不触发 getter
                // 获取关联数据（用户信息）
                if (method_exists($item, 'user') && $item->user) {
                    $userData = is_object($item->user) ? $item->user->getData() : $item->user;
                    $itemData['user_id'] = $userData['user_id'] ?? 0;
                    $itemData['nickName'] = $userData['nickName'] ?? '';
                    $itemData['user_code'] = $userData['user_code'] ?? '';
                }
            } else {
                $itemData = is_array($item) ? $item : [];
            }
            
            $payTime = isset($itemData['pay_time']) ? $itemData['pay_time'] : 0;
            // pay_time 应该是原始时间戳，直接使用
            if (!is_numeric($payTime)) {
                $payTime = strtotime($payTime);
            }
            $payTime = intval($payTime);
            
            // 对于集运订单，使用 is_pay_type 字段
            $payType = isset($itemData['is_pay_type']) ? $itemData['is_pay_type'] : (isset($itemData['pay_type']) ? $itemData['pay_type'] : 0);
            $payType = intval($payType);
            
            $result[] = [
                'id' => intval($itemData['id'] ?? 0),
                'order_type' => 'inpack',
                'order_type_text' => '集运订单',
                'order_no' => $itemData['order_no'] ?? '',
                'pay_price' => floatval($itemData['pay_price'] ?? 0),
                'pay_time' => $payTime > 0 ? date('Y-m-d H:i:s', $payTime) : '',
                'pay_type' => $this->getPayTypeText($payType),
                'transaction_id' => $itemData['transaction_id'] ?? '',
                'user_id' => intval($itemData['user_id'] ?? 0),
                'nickName' => $itemData['nickName'] ?? '',
                'user_code' => $itemData['user_code'] ?? '',
            ];
        }
        
        return $result;
    }
    
    /**
     * 获取充值订单支付流水
     */
    private function getRechargePayments($startDate = '', $endDate = '', $search = '')
    {
        $wxappId = RechargeOrder::$wxapp_id;
        $model = new RechargeOrder();
        $query = $model->alias('r')
            ->join('user u', 'u.user_id = r.user_id', 'left')
            ->where('r.pay_status', 20) // 20表示支付成功
            ->where('r.wxapp_id', $wxappId)
            ->where(function($query) use ($startDate, $endDate) {
                // 支付时间大于0的订单（正常支付）
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->where('r.pay_time', '>', 0);
                    if (!empty($startDate)) {
                        $q->where('r.pay_time', '>=', strtotime($startDate));
                    }
                    if (!empty($endDate)) {
                        $q->where('r.pay_time', '<=', strtotime($endDate . ' 23:59:59'));
                    }
                })
                // 或者支付时间为0但已支付的订单（线下支付，使用创建时间筛选）
                ->whereOr(function($q) use ($startDate, $endDate) {
                    $q->where('r.pay_time', '=', 0)
                      ->where('r.pay_status', 20);
                    if (!empty($startDate)) {
                        $q->where('r.create_time', '>=', strtotime($startDate));
                    }
                    if (!empty($endDate)) {
                        $q->where('r.create_time', '<=', strtotime($endDate . ' 23:59:59'));
                    }
                });
            })
            ->field('r.order_id as id, r.order_no, r.pay_price, r.pay_time, r.create_time, r.transaction_id, 
                     u.user_id, u.nickName, u.user_code, r.user_id as member_id');
        
        // 搜索条件
        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('r.order_no', 'like', "%{$search}%")
                      ->whereOr('u.nickName', 'like', "%{$search}%")
                      ->whereOr('u.user_code', 'like', "%{$search}%");
            });
        }
        
        $data = $query->select();
        $result = [];
        
        foreach ($data as $item) {
            // 处理模型对象，使用 getData() 获取原始数据，避免触发 getter
            if (is_object($item) && method_exists($item, 'getData')) {
                $itemData = $item->getData(); // 获取原始数据，不触发 getter
                // 获取关联数据（用户信息）
                if (method_exists($item, 'user') && $item->user) {
                    $userData = is_object($item->user) ? $item->user->getData() : $item->user;
                    $itemData['user_id'] = $userData['user_id'] ?? 0;
                    $itemData['nickName'] = $userData['nickName'] ?? '';
                    $itemData['user_code'] = $userData['user_code'] ?? '';
                }
            } else {
                $itemData = is_array($item) ? $item : [];
            }
            
            $payTime = isset($itemData['pay_time']) ? $itemData['pay_time'] : 0;
            // pay_time 应该是原始时间戳，直接使用
            if (!is_numeric($payTime)) {
                $payTime = strtotime($payTime);
            }
            $payTime = intval($payTime);
            
            // 如果 pay_time 为 0（线下支付），使用 create_time
            if ($payTime == 0 && isset($itemData['create_time'])) {
                $createTime = $itemData['create_time'];
                if (!is_numeric($createTime)) {
                    $createTime = strtotime($createTime);
                }
                $payTime = intval($createTime);
            }
            
            // 充值订单没有 pay_type 字段，根据 transaction_id 推断支付方式
            // 如果有 transaction_id，可能是微信支付或OMIPAY；如果没有，可能是汉特支付或其他
            $payType = 0;
            if (!empty($itemData['transaction_id'])) {
                // 有交易号，可能是微信支付(20)或OMIPAY，默认为微信支付
                $payType = 20; // 微信支付
            } else {
                // 没有交易号，可能是汉特支付(30)或其他，默认为汉特支付
                $payType = 30; // 汉特支付
            }
            
            $result[] = [
                'id' => intval($itemData['id'] ?? 0),
                'order_type' => 'recharge',
                'order_type_text' => '充值订单',
                'order_no' => $itemData['order_no'] ?? '',
                'pay_price' => floatval($itemData['pay_price'] ?? 0),
                'pay_time' => $payTime > 0 ? date('Y-m-d H:i:s', $payTime) : '',
                'pay_type' => $this->getPayTypeText($payType),
                'transaction_id' => $itemData['transaction_id'] ?? '',
                'user_id' => intval($itemData['user_id'] ?? 0),
                'nickName' => $itemData['nickName'] ?? '',
                'user_code' => $itemData['user_code'] ?? '',
                '_sort_time' => $payTime, // 用于排序
            ];
        }
        
        // 对结果进行排序：按 _sort_time 降序
        usort($result, function($a, $b) {
            $timeA = isset($a['_sort_time']) ? $a['_sort_time'] : 0;
            $timeB = isset($b['_sort_time']) ? $b['_sort_time'] : 0;
            return $timeB - $timeA; // 降序
        });
        
        // 移除临时排序字段
        foreach ($result as &$item) {
            unset($item['_sort_time']);
        }
        unset($item);
        
        return $result;
    }
    
    /**
     * 获取商城订单支付流水
     */
    private function getShopOrderPayments($startDate = '', $endDate = '', $search = '')
    {
        $wxappId = Order::$wxapp_id;
        $model = new Order();
        $query = $model->alias('o')
            ->join('user u', 'u.user_id = o.user_id', 'left')
            ->where('o.pay_status', 20) // 20表示支付成功
            ->where('o.pay_time', '>', 0)
            ->where('o.pay_type', '<>', 10) // 排除余额支付（10表示余额支付）
            ->where('o.wxapp_id', $wxappId)
            ->field('o.order_id as id, o.order_no, o.pay_price, o.pay_time, o.pay_type, o.transaction_id,
                     u.user_id, u.nickName, u.user_code, o.user_id as member_id')
            ->order('o.pay_time', 'desc');
        
        // 时间筛选
        if (!empty($startDate)) {
            $query->where('o.pay_time', '>=', strtotime($startDate));
        }
        if (!empty($endDate)) {
            $query->where('o.pay_time', '<=', strtotime($endDate . ' 23:59:59'));
        }
        
        // 搜索条件
        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('o.order_no', 'like', "%{$search}%")
                      ->whereOr('u.nickName', 'like', "%{$search}%")
                      ->whereOr('u.user_code', 'like', "%{$search}%");
            });
        }
        
        $data = $query->select();
        $result = [];
        
        foreach ($data as $item) {
            // 处理模型对象，使用 getData() 获取原始数据，避免触发 getter
            if (is_object($item) && method_exists($item, 'getData')) {
                $itemData = $item->getData(); // 获取原始数据，不触发 getter
                // 获取关联数据（用户信息）
                if (method_exists($item, 'user') && $item->user) {
                    $userData = is_object($item->user) ? $item->user->getData() : $item->user;
                    $itemData['user_id'] = $userData['user_id'] ?? 0;
                    $itemData['nickName'] = $userData['nickName'] ?? '';
                    $itemData['user_code'] = $userData['user_code'] ?? '';
                }
            } else {
                $itemData = is_array($item) ? $item : [];
            }
            
            $payTime = isset($itemData['pay_time']) ? $itemData['pay_time'] : 0;
            // pay_time 应该是原始时间戳，直接使用
            if (!is_numeric($payTime)) {
                $payTime = strtotime($payTime);
            }
            $payTime = intval($payTime);
            
            $payType = isset($itemData['pay_type']) ? $itemData['pay_type'] : 0;
            $payType = intval($payType);
            
            $result[] = [
                'id' => intval($itemData['id'] ?? 0),
                'order_type' => 'shop',
                'order_type_text' => '商城订单',
                'order_no' => $itemData['order_no'] ?? '',
                'pay_price' => floatval($itemData['pay_price'] ?? 0),
                'pay_time' => $payTime > 0 ? date('Y-m-d H:i:s', $payTime) : '',
                'pay_type' => $this->getPayTypeText($payType),
                'transaction_id' => $itemData['transaction_id'] ?? '',
                'user_id' => intval($itemData['user_id'] ?? 0),
                'nickName' => $itemData['nickName'] ?? '',
                'user_code' => $itemData['user_code'] ?? '',
            ];
        }
        
        return $result;
    }
    
    /**
     * 获取支付方式文本
     */
    private function getPayTypeText($payType)
    {
        // 如果 $payType 是数组或对象，提取实际值
        if (is_array($payType)) {
            // 如果数组中有 'text' 键，直接返回（已经是处理过的文本）
            if (isset($payType['text'])) {
                return $payType['text'];
            }
            // 否则提取 value
            $payType = isset($payType['value']) ? $payType['value'] : (isset($payType[0]) ? $payType[0] : 0);
        } elseif (is_object($payType)) {
            // 如果对象有 text 属性，直接返回
            if (isset($payType->text)) {
                return $payType->text;
            }
            $payType = isset($payType->value) ? $payType->value : (isset($payType->pay_type) ? $payType->pay_type : 0);
        }
        
        // 确保是标量值
        $payType = intval($payType);
        
        // 集运订单的支付类型：0后台操作 1微信 2余额 3汉特 4OMIPAY 5现金支付 6线下支付
        // 商城订单的支付类型：10余额 20微信 30支付宝 40其他
        $payTypes = [
            0 => '后台操作',
            1 => '微信支付',
            2 => '余额支付',
            3 => '汉特支付',
            4 => 'OMIPAY',
            5 => '现金支付',
            6 => '线下支付',
            10 => '余额支付',
            20 => '微信支付',
            30 => '支付宝支付',
            40 => '其他支付',
        ];
        
        return isset($payTypes[$payType]) ? $payTypes[$payType] : '未知(' . $payType . ')';
    }
    
    /**
     * 获取统计信息
     */
    private function getStatistics($orderType = '', $startDate = '', $endDate = '', $search = '')
    {
        $statistics = [
            'total_count' => 0,
            'total_amount' => 0,
            'inpack_count' => 0,
            'inpack_amount' => 0,
            'recharge_count' => 0,
            'recharge_amount' => 0,
            'shop_count' => 0,
            'shop_amount' => 0,
        ];
        
        // 集运订单统计 - 直接使用列表查询结果计算，确保100%一致
        if (empty($orderType) || $orderType == 'inpack') {
            // 直接调用列表查询方法获取数据
            $inpackList = $this->getInpackPayments($startDate, $endDate, $search);
            
            // 统计数量和金额
            $statistics['inpack_count'] = count($inpackList);
            $statistics['inpack_amount'] = 0;
            foreach ($inpackList as $item) {
                $statistics['inpack_amount'] += floatval($item['pay_price'] ?? 0);
            }
        }
        
        // 充值订单统计 - 直接使用列表查询结果计算，确保100%一致
        if (empty($orderType) || $orderType == 'recharge') {
            // 直接调用列表查询方法获取数据
            $rechargeList = $this->getRechargePayments($startDate, $endDate, $search);
            
            // 统计数量和金额
            $statistics['recharge_count'] = count($rechargeList);
            $statistics['recharge_amount'] = 0;
            foreach ($rechargeList as $item) {
                $statistics['recharge_amount'] += floatval($item['pay_price'] ?? 0);
            }
        }
        
        // 商城订单统计 - 直接使用列表查询结果计算，确保100%一致
        if (empty($orderType) || $orderType == 'shop') {
            // 直接调用列表查询方法获取数据
            $shopList = $this->getShopOrderPayments($startDate, $endDate, $search);
            
            // 统计数量和金额
            $statistics['shop_count'] = count($shopList);
            $statistics['shop_amount'] = 0;
            foreach ($shopList as $item) {
                $statistics['shop_amount'] += floatval($item['pay_price'] ?? 0);
            }
        }
        
        // 总计
        $statistics['total_count'] = $statistics['inpack_count'] + $statistics['recharge_count'] + $statistics['shop_count'];
        $statistics['total_amount'] = $statistics['inpack_amount'] + $statistics['recharge_amount'] + $statistics['shop_amount'];
        
        return $statistics;
    }
}

