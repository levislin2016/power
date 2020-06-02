<?php
namespace app\index\service;

use app\index\model\StockInfo as StockInfoModel;

class StockInfo{

    // 获取列表
    public function getList($params, $limit = 15){
        $where = [];
        $hasWhere = [];
        if (isset($params['search']) && $params['search']){
            $hasWhere[] = ['Goods.name|number', 'like', "%{$params['search']}%"];
        }

        if (isset($params['stock_id']) && $params['stock_id']){
            $where[] = ['stock_id', '=', $params['stock_id']];
        }

        if (isset($params['project_id']) && $params['project_id']){
            $where[] = ['project_id', '=', $params['project_id']];
        }

        if (isset($params['buy_id']) && $params['buy_id']){
            $where[] = ['buy_id', '=', $params['buy_id']];
        }

        if (isset($params['type']) && $params['type']){
            $where[] = ['type', '=', $params['type']];
        }

        if (isset($params['create_time']) && $params['create_time']){
            $time = explode('至', $params['create_time']);
            $where[] = ['Need.create_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        $list = StockInfoModel::hasWhere('goods', $hasWhere)->with(['goods' => ['unit', 'cate'], 'need', 'supply', 'project'])->where($where)->order('create_time desc')->paginate($limit);
        return $list;
    }


} 