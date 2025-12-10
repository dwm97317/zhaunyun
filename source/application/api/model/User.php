<?php

namespace app\api\model;

use think\Cache;
use app\common\library\wechat\WxUser;
use app\common\exception\BaseException;
use app\api\service\user\Oauth as OauthService;
use app\common\model\User as UserModel;
use app\api\model\dealer\Referee as RefereeModel;
use app\api\model\dealer\Setting as DealerSettingModel;
use app\common\model\Setting;
use app\api\model\user\Grade;
use app\api\model\store\shop\Clerk;
use app\api\model\UserCoupon;
use think\Db;
/**
 * 用户模型类
 * Class User
 * @package app\api\model
 */
class User extends UserModel
{
    private $token;

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'password',
        'is_delete',
        'wxapp_id',
        'create_time',
        'update_time'
    ];
    
    public function getWxappid(){
         $wxapp_id = self::$wxapp_id;
         return $wxapp_id;
    }
    
    public function getList($query){
        return $this->where('nickName|user_id|user_code','like','%'.$query['keyword'].'%')
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 获取用户信息
     * @param $token
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function getUser($token)
    {
        $openId = Cache::get($token)['openid'];
        return self::detail(['open_id' => $openId], ['address', 'addressDefault', 'grade','userimage','service']);
    }
    
    //仓管app员工登录
    public function loginClerk($data){
        $Clerk = new Clerk;
        $where['mobile'] = $data['mobile'];
        $where['password'] = $data['password'];
        $clerkData = $Clerk->with(['user','storage'])->where($where)->find();
        if(empty($clerkData)){
            throw new BaseException(['msg' => "账号或密码不正确"]);
        }
        $this->token = $this->token($clerkData['user']['open_id']);
        return $clerkData['user']['user_id']; 
    }

    /**
     * 用户登录
     * @param array $post
     * @return string
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function login($post)
    {
        // 微信登录 获取session_key
        $session = $this->wxlogin($post['code']);
        // 自动注册用户
        $refereeId = isset($post['referee_id']) ? $post['referee_id'] : null;
        // $userInfo = $post['user_info'];
        // $user_id = $this->register($session, $userInfo, $refereeId);
        $user_id = $this->register($session, $refereeId);
        // 生成token (session3rd)
        $this->token = $this->token($session['openid']);
        // 记录缓存, 7天
        Cache::set($this->token, $session, 86400 * 7);
        return $user_id;
    }
    
    public function loginphoneTogetOpenid($user,$post)
    {
        // 微信登录 获取session_key
        $session = $this->wxlogin($post['code']);
        $user->save([
            'open_id'=>$session['openid'],
            'union_id'=>isset($session['unionid'])?$session['unionid']:$user['union_id'],
        ]);
        return true;
    }
    
    public function loginwxTogetOpenid($post)
    {
        // 微信登录 获取session_key
        $session = $this->wxApplogin($post['code']);
        $user = self::detail(['union_id' => $session['unionid']]);
        // dump();die;
        $user->save(['gzh_openid'=>$session['openid']]);
        return true;
    }
    
    public function loginwx($post)
    {
        // 微信登录 获取session_key
        $session = $this->wxApplogin($post['code']);
        // dump($session);die;
        // 自动注册用户
        $refereeId = isset($post['referee_id']) ? $post['referee_id'] : null;
        // $userInfo = $post['user_info'];
        $userInfo = OauthService::sessionGetUserInfo($session['access_token'],$session['openid']);
                        // dump($userInfo);die;   
        $user = $this->registerwx($session, $userInfo, $refereeId);

        // 生成token (session3rd)
        $this->token = $this->token($user['open_id']);
        // 记录缓存, 7天
        $session['openid'] = $user['open_id'];
        Cache::set($this->token, $session, 86400 * 7);
        return $user['user_id'];
    }
    
    public function loginwxopen($post)
    {
        // 微信登录 获取session_key
        $session = $this->wxApploginopenid($post['code']);
        // dump($session);die;
        // 自动注册用户
        $refereeId = isset($post['referee_id']) ? $post['referee_id'] : null;
        // $userInfo = $post['user_info'];
        $userInfo = OauthService::sessionGetUserInfo($session['access_token'],$session['openid']);
                        // dump($userInfo);die;   
        $user = $this->registerwx($session, $userInfo, $refereeId);

        // 生成token (session3rd)
        $this->token = $this->token($user['open_id']);
        // 记录缓存, 7天
        $session['openid'] = $user['open_id'];
        Cache::set($this->token, $session, 86400 * 7);
        return $user['user_id'];
    }
    
    
    
    public function wxApplogin($code){
       // 获取当前小程序信息
        $wxConfig = Wxapp::getWxappCache();
        // 验证appid和appsecret是否填写
        if (empty($wxConfig['app_wxappid']) || empty($wxConfig['app_wxsecret'])) {
            throw new BaseException(['msg' => '请到 [后台-小程序设置] 填写app_wxappid 和 app_wxsecret']);
        }
        // 微信登录 (获取session_key)
        $WxUser = new WxUser();
        if (!$session = $WxUser->sessionWxKey($code,$wxConfig['app_wxappid'],$wxConfig['app_wxsecret'])) {
            throw new BaseException(['msg' => $WxUser->getError()]);
        }
        // dump($session);die;
        return $session; 
    }
    
    public function wxApploginopenid($code){
       // 获取当前小程序信息
        $wxConfig = Wxapp::getWxappCache();
        // 验证appid和appsecret是否填写
        if (empty($wxConfig['app_wxoepnid']) || empty($wxConfig['app_wxopensecret'])) {
            throw new BaseException(['msg' => '请到 [后台-小程序设置] 填写app_wxoepnid 和 app_wxopensecret']);
        }
        // 微信登录 (获取session_key)
        $WxUser = new WxUser();
        if (!$session = $WxUser->sessionWxKey($code,$wxConfig['app_wxoepnid'],$wxConfig['app_wxopensecret'])) {
            throw new BaseException(['msg' => $WxUser->getError()]);
        }
        // dump($session);die;
        return $session; 
    }

    /**
     * 获取token
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * 微信登录
     * @param $code
     * @return array|mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function wxlogin($code)
    {
        // 获取当前小程序信息
        $wxConfig = Wxapp::getWxappCache();
        // 验证appid和appsecret是否填写
        if (empty($wxConfig['app_id']) || empty($wxConfig['app_secret'])) {
            throw new BaseException(['msg' => '请到 [后台-小程序设置] 填写appid 和 appsecret']);
        }
        // 微信登录 (获取session_key)
        $WxUser = new WxUser($wxConfig['app_id'], $wxConfig['app_secret']);

        if (!$session = $WxUser->sessionKey($code)) {
            throw new BaseException(['msg' => $WxUser->getError()]);
        }
        return $session;
    }

    /**
     * 生成用户认证的token
     * @param $openid
     * @return string
     */
    private function token($openid)
    {
        $wxapp_id = self::$wxapp_id;
        // 生成一个不会重复的随机字符串
        $guid = \getGuidV4();
        // 当前时间戳 (精确到毫秒)
        $timeStamp = microtime(true);
        // 自定义一个盐
        $salt = 'token_salt';
        return md5("{$wxapp_id}_{$timeStamp}_{$openid}_{$guid}_{$salt}");
    }
    
     /**
     * 自动注册用户
     * @param $open_id
     * @param $data
     * @param int $refereeId
     * @return mixed
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    private function registerwx($session, $data, $refereeId = null)
    {
        // 查询用户是否已存在
        $userclient = Setting::getItem('userclient',self::$wxapp_id);
        //有微信开放平台，并且需要合并时
        if($userclient['loginsetting']['is_wxopen']==1 && $userclient['loginsetting']['is_merge_user']==1){
            $user1 = self::detail(['union_id' => $session['unionid']]);
            $user2 = self::detail(['gzh_openid' => $session['openid']]);
            $user3 = self::detail(['open_id' => $session['openid']]);
            $user = $user2;
            !empty($user1) && $user = $user1;
            !empty($user2) && $user = $user2;
            !empty($user3) && $user = $user3;
        }else{
            $user = self::detail(['open_id' => $session['openid']]);
        }
        // dump($session);die;
        $setting = Setting::getItem('store',self::$wxapp_id);
        $couponsetting = Setting::getItem('coupon',self::$wxapp_id);
        $user_code= $user['user_code'];
        if($setting['usercode_mode']['is_show']==1 || $setting['usercode_mode']['is_show']==2){
            empty($user['user_code']) && $user_code = $this->checkUserCode($user,$setting); 
        }
        $model = $user ?: $this;
        $this->startTrans();
        try {
            // 保存/更新用户记录
            if (!$model->allowField(true)->save(array_merge($data, [
                'gzh_openid' => $session['openid'],
                'open_id'=> $user['open_id']??$session['openid'],
                'union_id'=> empty($user['union_id'])?(isset($session['unionid'])?$session['unionid']:''):$user['union_id'],
                'last_login_time' =>date("Y-m-d H:i:s",time()),
                'wxapp_id' => self::$wxapp_id,
                'paytype'=> $user['paytype']??$setting['moren']['user_pack_in_pay'],
                'nickName' => empty($user['nickName'])?$data['nickname']:$user['nickName'],
                'avatarUrl' => $data['headimgurl'],
                'user_code' => !empty($user['user_code'])?$user['user_code']:$user_code,
            ]))) {
                throw new BaseException(['msg' => '用户注册失败']);
            }
            // 记录推荐人关系
            if (!$user && $refereeId > 0) {
                RefereeModel::createRelation($model['user_id'], $refereeId);
            }
            //发送优惠券
            if($couponsetting['is_register']==1 && (time() - strtotime($model['create_time']) < 86400)){
                (new UserCoupon())->newUserReceive($model,$couponsetting['register_coupon']);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new BaseException(['msg' => $e->getMessage()]);
        }
        return ['open_id'=>$model['open_id'],'user_id'=>$model['user_id']];
    }

    /**
     * 自动注册用户
     * @param $open_id
     * @param $data
     * @param int $refereeId
     * @return mixed
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    private function register($session, $refereeId = null)
    {
        // 查询用户是否已存在
        // dump($session);die;
        $userclient = Setting::getItem('userclient',self::$wxapp_id);
        
        if($userclient['loginsetting']['is_wxopen']==1 && $userclient['loginsetting']['is_merge_user']==1){
            $user1 = self::detail(['union_id' => $session['unionid']]);
            $user2 = self::detail(['open_id' => $session['openid']]);
            $user = $user1;
            !empty($user2) && $user = $user2;
        }else{
            $user = self::detail(['open_id' => $session['openid']]);
        }
       
        $user_code = '';
        $data = [];
        if(!empty($user)){
            $user_code = $user['user_code'];
        }else{
            $data['nickName'] = "";
            $data['avatarUrl'] = "";
        }
        //根据设置来生成user_code
        $setting = Setting::getItem('store',self::$wxapp_id);
        if($setting['usercode_mode']['is_show']==1 || $setting['usercode_mode']['is_show']==2){
            empty($user['user_code']) && $user_code = $this->checkUserCode($user,$setting); 
        }
        
        $couponsetting = Setting::getItem('coupon',self::$wxapp_id);
        
        $model = $user ?: $this;
        $this->startTrans();
        try {
            // 保存/更新用户记录 
            if (!$model->allowField(true)->save(array_merge($data, [
                'open_id' => $session['openid'],
                'union_id'=> isset($session['unionid'])?$session['unionid']:'',
                'paytype'=> $user['paytype']??$setting['moren']['user_pack_in_pay'],
                'last_login_time' =>date("Y-m-d H:i:s",time()),
                'wxapp_id' => self::$wxapp_id,
                'user_code' => !empty($user['user_code'])?$user['user_code']:$user_code,
            ]))) {
                throw new BaseException(['msg' => '用户注册失败']);
            }
            // 记录推荐人关系
            if (!$user && $refereeId > 0) {
                RefereeModel::createRelation($model['user_id'], $refereeId);
            }
            //发送优惠券
            if($couponsetting['is_register']==1 && (time() - strtotime($model['create_time']) < 86400)){
                (new UserCoupon())->newUserReceive($model,$couponsetting['register_coupon']);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new BaseException(['msg' => $e->getMessage()]);
        }
        return $model['user_id'];
    }
    
    public function checkUserCode($user,$setting){
        if($user['user_code']){
            return $user['user_code'];
        }else{
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
                case '40':
                    //顺序模式
                    $zimu = $setting['usercode_mode'][40]['char'];
                    $num = $setting['usercode_mode'][40]['number'];
                    $userCode = $this->createCharShunxuNum($num,$zimu);
                    break;
                default:
                    $num = $setting['usercode_mode'][10]['number'];
                    $userCode = $this->createNum($num);
                    break;
            }
            return $userCode;
        }
    }
    
    
    public function generateUserNo($num,$zimu)
    {
        try {
            // 使用事务确保并发安全
            Db::startTrans();
            
            // 锁表查询，防止并发问题
            // 找到最新的用户code：按user_code的数值部分降序排列
            $zimuLength = strlen($zimu);
            // 直接按数值部分排序，找到最大的编号（like已经能过滤出以字母开头的记录）
            $lastUserNo = self::where('user_code', 'like', $zimu.'%')
                ->lock(true)
                ->orderRaw('CAST(SUBSTRING(user_code, ' . ($zimuLength + 1) . ') AS UNSIGNED) DESC')
                ->value('user_code');
            
            if (empty($lastUserNo)) {
                $newUserNo = $this->createCharNum($num,$zimu);
            } else {
                // 提取数字部分（去掉前面的字母）
                $lastNumber = intval(substr($lastUserNo, $zimuLength));
                $newNumber = $lastNumber + 1;
                $newUserNo = $zimu . str_pad($newNumber, $num, '0', STR_PAD_LEFT);
            }
            
            Db::commit();
            return $newUserNo;
            
        } catch (\Exception $e) {
            Db::rollback();
            throw new \Exception("生成用户编号失败: " . $e->getMessage());
        }
    }
    
    //生成随机数
    public function createNum($num){
        $x = pow(10,$num-1);
        $y = pow(10,$num)-1;
        $ucode =rand($x,$y);
        $ucode = $this->checkOnlyOne($ucode,'createNum',$num);
        return $ucode;
    }
    
    //生成随机英文编号
    public function createChar($num){
        //用字符数组的方式随机  
        $randomcode2 = ""; 
        $char = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";  
        $m = explode(',',$char);
        for ($j=0;$j<$num ;$j++ )  
        {  
           $c = $m[rand(0,25)];  
            $randomcode2 = $randomcode2.$c;  
        }
        $ucode = $this->checkOnlyOne($randomcode2,'createChar',$num);
        return $ucode;
    }
    
    //生成随机英文+数字的编号
    public function createCharNum($num,$zimu){
        $x = pow(10,$num-1);
        $y = pow(10,$num)-1;
        $ucode =$zimu.rand($x,$y);
        $ucode = $this->checkOnlyOne($ucode,'createCharNum',$num,$zimu);
        return $ucode;
    }
    
    //顺序生成
    public function createCharShunxuNum($num,$zimu){
        $ucode = $this->generateUserNo($num,$zimu);
        $ucode = $this->checkOnlyOne($ucode,'createCharShunxuNum',$num,$zimu);
        return $ucode;
    }
    
    //校验唯一
     public function checkOnlyOne($ucode,$functionname,$num,$zimu = null){
        $maxAttempts = 100; // 防止无限循环
        $attempts = 0;
        
        while($attempts < $maxAttempts){
            $user = self::detail(['user_code' => $ucode]); 
            if(!$user){
                // 用户不存在，ucode唯一，返回
                return $ucode;
            }
            
            // 用户已存在，生成新的ucode
            $attempts++;
            if($zimu !== null){
                // 需要zimu参数的函数（如createCharNum, generateUserNo）
                $ucode = $this->$functionname($num, $zimu);
            } else {
                // 只需要num参数的函数（如createNum, createChar）
                $ucode = $this->$functionname($num);
            }
        }
        
        // 如果尝试100次还是重复，抛出异常
        throw new \Exception("生成唯一用户编号失败，已尝试{$maxAttempts}次");
    }

    
    // 累积收益
    public function setIncome($userId,$money){
        $model = $this->find($userId);
        return $model->setInc('income',$money);         
    }
    
    /**
     * 资金冻结
     * @param $money
     * @return false|int
     */
    public function freezeIncome($money)
    {
        return $this->save([
            'income' => $this['income'] - $money,
            'freeze_income' => $this['freeze_income'] + $money,
        ]);
    }
    
    /**
     * 显示会员到期时间
     * @param $value
     * @return mixed
     */
    public function getGradeTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }
   
    /**
     * 个人中心菜单列表
     * @return array
     */
    public function getMenus()
    {
        $menus = [
            'address' => [
                'name' => '收货地址',
                'url' => 'pages/address/index',
                'icon' => 'map'
            ],
            'coupon' => [
                'name' => '领券中心',
                'url' => 'pages/coupon/coupon',
                'icon' => 'lingquan'
            ],
            'my_coupon' => [
                'name' => '我的优惠券',
                'url' => 'pages/user/coupon/coupon',
                'icon' => 'youhuiquan'
            ],
            'sharing_order' => [
                'name' => '拼团订单',
                'url' => 'pages/sharing/order/index',
                'icon' => 'pintuan'
            ],
            'my_bargain' => [
                'name' => '我的砍价',
                'url' => 'pages/bargain/index/index?tab=1',
                'icon' => 'kanjia'
            ],
            'dealer' => [
                'name' => '分销中心',
                'url' => 'pages/dealer/index/index',
                'icon' => 'fenxiaozhongxin'
            ],
            'help' => [
                'name' => '我的帮助',
                'url' => 'pages/user/help/index',
                'icon' => 'help'
            ],
        ];
        // 判断分销功能是否开启
        if (DealerSettingModel::isOpen()) {
            $menus['dealer']['name'] = DealerSettingModel::getDealerTitle();
        } else {
            unset($menus['dealer']);
        }
        return $menus;
    }

}
