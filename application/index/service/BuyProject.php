<?php
namespace app\index\service;

use app\index\model\Goods as GoodsModel;
use app\index\model\BuyProject as BuyProjectModel;
use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\lib\exception\BaseException;
use think\Db;
use think\facade\App;


class BuyProject{
    // 获取材料列表
    public function getList($params, $limit = 10, $order = 'desc'){
        $list = BuyProjectModel::useGlobalScope(false)->alias('bj')
            ->leftJoin('project p','bj.project_id = p.id')
            ->where(function ($query) use($params) {
                if(!empty($params['keywords'])){
                    $query->where('p.name', 'like', '%'.$params['keywords'].'%');
                }
            })
            ->field('bj.id, p.name')
            ->order('bj.create_time', 'desc')
            ->paginate(10, false, ['path' => "javascript:AjaxPage([PAGE]);"]);

        return $list;
    }

} 