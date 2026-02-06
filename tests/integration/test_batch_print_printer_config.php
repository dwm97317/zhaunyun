<?php
/**
 * 批量打印打印机配置映射测试
 * 
 * 测试场景：
 * 1. 顺丰快递使用指定打印机
 * 2. 京东快递使用指定打印机
 * 3. 未配置打印机时使用默认行为
 * 
 * 运行方式：
 * php tests/integration/test_batch_print_printer_config.php
 */

// 设置测试模式环境变量
putenv('TEST_MODE=1');
$_ENV['TEST_MODE'] = '1';

// 修复路径：从 tests/ 目录运行时需要调整
$rootPath = dirname(dirname(__DIR__));
require $rootPath . '/source/thinkphp/base.php';

// 加载应用
$appPath = $rootPath . '/source/application/';
\think\App::run()->send();

use app\common\service\OrderBatchPrinter;
use app\common\service\DitchCache;

echo "========================================\n";
echo "批量打印打印机配置映射测试\n";
echo "========================================\n\n";

// 测试配置
$testCases = [
    [
        'name' => '顺丰快递 - 指定打印机',
        'ditch_id' => 10077,  // 替换为实际的顺丰渠道ID
        'order_ids' => [69411],  // 替换为实际的订单ID
        'expected_mode' => 'sf_plugin',
        'check_printer' => true
    ],
    [
        'name' => '京东快递 - 指定打印机',
        'ditch_id' => 10078,  // 替换为实际的京东渠道ID
        'order_ids' => [69412],  // 替换为实际的订单ID
        'expected_mode' => 'jd_cloud_print',
        'check_printer' => true
    ]
];

foreach ($testCases as $index => $testCase) {
    echo "测试 " . ($index + 1) . ": " . $testCase['name'] . "\n";
    echo str_repeat('-', 40) . "\n";
    
    // 1. 检查渠道配置
    echo "1. 检查渠道配置...\n";
    $ditchConfig = DitchCache::getConfig($testCase['ditch_id']);
    
    if (!$ditchConfig) {
        echo "   ❌ 渠道配置不存在 (ID: {$testCase['ditch_id']})\n";
        echo "   跳过此测试\n\n";
        continue;
    }
    
    echo "   ✅ 渠道: {$ditchConfig['ditch_name']}\n";
    
    // 2. 检查打印机配置
    if ($testCase['expected_mode'] === 'sf_plugin') {
        $sfPrintOptions = !empty($ditchConfig['sf_print_options']) 
            ? json_decode($ditchConfig['sf_print_options'], true) 
            : [];
        
        echo "   顺丰打印配置:\n";
        echo "   - enable_select_printer: " . (isset($sfPrintOptions['enable_select_printer']) ? ($sfPrintOptions['enable_select_printer'] ? 'true' : 'false') : 'N/A') . "\n";
        echo "   - default_printer: " . (isset($sfPrintOptions['default_printer']) ? $sfPrintOptions['default_printer'] : 'N/A') . "\n";
        
        if (empty($sfPrintOptions['default_printer'])) {
            echo "   ⚠️  未配置默认打印机，将使用系统默认\n";
        }
    } elseif ($testCase['expected_mode'] === 'jd_cloud_print') {
        $jdPrintConfig = !empty($ditchConfig['jd_print_config']) 
            ? json_decode($ditchConfig['jd_print_config'], true) 
            : [];
        
        echo "   京东打印配置:\n";
        echo "   - orderType: " . (isset($jdPrintConfig['orderType']) ? $jdPrintConfig['orderType'] : 'N/A') . "\n";
        echo "   - printName: " . (isset($jdPrintConfig['printName']) ? $jdPrintConfig['printName'] : 'N/A') . "\n";
        
        if (empty($jdPrintConfig['printName'])) {
            echo "   ⚠️  未配置打印机名称，将使用系统默认\n";
        }
    }
    
    // 3. 执行批量打印
    echo "\n2. 执行批量打印...\n";
    
    try {
        $result = OrderBatchPrinter::print(
            $testCase['order_ids'],
            $testCase['ditch_id'],
            ['label' => 60, 'print_all' => 1]
        );
        
        echo "   打印结果:\n";
        echo "   - 总数: {$result['total']}\n";
        echo "   - 成功: {$result['success_count']}\n";
        echo "   - 失败: {$result['error_count']}\n";
        echo "   - 耗时: {$result['elapsed_time']}s\n";
        
        // 4. 检查打印数据
        if ($result['success_count'] > 0 && !empty($result['results'])) {
            echo "\n3. 检查打印数据...\n";
            
            foreach ($result['results'] as $item) {
                if ($item['success'] && $item['print_data']) {
                    $printData = $item['print_data'];
                    
                    echo "   订单 #{$item['order_id']}:\n";
                    echo "   - 打印模式: " . (isset($printData['mode']) ? $printData['mode'] : 'N/A') . "\n";
                    
                    // 检查顺丰打印机配置
                    if ($testCase['expected_mode'] === 'sf_plugin') {
                        if (isset($printData['sfPrintOptions'])) {
                            echo "   ✅ sfPrintOptions 字段存在\n";
                            
                            $sfOpts = $printData['sfPrintOptions'];
                            if (isset($sfOpts['default_printer']) && !empty($sfOpts['default_printer'])) {
                                echo "   ✅ 打印机配置: {$sfOpts['default_printer']}\n";
                            } else {
                                echo "   ⚠️  未配置打印机，将使用默认\n";
                            }
                        } else {
                            echo "   ❌ sfPrintOptions 字段缺失\n";
                        }
                    }
                    
                    // 检查京东打印机配置
                    if ($testCase['expected_mode'] === 'jd_cloud_print') {
                        if (isset($printData['jdPrintConfig'])) {
                            echo "   ✅ jdPrintConfig 字段存在\n";
                            
                            $jdCfg = $printData['jdPrintConfig'];
                            if (isset($jdCfg['printName']) && !empty($jdCfg['printName'])) {
                                echo "   ✅ 打印机配置: {$jdCfg['printName']}\n";
                            } else {
                                echo "   ⚠️  未配置打印机，将使用默认\n";
                            }
                        } else {
                            echo "   ❌ jdPrintConfig 字段缺失\n";
                        }
                    }
                }
            }
        }
        
        echo "\n✅ 测试完成\n\n";
        
    } catch (\Exception $e) {
        echo "   ❌ 测试失败: " . $e->getMessage() . "\n\n";
    }
}

echo "========================================\n";
echo "所有测试完成\n";
echo "========================================\n";
