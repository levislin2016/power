<?php
namespace app\index\service;

use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\index\model\Goods as GoodsModel;
use app\index\model\Buy as BuyModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\Need as NeedModel;
use app\index\model\StockOrder as StockOrderModel;
use app\index\model\StockOrderInfo as StockOrderInfoModel;
use app\index\model\Project as ProjectModel;
use app\index\model\StockAll as StockAllModel;
use app\index\model\ProjectStock as ProjectStockModel;
use app\lib\exception\BaseException;
use think\Db;

class BuyInfo{

    // 根据项目id获取采购单的商品列表
    public function getList($params, $type = false, $limit = 20, $order = 'desc'){
        $list = BuyInfoModel::useGlobalScope(false)->alias('bi')
                    ->leftJoin('goods g','bi.goods_id = g.id')
                    ->leftJoin('unit u','g.unit_id = u.id')
                    ->leftJoin('need n','bi.need_id = n.id')
                    ->where(function ($query) use($params) {
                        $query->where('bi.buy_id', '=', $params['buy_id']);
                    })
                    ->field('bi.id, bi.goods_id, bi.project_id, bi.num, n.need, n.need_ok, g.number, g.name, g.unit_id, u.name as unit')
                    ->order('bi.create_time', $order)
                    ->paginate($limit, false, ['path' => "javascript:ajaxPage([PAGE]);"]);

        // 根据项目的采购单详情列表
        if ($type == 'project'){
            $list = $this->getListProject($list);
        }

        // 根据项目的采购单详情列表
        if ($type == 'total'){
            $list = $this->getListTotal($list);
        }

//        dump(Db::getLastSql());
        return $list;
    }

    // 根据工程的采购单详情列表
    public function getListProject($list){
        $arr = [];
        foreach ($list as $k => $v){
            $project = ProjectModel::get($v['project_id']);
            $arr[$v['project_id']]['project_name'] = $project['name'];
            $arr[$v['project_id']]['list'][] = $v;
        }
        return $arr;
    }

    // 根据需求数量，计算各个材料的总数量
    public function getListTotal($list){
        $arr = [];
        foreach ($list as $k => $v){
            if (!isset($arr[$v['goods_id']]['num'])){
                $arr[$v['goods_id']]['num'] = $v['num'];
            }else{
                $arr[$v['goods_id']]['num'] += $v['num'];
            }
            $arr[$v['goods_id']]['list'] = $v;
        }
        return $arr;
    }



}