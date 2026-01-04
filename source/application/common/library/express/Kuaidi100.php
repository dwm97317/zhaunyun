<?php

namespace app\common\library\express;

use think\Cache;

/**
 * 快递100API模块
 * Class Kuaidi100
 * @package app\common\library\express
 */
class Kuaidi100
{
    /* @var array $config 快递100配置 */
    private $config;

    /* @var string $error 错误信息 */
    private $error;

    /**
     * 构造方法
     * @param $config
     */
    public function __construct($config)

    {
        $this->config = $config;
    }

    /**
     * 执行查询
     * @param $express_code
     * @param $express_no
     * @return bool
     */
    public function query($express_code, $express_no)
    {
        // 缓存索引
        $cacheIndex = 'express_' . $express_code . '_' . $express_no;
        if ($data = Cache::get($cacheIndex)) {
            return $data;
        }
        // 参数设置
        $postData = [
            'customer' => $this->config['customer'],
            'param' => json_encode([
                'resultv2' => '1',
                'com' => $express_code,
                'num' => $express_no
            ])
        ];
        $postData['sign'] = strtoupper(md5($postData['param'] . $this->config['key'] . $postData['customer']));
        // 请求快递100 api
        $url = 'http://poll.kuaidi100.com/poll/query.do';
        $result = curlPost($url, http_build_query($postData));
        $express = json_decode($result, true);
        // 记录错误信息
        // dump($express);die;
        if (isset($express['returnCode']) || !isset($express['data'])) {
            $this->error = isset($express['message']) ? $express['message'] : '查询失败';
            return false;
        }
        // 记录缓存, 时效5分钟
        Cache::set($cacheIndex, $express['data'], 300);
        return $express['data'];
    }

    /**
     * 智能识别快递公司（使用专门的智能识别接口）
     * @param $express_no 快递单号
     * @return array|false 返回可能的快递公司列表，格式：[['code' => 'sf', 'name' => '顺丰速运'], ...]
     */
    public function autoDetect($express_no)
    {
        // 参数验证
        if (empty($express_no)) {
            $this->error = '快递单号不能为空';
            return false;
        }
    
        // 缓存索引
        $cacheIndex = 'express_autodetect_' . md5($express_no);
        if ($data = Cache::get($cacheIndex)) {
            return $data;
        }
        
        // 请求智能识别接口
        $url = 'http://www.kuaidi100.com/autonumber/auto';
        $params = [
            'num' => $express_no
        ];
        // 如果有key配置，则传入key参数（部分接口可能需要）
        if (!empty($this->config['key'])) {
            $params['key'] = $this->config['key'];
        }
        $result = curl($url, $params);
        dump($result);die;
        // 解析返回结果
        $express = json_decode($result, true);
        
        // 检查识别结果
        if (!is_array($express) || empty($express)) {
            $this->error = '识别失败：无法识别该快递单号';
            return false;
        }
        
        // 检查是否有错误信息
        if (isset($express['returnCode']) && $express['returnCode'] != '200') {
            $this->error = isset($express['message']) ? $express['message'] : '识别失败';
            return false;
        }
        
        // 格式化返回结果
        $detectList = [];
        foreach ($express as $item) {
            // 处理不同的返回格式
            $code = isset($item['comCode']) ? $item['comCode'] : (isset($item['code']) ? $item['code'] : '');
            $name = isset($item['name']) ? $item['name'] : '';
            
            if (!empty($code)) {
                $detectList[] = [
                    'code' => $code,
                    'name' => $name
                ];
            }
        }
        
        // 如果没有识别结果
        if (empty($detectList)) {
            $this->error = '识别失败：未找到匹配的快递公司';
            return false;
        }
        
        // 记录缓存, 时效5分钟
        Cache::set($cacheIndex, $detectList, 300);
        return $detectList;
    }

    /**
     * 返回错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

}