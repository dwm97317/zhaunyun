<?php
namespace app\common\model;

/**
 * 微信公众号自定义菜单
 * Class WechatMenu
 * @package app\common\model
 */
class WechatMenu extends BaseModel
{
    protected $name = 'wechat_menus';
    
    // 菜单类型常量
    const TYPE_CLICK = 'click';
    const TYPE_VIEW = 'view';
    const TYPE_MINIPROGRAM = 'miniprogram';
    const TYPE_SCANCODE_PUSH = 'scancode_push';
    
    // 获取类型映射
    public static function getTypeMap()
    {
        return [
            self::TYPE_CLICK => '点击事件',
            self::TYPE_VIEW => '跳转链接',
            self::TYPE_MINIPROGRAM => '小程序',
            self::TYPE_SCANCODE_PUSH => '扫码推事件'
        ];
    }
    
    public static function formatForWechat($menus) {
        $formatted = ['button' => []];
        foreach ($menus as $menu) {
            $item = ['name' => $menu['name']];
            
            if (!empty($menu['sub_button'])) {
                // 有子菜单时不设置type
                $item['sub_button'] = [];
                foreach ($menu['sub_button'] as $subMenu) {
                    $subItem = [
                        'type' => $subMenu['type'],
                        'name' => $subMenu['name']
                    ];
                    
                    // 根据类型设置不同字段
                    switch ($subMenu['type']) {
                        case 'click':
                            $subItem['key'] = $subMenu['key'];
                            break;
                        case 'view':
                            $subItem['url'] = $subMenu['url'];
                            break;
                        case 'miniprogram':
                            $subItem['url'] = $subMenu['url'];
                            $subItem['appid'] = $subMenu['appid'];
                            $subItem['pagepath'] = $subMenu['pagepath'];
                            break;
                    }
                    
                    $item['sub_button'][] = $subItem;
                }
            } else {
                // 没有子菜单时必须设置type
                $item['type'] = $menu['type'];
                switch ($menu['type']) {
                    case 'click':
                        $item['key'] = $menu['key'];
                        break;
                    case 'view':
                        $item['url'] = $menu['url'];
                        break;
                    case 'miniprogram':
                        $item['url'] = $menu['url'];
                        $item['appid'] = $menu['appid'];
                        $item['pagepath'] = $menu['pagepath'];
                        break;
                }
            }
            
            $formatted['button'][] = $item;
        }
        
        return $formatted;
    }
    
    /**
     * 转义链接
     * @param $value
     * @return mixed
     */
    public function getUrlAttr($value)
    {
        return htmlspecialchars_decode($value);
    }
}
