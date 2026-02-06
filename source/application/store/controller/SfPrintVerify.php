<?php
namespace app\store\controller;

use think\Controller;

/**
 * 顺丰云打印修复验证控制器
 * 访问地址: /index.php?s=/store/sf_print_verify/check
 */
class SfPrintVerify extends Controller
{
    /**
     * 验证修复是否成功
     * 访问: /index.php?s=/store/sf_print_verify/check&order_id=69463
     */
    public function check()
    {
        $orderId = input('order_id', 69463);
        
        $output = [];
        $output[] = "========================================";
        $output[] = "修复验证工具";
        $output[] = "========================================";
        $output[] = "订单 ID: {$orderId}";
        $output[] = "时间: " . date('Y-m-d H:i:s');
        $output[] = "";
        
        // 模拟调用 getPrintConfig
        $output[] = "【验证步骤】调用 getPrintConfig API";
        $output[] = "----------------------------------------";
        
        // 使用 curl 调用 API
        $url = "http://localhost:8080/index.php?s=/store/sf_print/getPrintConfig&order_id={$orderId}";
        $output[] = "API URL: {$url}";
        $output[] = "";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $output[] = "❌ API 调用失败";
            $output[] = "  - HTTP Code: {$httpCode}";
            return $this->displayOutput($output, false);
        }
        
