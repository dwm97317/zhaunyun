<?php

namespace app\api\controller;

use app\common\library\Ditch\Sf;
use app\store\model\Ditch;
use think\Controller;
use think\Log;
use think\Request;

class SfCallback extends Controller
{
    public function notify()
    {
        $logFile = ROOT_PATH . 'logs' . DS . 'sf_callback.log';
        
        // 1. 获取原始 POST 数据
        $rawContent = file_get_contents('php://input');
        $postData = input('post.');
        
        $logContent = "[" . date('Y-m-d H:i:s') . "] 收到回调请求:\n";
        $logContent .= "Client IP: " . request()->ip() . "\n";
        $logContent .= "Raw Input: " . $rawContent . "\n";
        $logContent .= "POST Data: " . json_encode($postData, JSON_UNESCAPED_UNICODE) . "\n";
        
        // 确保日志目录存在
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents($logFile, $logContent, FILE_APPEND);

        try {
            // 2. 验证参数
            if (empty($postData['msgData'])) {
                // 有时候顺丰可能发送 Content-Type: application/json，需要手动解析
                $jsonInput = json_decode($rawContent, true);
                if (isset($jsonInput['msgData'])) {
                     $postData = $jsonInput;
                     file_put_contents($logFile, "解析 JSON Body 成功\n", FILE_APPEND);
                } else {
                     file_put_contents($logFile, "错误: 缺少 msgData 参数\n", FILE_APPEND);
                     return json(['apiResultCode' => 'A1001', 'apiErrorMsg' => '缺少关键参数']);
                }
            }

            $msgDataStr = $postData['msgData'];
            $msgData = json_decode($msgDataStr, true);
            
            if (!$msgData) {
                file_put_contents($logFile, "错误: msgData 解析失败\n", FILE_APPEND);
                return json(['apiResultCode' => 'A1002', 'apiErrorMsg' => 'JSON解析失败']);
            }
            
            file_put_contents($logFile, "解析 msgData 成功: " . json_encode($msgData, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

            // 3. 提取文件列表
            $files = [];
            if (isset($msgData['obj']['files'])) {
                $files = $msgData['obj']['files'];
            } elseif (isset($msgData['files'])) {
                 // 兼容不同层级
                $files = $msgData['files'];
            }

            if (empty($files)) {
                file_put_contents($logFile, "警告: 未找到 files 字段\n", FILE_APPEND);
                return json(['apiResultCode' => 'A1000', 'apiResultData' => '{"success": true, "msg": "无文件需处理"}']);
            }

            // 4. 处理每个文件
            $downloadCount = 0;
            foreach ($files as $fileInfo) {
                $url = $fileInfo['url'] ?? '';
                $waybillNo = $fileInfo['waybillNo'] ?? 'unknown';
                $token = $fileInfo['token'] ?? '';
                
                if (empty($url)) continue;

                file_put_contents($logFile, "开始下载运单: {$waybillNo}, URL: {$url}\n", FILE_APPEND);
                
                if ($this->savePdf($waybillNo, $url, $token)) {
                    $downloadCount++;
                    file_put_contents($logFile, "下载成功: {$waybillNo}\n", FILE_APPEND);
                } else {
                    file_put_contents($logFile, "下载失败: {$waybillNo}\n", FILE_APPEND);
                }
            }

            file_put_contents($logFile, "处理完成，共下载 {$downloadCount} 个文件\n--------------------\n", FILE_APPEND);
            
            // 5. 返回成功响应
            return json([
                'apiResultCode' => 'A1000',
                'apiErrorMsg' => '',
                'apiResponseID' => isset($postData['requestID']) ? $postData['requestID'] : uniqid(),
                'apiResultData' => json_encode(['success' => true])
            ]);

        } catch (\Exception $e) {
            file_put_contents($logFile, "异常: " . $e->getMessage() . "\n--------------------\n", FILE_APPEND);
            return json(['apiResultCode' => 'E1000', 'apiErrorMsg' => 'System Error: ' . $e->getMessage()]);
        }
    }

    private function savePdf($waybillNo, $url, $token)
    {
        $webPath = ROOT_PATH . 'web' . DS . 'uploads' . DS . 'sf_label';
        if (!file_exists($webPath)) {
            if (!mkdir($webPath, 0755, true)) {
                Log::error("SF Callback: Create dir failed {$webPath}");
                return false;
            }
        }

        $fileName = $waybillNo . '_' . time() . '_async.pdf';
        $filePath = $webPath . DS . $fileName;

        // 下载 PDF
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        if (!empty($token)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Auth-Token: ' . $token
            ]);
        }
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 && $content) {
            $res = file_put_contents($filePath, $content);
            if ($res) {
                Log::info("SF Callback: Saved PDF for {$waybillNo} to {$filePath}");
                return true;
            }
        }
        
        Log::error("SF Callback: Download failed for {$waybillNo}, HTTP {$httpCode}");
        return false;
    }
}
