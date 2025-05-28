<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\WxappMenus as WxappMenusModel;
use app\store\model\Setting as SettingModel;

/**
 * 小程序菜单
 * Class Menus
 * @package app\store\controller\setting
 */
class Menus extends Controller
{
    /**
     * 菜单列表
     * @return mixed
     */
    public function index()
    {
        $model = new WxappMenusModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除商品分类
     * @param $category_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        $model = WxappMenusModel::get($id);
        if (!$model->remove($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加导航
     * @return array|mixed
     */
    public function add()
    {
        $model = new WxappMenusModel;
        $SettingModel = new SettingModel;
        $lang = $SettingModel::getItem("lang");
        $langlist = [
            0 =>[
                "name"=>'简体中文',
                'enname'=>'zhHans'
            ],
            2 =>[
                "name"=>'繁体中文',
                'enname'=>'zhHant'
            ]
        ];
        foreach ($lang['langlist'] as $key=>$val){
            $langlist[] = json_decode($val,true);
        }
        if (!$this->request->isAjax()) {
            // 获取所有地区
            $list = $model->getList();
            return $this->fetch('add', compact('list','langlist'));
        }
        // 新增记录
        if ($model->add($this->postData('nav'))) {
            return $this->renderSuccess('添加成功', url('setting.menus/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑导航
     * @param $category_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        // 模板详情
        $model = WxappMenusModel::get($id, ['image']);
        if (!$this->request->isAjax()) {
            $SettingModel = new SettingModel;
            $lang = $SettingModel::getItem("lang");
            $langlist = [
                0 =>[
                    "name"=>'简体中文',
                    'enname'=>'zhHans'
                ],
                2 =>[
                    "name"=>'繁体中文',
                    'enname'=>'zhHant'
                ]
            ];
            foreach ($lang['langlist'] as $key=>$val){
                $langlist[] = json_decode($val,true);
            }
            $list = $model->getList();
            return $this->fetch('edit', compact('model','list','langlist'));
        }
        // 更新记录
        if ($model->edit($this->postData('nav'))) {
            return $this->renderSuccess('更新成功', url('setting.menus/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
