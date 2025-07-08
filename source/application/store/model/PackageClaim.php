<?php


namespace app\store\model;

use think\Model;
use app\common\model\PackageClaim as PackageClaimModel;

class PackageClaim extends PackageClaimModel
{
    /**
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($param)
    {
        return $this
            ->setListQueryWhere($param)->alias('a')->with(['user','package.shelfunititem.shelfunit'])
            ->join('package pa', 'pa.id = a.package_id',"LEFT")
            ->field('a.*,pa.express_num,pa.storage_id')
            ->order(['a.create_time' => 'desc','a.status' => 'asc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }
    
    public function setListQueryWhere($param){
        !empty($param['express_num'])&& $this->where('pa.express_num','=',$param['express_num']);
        !empty($param['extract_shop_id'])&& $this->where('pa.storage_id','=',$param['extract_shop_id']);
        !empty($param['start_time']) && $this->where('a.create_time', '>=', $param['start_time']);
        !empty($param['end_time']) && $this->where('a.create_time', '<=', $param['end_time']." 23:59:59");
        return $this;
    }
}