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
        $hasWhere = [];
        if (isset($params['search']) && $params['search']){
            $hasWhere[] = ['name', 'like', "%{$params['search']}%"];
        }

        if (isset($params['buy_id']) && $params['buy_id']){
            $where[] = ['buy_id', '=', $params['buy_id']];
        }

        if (isset($params['open_time']) && $params['open_time']){
            $time = explode('至', $params['open_time']);
            $where[] = ['Project.open_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        $list = BuyProjectModel::hasWhere('project', $hasWhere)->with(['project' => ['contract']])->where($where)->paginate($limit);

        return $list;
    }

} 