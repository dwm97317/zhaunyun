<?php
namespace app\store\controller;

use think\Controller;
use app\common\library\Ditch\Sf;
use app\store\model\Ditch as DitchModel;
use app\store\model\Inpack;

/**
 * 顺丰 JS 打印测试控制器
 */
class SfPrint extends Controller
{
    /**
     * 获取 JS 打印所需的配置参数 (Ajax)
     */
    public function getPrintConfig()
    {
        $orderId = input('order_id');
        if (empty($orderId)) {
            return json(['code' => 0, 'msg' => '订单ID不能为空']);
        }

        // 1. 获取配置
        $ditch = DitchModel::where('ditch_id', 10076)->find();
        if (!$ditch) {
            return json(['code' => 0, 'msg' => '未找到顺丰配置']);
        }

        $config = [
            'key' => $ditch['app_key'],
            'token' => $ditch['app_token'],
            'apiurl' => 'https://sfapi-sbox.sf-express.com/std/service', // 沙箱
            'customer_code' => isset($ditch['customer_code']) ? $ditch['customer_code'] : '',
            // 使用自定义模板
            'template_code' => 'fm_76130_fyp_standard_custom_10050050684_1' 
        ];

        // 2. 获取 Token
        $sf = new Sf($config);
        $accessToken = $sf->getCloudPrintAccessToken();
        if (!$accessToken) {
            return json(['code' => 0, 'msg' => '获取 AccessToken 失败: ' . $sf->getError()]);
        }

        // 3. 获取订单信息
        $order = Inpack::detail($orderId);
        if (!$order) {
            return json(['code' => 0, 'msg' => '订单不存在']);
        }
        $waybillNo = isset($order['t_order_sn']) ? $order['t_order_sn'] : $order['order_sn'];
        if (empty($waybillNo)) {
             return json(['code' => 0, 'msg' => '运单号不存在']);
        }

        // 4. 组装打印数据 (符合 SCPPrint.print 格式)
        $printData = [
            'requestID' => uniqid('PRT'),
            'accessToken' => $accessToken,
            'templateCode' => $config['template_code'],
            'documents' => [
                [
                    'masterWaybillNo' => $waybillNo,
                    'remark' => isset($order['remark']) ? $order['remark'] : 'JS打印测试备注'
                ]
            ]
        ];

        return json([
            'code' => 1, 
            'msg' => 'ok', 
            'data' => $printData,
            'partnerID' => $config['key'],
            'env' => 'sbox' // 前端初始化 SDK 用
        ]);
    }

    /**
     * 渲染打印测试页面
     */
    public function demo()
    {
        // 简单渲染一个视图，实际项目中可以是 .html 文件
        return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>顺丰云打印 JS 模式测试</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .box { border: 1px solid #ddd; padding: 20px; margin-top: 20px; border-radius: 5px; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; background: #d0021b; color: #fff; border: none; border-radius: 4px; }
        button:disabled { background: #ccc; }
        #log { background: #f5f5f5; padding: 10px; margin-top: 10px; height: 300px; overflow-y: scroll; font-family: monospace; }
    </style>
    <!-- 引入顺丰云打印 SDK (2.7 版本) -->
    <script src="https://scp-tcdn.sf-express.com/prd/sdk/lodop/2.7/SCPPrint.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <h1>顺丰云打印 (JS 插件模式) 测试</h1>
    
    <div class="box">
        <label>输入订单ID (Inpack ID): </label>
        <input type="text" id="orderId" value="" placeholder="例如: 69451">
        <button id="btnPrint" onclick="doPrint()">立即打印</button>
    </div>

    <div class="box">
        <h3>打印日志:</h3>
        <div id="log"></div>
    </div>

    <script>
        // 顺丰打印实例
        var scpPrint = null;

        function log(msg) {
            var el = document.getElementById('log');
            el.innerHTML += '<div>[' + new Date().toLocaleTimeString() + '] ' + msg + '</div>';
            el.scrollTop = el.scrollHeight;
        }

        function initSf(partnerID, env) {
            if (scpPrint) return;
            
            log('正在初始化顺丰 SDK... PartnerID: ' + partnerID + ', Env: ' + env);
            try {
                scpPrint = new SCPPrint({
                    partnerID: partnerID,
                    env: env, // 'sbox' or 'pro'
                    notips: false // 开启提示，方便调试
                });
                log('SDK 初始化完成');
            } catch (e) {
                log('SDK 初始化失败: ' + e.message);
                alert('请确保已安装顺丰打印组件(C-Lodop)!');
            }
        }

        function doPrint() {
            var orderId = $('#orderId').val();
            if (!orderId) {
                alert('请输入订单ID');
                return;
            }

            $('#btnPrint').prop('disabled', true).text('准备中...');
            log('正在获取打印配置...');

            // 1. 请求后端获取 AccessToken 和打印数据
            // 注意 URL 变更: sf_print
            $.get('/index.php?s=/store/sf_print/getPrintConfig', { order_id: orderId }, function(res) {
                if (res.code !== 1) {
                    log('获取配置失败: ' + res.msg);
                    $('#btnPrint').prop('disabled', false).text('立即打印');
                    return;
                }

                // 2. 初始化 SDK (如果还没初始化)
                initSf(res.partnerID, res.env);

                // 3. 调用打印
                var printData = res.data;
                log('获取配置成功，开始调用插件打印...');
                log('RequestID: ' + printData.requestID);
                log('Template: ' + printData.templateCode);

                scpPrint.print(printData, function(result) {
                    // 回调
                    log('打印结果: Code=' + result.code + ', Msg=' + result.msg);
                    if (result.code == 1) {
                        log('>>> 任务推送成功，请查看打印机');
                    } else {
                        log('>>> 打印失败');
                        if (result.downloadUrl) {
                             log('提示: 请下载并安装打印插件: ' + result.downloadUrl);
                             window.open(result.downloadUrl);
                        }
                    }
                    $('#btnPrint').prop('disabled', false).text('立即打印');
                }, {
                    // 可选配置
                    // preview: true // 如果想强制预览
                });

            }, 'json').fail(function() {
                log('网络请求失败');
                $('#btnPrint').prop('disabled', false).text('立即打印');
            });
        }
    </script>
</body>
</html>
HTML;
    }
}
