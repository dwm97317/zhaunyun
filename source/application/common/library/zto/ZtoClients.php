<?php

namespace app\common\library\zto;

/**
 * 中通快递 HTTP 客户端
 * 负责处理 HTTP 请求和响应
 */
class ZtoClients
{
    /** @var string 错误信息 */
    private $error;

    /**
     * 发送 POST 请求
     * @param string $url 请求 URL
     * @param string $body 请求体（JSON）
     * @param array $headers 请求头
     * @return string|false 响应内容或 false
     */
    public function post($url, $body, array $headers = [])
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (ZTO_SDK)',
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_HTTPHEADER     => $headers,
        ]);
        
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        
        if ($err) {
            $this->error = $err;
            return false;
        }
        
        return $result;
    }

    /**
     * 解析 API 响应
     * @param string $response 响应内容
     * @return array|false 解析后的数组或 false
     */
    public function parseResponse($response)
    {
        if ($response === false) {
            return false;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            $this->error = '响应解析失败';
            return false;
        }

        return $data;
    }

    /**
     * 检查响应是否成功
     * @param array $data 响应数据
     * @return bool
     */
    public function isSuccess(array $data)
    {
        return !empty($data['status']) || isset($data['status']) && $data['status'] === true;
    }

    /**
     * 获取响应消息
     * @param array $data 响应数据
     * @return string
     */
    public function getMessage(array $data)
    {
        return isset($data['message']) ? $data['message'] : (isset($data['msg']) ? $data['msg'] : '');
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 设置错误信息
     * @param string $error 错误信息
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * 构建标准响应格式
     * @param bool $success 是否成功
     * @param string $trackingNumber 运单号
     * @param string $message 消息
     * @param string $orderId 订单ID
     * @return array
     */
    public static function buildResponse($success, $trackingNumber = '', $message = '', $orderId = '')
    {
        return [
            'ack'             => $success ? 'true' : 'false',
            'tracking_number' => (string) $trackingNumber,
            'message'         => $message !== '' ? $message : ($success ? 'ok' : '操作失败'),
            'order_id'        => (string) $orderId,
        ];
    }
}
