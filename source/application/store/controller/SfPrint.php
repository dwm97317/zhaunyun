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
     * 本地测试顺丰面单打印
     * 访问地址: /index.php?s=/store/sf_print/test
     */
    public function test()
    {
        // 1. 获取顺丰真实/沙箱配置
        $ditch = DitchModel::where('ditch_id', 10076)->find();
        
        if (!$ditch) {
            $config = [
                'key' => 'THGJH89TNITE',           
                'token' => '7D88D4D6DC58914624F087796D1244C3',         
                'apiurl' => 'https://sfapi-sbox.sf-express.com/std/service',
                'template_code' => 'fm_76130_standard_THGJH89TNITE', 
                'sync_mode' => 0 
            ];
        } else {
            $config = [
                'key' => $ditch['app_key'],
                'token' => $ditch['app_token'],
                'apiurl' => 'https://sfapi-sbox.sf-express.com/std/service', // 强制沙箱
                'customer_code' => isset($ditch['customer_code']) ? $ditch['customer_code'] : '',
                'template_code' => 'fm_76130_standard_' . $ditch['app_key'],
                'sync_mode' => 0
            ];
        }

        $orderSn = 'TEST_LOC_' . date('YmdHis');
        $sf = new Sf($config);
        
        // 2. 下单
        $orderParams = [
            'partnerOrderCode' => $orderSn,
            'order_sn' => $orderSn,
            'consignee_name' => '本地测试',
            'consignee_mobile' => '13800138000',
            'consignee_province' => '广东省',
            'consignee_city' => '深圳市',
            'consignee_suburb' => '南山区',
            'consignee_address' => '科技南十二路2号',
            'sender_name' => '本地寄件',
            'sender_mobile' => '13800138000',
            'sender_province' => '广东省',
            'sender_city' => '广州市',
            'sender_district' => '越秀区',
            'sender_address' => '建设六马路',
            'quantity' => 1,
            'weight' => 1.0,
            'payMethod' => 1,
        ];
        
        $res = $sf->createOrder($orderParams);
        if ($res['ack'] !== 'true') {
             return json(['code' => 0, 'msg' => '下单失败: ' . $res['message']]);
        }
        $waybillNo = $res['tracking_number'];
        
        // 3. 存入数据库
        $orderId = $this->createTempOrder($orderSn, $waybillNo);
        if (!$orderId) {
             return json(['code' => 0, 'msg' => '创建临时订单记录失败']);
        }
        
        // 4. 调用打印
        try {
            $result = $sf->printlabelParsedData($orderId);
            
            if ($result === 'ASYNC_REQUEST_SENT') {
                return json(['code' => 1, 'msg' => '异步请求已发送，请等待回调', 'data' => ['order_sn' => $orderSn, 'waybill_no' => $waybillNo]]);
            }

            if ($result) {
                // 如果返回的是 URL 字符串（兼容旧模式/PDF模式）
                if (is_string($result)) {
                    return json([
                        'code' => 1, 
                        'msg' => '测试成功(PDF模式)', 
                        'data' => [
                            'order_sn' => $orderSn,
                            'waybill_no' => $waybillNo,
                            'url' => $result,
                            'is_local' => strpos($result, '/uploads/sf_label/') !== false
                        ]
                    ]);
                } 
                // 如果返回的是数组（ParsedData 模式，返回 contents）
                else if (is_array($result)) {
                     return json([
                        'code' => 1, 
                        'msg' => '测试成功(Plugin接口数据模式)', 
                        'data' => [
                            'order_sn' => $orderSn,
                            'waybill_no' => $waybillNo,
                            'plugin_data' => $result // 直接返回给前端查看，或者供插件使用
                        ]
                    ]);
                }
            }
            
            return json(['code' => 0, 'msg' => '打印接口调用失败: ' . $sf->getError()]);

        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '异常: ' . $e->getMessage()]);
        }
    }
    
    private function createTempOrder($orderSn, $waybillNo)
    {
         try {
            $model = new Inpack();
            $exist = $model->where('order_sn', $orderSn)->find();
            if ($exist) return $exist['id'];
            $data = [
                'order_sn' => $orderSn,
                't_order_sn' => $waybillNo,
                'wxapp_id' => 10001,
                'member_id' => 1, 
                'created_time' => date('Y-m-d H:i:s'),
                'inpack_type' => 2
            ];
            $model->save($data);
            return $model->id;
        } catch (\Exception $e) {
            return 0;
        }
    }


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

        // 3. 获取订单信息 & 调用 PARSEDDATA 获取云打印数据
        $parsedData = $sf->printlabelParsedData($orderId);
        if (!$parsedData) {
             return json(['code' => 0, 'msg' => '获取云打印数据失败: ' . $sf->getError()]);
        }
        
        // 如果返回的是字符串(URL), 说明可能配置错误或者是PDF模式
        if (is_string($parsedData)) {
            return json(['code' => 0, 'msg' => '接口返回了文件URL而不是数据，请检查配置。URL: ' . $parsedData]);
        }

        // 4. 组装打印数据 (符合 SCPPrint.print 格式)
        // 注意：当使用 parsedData 模式时，我们将后端返回的 contents 直接传给 documents
        // 但 SCPPrint.print 的 data 结构通常是 { requestID, accessToken, templateCode, documents }
        // 文档中 COM_RECE_CLOUD_PRINT_PARSEDDATA 返回的是一份 contents 数据
        // 在 JS SDK 中，如果是传递已解析的数据，通常直接作为 document 的 contents
        
        $printData = [
            'requestID' => uniqid('PRT'),
            'accessToken' => $accessToken,
            'templateCode' => $config['template_code'],
            'documents' => [
                 $parsedData // parsedData 是包含 contents 字段的对象
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
        .box { border: 1px solid #ddd; padding: 20px; margin-top: 20px; border-radius: 5px; background: #fff; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; background: #d0021b; color: #fff; border: none; border-radius: 4px; }
        button:disabled { background: #ccc; }
        #log { background: #f5f5f5; padding: 10px; margin-top: 10px; height: 300px; overflow-y: scroll; font-family: monospace; border: 1px solid #ccc; }
        .status { margin-bottom: 10px; font-weight: bold; }
        .success { color: green; }
        .error { color: red; }
    </style>
    <!-- 引入 jQuery -->
    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <h1>顺丰云打印 (JS 插件模式) 测试</h1>
    
    <div class="status" id="sdkStatus">SDK 加载状态: 等待加载...</div>

    <div class="box">
        <label>输入订单ID (Inpack ID): </label>
        <input type="text" id="orderId" value="" placeholder="例如: 69451" style="padding: 8px; width: 200px;">
        <button id="btnPrint" onclick="doPrint()">立即打印</button>
        <button onclick="checkLodop()" style="background: #333; margin-left: 10px;">检查插件状态</button>
    </div>

    <div class="box">
        <h3>操作日志:</h3>
        <div id="log"></div>
    </div>

    <!-- 动态加载顺丰 SDK，防止阻塞 -->
    <script>
        // 屏蔽无关的全局错误，防止 common.js 干扰
        window.onerror = function(msg, url, line) {
            console.warn('捕获到全局错误(已忽略):', msg);
            return true; // 返回 true 阻止错误冒泡
        };

        var scpPrint = null;
        var sdkLoaded = false;

        function log(msg, type) {
            var el = document.getElementById('log');
            var color = type === 'error' ? 'red' : (type === 'success' ? 'green' : 'black');
            el.innerHTML += '<div style="color:' + color + '">[' + new Date().toLocaleTimeString() + '] ' + msg + '</div>';
            el.scrollTop = el.scrollHeight;
        }

        // 动态加载 SDK
        function loadSdk() {
            var script = document.createElement('script');
            script.src = "https://scp-tcdn.sf-express.com/prd/sdk/lodop/2.7/SCPPrint.js";
            script.onload = function() {
                sdkLoaded = true;
                $('#sdkStatus').text('SDK 加载状态: 加载成功').addClass('success');
                log('顺丰 SCPPrint.js 加载成功', 'success');
            };
            script.onerror = function() {
                $('#sdkStatus').text('SDK 加载状态: 加载失败').addClass('error');
                log('顺丰 SCPPrint.js 加载失败，请检查网络', 'error');
            };
            document.head.appendChild(script);
        }

        function checkLodop() {
            if (!sdkLoaded) {
                alert('SDK 尚未加载完成');
                return;
            }
            try {
                // 尝试实例化一个临时的检查对象 (参数随便填，主要是为了触发内部的 CLodop 检测)
                // 注意：SCPPrint 实例化时会尝试连接本地服务
                log('正在检测本地打印插件...');
                var temp = new SCPPrint({
                     partnerID: 'CHECK',
                     env: 'sbox',
                     notips: true
                });
                log('插件连接检测指令已发送（请留意浏览器控制台或弹窗）');
            } catch (e) {
                log('检测异常: ' + e.message, 'error');
                alert('请确认已安装顺丰打印插件！');
            }
        }

        function initSf(partnerID, env) {
            if (scpPrint) return true;
            
            log('正在初始化打印实例... PartnerID: ' + partnerID, 'info');
            try {
                scpPrint = new SCPPrint({
                    partnerID: partnerID,
                    env: env, 
                    notips: false 
                });
                log('打印实例初始化成功', 'success');
                return true;
            } catch (e) {
                log('SDK 初始化异常: ' + e.message, 'error');
                if (e.message.indexOf('not defined') > -1) {
                     log('可能是 SCPPrint.js 加载失败', 'error');
                } else {
                     log('请检查是否安装了打印插件', 'error');
                }
                return false;
            }
        }

        function doPrint() {
            if (!sdkLoaded) {
                alert('正在加载 SDK，请稍候...');
                return;
            }

            var orderId = $('#orderId').val();
            if (!orderId) {
                alert('请输入订单ID');
                return;
            }

            $('#btnPrint').prop('disabled', true).text('处理中...');
            log('开始打印流程，订单ID: ' + orderId);

            // 1. 请求后端获取配置
            $.get('/index.php?s=/store/sf_print/getPrintConfig', { order_id: orderId }, function(res) {
                if (res.code !== 1) {
                    log('后端报错: ' + res.msg, 'error');
                    $('#btnPrint').prop('disabled', false).text('立即打印');
                    return;
                }

                log('获取后端配置成功，准备调用打印机...');

                // 2. 初始化
                if (!initSf(res.partnerID, res.env)) {
                    $('#btnPrint').prop('disabled', false).text('立即打印');
                    return;
                }

                // 3. 调用打印
                var printData = res.data;
                log('发送打印任务... RequestID: ' + printData.requestID);

                try {
                    scpPrint.print(printData, function(result) {
                        log('收到打印回调: Code=' + result.code + ', Msg=' + result.msg, result.code == 1 ? 'success' : 'error');
                        
                        if (result.code == 1) {
                            alert('打印任务已下发！');
                        } else {
                            if (result.downloadUrl) {
                                 log('需安装插件，下载地址: ' + result.downloadUrl, 'error');
                                 // 自动弹出下载
                                 if(confirm('检测到未安装打印插件，是否立即下载？')) {
                                     window.open(result.downloadUrl);
                                 }
                            }
                        }
                        $('#btnPrint').prop('disabled', false).text('立即打印');
                    });
                } catch (e) {
                    log('调用 print 方法异常: ' + e.message, 'error');
                    $('#btnPrint').prop('disabled', false).text('立即打印');
                }

            }, 'json').fail(function(xhr, status, error) {
                log('网络请求失败: ' + error, 'error');
                console.error(xhr.responseText);
                $('#btnPrint').prop('disabled', false).text('立即打印');
            });
        }

        // 页面加载完成后加载 SDK
        loadSdk();
    </script>
</body>
</html>
HTML;
    }
}
