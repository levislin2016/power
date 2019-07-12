<?php
namespace app\index\service;

use app\index\model\StockAll as StockAllModel;
use app\index\model\ProjectStock as ProjectStockModel;
use app\lib\exception\BaseException;

class StockAll{

    public function stock_num(){
        $params = input('get.');

        $field = input('get.field', 'id');
        $order = input('get.order', 'desc');
        if($order == 'null'){ 
            $field = 'id';
            $order = 'asc';
        }

        $list = StockAllModel::useGlobalScope(false)->alias('sa')
            ->leftJoin('stock st','sa.stock_id = st.id')
            ->leftJoin('supply_goods sg','sa.supply_goods_id = sg.id')
            ->leftJoin('supply s','sg.s_id = s.id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){ 
                    $query->where('g.number|g.name|st.name|s.name', 'like', '%'.$params['search'].'%');    
                }
            })
            ->field('sa.*, st.name as stock_name, g.name, g.number, g.image, s.name as supply_name, u.name as unit')
            ->order($field, $order)
            ->paginate($params['nums'], false);

        return $list;
        
    }

    public function project_num(){
        $params = input('get.');

        $field = input('get.field', 'id');
        $order = input('get.order', 'desc');
        if($order == 'null'){ 
            $field = 'id';
            $order = 'asc';
        }

        $list = ProjectStockModel::useGlobalScope(false)->alias('ps')
            ->leftJoin('stock st','ps.stock_id = st.id')
            ->leftJoin('project p','ps.project_id = p.id')
            ->leftJoin('supply_goods sg','ps.supply_goods_id = sg.id')
            ->leftJoin('supply s','sg.s_id = s.id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){ 
                    $query->where('g.number|g.name|st.name|s.name|p.name', 'like', '%'.$params['search'].'%');    
                }
            })
            ->field('ps.*, (ps.have + ps.offer) as ho, p.name as project_name, st.name as stock_name, g.name, g.number, g.image, s.name as supply_name, u.name as unit')
            ->order($field, $order)
            ->paginate($params['nums'], false);

        return $list;
    }

}