<?php
/**
 * Inpack Controller 诊断脚本
 * 
 * 访问方式：https://alibt.itaoth.com/diagnose_inpack.php
 * 
 * 用途：诊断为什么 /store/inpack/orderbatchprinter 返回 404
 */

header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Inpack Controller 诊断</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        h2 { border-bottom: 2px solid #333; padding-bottom: 5px; }
        pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Inpack Controller 诊断报告</h1>
    
    <?php
    // 1. PHP 环境信息
    echo '<div class="section">';
    echo '<h2>1. PHP 环境信息</h2>';
    echo '<pre>';
    echo "PHP 版本: " . PHP_VERSION . "\n";
    echo "OPcache 状态: " . (function_exists('opcache_get_status') ? (opcache_get_status() ? '启用' : '禁用') : '不可用') . "\n";
    echo "当前时间: " . date('Y-m-d H:i:s') . "\n";
    echo "服务器软件: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
    echo '</pre>';
    echo '</div>';
    
    // 2. 文件检查
    echo '<div class="section">';
    echo '<h2>2. 文件检查</h2>';
    
    $files = [
        'Inpack Controller' => __DIR__ . '/../source/application/store/controller/Inpack.php',
        'OrderBatchPrinter' => __DIR__ . '/../source/application/common/service/OrderBatchPrinter.php',
        'AsyncTaskQueue' => __DIR__ . '/../source/application/common/service/AsyncTaskQueue.php',
        'PrintLogger' => __DIR__ . '/../source/application/common/service/PrintLogger.php',
        'RetryHelper' => __DIR__ . '/../source/application/common/service/RetryHelper.php',
    ];
    
    echo '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
    echo '<tr><th>文件</th><th>状态</th><th>大小</th><th>修改时间</th></tr>';
    
    foreach ($files as $name => $path) {
        $exists = file_exists($path);
        $size = $exists ? filesize($path) : 0;
        $mtime = $exists ? date('Y-m-d H:i:s', filemtime($path)) : 'N/A';
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($name) . '</td>';
        echo '<td class="' . ($exists ? 'success' : 'error') . '">' . ($exists ? '✅ 存在' : '❌ 不存在') . '</td>';
        echo '<td>' . ($exists ? number_format($size) . ' bytes' : 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($mtime) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</div>';
    
    // 3. 类加载检查
    echo '<div class="section">';
    echo '<h2>3. 类加载检查</h2>';
    
    // 加载 ThinkPHP
    $thinkPath = __DIR__ . '/../source/thinkphp/start.php';
    if (file_exists($thinkPath)) {
        echo '<p class="success">✅ ThinkPHP 路径存在</p>';
        
        try {
            require_once $thinkPath;
            echo '<p class="success">✅ ThinkPHP 加载成功</p>';
            
            // 检查 Inpack 类
            $className = 'app\\store\\controller\\Inpack';
            if (class_exists($className)) {
                echo '<p class="success">✅ Inpack 类可加载</p>';
                
                $reflection = new ReflectionClass($className);
                $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                
                echo '<p><strong>公共方法列表：</strong></p>';
                echo '<ul>';
                foreach ($methods as $method) {
                    if ($method->class === $className) {
                        $isTarget = ($method->name === 'orderbatchprinter');
                        echo '<li class="' . ($isTarget ? 'success' : '') . '">';
                        echo htmlspecialchars($method->name);
                        if ($isTarget) echo ' ← 目标方法';
                        echo '</li>';
                    }
                }
                echo '</ul>';
                
                if ($reflection->hasMethod('orderbatchprinter')) {
                    echo '<p class="success">✅ orderbatchprinter 方法存在</p>';
                } else {
                    echo '<p class="error">❌ orderbatchprinter 方法不存在</p>';
                }
            } else {
                echo '<p class="error">❌ Inpack 类无法加载</p>';
            }
            
        } catch (Exception $e) {
            echo '<p class="error">❌ 加载异常: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
    } else {
        echo '<p class="error">❌ ThinkPHP 不存在</p>';
    }
    
    echo '</div>';
    
    // 4. 路由测试
    echo '<div class="section">';
    echo '<h2>4. 路由测试</h2>';
    echo '<p>尝试访问路由: <code>/store/inpack/orderbatchprinter</code></p>';
    
    echo '<button onclick="testRoute()">测试路由</button>';
    echo '<div id="routeResult" style="margin-top: 10px;"></div>';
    
    echo '<script>
    function testRoute() {
        var result = document.getElementById("routeResult");
        result.innerHTML = "<p class=\"info\">正在测试...</p>";
        
        fetch("/store/inpack/orderbatchprinter", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                order_ids: [1],
                ditch_id: 1
            })
        })
        .then(response => {
            result.innerHTML = "<p class=\"success\">✅ 路由可访问 (状态码: " + response.status + ")</p>";
            return response.text();
        })
        .then(data => {
            result.innerHTML += "<pre>" + data.substring(0, 500) + "</pre>";
        })
        .catch(error => {
            result.innerHTML = "<p class=\"error\">❌ 路由访问失败: " + error + "</p>";
        });
    }
    </script>';
    
    echo '</div>';
    
    // 5. OPcache 信息
    if (function_exists('opcache_get_status')) {
        echo '<div class="section">';
        echo '<h2>5. OPcache 信息</h2>';
        
        $status = opcache_get_status();
        if ($status) {
            echo '<pre>';
            echo "启用状态: " . ($status['opcache_enabled'] ? '是' : '否') . "\n";
            echo "缓存命中率: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
            echo "已缓存脚本数: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
            echo "内存使用: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
            echo '</pre>';
            
            echo '<form method="post">';
            echo '<button type="submit" name="clear_opcache">清除 OPcache</button>';
            echo '</form>';
            
            if (isset($_POST['clear_opcache'])) {
                if (opcache_reset()) {
                    echo '<p class="success">✅ OPcache 已清除</p>';
                } else {
                    echo '<p class="error">❌ OPcache 清除失败</p>';
                }
            }
        } else {
            echo '<p class="info">OPcache 未启用</p>';
        }
        
        echo '</div>';
    }
    
    // 6. 建议操作
    echo '<div class="section">';
    echo '<h2>6. 建议操作</h2>';
    echo '<ol>';
    echo '<li>如果 OPcache 启用，点击上方按钮清除缓存</li>';
    echo '<li>重启 PHP-FPM: <code>systemctl restart php-fpm</code> 或 <code>systemctl restart php74-php-fpm</code></li>';
    echo '<li>重启 Web 服务器: <code>systemctl restart nginx</code> 或 <code>systemctl restart httpd</code></li>';
    echo '<li>清除 ThinkPHP 缓存: <code>rm -rf source/runtime/cache/* source/runtime/temp/*</code></li>';
    echo '<li>检查文件权限: <code>chmod 644 source/application/store/controller/Inpack.php</code></li>';
    echo '</ol>';
    echo '</div>';
    
    ?>
    
    <div class="section">
        <h2>7. 快速修复命令</h2>
        <pre>
# 1. 清除所有缓存
rm -rf source/runtime/cache/* source/runtime/temp/*

# 2. 查找 PHP-FPM 服务名
systemctl list-units | grep php

# 3. 重启 PHP-FPM（根据实际服务名）
systemctl restart php-fpm
# 或
systemctl restart php74-php-fpm
# 或
systemctl restart php80-php-fpm

# 4. 重启 Nginx
systemctl restart nginx

# 5. 清除 OPcache（如果上面按钮不工作）
# 访问此页面并点击"清除 OPcache"按钮
        </pre>
    </div>
    
</body>
</html>
<?php
// 记录访问日志
$logFile = __DIR__ . '/diagnose_inpack.log';
$logEntry = date('Y-m-d H:i:s') . " - 诊断页面被访问\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);
?>
