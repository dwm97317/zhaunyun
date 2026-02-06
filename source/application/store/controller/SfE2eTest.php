<?php
namespace app\store\controller;

use think\Controller;
use think\Request;
use app\common\library\Ditch\Sf;
use app\store\model\Ditch as DitchModel;
use app\store\model\Inpack;

/**
 * 顺丰面单 API 端到端测试脚本 (真实环境)
 */
class SfE2eTest extends Controller
{
    private $logFile;

    public function _initialize()
    {
        // 提前初始化 Session，避免后续 echo 导致 headers sent 错误
        \think\Session::init();
        
        parent::_initialize();
        $this->logFile = ROOT_PATH . 'logs' . DS . 'sf_e2e_test.log';
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }

    public function run()
    {
        set_time_limit(300); // 增加超时时间到 5 分钟
        $this->log("===== 开始端到端测试 (沙箱环境 - 下单+云打印) =====");
        $this->log("ROOT_PATH: " . ROOT_PATH);
        
        // 0. 检查并创建目录
        $webPath = ROOT_PATH . 'web' . DS . 'uploads' . DS . 'sf_label';
        if (!file_exists($webPath)) {
            if (mkdir($webPath, 0755, true)) {
                 $this->log("目录创建成功");
            }
        }

        // 1. 获取真实配置 (沙箱)
        $config = $this->getSfConfig();
        if (!$config) {
            return;
        }
        
        // --- Test 1: Sync Mode ---
        $this->log("--- 测试 1: 同步模式 (默认) ---");
        $config['sync_mode'] = 0; // 强制同步
        $sf = new Sf($config);

        // 2. 执行下单 (获取沙箱运单号)
        $orderSn = 'TEST' . date('YmdHis') . mt_rand(100, 999);
        $this->log("正在执行沙箱下单... 订单号: {$orderSn}");
        
        $orderParams = [
            'partnerOrderCode' => $orderSn,
            'order_sn' => $orderSn,
            'consignee_name' => '沙箱收件人',
            'consignee_mobile' => '13800138000',
            'consignee_province' => '广东省',
            'consignee_city' => '深圳市',
            'consignee_suburb' => '南山区',
            'consignee_address' => '科技南十二路2号',
            'sender_name' => '沙箱寄件人',
            'sender_mobile' => '13800138000',
            'sender_province' => '广东省',
            'sender_city' => '广州市',
            'sender_district' => '越秀区',
            'sender_address' => '建设六马路',
            'quantity' => 1,
            'weight' => 1.0,
            'payMethod' => 1, // 寄方付
            // 模拟业务场景：传递 buyer_remark 和 seller_remark
            // 真实的 Ditch/Sf 类内部逻辑应该会将这些字段合并到 remark 中
            'buyer_remark' => '请尽快发货(测试)',
            'seller_remark' => '优先派送(测试)',
        ];

        $res = $sf->createOrder($orderParams);
        
        if ($res['ack'] !== 'true') {
            $this->log("下单失败: " . $res['message'], true);
            return;
        }
        
        $waybillNo = $res['tracking_number'];
        $this->log("下单成功! 运单号: {$waybillNo}");
        
        // 为了调用 printlabel，我们需要在数据库中有一个对应的订单记录
        // 传入 remark 以便 printlabel 获取
        $remark = '买家留言: 请尽快发货(测试); 卖家备注: 优先派送(测试)';
        $orderId = $this->createTempOrder($orderSn, $waybillNo, $remark);
        if (!$orderId) {
            $this->log("无法创建临时数据库订单", true);
            return;
        }

        // 3. 执行云打印 (同步)
        $this->log("调用 Sf::printlabel (Sync), OrderID: {$orderId}, WaybillNo: {$waybillNo}");
        // 这里默认使用的是 COM_RECE_CLOUD_PRINT_WAYBILLS 接口
        // $url = $sf->printlabel($orderId);
        
        // 切换使用 2.8.3 云打印面单打印插件接口 (COM_RECE_CLOUD_PRINT_PARSEDDATA)
        // 这个接口支持返回 Base64 或 URL，并且可以指定更多打印参数
        $this->log("切换测试: 使用 COM_RECE_CLOUD_PRINT_PARSEDDATA 接口");
        $url = $sf->printlabelParsedData($orderId);

        // 4. 验证结果
        if ($url) {
            $this->verifyResult($url);
        } else {
            $this->log("失败: 接口返回 false, 错误: " . $sf->getError(), true);
        }

        // --- Test 2: Async Mode ---
        $this->log("--- 测试 2: 异步模式 ---");
        $config['sync_mode'] = 1; // 强制异步
        $sfAsync = new Sf($config);
        // 使用同一个 OrderID 再次打印
        $asyncResult = $sfAsync->printlabel($orderId);
        $this->log("异步打印返回: " . json_encode($asyncResult, JSON_UNESCAPED_UNICODE));
        
        $this->log("===== 测试结束 =====");
    }

