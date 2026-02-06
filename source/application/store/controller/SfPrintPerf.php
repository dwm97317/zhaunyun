<?php
namespace app\store\controller;

use think\Controller;
use app\common\library\Ditch\Sf;
use app\store\model\Ditch as DitchModel;
use app\store\model\Inpack;

/**
 * 性能诊断控制器
 */
class SfPrintPerf extends Controller
{
    public function diagnose()
    {
        $orderId = input('order_id', 69463);
        $times = [];
        
        $startTotal = microtime(true);
        
        // 1. 获取订单
        $t1 = microtime(true);
        $order = Inpack::detail($orderId);
        $times['1_get_order'] = round((microtime(true) - $t1) * 1000, 2);
        
        if (!$order) {
            return json(['error' => '订单不存在']);
        }
        
        $waybillNo = $order['t_order_sn'] ?? $order['order_sn'] ?? '';
        
        // 2. 获取配置
        $t2 = microtime(true);
        $ditch = DitchModel::where('ditch_id', 10076)->find();
        $times['2_get_config'] = round((microtime(true) - $t2) * 1000, 2);
        
        $config = [
            'key' => $ditch['app_key'] ?? 'THGJH89TNITE',
            'token' => $ditch['app_token'] ?? '7D88D4D6DC58914624F087796D1244C3',
            'apiurl' => 'https://sfapi-sbox.sf-express.com/std/service',
            'template_code' => 'fm_76130_standard_' . ($ditch['app_key'] ?? 'THGJH89TNITE'),
            'sync_mode' => 0
        ];
        
        $sf = new Sf($config);
        
        // 3. 获取 AccessToken
        $t3 = microtime(true);
        $accessToken = $sf->getCloudPrintAccessToken();
        $times['3_get_token'] = round((microtime(true) - $t3) * 1000, 2);
        
        if (!$accessToken) {
            return json(['error' => 'Token 失败: ' . $sf->getError()]);
        }
        
        // 4. 获取 ParsedData（关键步骤）
        $t4 = microtime(true);
        $parsedData = $sf->printlabelParsedData($orderId);
        $times['4_get_parseddata'] = round((microtime(true) - $t4) * 1000, 2);
        
        if (!$parsedData) {
            return json(['error' => 'ParsedData 失败: ' . $sf->getError()]);
        }
        
        // 5. 组装数据
        $t5 = microtime(true);
        $document = array_merge(['masterWaybillNo' => $waybillNo], $parsedData);
        $printData = [
            'requestID' => uniqid('PRT'),
            'accessToken' => $accessToken,
            'templateCode' => $config['template_code'],
            'documents' => [$document]
        ];
        $times['5_assemble_data'] = round((microtime(true) - $t5) * 1000, 2);
        
        $times['total'] = round((microtime(true) - $startTotal) * 1000, 2);
        
        // 分析
        $analysis = [
            'bottleneck' => '',
            'recommendation' => []
        ];
        
        $maxTime = max($times['3_get_token'], $times['4_get_parseddata']);
        if ($maxTime == $times['3_get_token']) {
            $analysis['bottleneck'] = 'AccessToken 获取';
            $analysis['recommendation'][] = '考虑缓存 AccessToken（有效期通常 2 小时）';
        }
        if ($maxTime == $times['4_get_parseddata']) {
            $analysis['bottleneck'] = 'ParsedData 获取';
            $analysis['recommendation'][] = '这是顺丰 API 调用，无法通过 CDN 加速';
            $analysis['recommendation'][] = '考虑缓存已打印过的面单数据';
        }
        
        if ($times['total'] > 3000) {
            $analysis['recommendation'][] = '总耗时超过 3 秒，建议优化';
        }
        
        return json([
            'times' => $times,
            'analysis' => $analysis,
            'data_size' => strlen(json_encode($printData))
        ]);
    }
}
