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
        // 增加内存限制和执行时间，防止大文件处理超时
        ini_set('memory_limit', '256M');
        set_time_limit(120);

        $logFile = ROOT_PATH . 'logs' . DS . 'sf_callback.log';
        
        // 1. 获取原始 POST 数据
        $rawContent = file_get_contents('php://input');
        
        // 记录简要日志 (避免记录 70KB+ 的 Base64 内容导致日志爆炸)
        $rawLength = strlen($rawContent);
        $logContent = "[" . date('Y-m-d H:i:s') . "] 收到回调请求 (Length: {$rawLength}):\n";
        $logContent .= "Client IP: " . request()->ip() . "\n";
        
        // 只记录前 500 个字符用于调试格式
        $previewContent = substr($rawContent, 0, 500) . ($rawLength > 500 ? "..." : "");
        $logContent .= "Raw Input Preview: " . $previewContent . "\n";
        
        // 确保日志目录存在
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents($logFile, $logContent, FILE_APPEND);

        try {
            // 2. 解析数据
            // 顺丰数据通常是 x-www-form-urlencoded，php://input 获取到的是 key=value&...
            // 但如果 msgData 非常大，parse_str 可能会有问题 (php.ini max_input_vars 限制)
            // 所以我们手动尝试解析，或者优先使用 input('post.') 如果它没被截断
            
            $msgDataStr = input('post.msgData', '', null); // 获取原始内容不经过过滤
            $requestID = input('post.requestID');

            // 如果 input() 获取为空，尝试手动解析 rawContent
            if (empty($msgDataStr) && !empty($rawContent)) {
                // 简单的 query string 解析
                parse_str($rawContent, $parsedParams);
                if (isset($parsedParams['msgData'])) {
                    $msgDataStr = $parsedParams['msgData'];
                    file_put_contents($logFile, "手动解析 rawContent 成功提取 msgData\n", FILE_APPEND);
                } else {
                    // 尝试 JSON 解析 (Content-Type: application/json)
                    $jsonParams = json_decode($rawContent, true);
                    if (isset($jsonParams['msgData'])) {
                        $msgDataStr = $jsonParams['msgData']; // 注意：这里可能是数组或字符串
                        if (is_array($msgDataStr)) {
                            $msgDataStr = json_encode($msgDataStr); // 转回字符串统一处理
                        }
                        file_put_contents($logFile, "JSON 解析 rawContent 成功提取 msgData\n", FILE_APPEND);
                    }
                }
            }

            if (empty($msgDataStr)) {
                file_put_contents($logFile, "错误: 无法获取 msgData 参数\n", FILE_APPEND);
                return json(['apiResultCode' => 'A1001', 'apiErrorMsg' => '缺少关键参数']);
            }

            file_put_contents($logFile, "msgData 长度: " . strlen($msgDataStr) . "\n", FILE_APPEND);

            // 3. 解析 JSON 业务数据
            // 处理转义字符
            if (is_string($msgDataStr)) {
                // 优先尝试 HTML 实体解码 (&quot; -> ")
                // 某些环境下 input() 会自动进行 htmlspecialchars 过滤，导致 JSON 损坏
                if (strpos($msgDataStr, '&quot;') !== false) {
                    $msgDataStr = htmlspecialchars_decode($msgDataStr);
                    file_put_contents($logFile, "检测到 HTML 实体编码，已自动修复\n", FILE_APPEND);
                }

                $msgData = json_decode($msgDataStr, true);
                
                // 双重转义修复 (反斜杠)
                if (!$msgData) {
                    $cleanStr = stripslashes($msgDataStr);
                    $msgData = json_decode($cleanStr, true);
                    if ($msgData) {
                        file_put_contents($logFile, "警告: msgData 包含多余转义，已自动修复\n", FILE_APPEND);
                    }
                }
            } else {
                $msgData = $msgDataStr;
            }
            
            if (!$msgData) {
                // 记录最后一次解析错误的错误码
                $jsonError = json_last_error_msg();
                file_put_contents($logFile, "错误: JSON 解析失败 ({$jsonError})\n", FILE_APPEND);
                // 调试：记录头尾字符看是否完整
                $head = substr($msgDataStr, 0, 100);
                $tail = substr($msgDataStr, -100);
                file_put_contents($logFile, "数据片段: HEAD[{$head}] ... TAIL[{$tail}]\n", FILE_APPEND);
                return json(['apiResultCode' => 'A1002', 'apiErrorMsg' => 'JSON解析失败']);
            }
            
            // 4. 提取文件列表 (兼容 Base64 推送模式)
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
                return json([
                    'apiResultCode' => 'A1000', 
                    'apiResultData' => json_encode(['success' => 'true', 'msg' => '无文件需处理'])
                ]);
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
            // 顺丰文档要求 apiResultData 中包含 {"success": "true", "msg": ""}
            // 注意 success 必须是字符串 "true"
            return json([
                'apiResultCode' => 'A1000',
                'apiErrorMsg' => '',
                'apiResponseID' => !empty($requestID) ? $requestID : uniqid(),
                'apiResultData' => json_encode(['success' => 'true', 'msg' => ''])
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

        $fileName = $waybillNo . '_' . time() . '_yibu.pdf';
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

        $fileName = $waybillNo . '_' . time() . '_yibu.pdf';
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
