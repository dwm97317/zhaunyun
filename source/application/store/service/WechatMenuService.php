<?php
namespace app\store\service;

use app\store\model\WechatMenu;
use think\Config;
use app\common\library\wechat\WxUser;
use app\store\model\Wxapp as WxappModel;

class WechatMenuService
{
    //设置所属行业
    public function setindustry($wxapp_id){
        $wxappDetail = WxappModel::detail($wxapp_id);
        $WxUser = new WxUser($wxappDetail['app_id'], $wxappDetail['app_secret'],$wxappDetail['app_wxappid'],$wxappDetail['app_wxsecret'],$wxappDetail['wx_type']);
        $accessToken = $WxUser->getAccessToken();
        if (!$accessToken) {
            return ['code' => 0, 'msg' => '获取AccessToken失败'];
        }
        $url = "https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token={$accessToken}";
        $data = [
            'industry_id1' => 15,
            'industry_id2' => 14,
        ];
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            if ($result['errcode'] == 0 && $result['errmsg'] =='ok') {
                return true;
            }
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => '拉取模板消息异常'];
        }
    }
    
    //拉取微信模板消息
    public function wechattemplate($param,$wxapp_id){
        $wxappDetail = WxappModel::detail($wxapp_id);
        $WxUser = new WxUser($wxappDetail['app_id'], $wxappDetail['app_secret'],$wxappDetail['app_wxappid'],$wxappDetail['app_wxsecret'],$wxappDetail['wx_type']);
        $accessToken = $WxUser->getAccessToken();
        if (!$accessToken) {
            return ['code' => 0, 'msg' => '获取AccessToken失败'];
        }
         $url = "https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token={$accessToken}";
         $template = [
            '50716'=> ["订单号","收件人","收件地址","下单时间"],   //订单号,收件人,收件地址,下单时间
            '46591'=> ["预约单号","姓名","联系电话","取件时间","取件地址"],  //预约单号,姓名,联系电话,取件时间,取件地址
            '55117'=> ["订单编号","提交人","提交时间"],  //订单编号,提交人,提交时间
            '43369'=> ["支付单号","充值金额","充值时间"],  //支付单号,充值金额,充值时间
            '45318'=> ["订单号","客户代号","重量","金额","时间"],  //订单号、客户代号、重量、金额、时间
            '44375'=> ["订单号","运单号","发货量","承运商","发货时间"],  //订单号、运单号、发货量、承运商、发货时间
            '48064'=> ["运单号","仓库","到仓时间"],  //运单号、仓库、到仓时间
            '42835'=> ["单号","货主名称","数量","库房名称","时间"],  //单号、货主名称、数量、库房名称、时间
            '50795'=> ["订单号","仓库名称","新包裹重量","新包裹体积"],  //订单号、仓库名称、新包裹重量、新包裹体积
            '47030'=> ["订单号","支付金额","订单件数","订单重量"],  //订单号、支付金额、订单件数、订单重量
            '47689'=> ["包裹单号","重量","仓库","包裹状态","出库时间"],  //包裹单号、重量、仓库、包裹状态、出库时间
            '45458'=> ["入库仓库","快递单号","入库时间","入库重量","物品"],  //入库仓库、快递单号、入库时间、入库重量、物品
            '55992'=> ["快递单号","客户昵称","客户ID","申请时间","重量"],  //入库仓库、快递单号、入库时间、入库重量、物品
         ];
         
         $data = [
            'template_id_short' => $param,
            'keyword_name_list' => $template[$param],
         ];
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            if ($result['errcode'] == 0 && $result['errmsg'] =='ok') {
                return $result['template_id'];
            }
        } catch (\Exception $e) {
            Log::error('拉取模板消息异常: ' . $e->getMessage());
            return ['code' => 0, 'msg' => '拉取模板消息异常'];
        }
    }
    
    
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