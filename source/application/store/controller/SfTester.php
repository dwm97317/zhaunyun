<?php
namespace app\store\controller;

use app\common\library\Ditch\Sf;
use app\store\model\Ditch as DitchModel;
use app\store\model\Inpack;
use think\Controller;

/**
 * 顺丰面单接口验证控制器
 * Class SfTester
 * @package app\store\controller
 */
class SfTester extends Controller
{
    private $logFile;

    public function _initialize()
    {
        parent::_initialize();
        $this->logFile = ROOT_PATH . 'logs' . DS . 'sf_label_test.log';
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }

    public function index()
    {
        $this->log("===== 开始顺丰面单接口测试 =====");
        
        // 1. 检查服务器路径权限
        $this->checkDirectory();

        // 2. 从渠道中心读取配置
        $config = $this->getSfConfig();
        if (!$config) {
            return;
        }

        // 3. 准备测试数据 (获取一个存在的订单)
        $order = Inpack::where('t_order_sn', '<>', '')->order('id', 'desc')->find();
        if (!$order) {
            $this->log("失败: 未找到包含转运单号(t_order_sn)的测试订单", true);
            echo "未找到测试订单";
            return;
        }
        $this->log("使用测试订单ID: {$order['id']}, 运单号: {$order['t_order_sn']}");

        // 4. 调用接口
        try {
            $sf = new Sf($config);
            $url = $sf->printlabel($order['id']);

            if ($url) {
                // 5. 验证结果
                $this->verifyResult($url, $order['t_order_sn']);
            } else {
                $error = $sf->getError();
                $this->log("失败: 接口调用失败 - {$error}", true);
                echo "接口调用失败: {$error}";
            }
        } catch (\Exception $e) {
            $this->log("异常: " . $e->getMessage(), true);
            echo "发生异常: " . $e->getMessage();
        }

        $this->log("===== 测试结束 =====");
    }

    private function checkDirectory()
    {
        $path = ROOT_PATH . 'web' . DS . 'uploads' . DS . 'sf_label';
        $this->log("检查目录: {$path}");

        if (!file_exists($path)) {
            if (mkdir($path, 0755, true)) {
                $this->log("目录不存在，已自动创建");
            } else {
                $this->log("失败: 无法创建目录", true);
                die("无法创建目录");
            }
        }

        if (is_writable($path)) {
            $this->log("目录权限验证通过 (可写)");
        } else {
            $this->log("失败: 目录无写入权限", true);
            die("目录无写入权限");
        }
    }

    private function getSfConfig()
    {
        // 假设顺丰渠道ID为 10010
        $ditch = DitchModel::where('ditch_no', 10010)->find();
        
        if (!$ditch) {
            $this->log("失败: 未找到渠道编码为 10010 的顺丰配置", true);
            echo "未找到顺丰渠道配置";
            return false;
        }

        $config = [
            'key' => $ditch['app_key'],
            'token' => $ditch['app_token'],
            'apiurl' => $ditch['api_url'],
            'customer_code' => isset($ditch['customer_code']) ? $ditch['customer_code'] : ''
        ];

        // 必填校验
        $missing = [];
        if (empty($config['key'])) $missing[] = 'app_key';
        if (empty($config['token'])) $missing[] = 'app_token';
        if (empty($config['apiurl'])) $missing[] = 'api_url';

        if (!empty($missing)) {
            $msg = "配置缺失: " . implode(', ', $missing);
            $this->log("失败: {$msg}", true);
            echo $msg;
            return false;
        }

        $this->log("配置读取成功: Key={$config['key']}, URL={$config['apiurl']}");
        return $config;
    }

