<?php
namespace app\common\library\Jdl;

use think\Db;
use think\Log;

/**
 * 京东物流认证类
 * 处理OAuth2.0授权、Token管理
 * 
 * 注意：本类不自动获取Token，只提供Token验证和刷新功能
 * AccessToken需要通过OAuth2.0授权码模式获取并存储到数据库
 */
class JdlAuth
{
    /**
     * OAuth2.0授权地址
     */
    const OAUTH_AUTHORIZE_URL = 'https://open.jdl.com/oauth2/authorize';
    
    /**
     * OAuth2.0获取Token地址
     */
    const OAUTH_TOKEN_URL = 'https://open.jdl.com/oauth2/accessToken';
    
    /**
     * 从数据库读取AccessToken
     * @param int $ditchId 渠道ID
     * @return string|false
     */
    public static function getAccessTokenFromDb($ditchId)
    {
        try {
            $ditch = Db::name('ditch')->where('ditch_id', $ditchId)->find();
            if (!$ditch) {
                Log::error("京东物流渠道不存在: ditch_id={$ditchId}");
                return false;
            }
            
            $accessToken = isset($ditch['app_token']) ? $ditch['app_token'] : '';
            if (empty($accessToken)) {
                Log::error("京东物流AccessToken为空: ditch_id={$ditchId}");
                return false;
            }
            
            return $accessToken;
        } catch (\Exception $e) {
            Log::error("读取京东物流AccessToken失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 验证AccessToken是否有效
     * @param string $accessToken
     * @param string $appKey
     * @param string $appSecret
     * @return bool
     */
    public static function validateAccessToken($accessToken, $appKey, $appSecret)
    {
        // 简单验证：检查是否为空
        if (empty($accessToken)) {
            return false;
        }
        
        // TODO: 可以调用京东API验证Token有效性
        // 目前只做基本验证
        return true;
    }
    
    /**
     * 刷新AccessToken
     * @param string $refreshToken
     * @param string $appKey
     * @param string $appSecret
     * @return array|false ['access_token' => '', 'refresh_token' => '', 'expires_in' => 0]
     */
    public static function refreshAccessToken($refreshToken, $appKey, $appSecret)
    {
        try {
            $params = [
                'app_key' => $appKey,
                'app_secret' => $appSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ];
            
            $url = self::OAUTH_TOKEN_URL . '?' . http_build_query($params);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode != 200) {
                Log::error("刷新京东AccessToken失败: HTTP {$httpCode}");
                return false;
            }
            
            $result = json_decode($response, true);
            if (!$result || !isset($result['access_token'])) {
                Log::error("刷新京东AccessToken响应异常: " . $response);
                return false;
            }
            
            return [
                'access_token' => $result['access_token'],
                'refresh_token' => isset($result['refresh_token']) ? $result['refresh_token'] : $refreshToken,
                'expires_in' => isset($result['expires_in']) ? $result['expires_in'] : 2592000, // 默认30天
            ];
        } catch (\Exception $e) {
            Log::error("刷新京东AccessToken异常: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 更新数据库中的Token
     * @param int $ditchId 渠道ID
     * @param string $accessToken
     * @param string $refreshToken
     * @return bool
     */
    public static function updateTokenInDb($ditchId, $accessToken, $refreshToken = '')
    {
        try {
            $data = ['app_token' => $accessToken];
            if (!empty($refreshToken)) {
                $data['account_password'] = $refreshToken;
            }
            
            $result = Db::name('ditch')->where('ditch_id', $ditchId)->update($data);
            return $result !== false;
        } catch (\Exception $e) {
            Log::error("更新京东物流Token失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 生成OAuth2.0授权URL
     * @param string $appKey
     * @param string $redirectUri 回调地址
     * @param string $state 状态参数
     * @return string
     */
    public static function getAuthorizeUrl($appKey, $redirectUri, $state = '')
    {
        $params = [
            'app_key' => $appKey,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
        ];
        
        if (!empty($state)) {
            $params['state'] = $state;
        }
        
        return self::OAUTH_AUTHORIZE_URL . '?' . http_build_query($params);
    }
    
    /**
     * 通过授权码获取AccessToken
     * @param string $code 授权码
     * @param string $appKey
     * @param string $appSecret
     * @return array|false ['access_token' => '', 'refresh_token' => '', 'expires_in' => 0]
     */
    public static function getAccessTokenByCode($code, $appKey, $appSecret)
    {
        try {
            $params = [
                'app_key' => $appKey,
                'app_secret' => $appSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
            ];
            
            $url = self::OAUTH_TOKEN_URL . '?' . http_build_query($params);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode != 200) {
                Log::error("获取京东AccessToken失败: HTTP {$httpCode}");
                return false;
            }
            
            $result = json_decode($response, true);
            if (!$result || !isset($result['access_token'])) {
                Log::error("获取京东AccessToken响应异常: " . $response);
                return false;
            }
            
            return [
                'access_token' => $result['access_token'],
                'refresh_token' => isset($result['refresh_token']) ? $result['refresh_token'] : '',
                'expires_in' => isset($result['expires_in']) ? $result['expires_in'] : 2592000, // 默认30天
            ];
        } catch (\Exception $e) {
            Log::error("获取京东AccessToken异常: " . $e->getMessage());
            return false;
        }
    }
}
