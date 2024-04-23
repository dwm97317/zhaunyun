<?php

namespace app\common\library\printer\engine;

use app\common\library\printer\party\FeieHttpClient;

/**
 * 芯烨云API引擎
 * Class Xprinter
 * @package app\common\library\printer\engine
 */
class Xprinter extends Basics
{
    /** @const IP 接口IP或域名 */
    const API = 'https://open.xpyun.net/api/openapi/xprinter/printLabel';

    /**
     * 执行订单打印
     * @param $content
     * @return bool|mixed
     */
    public function printTicket($content)
    {
        // 构建请求参数
        $params = $this->getParams($content);
        // API请求：开始打印
        $header = [
            "Content-Type:application/json",
            "charset:UTF-8",
        ];
       
        $data = [
            'shipment' => $Verify
        ];
        // 参数设置
        $result = $this->post($this->API,$params,$header);
        log_write($result);
        // 返回状态
        if ($result->ret != 0) {
            $this->error = $result->msg;
            return false;
        }
        return true;
    }

    /**
     * 构建Api请求参数
     * @param $content
     * @return array
     */
    private function getParams(&$content)
    {
        $time = time();
        return [
            'user' => $this->config['USER'],
            'timestamp' => $time,
            'sign' => sha1("{$this->config['USER']}{$this->config['UserKEY']}{$time}"),
            'sn' => $this->config['SN'],
            'content' => $content,
            'copies' => $this->times   // 打印次数
        ];
    }

}