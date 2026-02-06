<?php
namespace app\store\controller\apps\sharing;
use app\store\controller\Controller;
use app\store\model\sharing\SharingUser;
use app\store\model\User as UserModel;
use app\store\model\Setting;

class Verify extends Controller{
    
    public function index(){
        $list = (new SharingUser())->getList();
        return $this->fetch('index',compact('list'));
    }
    
    
    public function list(){
        $list = (new SharingUser())->getListX();
        $set = Setting::detail('store')['values']['usercode_mode'];
        return $this->fetch('list',compact('list','set'));
    }
    
    /**
     * 删除团长
     * @param $dealer_id
     * @return array|bool
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        $model = SharingUser::detail($id);
        if (!$model->setDelete()) {
            return $this->renderError('删除失败');
        }
        $detail = UserModel::detail($model['user_id']);
        $detail->save(['is_sharp'=>0]);
        return $this->renderSuccess('删除成功');
    }
 
    public function update(){
       $ids = $this->postData('selectIds')[0];
       $form = $this->postData('verify');
       $idsArr = explode(',',$ids);  
       $model = (new SharingUser());
       if($model->modifyStatus($idsArr,$form)){
          return $this->renderSuccess('审核操作成功'); 
       }
       return $this->renderError($model->getError()??'审核操作失败');
    }
}
?>