<?php

namespace app\api\model\user;

use app\common\model\user\UserMark as UserMarkModel;

/**
 * 用户唛头模型
 * Class user_mark
 * @package app\common\model\user
 */
class UserMark extends UserMarkModel
{
    public function getList($user_id)
    {
        return $this->where('user_id',$user_id)->select();
    }
}