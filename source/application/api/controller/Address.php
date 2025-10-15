<?php
namespace app\api\controller;
use app\api\model\UserAddress;
use app\api\model\Inpack;

/**
 * 收货地址管理
 * Class Address
 * @package app\api\controller
 */
class Address extends Controller
{
    /**
     * 收货地址列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        $user = $this->getUser();
        $model = new UserAddress;
        $list = $model->getList($user['user_id']);
        return $this->renderSuccess([
            'list' => $list,
            'default_id' => $user['address_id'],
        ]);
    }
    
    
    /**
     * 发货地址列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function getSendAddresslist()
    {
        $user = $this->getUser();
        $model = new UserAddress;
        $list = $model->getSendList($user['user_id'],$addressty=1);
        return $this->renderSuccess([
            'list' => $list,
            'default_id' => $user['address_id'],
        ]);
    }
    
    /**
     * 收货地址列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function getRecivelists()
    {
        $user = $this->getUser();
        $model = new UserAddress;
        $list = $model->getSendList($user['user_id'],$addressty=0);
        return $this->renderSuccess([
            'list' => $list,
            'default_id' => $user['address_id'],
        ]);
    }
    
    /**
     * 根据用户id，code，手机号检索地址
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function getAlllists()
    {
        $data = $this->request->post();
        $model = new UserAddress;
        $list = $model->getAllList($data);
        // dump($list);die;
        return $this->renderSuccess([
            'list' => $list,
            // 'default_id' => $user['address_id'],
        ]);
    }
    
    /**
     * 代收点地址列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function dslists()
    {
        $param = $this->request->param();
        $user = $this->getUser();
        $model = new UserAddress;
        $list = $model->getDsList($param);
        return $this->renderSuccess([
            'list' => $list,
            'default_id' => $user['address_id'],
        ]);
    }
    
    /**
     * 代收点地址列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function zitidianlists()
    {
        $param = $this->request->param();
        // dump($param['keyword']);die;
        $model = new UserAddress;
        $list = $model->getDsList($param);
        return $this->renderSuccess([
            'list' => $list,
        ]);
    }

    /**
     * 添加收货地址
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function add()
    {
        $model = new UserAddress;
        if ($model->add($this->getUser(), $this->request->post())) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 收货地址详情
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail($address_id)
    {
        $user = $this->getUser();
        $detail = UserAddress::detail($user['user_id'], $address_id);
        
        return $this->renderSuccess(compact('detail'));
    }
    
    public function getdetail($id){
        $model = new UserAddress;
        $detail = $model::getdetail($id);
        return $this->renderSuccess($detail);
    }
    
    /**
     * 收货地址详情
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function getAddress($address_id)
    {
        // $user = $this->getUser();
        $detail = UserAddress::getdetail($address_id);
        
        return $this->renderSuccess(compact('detail'));
    }

    /**
     * 编辑收货地址
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function edit($address_id)
    {
        $user = $this->getUser();
        $model = UserAddress::detail($user['user_id'], $address_id);
        // 状态 1 待查验 2 待支付 3 待发货 4 拣货中 5 已打包  6已发货 7 已到货 8 已完成  9已取消 10草稿
        $result = (new Inpack())->where('address_id',$address_id)->where('status','in',[2,3,4,5,6,7])->find();
        if(!empty($result)){
            return $this->renderError('此地址已被发货中的订单使用，不可修改');
        }
        if ($model->edit($this->request->post())) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 设为默认地址
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function setDefault($address_id)
    {
        $user = $this->getUser();
        $model = UserAddress::detail($user['user_id'], $address_id);
        if ($model->setDefault($user)) {
            return $this->renderSuccess([], '设置成功');
        }
        return $this->renderError($model->getError() ?: '设置失败');
    }

    /**
     * 删除收货地址
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function delete($address_id)
    {
        $user = $this->getUser();
        $model = UserAddress::detail($user['user_id'], $address_id);
        // 状态 1 待查验 2 待支付 3 待发货 4 拣货中 5 已打包  6已发货 7 已到货 8 已完成  9已取消 10草稿
        $result = (new Inpack())->where('address_id',$address_id)->where('status','in',[2,3,4,5,6,7])->find();
        if(!empty($result)){
            return $this->renderError('此地址已被发货中的订单使用，不可删除');
        }
        if ($model->remove($user)) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

}
