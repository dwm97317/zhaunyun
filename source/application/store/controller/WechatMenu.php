<?php
namespace app\store\controller;

use app\store\model\WechatMenu as WechatMenuModel;
use think\Session;
use app\store\service\WechatMenuService;
use think\View;

/**
 * 微信公众号自定义菜单
 * Class WechatMenu
 * @package app\store\controller
 */
class WechatMenu extends Controller
{
    protected $menuService;
    
    public function __construct()
    {
        parent::__construct();
        $this->menuService = new WechatMenuService();
    }
    
    /**
     * 获取微信消息模板
     */
     public function api_add_template(){
         $param = $this->request->param();
         $result = $this->menuService->wechattemplate($param['id'],$this->getWxappId()); 
         return $this->renderSuccess('获取成功','',$result);
     }
    
    /**
     * 设置所属行业
     */
     public function api_set_industry(){
         $result = $this->menuService->setindustry($this->getWxappId()); 
         if($result==true){
             return $this->renderSuccess('设置成功');
         }
         return $this->renderError('设置失败');
     }
    
    /**
     * 获取微信素材列表
     */
    public function wechat_material()
    {
        try {
            // 获取各种类型素材
            $news = $this->menuService->getArticleList(0, 20,$this->getWxappId())['item'] ?? [];
            $image = $this->menuService->getMaterialList('image', 0, 20,$this->getWxappId())['item'] ?? [];
            $voice = $this->menuService->getMaterialList('voice', 0, 20,$this->getWxappId())['item'] ?? [];
            $video = $this->menuService->getMaterialList('video', 0, 20,$this->getWxappId())['item'] ?? [];
            
            return json([
                'code' => 1,
                'data' => [
                    'news' => $news,
                    'image' => $image,
                    'voice' => $voice,
                    'video' => $video
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 0,
                'msg' => '获取素材失败: ' . $e->getMessage(),
                'data' => [
                    'news' => [],
                    'image' => [],
                    'voice' => [],
                    'video' => []
                ]
            ]);
        }
    }
    
    /**
     * 获取素材详情
     */
    public function material_detail($type = 'news')
    {
        $mediaId = $this->request->param()['media_id'];

        try {
            $material = $this->menuService->getMaterial($mediaId,$this->getWxappId());
            
            // 根据不同类型处理返回数据
            if ($type == 'news') {
                // 图文素材
                return json(['code' => 1, 'data' => $material['news_item'] ?? []]);
            } else {
                // 其他素材（图片、语音、视频等）
                return json(['code' => 1, 'data' => $material]);
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '获取素材详情失败: ' . $e->getMessage()]);
        }
    }

    
    // 添加/编辑菜单
    public function edit()
    {
        $id = $this->request->param()['id'];
        $menu = WechatMenuModel::find($id);
        return $this->renderSuccess('更新成功','',$menu);
    }
    
    // 保存菜单
    public function save()
    {
        $data =  $this->request->param();
        $data['wxapp_id'] = $this->getWxappId();
        // dump($data);die;
        try {
            if (empty($data['name'])) {
                throw new \Exception('菜单名称不能为空');
            }
            
            if (empty($data['id'])) {
                $menu = new WechatMenuModel();
            } else {
                $menu = WechatMenuModel::find($data['id']);
                if (!$menu) {
                    throw new \Exception('菜单不存在');
                }
            }
            
            $menu->allowField(true)->save($data);
            return $this->renderSuccess('更新成功');
        } catch (\Exception $e) {
            return $this->renderError($menu->getError() ?: '更新失败');
        }
    }
    
    // 删除菜单
    public function delete()
    {
        $id = $this->request->param()['id'];
        
        try {
            $menu = WechatMenuModel::find($id);
            if (!$menu) {
                throw new \Exception('菜单不存在');
            }
            
            // 检查是否有子菜单
            if (WechatMenuModel::where('parent_id', $id)->count() > 0) {
                throw new \Exception('请先删除子菜单');
            }
            
            $menu->delete();
            return json(['code' => 1, 'msg' => '删除成功']);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }
    
    // 发布菜单到微信
    public function publish()
    {
        $result = $this->menuService->publishMenuFromDb($this->getWxappId());
        return json($result);
    }
    
    // 排序
    public function sort()
    {
        $ids = $this->request->param()('ids');
        
        try {
            foreach ($ids as $sort => $id) {
                WechatMenuModel::where('id', $id)->update(['sort' => $sort]);
            }
            return json(['code' => 1, 'msg' => '排序成功']);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }
}
