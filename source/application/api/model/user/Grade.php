<?php

namespace app\api\model\user;

use app\common\model\user\Grade as GradeModel;

/**
 * 用户会员等级模型
 * Class Grade
 * @package app\api\model\user
 */
class Grade extends GradeModel
{
    
    /**
     * 文章详情：HTML实体转换回普通字符
     * @param $value
     * @return string
     */
    public function getDescAttr($value)
    {
        return htmlspecialchars_decode($value);
    }
}