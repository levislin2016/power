<?php
namespace app\index\service;

use app\index\model\Goods as GoodsModel;
use app\index\model\BuyProject as BuyProjectModel;
use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\lib\exception\BaseException;
use think\Db;
use think\facade\App;


class BuyProject{
    // 获取采购对应的工程列表
    public function getList($params, $limit = 10, $order = 'desc'){
        $where = [];
        if (isset($params['buy_id']) && $params['buy_id']){
            $where[] = ['buy_id', '=', $params['buy_id']];
        }
        $list = BuyProjectModel::with('project')->where($where)->paginate($limit);

        return $list;
    }

} 