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
            
            // 尝试处理转义字符问题
            // 如果已经是数组，直接使用
            if (is_array($msgDataStr)) {
                $msgData = $msgDataStr;
            } else {
                // 如果是字符串，尝试解码
                $msgData = json_decode($msgDataStr, true);
                
                // 如果解码失败，尝试去除反斜杠再解码 (处理双重转义情况)
                if (!$msgData && is_string($msgDataStr)) {
                    $cleanStr = stripslashes($msgDataStr);
                    $msgData = json_decode($cleanStr, true);
                    
                    if ($msgData) {
                         file_put_contents($logFile, "警告: msgData 包含多余转义，已自动修复\n", FILE_APPEND);
                    }
                }
            }
            
            if (!$msgData) {
                file_put_contents($logFile, "错误: msgData 解析失败，原始数据: " . print_r($msgDataStr, true) . "\n", FILE_APPEND);
                return json(['apiResultCode' => 'A1002', 'apiErrorMsg' => 'JSON解析失败']);
            }
            
            file_put_contents($logFile, "解析 msgData 成功: " . json_encode($msgData, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

            // 3. 提取文件列表 (兼容 Base64 推送模式)
            $files = [];
            
            // 模式 A: 只有单个文件对象直接在 msgData 中
            if (isset($msgData['content'])) {
                $files[] = $msgData;
            }
            // 模式 B: 在 obj.files 中 (之前的 URL 模式，可能也用于 Base64)
            elseif (isset($msgData['obj']['files'])) {
                $files = $msgData['obj']['files'];
            }
            // 模式 C: 在 files 数组中
            elseif (isset($msgData['files'])) {
                $files = $msgData['files'];
            }
            // 模式 D: msgData 本身就是文件数组
            elseif (is_array($msgData) && isset($msgData[0]['content'])) {
                $files = $msgData;
            }

            if (empty($files)) {
                file_put_contents($logFile, "警告: 未识别到有效的文件结构\n", FILE_APPEND);
                return json(['apiResultCode' => 'A1000', 'apiResultData' => '{"success": true, "msg": "无文件需处理"}']);
            }

            // 4. 处理每个文件
            $downloadCount = 0;
            foreach ($files as $fileInfo) {
                $waybillNo = $fileInfo['waybillNo'] ?? 'unknown';
                
                // 优先尝试 Base64 内容保存
                if (isset($fileInfo['content']) && !empty($fileInfo['content'])) {
                    file_put_contents($logFile, "检测到 Base64 内容，开始保存: {$waybillNo}\n", FILE_APPEND);
                    if ($this->saveBase64Pdf($waybillNo, $fileInfo['content'])) {
                        $downloadCount++;
                        file_put_contents($logFile, "Base64 保存成功: {$waybillNo}\n", FILE_APPEND);
                    } else {
                        file_put_contents($logFile, "Base64 保存失败: {$waybillNo}\n", FILE_APPEND);
                    }
                    continue;
                }
                
                // 降级尝试 URL 下载
                $url = $fileInfo['url'] ?? '';
                $token = $fileInfo['token'] ?? '';
                
                if (!empty($url)) {
                    file_put_contents($logFile, "开始下载运单(URL): {$waybillNo}, URL: {$url}\n", FILE_APPEND);
                    if ($this->savePdf($waybillNo, $url, $token)) {
                        $downloadCount++;
                        file_put_contents($logFile, "URL 下载成功: {$waybillNo}\n", FILE_APPEND);
                    } else {
                        file_put_contents($logFile, "URL 下载失败: {$waybillNo}\n", FILE_APPEND);
                    }
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

    private function saveBase64Pdf($waybillNo, $base64Content)
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

        $pdfContent = base64_decode($base64Content);
        if (!$pdfContent) {
            Log::error("SF Callback: Base64 decode failed for {$waybillNo}");
            return false;
        }

        $res = file_put_contents($filePath, $pdfContent);
        if ($res) {
            Log::info("SF Callback: Saved Base64 PDF for {$waybillNo} to {$filePath}");
            return true;
        }
        
        Log::error("SF Callback: Write file failed for {$waybillNo}");
        return false;
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
