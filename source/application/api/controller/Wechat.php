<?php
namespace app\api\controller;

use think\Request;
use app\common\model\User;
use app\api\model\User as UserModel;
use app\api\model\Wxapp as WxappModel;
use think\Log;
use app\common\library\wechat\WxUser;
use app\common\model\Setting;
use app\api\model\UserCoupon;

class Wechat
{
    /**
     * 微信公众平台回调入口
     * @return mixed
     */
    public function callback()
    {
        // 获取请求对象
        $request = Request::instance();
        // 获取微信应用ID
        $wxapp_id = $request->param('wxapp_id');
        if (empty($wxapp_id)) {
            return $this->responseError('参数错误');
        }
        
        // 获取微信应用配置
        $wxappDetail = WxappModel::detail($wxapp_id);
        if (empty($wxappDetail)) {
            return $this->responseError('应用配置不存在');
        }
        
        // 验证签名
        if (!$this->checkSignature($request, $wxappDetail['wechat_token'])) {
            return $this->responseError('签名验证失败', 403);
        }
        
        // 处理验证请求（首次配置时微信会发送echostr）
        if ($request->has('echostr')) {
            ob_clean();
            header('Content-Type: text/plain');
            echo $_GET['echostr'];
            exit;
        }
        
        // 处理普通消息（POST请求）
        if ($request->isPost()) {
            return $this->handleMessage($request, $wxappDetail);
        }
        
        return $this->responseError('非法请求');
    }
    
    /**
     * 签名验证
     * @param Request $request
     * @param string $token
     * @return bool
     */
    private function checkSignature(Request $request, $token)
    {
        $signature = $request->param('signature');
        $timestamp = $request->param('timestamp');
        $nonce = $request->param('nonce');
        
        if (empty($signature) || empty($timestamp) || empty($nonce)) {
            return false;
        }
        
        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = sha1(implode($tmpArr));
        
        return $tmpStr === $signature;
    }
    