        $output[] = "✅ API 调用成功 (HTTP 200)";
        $output[] = "";
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['code'])) {
            $output[] = "❌ 响应格式错误";
            $output[] = "  - Response: " . substr($response, 0, 200);
            return $this->displayOutput($output, false);
        }
        
        if ($data['code'] !== 1) {
            $output[] = "❌ API 返回错误";
            $output[] = "  - Message: " . ($data['msg'] ?? 'Unknown error');
            return $this->displayOutput($output, false);
        }
        
        $output[] = "✅ API 返回成功";
        $output[] = "";
        
        // 验证数据结构
        $output[] = "【数据结构验证】";
        $output[] = "----------------------------------------";
        
        $printData = $data['data'] ?? [];
        
        // 检查基础字段
        $checks = [
            'requestID' => isset($printData['requestID']),
            'accessToken' => isset($printData['accessToken']),
            'templateCode' => isset($printData['templateCode']),
            'documents' => isset($printData['documents']) && is_array($printData['documents'])
        ];
        
        foreach ($checks as $field => $pass) {
            $output[] = "  - {$field}: " . ($pass ? '✅ 存在' : '❌ 缺失');
        }
        
        $output[] = "";
        
        // 检查 documents[0]
        if (!isset($printData['documents'][0])) {
            $output[] = "❌ documents[0] 不存在";
            return $this->displayOutput($output, false);
        }
        
        $doc = $printData['documents'][0];
        
        $output[] = "【documents[0] 结构验证】";
        $output[] = "----------------------------------------";
        
        $hasMasterWaybillNo = isset($doc['masterWaybillNo']);
        $hasContents = isset($doc['contents']);
        
        $output[] = "  - masterWaybillNo: " . ($hasMasterWaybillNo ? '✅ 存在' : '❌ 缺失');
        if ($hasMasterWaybillNo) {
            $output[] = "    值: " . $doc['masterWaybillNo'];
        }
        
        $output[] = "  - contents: " . ($hasContents ? '✅ 存在' : '❌ 缺失');
        if ($hasContents) {
            $contentsType = gettype($doc['contents']);
            $output[] = "    类型: {$contentsType}";
            if (is_array($doc['contents'])) {
                $output[] = "    键数量: " . count($doc['contents']);
            }
        }
        
        $output[] = "";
        
        // 最终结论
        $output[] = "========================================";
        $output[] = "【验证结论】";
        $output[] = "========================================";
        
        if ($hasMasterWaybillNo && $hasContents) {
            $output[] = "✅ 修复成功！数据结构完整";
            $output[] = "";
            $output[] = "【下一步】";
            $output[] = "1. 访问打印测试页面: http://localhost:8080/index.php?s=/store/sf_print/demo";
            $output[] = "2. 输入订单 ID: {$orderId}";
            $output[] = "3. 点击「立即打印」按钮";
            $output[] = "4. 观察是否弹出预览窗口或开始打印";
            $output[] = "";
            $output[] = "如果还是失败，请检查 C-Lodop 插件是否正常运行";
            $output[] = "测试链接: http://localhost:8080/test_clodop_connectivity.html";
            
            return $this->displayOutput($output, true, $printData);
        } else {
            $output[] = "❌ 修复未生效或数据结构仍有问题";
            $output[] = "";
            if (!$hasMasterWaybillNo) {
                $output[] = "问题: documents[0] 仍然缺少 masterWaybillNo";
                $output[] = "建议: 检查 SfPrint.php::getPrintConfig() 方法是否正确修改";
            }
            if (!$hasContents) {
                $output[] = "问题: documents[0] 缺少 contents";
                $output[] = "建议: 检查 Sf.php::printlabelParsedData() 方法返回值";
            }
            
            return $this->displayOutput($output, false);
        }
    }
    
    /**
     * 显示输出
     */
    private function displayOutput($lines, $success, $data = null)
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>修复验证结果</title>';
        $html .= '<style>body{font-family:Consolas,monospace;background:#1e1e1e;color:#d4d4d4;padding:20px;line-height:1.6}';
        $html .= '.container{max-width:1200px;margin:0 auto;background:#252526;padding:30px;border-radius:8px}';
        $html .= 'pre{background:#1e1e1e;padding:15px;border-radius:4px;overflow-x:auto;border-left:4px solid ' . ($success ? '#4ec9b0' : '#f48771') . '}';
        $html .= '.success{color:#4ec9b0}.error{color:#f48771}.warning{color:#dcdcaa}.info{color:#9cdcfe}';
        $html .= 'button{background:#007acc;color:white;border:none;padding:12px 24px;margin:10px 5px;cursor:pointer;border-radius:4px;font-size:14px;font-weight:600}';
        $html .= 'button:hover{background:#005a9e}';
        $html .= 'button.success{background:#4ec9b0}button.success:hover{background:#3da88a}';
        $html .= '.banner{background:' . ($success ? '#4ec9b0' : '#f48771') . ';color:white;padding:20px;border-radius:8px;margin-bottom:20px;text-align:center;font-size:20px;font-weight:600}';
        $html .= '</style></head><body><div class="container">';
        
        $html .= '<div class="banner">' . ($success ? '✅ 修复验证通过' : '❌ 修复验证失败') . '</div>';
        
        $html .= '<pre>';
        foreach ($lines as $line) {
            $class = '';
            if (strpos($line, '✅') !== false || strpos($line, '通过') !== false || strpos($line, '成功') !== false) {
                $class = 'success';
            } elseif (strpos($line, '❌') !== false || strpos($line, '错误') !== false || strpos($line, '失败') !== false) {
                $class = 'error';
            } elseif (strpos($line, '⚠️') !== false || strpos($line, '警告') !== false) {
                $class = 'warning';
            } elseif (strpos($line, '【') !== false) {
                $class = 'info';
            }
            
            $html .= '<span class="' . $class . '">' . htmlspecialchars($line) . '</span>' . "\n";
        }
        $html .= '</pre>';
        
        if ($success) {
            $html .= '<div style="text-align:center;margin-top:30px">';
            $html .= '<button class="success" onclick="window.open(\'http://localhost:8080/index.php?s=/store/sf_print/demo\')">🖨️ 打开打印测试页面</button>';
            $html .= '<button onclick="window.open(\'http://localhost:8080/test_clodop_connectivity.html\')">🔌 测试 C-Lodop 连接</button>';
            $html .= '</div>';
        } else {
            $html .= '<div style="text-align:center;margin-top:30px">';
            $html .= '<button onclick="location.reload()">🔄 重新验证</button>';
            $html .= '<button onclick="window.open(\'http://localhost:8080/index.php?s=/store/sf_print_test/diagnose&order_id=69463\')">🔍 重新诊断</button>';
            $html .= '</div>';
        }
        
        if ($data) {
            $html .= '<h3 style="color:#4ec9b0;margin-top:40px">完整数据结构</h3>';
            $html .= '<pre style="border-left-color:#007acc">' . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
        }
        
        $html .= '</div></body></html>';
        
        return $html;
    }
}
