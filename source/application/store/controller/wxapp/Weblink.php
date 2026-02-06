<?php
namespace app\store\controller\wxapp;

use app\store\controller\Controller;
use app\store\model\WebLink as WebLinkModel;
use app\store\model\UploadFile;

/**
 * 友情链接设置
 * Class WebLink
 * @package app\store\controller\wxapp
 */
class WebLink extends Controller
{
    /**
     * 删除模板
     * @param $delivery_id
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete()
    {
        $id = $this->request->param('id');

        $model = (new WebLinkModel())->details($id);
        if (!$model->remove()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加配送模板
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('wxapp/web_link/add');
        }
        // 新增记录
        $model = new WebLinkModel();
        if ($model->add($this->postData('link'))) {
            return $this->renderSuccess('添加成功', url('wxapp/weblink'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑配送模板
     * @param $delivery_id
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($id)
    {
        // 模板详情
        $model = (new WebLinkModel())->details($id);
        if ($model['image_id']){
            $model['file'] = (new UploadFile())->find($model['image_id']);
        }
        if (!$this->request->isAjax()) {
            return $this->fetch('wxapp/web_link/edit', compact('model'));
        }
        // 更新记录
        $data = $this->postData('link');
        $data['id'] = $id;
        if ($model->edit($data)) {
            return $this->renderSuccess('更新成功', url('wxapp/weblink'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
