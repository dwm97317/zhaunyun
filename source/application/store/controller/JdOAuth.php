<?php

namespace app\store\controller;

use app\common\library\Jdl\JdlAuth;
use think\Cache;
use think\Db;

/**
 * 京东物流OAuth回调控制器
 */
class JdOAuth
{
    /**
     * OAuth授权回调
     * 接收京东返回的授权码，换取access_token
     */
    public function callback()
    {
        // 获取授权码
        $code = input('code', '');
        $state = input('state', '');
        
        if (empty($code)) {
            return json([
                'code' => 0,
                'msg' => '授权失败：未获取到授权码',
            ]);
        }
        
        // 从state中解析出ditch_id（渠道ID）
        // state格式: ditch_{ditch_id}
        $ditchId = 0;
        if (!empty($state) && strpos($state, 'ditch_') === 0) {
            $ditchId = intval(substr($state, 6));
        }
        
        if ($ditchId <= 0) {
            return json([
                'code' => 0,
                'msg' => '授权失败：无效的state参数',
            ]);
        }
        
        // 获取渠道配置
        $ditch = Db::name('ditch')->where('ditch_id', $ditchId)->find();
        if (!$ditch) {
            return json([
                'code' => 0,
                'msg' => '授权失败：渠道不存在',
            ]);
        }
        
        // 检查必要配置
        if (empty($ditch['app_key']) || empty($ditch['app_token'])) {
            return json([
                'code' => 0,
                'msg' => '授权失败：渠道配置不完整（缺少app_key或app_token）',
            ]);
        }
        
        // 判断是否沙箱环境
        $isSandbox = (strpos($ditch['api_url'], 'sbox') !== false);
        
        // 使用授权码换取token
        $result = JdlAuth::getAccessTokenByCode(
            $code,
            $ditch['app_key'],
            $ditch['app_token'],
            $isSandbox
        );
        
        if ($result === false) {
            return json([
                'code' => 0,
                'msg' => '授权失败：无法获取access_token',
            ]);
        }
        
        // 保存refresh_token到数据库（用于后续刷新）
        if (!empty($result['refresh_token'])) {
            // 将refresh_token保存到渠道配置的某个字段
            // 这里使用 account_password 字段临时存储（实际项目中应该新增专门的字段）
            Db::name('ditch')->where('ditch_id', $ditchId)->update([
                'account_password' => $result['refresh_token'],
                'update_time' => time(),
            ]);
        }
        
        // 返回成功信息
        return json([
            'code' => 1,
            'msg' => '授权成功',
            'data' => [
                'access_token' => substr($result['access_token'], 0, 20) . '...',
                'expires_in' => $result['expires_in'],
                'access_expire' => isset($result['access_expire']) ? $result['access_expire'] : '',
                'refresh_expire' => isset($result['refresh_expire']) ? $result['refresh_expire'] : '',
            ],
        ]);
    }
    
    /**
     * 生成授权URL
     * 用于前端跳转到京东授权页面
     */
    public function getAuthorizeUrl()
    {
        $ditchId = input('ditch_id', 0);
        
        if ($ditchId <= 0) {
            return json([
                'code' => 0,
                'msg' => '参数错误：缺少ditch_id',
            ]);
        }
        
        // 获取渠道配置
        $ditch = Db::name('ditch')->where('ditch_id', $ditchId)->find();
        if (!$ditch) {
            return json([
                'code' => 0,
                'msg' => '渠道不存在',
            ]);
        }
        
        // 检查必要配置
        if (empty($ditch['app_key'])) {
            return json([
                'code' => 0,
                'msg' => '渠道配置不完整（缺少app_key）',
            ]);
        }
        
        // 判断是否沙箱环境
        $isSandbox = (strpos($ditch['api_url'], 'sbox') !== false);
        
        // 构建回调地址
        // ThinkPHP路由规则：驼峰命名的控制器会转换为小写+下划线
        $redirectUri = request()->domain() . '/store/jd_o_auth/callback';
        
        // 生成state参数（包含渠道ID）
        $state = 'ditch_' . $ditchId;
        
        // 生成授权URL
        $authorizeUrl = JdlAuth::getAuthorizeUrl(
            $ditch['app_key'],
            $redirectUri,
            $state,
            $isSandbox
        );
        
        return json([
            'code' => 1,
            'msg' => '成功',
            'data' => [
                'authorize_url' => $authorizeUrl,
            ],
        ]);
    }
    
    /**
     * 刷新AccessToken
     * 使用refresh_token刷新access_token
     */
    public function refreshToken()
    {
        $ditchId = input('ditch_id', 0);
        
        if ($ditchId <= 0) {
            return json([
                'code' => 0,
                'msg' => '参数错误：缺少ditch_id',
            ]);
        }
        
        // 获取渠道配置
        $ditch = Db::name('ditch')->where('ditch_id', $ditchId)->find();
        if (!$ditch) {
            return json([
                'code' => 0,
                'msg' => '渠道不存在',
            ]);
        }
        
        // 检查必要配置
        if (empty($ditch['app_key']) || empty($ditch['app_token'])) {
            return json([
                'code' => 0,
                'msg' => '渠道配置不完整（缺少app_key或app_token）',
            ]);
        }
        
        // 获取refresh_token（从 account_password 字段读取）
        $refreshToken = isset($ditch['account_password']) ? $ditch['account_password'] : '';
        if (empty($refreshToken)) {
            return json([
                'code' => 0,
                'msg' => '未找到refresh_token，请先完成授权',
            ]);
        }
        
        // 判断是否沙箱环境
        $isSandbox = (strpos($ditch['api_url'], 'sbox') !== false);
        
        // 刷新token
        $result = JdlAuth::refreshAccessTokenByRefreshToken(
            $ditch['app_key'],
            $ditch['app_token'],
            $refreshToken,
            $isSandbox
        );
        
        if ($result === false) {
            return json([
                'code' => 0,
                'msg' => 'Token刷新失败',
            ]);
        }
        
        // 更新refresh_token（如果返回了新的）
        if (!empty($result['refresh_token'])) {
            Db::name('ditch')->where('ditch_id', $ditchId)->update([
                'account_password' => $result['refresh_token'],
                'update_time' => time(),
            ]);
        }
        
        return json([
            'code' => 1,
            'msg' => 'Token刷新成功',
            'data' => [
                'access_token' => substr($result['access_token'], 0, 20) . '...',
                'expires_in' => $result['expires_in'],
                'access_expire' => isset($result['access_expire']) ? $result['access_expire'] : '',
                'refresh_expire' => isset($result['refresh_expire']) ? $result['refresh_expire'] : '',
            ],
        ]);
    }
}
