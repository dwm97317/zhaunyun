<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Track as TrackModel;


/**
 * 常用轨迹
 * Class Delivery
 * @package app\store\controller\setting
 */
class Track extends Controller
{


    /**
     * 列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){
        $model = new TrackModel();
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 新增
     */
    public function add(){
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        $model = new TrackModel();
        if ($model->add($this->postData('track'))) {
            return $this->renderSuccess('添加成功', url('setting.track/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }


    /**
     * 编辑
     */
    public function edit($id){
        // 模板详情
        $model = TrackModel::detail($id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('track'))) {
            return $this->renderSuccess('更新成功', url('setting.track/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    


    /**
     * 删除模板
     * @param $delivery_id
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete($id)
    {
        $model = TrackModel::detail($id);
        if (!$model->delete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }


}
