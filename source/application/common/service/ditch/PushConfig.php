<?php

namespace app\common\service\ditch;

use app\common\model\Inpack;
use app\common\model\User;
use app\common\model\UserAddress;
use app\common\model\Line;
use think\Log;

/**
 * 渠道推送配置服务
 * 用于管理推送字段字典及动态渲染推送内容
 */
class PushConfig
{
    /**
     * 获取全量字段字典
     * @return array
     */
    public static function getFieldDictionary()
    {
        return [
            'order' => [
                'label' => '订单信息',
                'fields' => [
                    ['key' => 'order_sn', 'label' => '集运单号', 'type' => 'string', 'example' => 'JY202310270001'],
                    ['key' => 't_order_sn', 'label' => '转运单号', 'type' => 'string', 'example' => '7712345678'],
                    ['key' => 'weight', 'label' => '订单重量(kg)', 'type' => 'number', 'example' => '5.20'],
                    ['key' => 'real_payment', 'label' => '实付金额', 'type' => 'number', 'example' => '128.00'],
                    ['key' => 'total_pay_price', 'label' => '总费用', 'type' => 'number', 'example' => '150.00'],
                    ['key' => 'remark', 'label' => '订单备注', 'type' => 'string', 'example' => '请尽快发货'],
                    ['key' => 'created_time', 'label' => '下单时间', 'type' => 'datetime', 'example' => '2023-10-27 10:00:00'],
                    ['key' => 'pay_time', 'label' => '支付时间', 'type' => 'datetime', 'example' => '2023-10-27 10:05:00'],
                    ['key' => 'status_text', 'label' => '订单状态', 'type' => 'string', 'example' => '待发货'],
                ]
            ],
            'user' => [
                'label' => '用户信息',
                'fields' => [
                    ['key' => 'user_code', 'label' => '会员标识码', 'type' => 'string', 'example' => 'U8888'],
                    ['key' => 'nickName', 'label' => '用户昵称', 'type' => 'string', 'example' => '张三'],
                    ['key' => 'mobile', 'label' => '用户手机', 'type' => 'string', 'example' => '13800138000'],
                    ['key' => 'user_id', 'label' => '用户ID', 'type' => 'number', 'example' => '10086'],
                ]
            ],
            'consignee' => [
                'label' => '收货人信息',
                'fields' => [
                    ['key' => 'name', 'label' => '收货人姓名', 'type' => 'string', 'example' => '李四'],
                    ['key' => 'phone', 'label' => '收货人电话', 'type' => 'string', 'example' => '13900139000'],
                    ['key' => 'province', 'label' => '省/州', 'type' => 'string', 'example' => '广东省'],
                    ['key' => 'city', 'label' => '城市', 'type' => 'string', 'example' => '深圳市'],
                    ['key' => 'region', 'label' => '区/县', 'type' => 'string', 'example' => '南山区'],
                    ['key' => 'detail', 'label' => '详细地址', 'type' => 'string', 'example' => '科技园南路88号'],
                    ['key' => 'zip_code', 'label' => '邮编', 'type' => 'string', 'example' => '518000'],
                ]
            ],
            'line' => [
                'label' => '线路信息',
                'fields' => [
                    ['key' => 'line_name', 'label' => '线路名称', 'type' => 'string', 'example' => '美国空运普货专线'],
                    ['key' => 'country', 'label' => '目的国家', 'type' => 'string', 'example' => '美国'],
                ]
            ]
        ];
    }

