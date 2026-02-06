<?php

namespace app\common\model\store\shop;

use app\common\model\BaseModel;

/**
 * 员工评价模型
 * Class Clerk
 * @package app\common\model\store
 */
class ClerkComment extends BaseModel
{
    protected $name = 'store_shop_clerk_comment';

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        $module = static::getCalledModule() ?: 'common';
        return $this->BelongsTo("app\\{$module}\\model\\User");
    }
    

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 关联门店表
     * @return \think\model\relation\BelongsTo
     */
    public function clerk()
    {
        $module = static::getCalledModule() ?: 'common';
        return $this->BelongsTo("app\\{$module}\\model\\store\\shop\\Clerk");
    }
    
    /**
     * 获取评价列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($param)
    {
        $res= $this->setindexListQueryWhere($param)
            ->with(['user','clerk'])
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
            // dump($this->getLastsql());die;
        return $res;
    }
    
    private function setindexListQueryWhere($param = [])
    {
        !empty($param['clerk_id']) && $this->where('clerk_id',$param['clerk_id']);
        !empty($param['search']) && $this->where('content',$param['search']);
        return $this;
    }

    /**
     * 店员详情
     * @param $where
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        $filter = is_array($where) ? $where : ['comment_id' => $where];
        return static::get(array_merge(['is_delete' => 0], $filter));
    }

}