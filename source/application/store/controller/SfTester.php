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

    private function log($msg, $isError = false)
    {
        $time = date('Y-m-d H:i:s');
        $level = $isError ? 'ERROR' : 'INFO';
        $line = "[{$time}] [{$level}] {$msg}" . PHP_EOL;
        file_put_contents($this->logFile, $line, FILE_APPEND);
    }
}