    /**
     * 处理微信消息
     * @param Request $request
     * @param array $wxappConfig
     * @return mixed
     */
    private function handleMessage(Request $request, $wxappConfig)
    {
        $postData = file_get_contents('php://input');
        // 解析XML数据
        libxml_disable_entity_loader(true);
        $postObj = simplexml_load_string($postData, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        if ($postObj === false) {
            return $this->responseError('消息解析失败');
        }
        $wxapp_id = $request->param('wxapp_id');
        $msgType = trim($postObj->MsgType);
        $fromUser = trim($postObj->FromUserName);
        $toUser = trim($postObj->ToUserName);
        
        // 根据消息类型处理
        switch ($msgType) {
            case 'text':
                $content = trim($postObj->Content);
                return $this->responseText($fromUser, $toUser, "您发送了: {$content}");
                
            case 'event':
                $event = trim($postObj->Event);
                if ($event == 'subscribe') {
                    //根据公众号openid = $fromUser来获取uniacid，并根据是否已经有用户信息来决定是否注册用户;
                    $userResult = $this->getUserInfo($fromUser,$wxapp_id);
                    $storesetting = Setting::getItem('store',$wxapp_id);
                    $setting = Setting::getItem('wechat',$wxapp_id);
                    if($storesetting['usercode_mode']['is_show']==0){
                        $welcomeMessage = str_replace('{code}', "ID:".$userResult['user_id'], $setting['subscribe']);
                    }else{
                        $welcomeMessage = str_replace('{code}', "编号:".$userResult['user_code'], $setting['subscribe']);
                    }
                    
                    log_write($welcomeMessage);
                    return $this->responseText($fromUser, $toUser,$welcomeMessage);
                }
                if ($event == 'unsubscribe') {
                    // 如果用户取消关注，先查询用户信息，查到后设置用户已经取消关注
                    $this->checkUserunsubscribe($fromUser,$wxapp_id);
                    return $this->responseText($fromUser, $toUser, "感谢关注！");
                }
                break;
                
            default:
                return $this->responseText($fromUser, $toUser, "暂不支持此类型消息");
        }
        
        return $this->responseSuccess();
    }
    
    // 用户取消关注后的操作
    public function checkUserunsubscribe($openid,$wxapp_id)
    {
        $UserModel = new UserModel();
        $userResult = $UserModel->where(['gzh_openid'=>$openid,'is_delete'=>0])->find();
        if(!empty($userResult)){
            $userResult->save(['is_subscribe'=>0]);
        }
        return true;
    }
    
    
     /**
     * 通过 OpenID 获取用户信息（含 UnionID）
     */
    public function getUserInfo($openid,$wxapp_id)
    {
        $wxappDetail = WxappModel::detail($wxapp_id);
        $WxUser = new WxUser($wxappDetail['app_id'], $wxappDetail['app_secret'],$wxappDetail['app_wxappid'],$wxappDetail['app_wxsecret'],$wxappDetail['wx_type']);
        $userInfo = $WxUser->getUserInfo($openid);
        $UserModel = new UserModel();
        if(!empty($userInfo['openid'])){
           $userResult = $UserModel->where([
               'gzh_openid'=>$userInfo['openid'],
               'is_delete'=>0
           ])->find();
            $setting = Setting::getItem('store',$wxapp_id);
            $couponsetting = Setting::getItem('coupon',$wxapp_id);
           //如果找不到用户，就注册一下，否则就更新下unionid
           if(empty($userResult)){
                //如果公众号openid找不到具体用户，就用unionid再查找一次，如果还是没有，则新增用户，否则就更新
                $userResult2 = $UserModel->where(['union_id'=>$userInfo['unionid'],'is_delete'=>0])->find();
                if(empty($userResult2)){
                    $UserModel->allowField(true)->save([
                        'nickName'=>"",
                        'avatarUrl'=>"",
                        'union_id'=> !empty($userInfo['unionid'])?$userInfo['unionid']:'',
                        'gzh_openid'=>$userInfo['openid'],
                        'paytype'=> $setting['moren']['user_pack_in_pay'],
                        'last_login_time' =>date("Y-m-d H:i:s",time()),
                        'wxapp_id' => $wxapp_id,
                        'user_code' => $this->checkUserCode($setting),
                        'is_subscribe'=>1
                    ]);
                    //发送优惠券
                    if($couponsetting['is_register']==1){
                        (new UserCoupon())->newUserReceive($UserModel,$couponsetting['register_coupon']);
                    }
                }
                $userResult = $userResult2;
           }
           $userResult->save([
               'union_id'=>!empty($userInfo['unionid'])?$userInfo['unionid']:$userResult['union_id'],
               'is_subscribe'=>1,
               'gzh_openid'=>$userInfo['openid'],
           ]);
        }
       
        return $userResult; // 包含 unionid（如果存在）
    }
    
    private function checkUserCode($setting){
        switch ($setting['usercode_mode']['mode']) {
            case '10':
                //纯数字
                $num = $setting['usercode_mode'][10]['number'];
                $userCode = $this->createNum($num);
                break;
            case '20':
                //英文子母
                $num = $setting['usercode_mode'][20]['char'];
                $userCode = $this->createChar($num);
                break;
            case '30':
                //混合模式
                $zimu = $setting['usercode_mode'][30]['char'];
                $num = $setting['usercode_mode'][30]['number'];
                $userCode = $this->createCharNum($num,$zimu);
                break;
            default:
                $num = $setting['usercode_mode'][10]['number'];
                $userCode = $this->createNum($num);
                break;
        }
        return $userCode;
    }
    
    //生成随机英文+数字的编号
    public function createCharNum($num,$zimu){
        $x = pow(10,$num-1);
        $y = pow(10,$num)-1;
        $ucode =$zimu.rand($x,$y);
        $ucode = $this->checkOnlyOne($ucode,'createCharNum',$num);
        return $ucode;
    }
    
    //生成随机数
    public function createNum($num){
        $x = pow(10,$num-1);
        $y = pow(10,$num)-1;
        $ucode =rand($x,$y);
        $ucode = $this->checkOnlyOne($ucode,'createNum',$num);
        return $ucode;
    }
    
    //校验唯一
     public function checkOnlyOne($ucode,$funcitonname,$num){
         $user = UserModel::detail(['user_code' => $ucode]); 
         if($user){
             $ucode=  $this->$funcitonname($num);  
         }
         return $ucode;
     }
    /**
     * 回复文本消息
     * @param string $fromUser
     * @param string $toUser
     * @param string $content
     * @return \think\Response
     */
    private function responseText($fromUser, $toUser, $content)
    {
        // 使用DOMDocument确保XML格式绝对正确
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $xml = $dom->createElement('xml');
        
        // 添加子节点
        $elements = [
            'ToUserName' => $fromUser,
            'FromUserName' => $toUser,
            'CreateTime' => time(),
            'MsgType' => 'text',
            'Content' => $content
        ];
        
        foreach ($elements as $tag => $value) {
            $element = $dom->createElement($tag);
            $cdata = $dom->createCDATASection($value);
            $element->appendChild($cdata);
            $xml->appendChild($element);
        }
        
        $dom->appendChild($xml);
        $response = $dom->saveXML();
        
        // 直接输出避免框架干扰
        ob_clean();
        header('Content-Type: application/xml');
        echo $response;
        exit;
    }
    
    
    
    /**
     * 返回错误响应
     * @param string $message
     * @param int $code
     * @return \think\Response
     */
    private function responseError($message = 'error', $code = 400)
    {
        return response($message, $code);
    }
    
    /**
     * 返回成功响应
     * @return \think\Response
     */
    private function responseSuccess()
    {
        return response('success');
    }
}