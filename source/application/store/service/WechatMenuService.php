<?php
namespace app\store\service;

use app\store\model\WechatMenu;
use think\Config;
use app\common\library\wechat\WxUser;
use app\store\model\Wxapp as WxappModel;

class WechatMenuService
{
    // 创建微信菜单
    public function createWechatMenu($menuData,$wxapp_id)
    {
        $wxappDetail = WxappModel::detail($wxapp_id);
        $WxUser = new WxUser($wxappDetail['app_id'], $wxappDetail['app_secret'],$wxappDetail['app_wxappid'],$wxappDetail['app_wxsecret'],$wxappDetail['wx_type']);
        $accessToken = $WxUser->getAccessToken();
        if (!$accessToken) {
            return ['code' => 0, 'msg' => '获取AccessToken失败'];
        }
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$accessToken}";
  
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($menuData, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            if ($result['errcode'] == 0) {
                return ['code' => 1, 'msg' => '菜单创建成功'];
            }
            return ['code' => 0, 'msg' => '菜单创建失败: ' . $result['errmsg']];
        } catch (\Exception $e) {
            Log::error('创建微信菜单异常: ' . $e->getMessage());
            return ['code' => 0, 'msg' => '菜单创建异常'];
        }
    }
    
    /**
     * 获取素材列表
     */
    public function getArticleList($offset = 0, $count = 20,$wxapp_id)
    {
        $wxappDetail = WxappModel::detail($wxapp_id);
        $WxUser = new WxUser($wxappDetail['app_id'], $wxappDetail['app_secret'],$wxappDetail['app_wxappid'],$wxappDetail['app_wxsecret'],$wxappDetail['wx_type']);
        $accessToken = $WxUser->getAccessToken();
        if (!$accessToken) {
            return ['code' => 0, 'msg' => '获取AccessToken失败'];
        }
        $url = "https://api.weixin.qq.com/cgi-bin/freepublish/batchget?access_token={$accessToken}";
        
        $data = [
            'no_content' => 0,
            'offset' => $offset,
            'count' => $count
        ];
        $result = $this->httpRequest($url, json_encode($data));
        // dump($accessToken);die;
        return $result;
    }
    
    /**
     * 获取素材列表
     */
    public function getMaterialList($type, $offset = 0, $count = 20,$wxapp_id)
    {
        $wxappDetail = WxappModel::detail($wxapp_id);
        $WxUser = new WxUser($wxappDetail['app_id'], $wxappDetail['app_secret'],$wxappDetail['app_wxappid'],$wxappDetail['app_wxsecret'],$wxappDetail['wx_type']);
        $accessToken = $WxUser->getAccessToken();
        if (!$accessToken) {
            return ['code' => 0, 'msg' => '获取AccessToken失败'];
        }
        $url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token={$accessToken}";
        
        $data = [
            'type' => $type,
            'offset' => $offset,
            'count' => $count
        ];
        $result = $this->httpRequest($url, json_encode($data));
        return $result;
    }
    
    
    /**
     * HTTP请求
     */
    private function httpRequest($url, $data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        $output = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($output, true);
    }
    /**
     * 获取素材详情
     */
    public function getMaterial($mediaId,$wxapp_id)
    {
        $wxappDetail = WxappModel::detail($wxapp_id);
        $WxUser = new WxUser($wxappDetail['app_id'], $wxappDetail['app_secret'],$wxappDetail['app_wxappid'],$wxappDetail['app_wxsecret'],$wxappDetail['wx_type']);
        $accessToken = $WxUser->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/material/get_material?access_token={$accessToken}";
        
        $data = [
            'media_id' => $mediaId
        ];
        
        return $this->httpRequest($url, json_encode($data));
    }
    
    
    public function publishMenuFromDb($wxapp_id)
    {
        $menus = WechatMenu::where('parent_id', 0)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
            
        foreach ($menus as &$menu) {
            $subMenus = WechatMenu::where('parent_id', $menu['id'])
                ->order('sort', 'asc')
                ->select()
                ->toArray();
            
            if ($subMenus) {
                $menu['sub_button'] = $subMenus;
                // 确保父菜单没有type字段
                unset($menu['type']); 
            }
        }
        
        // 验证一级菜单数量
        if (count($menus) > 3) {
            return ['code' => 0, 'msg' => '一级菜单不能超过3个'];
        }
        
        $wechatFormat = WechatMenu::formatForWechat($menus);
        return $this->createWechatMenu($wechatFormat,$wxapp_id);
    }
}