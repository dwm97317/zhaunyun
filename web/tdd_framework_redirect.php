<?php
/**
 * TDD 诊断工具 - 框架跳转问题
 * 访问: http://localhost:8080/tdd_framework_redirect.php
 */

// 第一步: 不加载任何框架,直接输出
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>TDD 框架跳转诊断</title>";
echo "<style>body{font-family:Arial;margin:20px;background:#f5f5f5;}";
echo ".container{max-width:900px;margin:0 auto;background:white;padding:30px;border-radius:8px;}";
echo "h1{color:#d9001b;}h2{color:#333;border-left:4px solid #d9001b;padding-left:10px;}";
echo ".pass{color:#00b894;font-weight:bold;}.fail{color:#d63031;font-weight:bold;}";
echo ".test{margin:15px 0;padding:10px;background:#f9f9f9;border-left:4px solid #ccc;}";
echo ".test.pass{border-color:#00b894;}.test.fail{border-color:#d63031;}";
echo "pre{background:#2d3436;color:#dfe6e9;padding:15px;border-radius:4px;overflow-x:auto;}";
echo "</style></head><body><div class='container'>";

echo "<h1>🔍 TDD 框架跳转诊断</h1>";
echo "<p><strong>测试时间:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 测试 1: 检查当前脚本是否会跳转
echo "<h2>测试 1: 当前脚本状态</h2>";
echo "<div class='test pass'>";
echo "<p class='pass'>✅ 脚本正常执行,没有跳转</p>";
echo "<p>当前 URL: <code>" . $_SERVER['REQUEST_URI'] . "</code></p>";
echo "</div>";

// 测试 2: 检查 $_GET 参数
echo "<h2>测试 2: GET 参数检查</h2>";
echo "<div class='test'>";
echo "<pre>" . print_r($_GET, true) . "</pre>";
echo "</div>";

// 测试 3: 检查 $_SERVER 变量
echo "<h2>测试 3: SERVER 变量检查</h2>";
echo "<div class='test'>";
$serverVars = [
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
    'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'N/A',
    'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? 'N/A',
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
];
echo "<pre>" . print_r($serverVars, true) . "</pre>";
echo "</div>";

// 测试 4: 模拟设置参数后检查
echo "<h2>测试 4: 模拟设置 ThinkPHP 参数</h2>";
$_GET['wxapp_id'] = 10001;
$_GET['s'] = '/store/index/index';
echo "<div class='test'>";
echo "<p>设置后的 \$_GET:</p>";
echo "<pre>" . print_r($_GET, true) . "</pre>";
echo "</div>";

// 测试 5: 检查路径定义
echo "<h2>测试 5: 路径常量定义</h2>";
define('APP_PATH', __DIR__ . '/../source/application/');
define('ROOT_PATH', __DIR__ . '/../source/');
define('VENDOR_PATH', ROOT_PATH . 'vendor/');

echo "<div class='test'>";
echo "<p><strong>APP_PATH:</strong> <code>" . APP_PATH . "</code></p>";
echo "<p>存在: " . (file_exists(APP_PATH) ? '<span class="pass">✅ 是</span>' : '<span class="fail">❌ 否</span>') . "</p>";
echo "<p><strong>ROOT_PATH:</strong> <code>" . ROOT_PATH . "</code></p>";
echo "<p>存在: " . (file_exists(ROOT_PATH) ? '<span class="pass">✅ 是</span>' : '<span class="fail">❌ 否</span>') . "</p>";
echo "<p><strong>VENDOR_PATH:</strong> <code>" . VENDOR_PATH . "</code></p>";
echo "<p>存在: " . (file_exists(VENDOR_PATH) ? '<span class="pass">✅ 是</span>' : '<span class="fail">❌ 否</span>') . "</p>";
echo "</div>";

// 测试 6: 检查 ThinkPHP 启动文件
echo "<h2>测试 6: ThinkPHP 启动文件检查</h2>";
$startFile = ROOT_PATH . 'thinkphp/start.php';
echo "<div class='test'>";
echo "<p><strong>启动文件:</strong> <code>" . $startFile . "</code></p>";
echo "<p>存在: " . (file_exists($startFile) ? '<span class="pass">✅ 是</span>' : '<span class="fail">❌ 否</span>') . "</p>";
echo "</div>";

// 测试 7: 尝试加载 ThinkPHP (捕获输出)
echo "<h2>测试 7: 尝试加载 ThinkPHP</h2>";
echo "<div class='test'>";
echo "<p class='fail'>⚠️  即将加载 ThinkPHP,如果页面跳转,说明问题在框架启动过程中</p>";
echo "<p>如果看到这条消息后页面没有跳转,说明框架加载成功</p>";
echo "</div>";

// 刷新输出缓冲区,确保上面的内容已经显示
flush();
ob_flush();

// 现在加载 ThinkPHP
echo "<h2>测试 8: ThinkPHP 加载结果</h2>";
echo "<div class='test'>";

try {
    // 开启输出缓冲,捕获框架的输出
    ob_start();
    
    require $startFile;
    
    $frameworkOutput = ob_get_clean();
    
    echo "<p class='pass'>✅ ThinkPHP 加载成功,没有跳转!</p>";
    
    if (!empty($frameworkOutput)) {
        echo "<p><strong>框架输出:</strong></p>";
        echo "<pre>" . htmlspecialchars($frameworkOutput) . "</pre>";
    } else {
        echo "<p>框架没有产生输出</p>";
    }
    
    // 测试是否可以使用 ThinkPHP 功能
    echo "<h2>测试 9: ThinkPHP 功能测试</h2>";
    echo "<div class='test'>";
    
    if (class_exists('think\Db')) {
        echo "<p class='pass'>✅ think\Db 类可用</p>";
    } else {
        echo "<p class='fail'>❌ think\Db 类不可用</p>";
    }
    
    if (class_exists('app\common\library\Ditch\Sf')) {
        echo "<p class='pass'>✅ Sf 类可用</p>";
    } else {
        echo "<p class='fail'>❌ Sf 类不可用</p>";
    }
    
    echo "</div>";
    
} catch (\Exception $e) {
    echo "<p class='fail'>❌ ThinkPHP 加载失败</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</div>";

// 总结
echo "<h2>📊 诊断总结</h2>";
echo "<div class='test'>";
echo "<p>如果你能看到这个总结,说明脚本执行完成,没有发生跳转。</p>";
echo "<p class='pass'>✅ 诊断完成</p>";
echo "</div>";

echo "</div></body></html>";
