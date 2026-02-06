<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\WxappNavLink as WxappNavLinkModel;
use app\store\model\Setting as SettingModel;


/**
 * 小程序导航
 * Class Nav
 * @package app\store\controller\setting
 */
class Nav extends Controller
{
    /**
     * 商品分类列表
     * @return mixed
     */
    public function index()
    {
        $model = new WxappNavLinkModel;
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
        $model = WxappNavLinkModel::get($id);
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
        $model = new WxappNavLinkModel;
        $SettingModel = new SettingModel;
        if (!$this->request->isAjax()) {
            $lang = $SettingModel::getItem("lang");
            $langlist = array_map(function($json) {return json_decode($json, true);}, $lang['langlist']);
            $list = $model->getList();
            return $this->fetch('add', compact('list','langlist'));
        }
        // 新增记录
        if ($model->add($this->postData('nav'))) {
            return $this->renderSuccess('添加成功', url('setting.nav/index'));
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
        $model = WxappNavLinkModel::get($id, ['image']);
        $SettingModel = new SettingModel;
        if (!$this->request->isAjax()) {
            $lang = $SettingModel::getItem("lang");
            $langlist = array_map(function($json) {return json_decode($json, true);}, $lang['langlist']);
            $list = $model->getList();
            return $this->fetch('edit', compact('model', 'list','langlist'));
        }
        // 更新记录
        if ($model->edit($this->postData('nav'))) {
            return $this->renderSuccess('更新成功', url('setting.nav/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
