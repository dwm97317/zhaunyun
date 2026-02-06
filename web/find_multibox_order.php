<?php
// 查找有子件的订单
define('APP_PATH', __DIR__ . '/../source/application/');
require APP_PATH . '../thinkphp/base.php';
require APP_PATH . 'common.php';

$dbConfig = include APP_PATH . 'database.php';
\think\Db::setConfig($dbConfig);

$orders = \think\Db::table('yoshop_tr_order')
    ->where('packageitems', '<>', '')
    ->where('packageitems', 'IS NOT', null)
    ->limit(5)
    ->select();

echo "有子件的订单:\n";
echo str_repeat("-", 80) . "\n";

foreach($orders as $o) {
    echo "ID: {$o['id']} | 订单号: {$o['order_sn']} | 运单号: {$o['t_order_sn']}\n";
    $pkg = json_decode($o['packageitems'], true);
    if($pkg && is_array($pkg)) {
        echo "  子件数: " . count($pkg) . "\n";
        foreach($pkg as $i => $p) {
            echo "    子件" . ($i+1) . ": " . ($p['sn'] ?? 'N/A') . "\n";
        }
    }
    echo "\n";
}
