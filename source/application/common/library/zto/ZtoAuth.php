<?php

namespace app\common\library\zto;

/**
 * 中通快递认证类
 * 负责生成签名和处理认证相关逻辑
 */
class ZtoAuth
{
    /**
     * 生成中通 API 签名
     * @param string $body JSON 请求体
     * @param string $appSecret 应用密钥
     * @return string Base64 编码的 MD5 签名
     */
    public static function generateDigest($body, $appSecret)
    {
        return base64_encode(md5($body . $appSecret, true));
    }

    /**
     * 构建请求头
     * @param string $appKey 应用 Key
     * @param string $digest 签名
     * @return array HTTP 请求头数组
     */
    public static function buildHeaders($appKey, $digest)
    {
        return [
            'Content-Type: application/json; charset=UTF-8',
            'x-appKey: ' . $appKey,
            'x-dataDigest: ' . $digest,
        ];
    }

    /**
     * 构建中通管家请求头（与标准中通相同）
     * @param string $appKey 应用 Key
     * @param string $digest 签名
     * @return array HTTP 请求头数组
     */
    public static function buildManagerHeaders($appKey, $digest)
    {
        return self::buildHeaders($appKey, $digest);
    }
}