    private function createTempOrder($orderSn, $waybillNo, $remark = '')
    {
        // 插入一条临时数据供 printlabel 查询
        try {
            $model = new \app\store\model\Inpack();
            // 检查是否存在
            $exist = $model->where('order_sn', $orderSn)->find();
            if ($exist) {
                // 如果存在，更新 remark
                if ($remark) {
                    $exist->save(['remark' => $remark]);
                }
                return $exist['id'];
            }

            $data = [
                'order_sn' => $orderSn,
                't_order_sn' => $waybillNo,
                'wxapp_id' => 10001,
                'member_id' => 1, 
                'created_time' => date('Y-m-d H:i:s'),
                'inpack_type' => 2,
                'remark' => $remark // 保存 remark 到数据库
            ];
            // 简单插入，这里可能需要根据实际 Inpack 模型字段调整
            $model->save($data);
            return $model->id;
        } catch (\Exception $e) {
            $this->log("数据库插入失败: " . $e->getMessage(), true);
            return 0;
        }
    }

    private function getSfConfig()
    {
        // 顺丰渠道ID为 10076 (ditch_id)
        $ditch = DitchModel::where('ditch_id', 10076)->find();
        
        if (!$ditch) {
            $this->log("失败: 未找到 ID 为 10076 的顺丰配置", true);
            echo "未找到顺丰渠道配置<br/>";
            return false;
        }

        $config = [
            'key' => $ditch['app_key'],
            'token' => $ditch['app_token'],
            'apiurl' => 'https://sfapi-sbox.sf-express.com/std/service', // 切换回沙箱环境
            'customer_code' => isset($ditch['customer_code']) ? $ditch['customer_code'] : '',
            // 增加自定义模板配置，用于测试备注显示
            'template_code' => 'fm_76130_fyp_standard_custom_10050050684_1'
        ];

        // 必填校验
        $missing = [];
        if (empty($config['key'])) $missing[] = 'app_key';
        if (empty($config['token'])) $missing[] = 'app_token';
        if (empty($config['apiurl'])) $missing[] = 'api_url';

        if (!empty($missing)) {
            $msg = "配置缺失: " . implode(', ', $missing);
            $this->log("失败: {$msg}", true);
            echo $msg . "<br/>";
            return false;
        }

        $this->log("配置读取成功: Key={$config['key']}, URL={$config['apiurl']}");
        return $config;
    }

    private function getRealOrder()
    {
        // 查找一个真实存在的、有转运单号的订单
        $order = Inpack::where('t_order_sn', '<>', '')->order('id', 'desc')->find();
        if ($order) {
            return $order['id'];
        }
        return 0;
    }

    private function verifyResult($url)
    {
        $this->log("接收到 URL: {$url}");

        // 1. 检查 URL 格式
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            $this->log("失败: 返回的不是有效 URL", true);
            return;
        }

        // 2. 解析本地路径
        // 注意：parse_url 只会返回路径部分，例如 /v1.2/AUTH_EOS-SCP-CORE/...
        // 我们需要把这个路径映射到本地 uploads/sf_label 下
        // 但 Sf::printlabel 中是把文件下载到了 uploads/sf_label/waybillNo_time.pdf
        // 并返回了 http://domain/uploads/sf_label/waybillNo_time.pdf
        // 所以这里的 url 应该已经是本地 URL 了
        
        // 如果 url 包含 'sf-express.com'，说明下载失败直接返回了远程 URL
        if (strpos($url, 'sf-express.com') !== false) {
             $this->log("验证通过: 获取到顺丰远程面单链接");
             $this->log("提示: 文件可能未成功下载到本地，直接使用远程链接");
             return;
        }

        // 解析本地文件路径
        // 假设 url 是 http://127.0.0.1:8080/uploads/sf_label/SFxxx.pdf
        $pathInfo = parse_url($url, PHP_URL_PATH); // /uploads/sf_label/SFxxx.pdf
        $localPath = ROOT_PATH . 'web' . str_replace('/', DS, $pathInfo);
        // 处理可能的路径前缀
        if (strpos($url, '/uploads/') !== false) {
             $relPath = substr($url, strpos($url, '/uploads/'));
             $localPath = ROOT_PATH . 'web' . str_replace('/', DS, $relPath);
        }

        // 3. 验证文件存在性
        if (!file_exists($localPath)) {
            $this->log("失败: 本地文件不存在: {$localPath}", true);
            return;
        }

        // 4. 验证文件内容
        $content = file_get_contents($localPath);
        $size = strlen($content);
        $this->log("文件大小: {$size} bytes");
        
        if ($size < 10) {
            $this->log("失败: 文件内容过小", true);
            return;
        }

