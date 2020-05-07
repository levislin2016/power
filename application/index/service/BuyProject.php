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

        $list = BuyProjectModel::where([])->paginate(10);

        return $list;
    }

} 