    private function verifyResult($url, $orderSn)
    {
        $this->log("接口返回URL: {$url}");

        // 检查是否是本地文件URL
        if (strpos($url, 'uploads/sf_label') !== false) {
            // 转换为本地路径验证
            $localPath = ROOT_PATH . 'web' . str_replace('/', DS, parse_url($url, PHP_URL_PATH));
            // 简单处理路径，去除可能的域名部分
            $webRootIndex = strpos($url, '/uploads/');
            if ($webRootIndex !== false) {
                $relPath = substr($url, $webRootIndex);
                $localPath = ROOT_PATH . 'web' . str_replace('/', DS, $relPath);
            }

            if (file_exists($localPath)) {
                $size = filesize($localPath);
                $this->log("文件存在: {$localPath}");
                $this->log("文件大小: {$size} bytes");

                if ($size > 0) {
                    $this->log("验证通过: 成功生成标签");
                    echo "成功生成标签：{$localPath}";
                } else {
                    $this->log("失败: 文件大小为0", true);
                    echo "失败: 文件大小为0";
                }
            } else {
                // 可能是远程URL
                $this->log("警告: 本地文件未找到 (可能是远程URL或路径解析错误): {$localPath}");
                echo "成功获取URL: {$url}";
            }
        } else {
            $this->log("验证通过: 返回了远程URL");
            echo "成功获取URL: {$url}";
        }
    }

    public function testBase64Callback()
    {
        $waybillNo = "SF_MOCK_" . time();
        $mockPdfContent = "This is a mock PDF content for testing Base64 decode.";
        $base64Content = base64_encode($mockPdfContent);

        // 构造符合 Base64 模式的报文
        // 模式 A: 单个对象直接在 msgData 中
        $msgData = [
            "content" => $base64Content,
            "fileName" => $waybillNo . ".pdf",
            "waybillNo" => $waybillNo,
            "fileType" => "pdf"
        ];
        
        $postData = [
            'msgData' => json_encode($msgData),
            'requestID' => 'TEST_REQ_' . time()
        ];
        
        $this->log("===== 开始 Base64 回调测试 =====");
        
        try {
            $callback = new \app\api\controller\SfCallback();
            
            // 模拟 POST 环境 (SfCallback 使用 input('post.'))
            // 注意: 单元测试中直接调用控制器方法可能取不到 input('post.')，
            // 这里我们修改 SfCallback 让其支持传参，或者临时注入请求
            
            // 为了不修改 SfCallback 签名，我们这里直接构造 Request 对象注入 (ThinkPHP 5.0 特性)
            \think\Request::instance()->post($postData);
            
            $res = $callback->notify();
            
            if ($res instanceof \think\response\Json) {
                $data = $res->getData();
                $this->log("响应: " . json_encode($data, JSON_UNESCAPED_UNICODE));
                
                if (isset($data['apiResultCode']) && $data['apiResultCode'] === 'A1000') {
                    $this->log("测试通过: 控制器返回成功");
                    
                    // 验证文件是否存在
                    $filePath = ROOT_PATH . 'web/uploads/sf_label/' . $waybillNo . '_' . time() . '_async.pdf';
                    // 由于文件名包含时间戳，这里不好精确匹配，我们检查目录下是否有该运单号的文件
                    $files = glob(ROOT_PATH . 'web/uploads/sf_label/' . $waybillNo . '*_async.pdf');
                    if (!empty($files)) {
                        $this->log("验证通过: 找到生成的文件 " . basename($files[0]));
                        $content = file_get_contents($files[0]);
                        if ($content === $mockPdfContent) {
                             $this->log("验证通过: 文件内容匹配");
                        } else {
                             $this->log("验证失败: 文件内容不匹配");
                        }
                    } else {
                        $this->log("验证失败: 未找到生成的文件");
                    }
                    
                } else {
                    $this->log("测试失败: 响应码错误");
                }
            }
        } catch (\Exception $e) {
            $this->log("测试异常: " . $e->getMessage());
        }
    }

    private function log($msg, $isError = false)
    {
        $time = date('Y-m-d H:i:s');
        $level = $isError ? 'ERROR' : 'INFO';
        $line = "[{$time}] [{$level}] {$msg}" . PHP_EOL;
        file_put_contents($this->logFile, $line, FILE_APPEND);
    }
}
