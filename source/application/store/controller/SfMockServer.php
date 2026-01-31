<?php

namespace app\store\controller;

use think\Controller;
use think\Request;

/**
 * 顺丰面单 API Mock Server
 * 用于模拟 EXP_RECE_SEARCH_WAYBILL_PICTURE 接口响应
 */
class SfMockServer extends Controller
{
    /**
     * 模拟入口
     */
    public function index(Request $request)
    {
        // 1. 获取请求参数
        $partnerID = $request->param('partnerID');
        $serviceCode = $request->param('serviceCode');
        $msgDataStr = $request->param('msgData');
        
        // 2. 基础校验
        if (empty($partnerID) || empty($serviceCode)) {
            return json(['apiResultCode' => 'A1001', 'apiErrorMsg' => '缺少必要参数']);
        }

        // 3. 路由分发
        if ($serviceCode === 'EXP_RECE_SEARCH_WAYBILL_PICTURE') {
            return $this->mockWaybillPicture($msgDataStr);
        }

        return json(['apiResultCode' => 'A1002', 'apiErrorMsg' => '不支持的服务代码']);
    }

    /**
     * 模拟面单图片响应
     */
    private function mockWaybillPicture($msgDataStr)
    {
        $msgData = json_decode($msgDataStr, true);
        $trackingNumber = isset($msgData['trackingNumber'][0]) ? $msgData['trackingNumber'][0] : 'UNKNOWN';

        // 生成一个更真实的 Base64 图片 (蓝色背景，100x50)
        // 这可以让用户在预览时看到更明显的效果
        $base64Image = 'iVBORw0KGgoAAAANSUhEUgAAAGQAAAAyCAYAAACqNX6+AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAABTSURBVHhe7cExAQAAAMKg9U9tCy8gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOB252AAGVwH16AAAAABJRU5ErkJggg==';

        $response = [
            'apiResultCode' => 'A1000',
            'apiErrorMsg' => '',
            'apiResultData' => json_encode([
                'success' => true,
                'errorCode' => 'S0000',
                'errorMsg' => null,
                'msgData' => [
                    'waybillPicture' => [$base64Image], // 顺丰可能返回数组
                    'images' => [$base64Image] // 兼容性字段
                ]
            ])
        ];

        return json($response);
    }
}
