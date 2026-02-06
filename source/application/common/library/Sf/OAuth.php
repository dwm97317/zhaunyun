<?php
namespace app\common\library\Sf;

use think\Cache;
use think\Log;

/**
 * 顺丰 OAuth2 认证通用类
 * 提供 AccessToken 获取与缓存管理，可供多个业务模块调用
 */
class OAuth
{
    /**
     * 获取顺丰 OAuth2 Access Token
     * @param string $partnerId 合作伙伴编码 (Customer Code)
     * @param string $secret    校验码 (CheckWord / Secret)
     * @param bool   $isSandbox 是否为沙箱环境
     * @return string|false     成功返回 AccessToken，失败返回 false
     */
    public static function getAccessToken($partnerId, $secret, $isSandbox = false)
    {
        if (empty($partnerId) || empty($secret)) {
            Log::error('SF_OAuth: PartnerID or Secret is empty');
            return false;
        }

        // 1. 尝试从缓存获取
        // Token 有效期通常为 2 小时，我们缓存 1 小时 (3600秒) 以确保安全
        $cacheKey = 'sf_oauth2_token_' . $partnerId . '_' . ($isSandbox ? 'sbox' : 'prod');
        $token = Cache::get($cacheKey);
        
        if ($token) {
            return $token;
        }

        // 2. 构造请求地址和数据
        $authUrl = $isSandbox 
            ? 'https://sfapi-sbox.sf-express.com/oauth2/accessToken'
            : 'https://sfapi.sf-express.com/oauth2/accessToken';

        $postData = [
            'partnerID' => $partnerId,
            'secret'    => $secret,
            'grantType' => 'password'
        ];

        // 3. 发送请求
        $resp = self::httpPost($authUrl, http_build_query($postData));
        
        if ($resp === false) {
            Log::error('SF_OAuth: Network error when requesting token');
            return false;
        }

        $data = json_decode($resp, true);

        // 4. 验证结果
        if (is_array($data) && isset($data['apiResultCode']) && $data['apiResultCode'] === 'A1000' && isset($data['accessToken'])) {
            $accessToken = $data['accessToken'];
            Cache::set($cacheKey, $accessToken, 3600);
            return $accessToken;
        }

        // 记录错误
        $errorMsg = isset($data['apiErrorMsg']) ? $data['apiErrorMsg'] : 'Unknown Error';
        Log::error("SF_OAuth: Failed to get token. Msg: {$errorMsg} Resp: {$resp}");
        
        return false;
    }

    /**
     * 简单的 HTTP POST 辅助方法
     */
    private static function httpPost($url, $body)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8'
            ]
        ]);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error("SF_OAuth: Curl error: {$err}");
            return false;
        }

        return $result;
    }
}