    /**
     * 渲染消息内容
     * @param array $configList 积木配置列表 [{"source":"order","field":"order_sn","prefix":"单号:","suffix":";"},{"source":"text","value":"固定文本"}]
     * @param array $inpackData 集运单完整数据
     * @return string
     */
    public static function renderMessage($configList, $inpackData)
    {
        if (empty($configList) || !is_array($configList)) {
            return '';
        }

        // 预处理数据源
        $dataSource = self::prepareDataSource($inpackData);
        
        $messageParts = [];
        foreach ($configList as $block) {
            $content = '';
            
            // 1. 处理固定文本
            if (isset($block['type']) && $block['type'] === 'text') {
                $content = isset($block['value']) ? $block['value'] : '';
            } 
            // 2. 处理动态字段
            elseif (isset($block['source']) && isset($block['field'])) {
                $source = $block['source'];
                $field = $block['field'];
                
                $value = '';
                if (isset($dataSource[$source]) && isset($dataSource[$source][$field])) {
                    $value = $dataSource[$source][$field];
                }

                // 格式化处理 (如时间格式化)
                if (isset($block['format']) && !empty($block['format']) && !empty($value)) {
                    // 简单日期格式化支持
                    if (strtotime($value) !== false) {
                        $value = date($block['format'], strtotime($value));
                    }
                }

                // 空值处理：如果配置了 skip_if_empty 且值为空，则跳过整个积木
                if ((!isset($value) || $value === '') && isset($block['skip_if_empty']) && $block['skip_if_empty']) {
                    continue;
                }

                // 默认值处理
                if ((!isset($value) || $value === '') && isset($block['default'])) {
                    $value = $block['default'];
                }

                // 拼接前后缀
                if (!empty($value) || $value === 0 || $value === '0') {
                    $prefix = isset($block['prefix']) ? $block['prefix'] : '';
                    $suffix = isset($block['suffix']) ? $block['suffix'] : '';
                    $content = $prefix . $value . $suffix;
                }
            }

            if ($content !== '') {
                $messageParts[] = $content;
            }
        }

        // 拼接最终字符串
        $fullMessage = implode('', $messageParts);

        // 长度截断 (500字符)
        if (mb_strlen($fullMessage) > 500) {
            $fullMessage = mb_substr($fullMessage, 0, 497) . '...';
        }

        return $fullMessage;
    }

    /**
     * 准备数据源
     * @param array $inpackData
     * @return array
     */
    private static function prepareDataSource($inpackData)
    {
        // 确保关联数据存在
        $user = isset($inpackData['user']) ? $inpackData['user'] : [];
        $address = isset($inpackData['address']) ? $inpackData['address'] : [];
        $line = isset($inpackData['line']) ? $inpackData['line'] : [];

        // 状态映射
        $statusMap = (new Inpack())->status; // 获取状态定义，这里简化处理，实际可能需要中文映射
        $statusText = isset($inpackData['status']) ? $inpackData['status'] : '';

        return [
            'order' => [
                'order_sn' => isset($inpackData['order_sn']) ? $inpackData['order_sn'] : '',
                't_order_sn' => isset($inpackData['t_order_sn']) ? $inpackData['t_order_sn'] : '',
                'weight' => isset($inpackData['weight']) ? $inpackData['weight'] : 0,
                'real_payment' => isset($inpackData['real_payment']) ? $inpackData['real_payment'] : 0,
                'total_pay_price' => isset($inpackData['total_pay_price']) ? $inpackData['total_pay_price'] : 0,
                'remark' => isset($inpackData['remark']) ? $inpackData['remark'] : '',
                'created_time' => isset($inpackData['created_time']) ? $inpackData['created_time'] : '',
                'pay_time' => isset($inpackData['pay_time']) ? date('Y-m-d H:i:s', $inpackData['pay_time']) : '',
                'status_text' => $statusText,
            ],
            'user' => [
                'user_code' => isset($user['user_code']) ? $user['user_code'] : '',
                'nickName' => isset($user['nickName']) ? $user['nickName'] : '',
                'mobile' => isset($user['mobile']) ? $user['mobile'] : '',
                'user_id' => isset($user['user_id']) ? $user['user_id'] : '',
            ],
            'consignee' => [
                'name' => isset($address['name']) ? $address['name'] : '',
                'phone' => isset($address['phone']) ? $address['phone'] : '',
                'province' => isset($address['province']) ? $address['province'] : '',
                'city' => isset($address['city']) ? $address['city'] : '',
                'region' => isset($address['region']) ? $address['region'] : '',
                'detail' => isset($address['detail']) ? $address['detail'] : '',
                'zip_code' => isset($address['zip_code']) ? $address['zip_code'] : '',
            ],
            'line' => [
                'line_name' => isset($line['name']) ? $line['name'] : '',
                'country' => isset($line['country']) ? $line['country'] : '', // 假设Line模型有country字段或关联
            ]
        ];
    }
}
