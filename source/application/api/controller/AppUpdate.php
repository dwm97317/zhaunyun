<?php
/**
 * 应用更新控制器
 * 支持App端热更新、整包更新和小程序更新
 */

namespace app\api\controller;

use think\Log;

class AppUpdate extends Controller
{
    /**
     * 检查应用更新
     * @return \think\response\Json
     */
    public function checkUpdate()
    {
        try {
            $data = $this->request->param();
            
            // 验证必要参数
            if (empty($data['version']) || empty($data['versionCode'])) {
                return json(['code' => 400, 'msg' => '参数不完整', 'data' => null]);
            }
            
            $version = $data['version'];
            $versionCode = intval($data['versionCode']);
            $platform = $data['platform'] ?? 'app';
            
            // 最新版本配置 - 直接在这里配置最新版本信息
            $latestVersion = [
                'version' => '1.204',
                'version_code' => 1204,
                'force_update' => false,
                'update_type' => 'apk', // wgt: 热更新, apk: 整包更新
                'update_log' => "请下载最新版",
                'download_url' => 'https://zhuanyun.sllowly.cn/xsgjv14.apk', // 直接配置下载地址
                'file_size' => 2048000, // 2MB
                'min_version' => '1.204'
            ];
            
            // 比较版本
            $hasUpdate = $this->compareVersion($latestVersion['version'], $version) > 0 || 
                        $latestVersion['version_code'] > $versionCode;
            
            if (!$hasUpdate) {
                return json(['code' => 200, 'msg' => 'success', 'data' => [
                    'hasUpdate' => false,
                    'version' => $version,
                    'versionCode' => $versionCode
                ]]);
            }
            
            $result = [
                'version' => $latestVersion['version'],
                'versionCode' => $latestVersion['version_code'],
                'forceUpdate' => $latestVersion['force_update'],
                'updateType' => $latestVersion['update_type'],
                'updateLog' => $latestVersion['update_log'],
                'downloadUrl' => $latestVersion['download_url'],
                'fileSize' => $latestVersion['file_size'],
                'minVersion' => $latestVersion['min_version'],
                'hasUpdate' => true
            ];
            
            return json(['code' => 200, 'msg' => 'success', 'data' => $result]);
            
        } catch (\Exception $e) {
            Log::error('检查更新失败: ' . $e->getMessage());
            return json(['code' => 500, 'msg' => '服务器错误', 'data' => null]);
        }
    }
    
   
    
    /**
     * 比较版本号
     * @param string $version1
     * @param string $version2
     * @return int
     */
    private function compareVersion($version1, $version2)
    {
        $v1Parts = array_map('intval', explode('.', $version1));
        $v2Parts = array_map('intval', explode('.', $version2));
        
        $maxLength = max(count($v1Parts), count($v2Parts));
        
        for ($i = 0; $i < $maxLength; $i++) {
            $v1Part = $v1Parts[$i] ?? 0;
            $v2Part = $v2Parts[$i] ?? 0;
            
            if ($v1Part > $v2Part) return 1;
            if ($v1Part < $v2Part) return -1;
        }
        
        return 0;
    }
}