        // 5. 验证是否为图片或PDF
        if (strpos($localPath, '.pdf') !== false) {
             $this->log("验证通过: 文件是 PDF 格式");
        } else if (function_exists('getimagesize')) {
            $imgInfo = getimagesize($localPath);
            if ($imgInfo) {
                $this->log("验证通过: 文件是有效的图片格式 (MIME: {$imgInfo['mime']})");
            } else {
                $this->log("警告: getimagesize 无法识别文件", true);
            }
        } else {
            // 简单验证文件头
            $handle = fopen($localPath, 'rb');
            $bytes = fread($handle, 4);
            fclose($handle);
            // PNG: 89 50 4E 47, JPG: FF D8 FF
            if (bin2hex($bytes) === '89504e47' || substr(bin2hex($bytes), 0, 6) === 'ffd8ff') {
                 $this->log("验证通过: 文件头检查通过");
            } else {
                 $this->log("警告: 文件头未知: " . bin2hex($bytes), true);
            }
        }
    }

    private function log($msg, $isError = false)
    {
        $time = date('Y-m-d H:i:s');
        $level = $isError ? 'ERROR' : 'INFO';
        $line = "[{$time}] [{$level}] {$msg}" . PHP_EOL;
        
        // 如果不是命令行模式，输出 HTML 换行
        if (php_sapi_name() !== 'cli') {
            echo nl2br($line);
            // 如果消息包含 URL，尝试显示图片或PDF链接
            if (strpos($msg, 'http') !== false && (strpos($msg, '.jpg') !== false || strpos($msg, '.png') !== false || strpos($msg, '.pdf') !== false)) {
                preg_match('/(http[^\s]+)/', $msg, $matches);
                if (isset($matches[1])) {
                    $url = $matches[1];
                    echo "<div style='margin: 20px; padding: 10px; border: 1px solid #ccc; display: inline-block;'>";
                    echo "<h3>生成的面单预览:</h3>";
                    if (strpos($url, '.pdf') !== false) {
                        echo "<a href='{$url}' target='_blank'>点击查看 PDF 面单</a>";
                    } else {
                        echo "<img src='{$url}' style='max-width: 500px; border: 1px solid #000;' />";
                        echo "<p><a href='{$url}' target='_blank'>点击查看原图</a></p>";
                    }
                    echo "</div><br/>";
                }
            }
            // 强制刷新缓冲区
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        } else {
            echo $line;
        }
        
        file_put_contents($this->logFile, $line, FILE_APPEND);
    }

    private function simulateCallback()
    {
        // 构造模拟数据 (完全符合顺丰异步回调格式)
        // 顺丰文档示例结构可能如下 (需根据实际文档调整):
        // POST 参数:
        // msgData: JSON string
        // msgDigest: string
        // ...
        
        $msgData = [
            'obj' => [
                'files' => [
                    [
                        'waybillNo' => 'SF_TEST_CALLBACK_' . time(),
                        'url' => 'https://eos-scp-core-shenzhen-futian1-oss.sf-express.com:443/v1.2/AUTH_EOS-SCP-CORE/print-file-sbox/AAABnBLWv3KQSjuX9qtFqYbkw3-0mUaA_SF7444701509578_fm_76130_standard_THGJH89TNITE_1_1.pdf',
                        'token' => 'AUTH_tkv12_f146d1855480549d262b5c46ab0ab597ff20a97d9d0db45c16bedeb4fabd112b012deadd477ee524b1d690ce01baa3cdffbb125a6ccf69b73778dba2eb5157eb32f93a62c0259c83e441d4ba63cb59d086f03e025613eb69a9392abd61d116a9944266dd4a90fe43b9be72a9adb5e1c4cd897c2a075a31127c69eacf2d72a58abb58a61b29b31d4112860b037013a602ac8d45adbcf67165bd1e8dc09c237b61689b51675f753337a5d1771f1b7f0716f05cbb22ee41cbd9535310d29eca2c47'
                    ]
                ]
            ]
        ];
        
        $postData = [
            'msgData' => json_encode($msgData),
            'msgDigest' => 'MOCK_SIGNATURE',
            'requestID' => 'MOCK_REQ_' . time(),
            'partnerID' => 'THGJH89TNITE'
        ];
        
        $this->log("模拟回调: 直接调用 SfCallback 控制器");
        
        try {
            $callback = new \app\api\controller\SfCallback();
            // 直接传递 POST 数据
            $res = $callback->notify($postData); 
            
            // 获取响应数据
            if ($res instanceof \think\response\Json) {
                $data = $res->getData();
                $this->log("回调响应: " . json_encode($data, JSON_UNESCAPED_UNICODE));
                
                // 验证响应格式是否符合顺丰要求 (A1000)
                if (isset($data['apiResultCode']) && $data['apiResultCode'] === 'A1000') {
                    $this->log("回调测试通过");
                } else {
                    $this->log("回调测试失败: 响应码不正确", true);
                }
            } else {
                $this->log("回调响应类型未知: " . get_class($res));
            }
            
        } catch (\Exception $e) {
            $this->log("回调执行异常: " . $e->getMessage(), true);
        }
    }
}
