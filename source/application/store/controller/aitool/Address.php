<?php
namespace app\store\controller\aitool;

use app\store\controller\Controller;
use app\common\library\AITool\BaiduAddress;
use app\store\model\Setting as SettingModel;
/**
 * 批次管理
 * Class Forwarder
 * @package app\store\controller
 */
class Address extends Controller
{
    
     /**
     * 地址解析
     * @param string $nickName 昵称
     * @param int $gender 性别
     * @param int $grade 会员等级
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function addressAi()
    {
        $param = $this->request->param();
        $setting = SettingModel::getItem('aiidentify',$this->getWxappId());
        if($setting['is_baiduaddress']==0){
            return $this->renderError("尚未开启智能AI识别功能，请更改API");
        }
        $BaiduAddress = new BaiduAddress($setting);
        $result = $BaiduAddress->getaddress($param['text']);
        return  $this->renderSuccess('操作成功','',$result);
    }
    
    
}