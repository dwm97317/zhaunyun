<?php

namespace app\store\controller\content;

use app\store\controller\Controller;
use app\store\model\Article as ArticleModel;
use app\store\model\article\Category as CategoryModel;
use app\store\model\Setting as SettingModel;

/**
 * 文章管理控制器
 * Class article
 * @package app\store\controller\content
 */
class Article extends Controller
{
    /**
     * 文章列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new ArticleModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }
    
    /**
     * 文章列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function list()
    {
        $model = new ArticleModel;
        $list = $model->getAllList();
        return $this->renderSuccess('获取成功','',compact('list'));
    }

    /**
     * 添加文章
     * @return array|mixed
     */
    public function add()
    {
        $model = new ArticleModel;
        $SettingModel = new SettingModel;
        if (!$this->request->isAjax()) {
            $lang = $SettingModel::getItem("lang");
            $langlist = array_map(function($json) {return json_decode($json, true);}, $lang['langlist']);
            $catgory = CategoryModel::getAll();
            return $this->fetch('add', compact('catgory','langlist'));
        }
        // 新增记录
        if ($model->add($this->postData('article'))) {
            return $this->renderSuccess('添加成功', url('content.article/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 更新文章
     * @param $article_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($article_id)
    {
        // 文章详情
        $SettingModel = new SettingModel;
        $model = ArticleModel::detail($article_id);
        if (!$this->request->isAjax()) {
            $lang = $SettingModel::getItem("lang");
            $langlist = array_map(function($json) {return json_decode($json, true);}, $lang['langlist']);
            $catgory = CategoryModel::getAll();
            return $this->fetch('edit', compact('model', 'catgory','langlist'));
        }
        // 更新记录
        $data =$this->postData('article');
        $data['article_id'] = $article_id;
        if ($model->edit($data)) {
            return $this->renderSuccess('更新成功', url('content.article/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除文章
     * @param $article_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($article_id)
    {
        // 文章详情
        $model = ArticleModel::detail($article_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}