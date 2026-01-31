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
        
        // 1.1 测试获取 AccessToken (调试用)
        $token = $sf->getCloudPrintAccessToken();
        if ($token === false) {
             return json(['code' => 0, 'msg' => 'Test: 获取AccessToken失败: ' . $sf->getError()]);
        }
        
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
                            'order_id' => $orderId, // 返回订单ID方便测试
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
        return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顺丰云打印一体化工作台</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #d9001b; /* SF Red */
            --primary-hover: #b80017;
            --bg-body: #f4f6f8;
            --bg-card: #ffffff;
            --text-main: #2c3e50;
            --text-sub: #636e72;
            --border: #dfe6e9;
            --shadow: 0 4px 12px rgba(0,0,0,0.05);
            --radius: 8px;
        }
        body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, sans-serif; background: var(--bg-body); color: var(--text-main); -webkit-font-smoothing: antialiased; }
        
        /* Layout */
        .app-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 300px 1fr; gap: 24px; }
        
        /* Sidebar (Settings) */
        .sidebar { background: var(--bg-card); padding: 24px; border-radius: var(--radius); box-shadow: var(--shadow); position: sticky; top: 20px; height: fit-content; }
        .sidebar h2 { font-size: 18px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; }
        .setting-group { margin-bottom: 20px; }
        .setting-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text-sub); margin-bottom: 8px; }
        .setting-control { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; font-family: inherit; font-size: 14px; background: #fff; transition: 0.2s; box-sizing: border-box; }
        .setting-control:focus { border-color: var(--primary); outline: none; }
        select.setting-control { appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='gray' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px; }

        /* Main Content */
        .main-content { display: flex; flex-direction: column; gap: 24px; }
        
        /* Header Card */
        .status-card { background: var(--bg-card); padding: 24px; border-radius: var(--radius); box-shadow: var(--shadow); display: flex; justify-content: space-between; align-items: center; }
        .status-item { display: flex; align-items: center; gap: 8px; font-size: 14px; }
        .status-indicator { width: 8px; height: 8px; border-radius: 50%; background: #ccc; }
        .status-indicator.active { background: #00b894; box-shadow: 0 0 0 3px rgba(0,184,148,0.2); }
        .status-indicator.error { background: #d63031; }

        /* Workspace Card */
        .work-card { background: var(--bg-card); padding: 32px; border-radius: var(--radius); box-shadow: var(--shadow); text-align: center; }
        .input-wrapper { max-width: 400px; margin: 0 auto 24px; position: relative; }
        .order-input { width: 100%; padding: 16px 20px; font-size: 18px; border: 2px solid var(--border); border-radius: 12px; transition: 0.3s; text-align: center; font-weight: 500; box-sizing: border-box; }
        .order-input:focus { border-color: var(--primary); box-shadow: 0 4px 20px rgba(217,0,27,0.1); outline: none; }
        
        .action-btn { background: var(--primary); color: white; border: none; padding: 14px 40px; font-size: 16px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 10px rgba(217,0,27,0.3); display: inline-flex; align-items: center; gap: 8px; }
        .action-btn:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .action-btn:disabled { background: #b2bec3; cursor: not-allowed; transform: none; box-shadow: none; }

        /* Console Log */
        .console-card { background: #2d3436; color: #dfe6e9; padding: 20px; border-radius: var(--radius); font-family: 'Consolas', monospace; font-size: 13px; height: 300px; overflow-y: auto; display: flex; flex-direction: column; box-shadow: var(--shadow); }
        .log-entry { margin-bottom: 6px; border-left: 3px solid transparent; padding-left: 10px; line-height: 1.5; }
        .log-entry.info { border-color: #0984e3; color: #74b9ff; }
        .log-entry.success { border-color: #00b894; color: #55efc4; }
        .log-entry.error { border-color: #d63031; color: #ff7675; }
        .log-entry.warn { border-color: #fdcb6e; color: #ffeaa7; }
        .log-time { opacity: 0.5; font-size: 11px; margin-right: 8px; }

        /* Utilities */
        .btn-text { background: none; border: none; color: var(--primary); font-size: 12px; cursor: pointer; font-weight: 600; padding: 0; margin-left: auto; text-decoration: underline; }
        .checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer; }
        
        /* Spinner */
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 1s linear infinite; display: none; }
        .action-btn.loading .spinner { display: block; }

    </style>
    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <div class="app-container">
        <!-- 左侧设置栏 -->
        <aside class="sidebar">
            <h2>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                打印设置
            </h2>
            
            <div class="setting-group">
                <label>目标打印机 <button class="btn-text" onclick="refreshPrinters()">刷新</button></label>
                <select id="printerSelect" class="setting-control" onchange="saveConfig()">
                    <option value="">(请先加载插件)</option>
                </select>
            </div>

            <div class="setting-group">
                <label>纸张模式</label>
                <select id="paperType" class="setting-control" onchange="saveConfig()">
                    <option value="">默认尺寸</option>
                    <option value="A4">A4 纸张</option>
                    <option value="A4_copy">A4 包含留底</option>
                </select>
            </div>

            <div class="setting-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="isPreview" onchange="saveConfig()">
                    打印前预览
                </label>
            </div>

             <div class="setting-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="showTips" checked onchange="saveConfig()">
                    显示顺丰提示弹窗
                </label>
            </div>

            <div style="margin-top: 30px; font-size: 12px; color: #999;">
                <p>SDK版本: <span id="sdkVersion">Checking...</span></p>
                <p>插件状态: <span id="pluginStatus">Checking...</span></p>
            </div>
        </aside>

        <!-- 主工作区 -->
        <main class="main-content">
            <!-- 状态头 -->
            <div class="status-card">
                <div class="status-item">
                    <span class="status-indicator" id="statusSdk"></span>
                    SDK 加载
                </div>
                <div class="status-item">
                    <span class="status-indicator" id="statusConnect"></span>
                    本地服务
                </div>
                <div class="status-item">
                    <span class="status-indicator" id="statusCloud"></span>
                    顺丰云端
                </div>
            </div>

            <!-- 操作卡片 -->
            <div class="work-card">
                <h2 style="margin-top:0; color:#2c3e50;">打印新的运单</h2>
                <p style="color:#636e72; margin-bottom: 30px;">输入系统订单号，自动获取云端数据并下发打印机</p>
                
                <div class="input-wrapper">
                    <input type="text" id="orderInput" class="order-input" placeholder="输入订单 ID (如: 69463)" autocomplete="off">
                </div>

                <button id="printBtn" class="action-btn" onclick="startPrint()">
                    <div class="spinner"></div>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:block"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    <span>立即打印</span>
                </button>
            </div>

            <!-- 日志控制台 -->
            <div class="console-card" id="console">
                <div class="log-entry info"><span class="log-time">[System]</span> 欢迎使用顺丰云打印工作台</div>
            </div>
        </main>
    </div>

    <!-- Logic -->
    <script>
        // State
        const state = {
            sdkReady: false,
            printerList: [],
            scp: null,
            lastPartnerId: ''
        };

        // Logger
        function log(msg, type = 'info') {
            const el = document.getElementById('console');
            const time = new Date().toLocaleTimeString();
            el.innerHTML += `<div class="log-entry \${type}"><span class="log-time">[\${time}]</span> \${msg}</div>`;
            el.scrollTop = el.scrollHeight;
        }

        // Init
        window.onload = function() {
            loadSdk();
            loadConfig();
        };

        function loadConfig() {
            const cfg = JSON.parse(localStorage.getItem('sf_print_config') || '{}');
            if(cfg.printer) $('#printerSelect').val(cfg.printer);
            if(cfg.paper) $('#paperType').val(cfg.paper);
            $('#isPreview').prop('checked', !!cfg.preview);
        }

        function saveConfig() {
            const cfg = {
                printer: $('#printerSelect').val(),
                paper: $('#paperType').val(),
                preview: $('#isPreview').is(':checked'),
                tips: $('#showTips').is(':checked')
            };
            localStorage.setItem('sf_print_config', JSON.stringify(cfg));
            return cfg;
        }

        // 1. Load SDK
        function loadSdk() {
            log('正在加载顺丰云打印 SDK...', 'info');
            const script = document.createElement('script');
            script.src = "https://scp-tcdn.sf-express.com/prd/sdk/lodop/2.7/SCPPrint.js";
            
            script.onload = () => {
                state.sdkReady = true;
                $('#statusSdk').addClass('active');
                $('#sdkVersion').text('v2.7 (Loaded)');
                log('SDK 加载成功', 'success');
                // 尝试用临时 ID 初始化以获取打印机
                initScp('THGJH89TNITE', 'sbox', true);
            };
            
            script.onerror = () => {
                $('#statusSdk').addClass('error');
                log('SDK 加载失败，请检查网络 (scp-tcdn.sf-express.com)', 'error');
            };
            document.body.appendChild(script);
        }

        // 2. Init SCP Instance
        function initScp(partnerID, env, isSilent = false) {
            if (!state.sdkReady) return;
            
            try {
                if(!isSilent) log(`初始化 SDK 实例 (PID: \${partnerID})...`, 'info');
                state.scp = new SCPPrint({
                    partnerID: partnerID,
                    env: env,
                    notips: !$('#showTips').is(':checked')
                });
                
                state.lastPartnerId = partnerID;
                $('#statusConnect').addClass('active');
                $('#pluginStatus').text('Connected');
                
                if(isSilent) {
                    refreshPrinters(true);
                } else {
                    log('打印服务连接成功', 'success');
                }
                return true;
            } catch (e) {
                $('#statusConnect').addClass('error');
                if(!isSilent) log('Init Error: ' + e.message, 'error');
                return false;
            }
        }

        // 3. Get Printers
        function refreshPrinters(silent = false) {
            if (!state.scp) return;
            if (!silent) log('正在刷新打印机列表...', 'info');
            
            state.scp.getPrinters((res) => {
                if (res.code === 1) {
                    const list = res.printers;
                    const $sel = $('#printerSelect');
                    const saved = $sel.val();
                    
                    $sel.empty().append('<option value="">-- 使用默认打印机 --</option>');
                    list.forEach(p => {
                        $sel.append(`<option value="\${p.name}">\${p.name}</option>`);
                    });
                    
                    if(saved) $sel.val(saved);
                    if (!silent) log(`找到 \${list.length} 台打印机`, 'success');
                } else {
                    if (!silent) log(`获取打印机失败: \${res.msg}`, 'warn');
                }
            });
        }

        // 4. Main Print Flow
        function startPrint() {
            const orderId = $('#orderInput').val();
            if (!orderId) return alert('请输入订单 ID');
            if (!state.sdkReady) return alert('SDK 未就绪');
            
            const $btn = $('#printBtn');
            $btn.prop('disabled', true).addClass('loading');
            
            log('============== 开始打印流程 ==============', 'info');
            log(`正在获取订单 [ \${orderId} ] 数据...`, 'info');

            // Step A: Get Data
            $.get('/index.php?s=/store/sf_print/getPrintConfig', { order_id: orderId }, (res) => {
                if (res.code !== 1) {
                    throw new Error(res.msg);
                }
                
                $('#statusCloud').addClass('active');
                log('云端数据获取成功', 'success');
                
                const { partnerID, env, data } = res;
                
                // Step B: Re-init if partner changed
                if (state.lastPartnerId !== partnerID) {
                    initScp(partnerID, env);
                }
                
                // Step C: Set Printer
                const cfg = saveConfig();
                if (cfg.printer) {
                    log(`设置打印机: \${cfg.printer}`, 'info');
                    state.scp.setPrinter(cfg.printer);
                } else {
                     log(`使用系统默认打印机`, 'info');
                }
                
                // Step D: Print
                const options = {
                    lodopFn: cfg.preview ? 'PREVIEW' : 'PRINT',
                    pageType: cfg.paper || undefined
                };
                
                log(`发送指令 (RID: \${data.requestID})`, 'info');
                state.scp.print(data, (ret) => {
                    handlePrintCallback(ret);
                    $btn.prop('disabled', false).removeClass('loading');
                }, options);

            }, 'json').fail((err) => {
                log('网络请求失败: ' + err.statusText, 'error');
                $btn.prop('disabled', false).removeClass('loading');
            });
        }
        
        function handlePrintCallback(res) {
            if (res.code === 1) {
                log('指令执行成功', 'success');
            } else if (res.code === 2 || res.code === 3) {
                 log('需安装/更新插件', 'error');
                 if( confirm(`需要安装打印插件: \${res.msg}\n点击确定下载`) ) {
                     window.open(res.downloadUrl);
                 }
            } else {
                log(`错误 (Code \${res.code}): \${res.msg}`, 'error');
            }
        }

    </script>
</body>
</html>
HTML;
    }
}
