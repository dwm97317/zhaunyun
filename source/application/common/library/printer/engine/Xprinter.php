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
    private  $API = 'https://open-barcode.xpyun.net/api/openapi/sprinter/printLabel';
    /**
     * 执行订单打印
     * @param $content
     * @return bool|mixed
     */
    public function printTicket($content)
    {
        // 构建请求参数
        $params = json_encode($this->getParams($content));
        // API请求：开始打印
        // 参数设置
        $result = $this->http_post_json($this->API,$params);
        // dump($params);die;   
        log_write($result);
        $result = json_decode($result[1]);
        // 返回状态
        if ($result->code != 0) {
            $this->error = $result->msg;
            return false;
        }
        return true;
    }
    
    public function http_post_json($url, $jsonStr)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);// 发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_URL, $url);// 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json;charset=UTF-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);

        return array($httpCode, $response);
    }


    /**
     * 构建Api请求参数
     * @param $content
     * @return array
     */
    private function getParams(&$content)
    {
        $time = time();
        $sign = sha1($this->config['USER'].$this->config['UserKEY'].$time);
        return [
            'user' => $this->config['USER'],
            'timestamp' => $time,
            'sign' => $sign,
            'paperType'=>'M',
            'sn' => $this->config['SN'], //打印机编号
            'content' => $content,
            'paperWidth'=>800,
            'copies' => $this->times,   // 打印次数
            'direct'=> true
        ];
    }
    
}