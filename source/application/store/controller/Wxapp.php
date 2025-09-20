<?php

namespace app\store\controller;

use app\store\model\Wxapp as WxappModel;
use app\store\model\Setting as SettingModel;
use app\store\model\store\User;
use think\Cache;
use app\common\library\AITool\BaiduTextTran;
use app\store\model\WechatMenu as WechatMenuModel;
use app\store\model\WebMenu as WebMenuModel;
use app\store\model\WebLink as WebLinkModel;
/**
 * 小程序管理
 * Class Wxapp
 * @package app\store\controller
 */
class Wxapp extends Controller
{
    /**
     * 小程序设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function setting()
    {
        // 当前小程序信息
        $model = WxappModel::detail();
        if (!$this->request->isAjax()) {
            return $this->fetch('setting', compact('model'));
        }
        // 更新小程序设置
        if ($model->edit($this->postData('wxapp'))) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 公众号设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function wechat()
    {
        if (!$this->request->isAjax()) {
            $values = SettingModel::getItem('wechat');
            return $this->fetch('wechat', ['values' => $values]);
        }
        $model = new SettingModel;
        if ($model->edit('wechat', $this->postData('wechat'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }
    

    


    // web端设置    
    public function web(){
         // 当前小程序信息
        $model = WxappModel::detail();
        if (!$this->request->isAjax()) {
            return $this->fetch('web', compact('model'));
        }
        $data = $this->postData('h5');
        $model->url_code = $data['code'];
        if ($model->save()) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    
    /**
     * web端菜单
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function webmenu()
    {
        $model = new WebMenuModel;
        $list = $model->getTree($this->getWxappId());
        return $this->fetch('web_menu/index', [
            'list' => $list,
            'typeMap' => $model::getTypeOptions()
        ]);
    }
    
    /**
     * 友情链接
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function weblink()
    {
        $query = $this->request->param();
        $model = new WebLinkModel;
        $list = $model->getList($query);
        return $this->fetch('wxapp/web_link/index',compact('list'));
    }
    
    

    // 微信公众号端设置    
    // 菜单列表
    public function mp()
    {
        $mpmenus = WechatMenuModel::where('parent_id', 0)
            ->order('sort', 'asc')
            ->select()->toArray();
            
        foreach ($mpmenus as &$menu) {
            $menu['subMenus'] = WechatMenuModel::where('parent_id', $menu['id'])
                ->order('sort', 'asc')
                ->select()->toArray();
        }
        $typeMap = WechatMenuModel::getTypeMap();
        return $this->fetch('wechat_menu/index', compact('mpmenus', 'typeMap'));
    }
    
    public function h5(){
         // 当前小程序信息
        $model = WxappModel::detail();
        // dump($model->toArray());die;
        if (!$this->request->isAjax()) {
            return $this->fetch('h5', compact('model'));
        }
        $data = $this->postData('wxapp');
       
        if ($model->save($data)) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    //新增语言
    public function addlang(){
        $SettingModel = new SettingModel;
        $lang = $SettingModel::getItem("lang");
        $list = $lang['langlist'];
        if (!$this->request->isAjax()) {
            return $this->fetch('lang', compact('lang','list'));
        }
        $data = $this->postData('lang');
        foreach ($lang['langlist'] as $k =>$v){
            $datalang[$k] = $lang['langlist'][$k];
        }
        $datalang[$data['enname']]= json_encode($data);
        $datas['langlist'] = $datalang;
        $datas['default'] = $lang['default'];
        if ($SettingModel->edit("lang",$datas)) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($SettingModel->getError() ?: '更新失败');
    
    }
    
    //编辑语言
    public function  editlang(){
        $SettingModel = new SettingModel;
        $param = $this->request->param();
        $lang = $SettingModel::getItem("lang"); 
        $detail = $lang['langlist'][$param['name']]; 
        return $this->renderSuccess("获取成功",'',json_decode($detail,true));
    }
    
    //保存编辑语言
    public function  saveeditlang(){
        $SettingModel = new SettingModel;
        $param = $this->request->param();
 
        $lang = $SettingModel::getItem("lang");
        $data = $this->postData('lang');
        foreach ($lang['langlist'] as $k =>$v){
            $datalang[$k] = $lang['langlist'][$k];
        }
        $datalang[$param['lang']['enname']] = json_encode($param['lang']);
             
        $datas['langlist'] = $datalang;
        $datas['default'] = $lang['default'];
        if ($SettingModel->edit("lang",$datas)) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($SettingModel->getError() ?: '更新失败');
    }
    
    //删除语言
    public function  deletealang(){
        $SettingModel = new SettingModel;
        $param = $this->request->param();
 
        $lang = $SettingModel::getItem("lang");
        $data = $this->postData('lang');
        foreach ($lang['langlist'] as $k =>$v){
            $datalang[$k] = $lang['langlist'][$k];
        }
        unset($datalang[$param['name']]);
        //  dump($datalang);die;  
        $datas['langlist'] = $datalang;
        $datas['default'] = $lang['default'];
        if ($SettingModel->edit("lang",$datas)) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($SettingModel->getError() ?: '更新失败');
    }
    
    //多语言设置
    public function langdetail(){
        $SettingModel = new SettingModel;
        $wxappId = $this->getWxappId();
        $param = $this->request->param();
        $lang = $SettingModel::getItem("lang");
        
        $zhHans=['zhHans' =>'简体','zhHant' =>'繁体'];
        $i = 0;
        foreach($lang['langlist'] as $key=> $val){
            $val= json_decode($val,true);
                $languages[$key] = $val['name'];
                $i ++;
        }
        $languages = array_merge($zhHans,$languages);
    
        // $languages = ['zhHans' =>'简体','en' =>'英文','zhHant' =>'繁体','thai' =>'泰文','vietnam' =>'越南文'];
        $language = $languages[$param['lang']];
       
        $lang = getFileDataForLang('lang/'.$wxappId.'/new_'.$param['lang'].'.json');
        $zhHans = getFileDataForLang('lang/'.$wxappId.'/new_zhHans.json');
         
        
        if(count($zhHans)==0){
            $zhHans = getFileDataForLang('lang/10001/new_zhHans.json');
        }
        if(count($lang)==0){
            $lang = getFileDataForLang('lang/10001/new_zhHans.json');
        }
       
            // dump($lang);die;
        if (!$this->request->isAjax()) {
            return $this->fetch('langdetail', compact('lang','zhHans','language'));
        }
        $data = $this->postData('lang');
     
        $dir = 'lang/'.$wxappId.'/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true); // 创建目录，并设置权限
        }
        $res = file_put_contents('lang/'.$wxappId.'/'.$param['lang'].'.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        if ($res) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($SettingModel->getError() ?: '更新失败');
    }
    
    
    public function ailang(){
        $SettingModel = new SettingModel;
        $wxappId = $this->getWxappId();
        $param = $this->request->param();
        $lang = $SettingModel::getItem("lang");
        
        $zhHans=['zhHans' =>'简体','zhHant' =>'繁体'];
        $i = 0;
        foreach($lang['langlist'] as $key=> $val){
            $val= json_decode($val,true);
                $languages[$key] = $val['name'];
                $i ++;
        }
        $languages = array_merge($zhHans,$languages);
        // $languages = ['zhHans' =>'简体','en' =>'英文','zhHant' =>'繁体','thai' =>'泰文','vietnam' =>'越南文'];
        $language = $languages[$param['lang']];
       
        $lang = getFileDataForLang('lang/'.$wxappId.'/'.$param['lang'].'.json');
        $zhHans = getFileDataForLang('lang/'.$wxappId.'/zhHans.json');
       
        
        if(count($zhHans)==0){
            $zhHans = getFileDataForLang('lang/10001/zhHans.json');
        }
        if(count($lang)==0){
            $lang = getFileDataForLang('lang/10001/zhHans.json');
        }
        // 翻译的内容
        $setting = SettingModel::getItem('aiidentify',$this->getWxappId());
        if($setting['is_baiduaddress']==0){
            return $this->renderError("尚未开启智能AI识别功能，请更改API");
        }
        $BaiduTextTran = new BaiduTextTran($setting);
        $data = $lang;
        foreach ($lang as $key => $value){
            foreach ($lang[$key] as $k => $v){
               $data[$key][$k] = $BaiduTextTran->gettexttrans($v,$param['to'])['result']['trans_result'][0]['dst'];
            }
        }
        $dir = 'lang/'.$wxappId.'/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true); // 创建目录，并设置权限
        }
        $res = file_put_contents('lang/'.$wxappId.'/'.$param['lang'].'.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        return $this->renderSuccess('更新成功');
   
    }
    
    
    //多语言设置
    public function lang(){
        $SettingModel = new SettingModel;
        $lang = $SettingModel::getItem("lang");
        $list = [];
         
        foreach ($lang['langlist'] as $key=>$val){
                $list[] = json_decode($val,true);
        }
        // dump($list);die;
        if (!$this->request->isAjax()) {
            return $this->fetch('lang', compact('lang','list'));
        }
        $datalang = [];
        foreach ($lang['langlist'] as $k =>$v){
            $datalang[$k] = $lang['langlist'][$k];
        }
        $data = $this->postData('lang');
        $datas = [
            'langlist' => $datalang,
            'default' => $data['default']
        ];
        //   dump($datas);die;
        if ($SettingModel->edit("lang",$datas)) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($SettingModel->getError() ?: '更新失败');
        // dump($data);die;
    }

    
    public function code(){
        // 当前小程序信息
        $data = $this->request->param();
        $User = new User();
        if(empty($data['password'])){
            return $this->renderError('请输入密码');
        }
        $username = $this->store['user']['user_name'];
        $result = $User->useGlobalScope(false)->with(['wxapp'])->where([
            'user_name' => $username,
            'password' => yoshop_hash($data['password']),
            'is_delete' => 0
        ])->find();
            
        if (empty($result)) {
            return $this->renderError('更新失败,重置URL密码不正确');
        }
        $model = WxappModel::detail();
        $key = generate_password(22);
        $key = Cache::set($model['wxapp_id'].'_en_key',$key);
        $code = encrypt($model['wxapp_id']);
        Cache::set($code,$model['wxapp_id']);
        $model->url_code = $code;
        if ($model->save()) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
