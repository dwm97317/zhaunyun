<?php
namespace app\web\model;

use app\common\model\WebLink as WebLinkModel;


/**
 * 友情链接设置
 * Class WebLink
 * @package app\web\model
 */
class WebLink extends WebLinkModel
{
    /**
     * 友情链接列表
     * @param int $type
     * @return array
     * @throws \think\exception\DbException
     */
    public function getList($params)
    {
        $params['type']>0 && $where['type'] = $params['type'];
        return $this->with('image')->where($where)->select();
    }


}
