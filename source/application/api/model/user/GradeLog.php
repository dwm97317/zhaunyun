<?php

namespace app\api\model\user;

use app\common\model\user\GradeLog as GradeLogModel;
use app\common\enum\user\grade\log\ChangeType as ChangeTypeEnum;
/**
 * 用户会员等级变更记录模型
 * Class GradeLog
 * @package app\api\model\user
 */
class GradeLog extends GradeLogModel
{
    /**
     * 新增变更记录
     * @param $data
     * @return array|false
     * @throws \Exception
     */
    public function records($data)
    {
        $saveData = [];
        foreach ($data as $item) {
            $saveData[] = array_merge([
                'change_type' => ChangeTypeEnum::PAY_UPGRADE,
                'wxapp_id' => static::$wxapp_id
            ], $item);
        }
        return $this->isUpdate(false)->saveAll($saveData);
    }

}