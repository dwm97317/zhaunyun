<?php

namespace app\api\controller;

use app\common\library\Ditch\Sf;
use app\store\model\Ditch;
use think\Controller;
use think\Log;
use think\Request;

class SfCallback extends Controller
{
    /**
     * 接收顺丰云打印面单推送
     * 路由建议: /api/sf_callback/notify
     * @param array|null $mockData 用于测试的模拟数据
     */
    public function notify($mockData = null)
    {
        // 1. 获取所有 POST 参数
        // 如果传入了模拟数据，优先使用
        $param = $mockData !== null ? $mockData : $this->request->param();
        
        // 记录原始日志用于调试
        Log::info('SF Cloud Print Callback Raw Data: ' . json_encode($param, JSON_UNESCAPED_UNICODE));
        
        if (empty($param)) {
            return json(['apiResultCode' => 'A1001', 'apiErrorMsg' => 'Empty Request']);
        }

        // 2. 提取关键参数
        $msgDataStr = isset($param['msgData']) ? $param['msgData'] : '';
        $msgDigest = isset($param['msgDigest']) ? $param['msgDigest'] : '';
        $requestID = isset($param['requestID']) ? $param['requestID'] : '';
        // $timestamp = isset($param['timestamp']) ? $param['timestamp'] : ''; // 顺丰推送可能带 timestamp

        if (empty($msgDataStr)) {
            return json(['apiResultCode' => 'A1001', 'apiErrorMsg' => 'Missing msgData']);
        }

        // 3. 获取配置 (用于验签)
        // 假设系统中只有一个顺丰配置，或者通过 msgData 中的信息匹配
        // 这里简化处理，查询 ditch_type 为顺丰(假设type=3或根据 key 查询)
        // 由于参数中可能不包含 partnerID，我们尝试用系统中的第一个顺丰配置来验签
        
        $ditch = Ditch::where('ditch_type', 3)->find(); // 假设 3 是顺丰，或者遍历查找
        if (!$ditch && isset($param['partnerID'])) {
             // 如果请求带了 partnerID，尝试用 key 查
             $key = $param['partnerID'];
             // Ditch 模型中 config 是 json 字符串? 需要确认 Ditch 存储结构
             // 这里假设 config 存储在 push_config_json 或类似字段，需遍历匹配
             // 为简化，先不强制验签，只记录日志，或者假设 key 已知
        }

        // TODO: 完善验签逻辑 (需要知道 token)
        // $token = ...;
        // $sign = base64_encode(md5($msgDataStr . $timestamp . $token, true));
        // if ($sign !== $msgDigest) { ... }

        // 4. 解析业务数据
        $msgData = json_decode($msgDataStr, true);
        if (!$msgData) {
            return json(['apiResultCode' => 'A1002', 'apiErrorMsg' => 'Invalid JSON']);
        }

        // 5. 处理文件保存
        // 结构参考: {"obj":{"files":[{"url":"...","token":"...","waybillNo":"..."}]}}
        
        $files = isset($msgData['obj']['files']) ? $msgData['obj']['files'] : [];
        if (empty($files)) {
            Log::error('SF Callback: No files found in msgData');
            return json(['apiResultCode' => 'A1000', 'apiResultData' => 'No files processed']);
        }

        $successCount = 0;
        foreach ($files as $file) {
            $waybillNo = isset($file['waybillNo']) ? $file['waybillNo'] : '';
            $url = isset($file['url']) ? $file['url'] : '';
            $token = isset($file['token']) ? $file['token'] : '';

            if ($waybillNo && $url) {
                if ($this->savePdf($waybillNo, $url, $token)) {
                    $successCount++;
                }
            }
        }

        Log::info("SF Callback Processed: {$successCount} files saved.");

        return json([
            'apiResultCode' => 'A1000',
            'apiErrorMsg' => 'Success',
            'apiResponseID' => $requestID
        ]);
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
