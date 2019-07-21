<?php
namespace app\index\service;

use app\index\model\Project as ProjectModel;
use app\index\model\ProjectStock as ProjectStockModel;
use app\index\model\ShoppingCart as ShoppingCartModel;
use app\index\service\Project as ProjectService;
use app\index\model\StockOrder as StockOrderModel;
use app\index\model\StockOrderInfo as StockOrderInfoModel;
use app\index\model\ProjectWoker as ProjectWokerModel;
use app\index\model\StockOrderInfo;
use app\lib\exception\BaseException;
use think\Db;

class Allocation{

    public function  project_list($params){
        $page=$params['page']?$params['page']:1;
        $page=intval($page);
        $limit=$params['limit']?$params['limit']:10;
        $limit=intval($limit);
        $start=$limit*($page-1);
        $count = ProjectModel::useGlobalScope(false)->alias('p')
            ->leftJoin('contract c','p.contract_id = c.id')
            ->leftJoin('owner o','c.owner_id = o.id')
            ->leftJoin('project_woker pw','pw.project_id = p.id')
            ->leftJoin('supply_goods sg','sg.id = pw.supply_goods_id')
            ->leftJoin('goods g','g.id = sg.g_id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){
                    $query->where('g.name', 'like', '%'.$params['search'].'%');
                }
                if(!empty($params['project_id'])){
                    $query->where('p.id', 'neq', $params['project_id']);
                }
                if(!empty($params['supply_goods_id'])){
                    $query->where('g.id', 'eq', $params['supply_goods_id']);
                }
                $query->where('status', 2);
                $query->where('not', '>', 0);
            })
            ->order('p.create_time', 'desc')
            ->count();

        $list = ProjectModel::useGlobalScope(false)->alias('p')
            ->leftJoin('contract c','p.contract_id = c.id')
            ->leftJoin('owner o','c.owner_id = o.id')
            ->leftJoin('project_woker pw','pw.project_id = p.id')
            ->leftJoin('supply_goods sg','sg.id = pw.supply_goods_id')
            ->leftJoin('goods g','g.id = sg.g_id')
            ->leftJoin('woker w','w.id = pw.woker_id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){
                    $query->where('g.name', 'like', '%'.$params['search'].'%');
                }
                if(!empty($params['project_id'])){
                    $query->where('p.id', 'neq', $params['project_id']);
                }
                if(!empty($params['supply_goods_id'])){
                    $query->where('g.id', 'eq', $params['supply_goods_id']);
                }
                $query->where('status', 2);
                $query->where('not', '>', 0);
            })
            ->field('p.id, p.company_id, pw.woker_id, p.contract_id, c.number as contract_number, p.name as name, p.status as status, p.create_time as create_time, o.id as owner_id, o.name as owner_name, pw.supply_goods_id as supply_goods_id, w.name as woker_name')
            ->order('p.create_time', 'desc')
            ->limit($start,$limit)
            ->select()->toArray();
        $is_type = \Db::table('pw_need')->field('type')->where('project_id',$params['project_id'])->find();
        if($is_type['type'] == 1){
            cookie('is_type', 1, 40);
        }else{
            cookie('is_type', 2, 40);
        }
        $project_status = config('extra.project_status');

        foreach ($list as &$v){
            $project_list = \Db::table('pw_project_woker')->alias('pw')
                ->leftJoin('woker w','w.id = pw.woker_id')
                ->leftJoin('supply_goods sg','sg.id = pw.supply_goods_id')
                ->leftJoin('goods g','g.id = sg.g_id')
                ->field('pw.id, pw.supply_goods_id, pw.not as not_num, g.number as goods_number, g.name as goods_name')
                ->where(function ($query) use($params) {
                    if(!empty($params['search'])){
                        $query->where('g.name', 'like', '%'.$params['search'].'%');
                    }
                })
                ->where([
                    'pw.project_id'      => $v['id'],
                    'pw.supply_goods_id' => $v['supply_goods_id'],
                    'pw.delete_time'     => 0,
                    'pw.woker_id'        => $v['woker_id']
                ])
                ->select();
            $project_list = $project_list->toArray();
            if($project_list) {
                foreach ($project_list as &$val) {
                    $stock_list = \Db::table('pw_stock_order')->alias('so')
                        ->leftJoin('stock_order_info soi', 'so.id = soi.stock_order_id')
                        ->field('so.supply_goods_id, soi.num')
                        ->where([
                            'soi.supply_goods_id' => $val['supply_goods_id'],
                            'so.type' => 9,
                            'so.delete_time' => 0
                        ])
                        ->sum('soi.num');
                    $val['can_num'] = $val['not_num'] + $stock_list;
                    $val['get_num'] = $stock_list;
                    unset($val['project_id']);
                    unset($val['woker_id']);
                    unset($val['supply_goods_id']);
                    unset($val['id']);
                    $v['goods'] = $val['goods_name'] . ' / ' . $val['goods_number'];
                    $v['can_num'] = $val['can_num'];
                    $v['get_num'] = $val['get_num'];
                    $v['not_num'] = $val['not_num'];
                }

                $v['status_name'] = $project_status[$v['status']];
            }else{
                $v['goods'] = '--';
                $v['can_num'] = '--';
                $v['get_num'] = '--';
                $v['not_num'] = '--';
                $v['status_name'] = '';
            }

        }
        return ['count' => $count, 'list' => $list];
    }


    public function project_stock_list($params){
        $page=$params['page']?$params['page']:1;
        $page=intval($page);
        $limit=$params['limit']?$params['limit']:10;
        $limit=intval($limit);
        $start=$limit*($page-1);
        $count = ProjectModel::useGlobalScope(false)->alias('p')
            ->leftJoin('contract c','p.contract_id = c.id')
            ->leftJoin('owner o','c.owner_id = o.id')
            ->leftJoin('project_stock ps','ps.project_id = p.id')
            ->leftJoin('supply_goods sg','sg.id = ps.supply_goods_id')
            ->leftJoin('goods g','g.id = sg.g_id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){
                    $query->where('g.name', 'like', '%'.$params['search'].'%');
                }
                if(!empty($params['project_id'])){
                    $query->where('p.id', 'neq', $params['project_id']);
                }
                if(!empty($params['supply_goods_id'])){
                    $query->where('g.id', 'eq', $params['supply_goods_id']);
                }
                $query->where('status', 2);
                $query->where('ps.in', '>', 0);
            })
            ->order('p.create_time', 'desc')
            ->count();

        $list = ProjectModel::useGlobalScope(false)->alias('p')
            ->leftJoin('contract c','p.contract_id = c.id')
            ->leftJoin('owner o','c.owner_id = o.id')
            ->leftJoin('project_stock ps','ps.project_id = p.id')
            ->leftJoin('supply_goods sg','sg.id = ps.supply_goods_id')
            ->leftJoin('goods g','g.id = sg.g_id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){
                    $query->where('g.name', 'like', '%'.$params['search'].'%');
                }
                if(!empty($params['project_id'])){
                    $query->where('p.id', 'neq', $params['project_id']);
                }
                if(!empty($params['supply_goods_id'])){
                    $query->where('g.id', 'eq', $params['supply_goods_id']);
                }
                $query->where('status', 2);
                $query->where('ps.in', '>', 0);
            })
            ->field('p.id, p.company_id, p.contract_id, c.number as contract_number, p.name as name, p.status as status, p.create_time as create_time, o.id as owner_id, o.name as owner_name, ps.supply_goods_id as supply_goods_id,ps.stock_id')
            ->order('p.create_time', 'desc')
            ->limit($start,$limit)
            ->select()->toArray();
        $is_type = \Db::table('pw_need')->field('type')->where('project_id',$params['project_id'])->find();
        if($is_type['type'] == 1){
            cookie('is_type', 1, 40);
        }else{
            cookie('is_type', 2, 40);
        }
        $project_status = config('extra.project_status');

        foreach ($list as &$v){
            $project_list = \Db::table('pw_project_stock')->alias('ps')
                ->leftJoin('supply_goods sg','sg.id = ps.supply_goods_id')
                ->leftJoin('goods g','g.id = sg.g_id')
                ->field('ps.id, ps.supply_goods_id, ps.in, g.number as goods_number, g.name as goods_name')
                ->where(function ($query) use($params) {
                    if(!empty($params['search'])){
                        $query->where('g.name', 'like', '%'.$params['search'].'%');
                    }
                })
                ->where([
                    'ps.project_id'      => $v['id'],
                    'ps.supply_goods_id' => $v['supply_goods_id'],
                    'ps.delete_time'     => 0,
                ])
                ->select();
            $project_list = $project_list->toArray();
            if($project_list) {
                foreach ($project_list as &$val) {
                    $stock_list = \Db::table('pw_stock_order')->alias('so')
                        ->leftJoin('stock_order_info soi', 'so.id = soi.stock_order_id')
                        ->field('so.supply_goods_id, soi.num')
                        ->where([
                            'soi.supply_goods_id' => $val['supply_goods_id'],
                            'so.type' => 14,
                            'so.delete_time' => 0
                        ])
                        ->sum('soi.num');
                    $val['can_num'] = $val['in'] + $stock_list;
                    $val['get_num'] = $stock_list;
                    unset($val['project_id']);
                    unset($val['woker_id']);
                    unset($val['supply_goods_id']);
                    unset($val['id']);
                    $v['goods'] = $val['goods_name'] . ' / ' . $val['goods_number'];
                    $v['can_num'] = $val['can_num'];
                    $v['get_num'] = $val['get_num'];
                    $v['in'] = $val['in'];
                }

                $v['status_name'] = $project_status[$v['status']];
            }else{
                $v['goods'] = '--';
                $v['can_num'] = '--';
                $v['get_num'] = '--';
                $v['in'] = '--';
                $v['status_name'] = '';
            }

        }
        return ['count' => $count, 'list' => $list];
    }

    public function balance_list($params){
        $page=$params['page']?$params['page']:1;
        $page=intval($page);
        $limit=$params['limit']?$params['limit']:10;
        $limit=intval($limit);
        $start=$limit*($page-1);
        $list = ProjectStockModel::useGlobalScope(false)->alias('ps')
            ->leftJoin('stock s','s.id = ps.stock_id ')
            ->leftJoin('supply_goods sg','sg.id = ps.supply_goods_id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('project p','p.id = ps.project_id')
            ->leftJoin('contract c','p.contract_id = c.id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){
                    $query->where('g.name', 'like', '%'.$params['search'].'%');
                }
                if(!empty($params['project_id'])){
                    $query->where('p.id', 'neq', $params['project_id']);
                }
                if(!empty($params['supply_goods_id'])){
                    $query->where('g.id', 'eq', $params['supply_goods_id']);
                }
                $query->where('ps.have', '>', 0);
            })
            ->field('p.id, max(ps.have) as have, max(p.name) as project_name, max(ps.stock_id) as stock_id, max(g.name) as supply_goods_name,max(sg.id) as supply_goods_id, max(p.name) as num, max(s.name) as stock_name, max(c.owner_id) as owner_id, max(c.number) as c_number, max(g.number) as g_number')
            ->order('p.create_time', 'desc')
            ->group('p.id')
            ->limit($start,$limit)
            ->select()->toArray();
        $is_type = \Db::table('pw_need')->field('type')->where('project_id',$params['project_id'])->find();
        if($is_type['type'] == 1){
            cookie('is_type', 1, 40);
        }else{
            cookie('is_type', 2, 40);
        }
        foreach ($list as &$val){
            $val['supply_goods_name'] = $val['supply_goods_name'].' / '.$val['g_number'];
        }
        $count = ProjectStockModel::useGlobalScope(false)->alias('ps')
            ->leftJoin('stock s','s.id = ps.stock_id ')
            ->leftJoin('supply_goods sg','sg.id = ps.supply_goods_id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('project p','p.id = ps.project_id')
            ->leftJoin('contract c','p.contract_id = c.id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){
                    $query->where('g.name', 'like', '%'.$params['search'].'%');
                }
                if(!empty($params['project_id'])){
                    $query->where('p.id', 'neq', $params['project_id']);
                }
                if(!empty($params['supply_goods_id'])){
                    $query->where('g.id', 'eq', $params['supply_goods_id']);
                }
                $query->where('ps.have', '>', 0);
            })
            ->group('p.id')
            ->count();
        return ['count' => $count, 'list' => $list];
    }

    public function project_type_list($params){
        if(!empty($params['project_id'])) {
            $p_owner = ProjectModel::useGlobalScope(false)->alias('p')
                ->field('p.id, c.owner_id as owner_id')
                ->leftJoin('contract c', 'p.contract_id = c.id')
                ->where(function ($query) use ($params) {
                    if (!empty($params['project_id'])) {
                        $query->where('p.id', 'eq', $params['project_id']);
                    }
                })
                ->find();
        }else{
            $p_owner['owner_id'] = '0';
        }
        $list = ProjectModel::useGlobalScope(false)->alias('p')
            ->leftJoin('contract c','p.contract_id = c.id')
            ->leftJoin('owner o','c.owner_id = o.id')
            ->leftJoin('project_woker pw','pw.project_id = p.id')
            ->leftJoin('supply_goods sg','sg.id = pw.supply_goods_id')
            ->leftJoin('goods g','g.id = sg.g_id')
            ->where(function ($query) use($params, $p_owner) {
                if(!empty($params['search'])){
                    $query->where('g.name', 'like', '%'.$params['search'].'%');
                }
                if(!empty($params['project_id'])){
                    $query->where('p.id', 'neq', $params['project_id']);
                }
                if(!empty($params['supply_goods_id'])){
                    $query->where('g.id', 'eq', $params['supply_goods_id']);
                }
                $query->where('status', 2);
                $query->where('c.owner_id', $p_owner['owner_id']);
            })
            ->field('p.id, max(p.company_id) as company_id, max(pw.woker_id) as woker_id, max(p.contract_id) as contract_id, max(c.number) as contract_number, max(p.name) as name, max(p.status) as status, max(p.create_time) as create_time, max(o.id) as owner_id, max(o.name) as owner_name')
            ->order('create_time', 'desc')
            ->group('p.id')
            ->paginate(10, false, [
                'query'     => $params,
            ]);
        $is_type = \Db::table('pw_need')->field('type')->where('project_id',$params['project_id'])->find();
        if($is_type['type'] == 1){
            cookie('is_type', 1, 40);
        }else{
            cookie('is_type', 2, 40);
        }
        foreach ($list as &$v){
            $project_list = \Db::table('pw_project_woker')->alias('pw')
                ->leftJoin('woker w','w.id = pw.woker_id')
                ->leftJoin('supply_goods sg','sg.id = pw.supply_goods_id')
                ->leftJoin('goods g','g.id = sg.g_id')
                ->field('pw.id, pw.supply_goods_id, sum(pw.not) as not_num, g.number as goods_number, g.name as goods_name')
                ->where([
                    'pw.project_id'  => $v['id'],
                    'pw.delete_time' => 0
                ])
                ->group('goods_number')
                ->select();
            $project_list = $project_list->toArray();
            foreach ($project_list as &$val){
                $stock_list = \Db::table('pw_stock_order')->alias('so')
                    ->leftJoin('stock_order_info soi','so.id = soi.stock_order_id')
                    ->field('so.supply_goods_id, soi.num')
                    ->where([
                        'soi.supply_goods_id' => $val['supply_goods_id'],
                        'so.type'             => 9,
                        'so.delete_time'      => 0
                    ])
                    ->sum('soi.num');
                $val['can_num'] = $val['not_num'] + $stock_list;
                $val['get_num'] = $stock_list;
                unset($val['project_id']);
                unset($val['woker_id']);
                unset($val['supply_goods_id']);
                unset($val['id']);
            }
            $v['goods_list'] = $project_list;
        }


        return $list;
    }

    public function balance_type_list($params){
        if(!empty($params['project_id'])) {
            $p_owner = ProjectModel::useGlobalScope(false)->alias('p')
                ->field('p.id, c.owner_id as owner_id')
                ->leftJoin('contract c', 'p.contract_id = c.id')
                ->where(function ($query) use ($params) {
                    if (!empty($params['project_id'])) {
                        $query->where('p.id', 'eq', $params['project_id']);
                    }
                })
                ->find();
        }else{
            $p_owner['owner_id'] = '0';
        }
        $count = ProjectStockModel::useGlobalScope(false)->alias('ps')
            ->leftJoin('stock s', 's.id = ps.stock_id ')
            ->leftJoin('supply_goods sg', 'sg.id = ps.supply_goods_id')
            ->leftJoin('goods g', 'sg.g_id = g.id')
            ->leftJoin('project p', 'p.id = ps.project_id')
            ->leftJoin('contract c', 'p.contract_id = c.id')
            ->where(function ($query) use ($params, $p_owner) {
                if (!empty($params['search'])) {
                    $query->where('g.name', 'like', '%' . $params['search'] . '%');
                }
                if (!empty($params['project_id'])) {
                    $query->where('p.id', 'neq', $params['project_id']);
                }
                if (!empty($params['supply_goods_id'])) {
                    $query->where('g.id', 'eq', $params['supply_goods_id']);
                }
                $query->where('ps.offer', '>', 0);
                $query->where('c.owner_id', $p_owner['owner_id']);
            })
            ->group('p.id')
            ->count();
        $list = ProjectStockModel::useGlobalScope(false)->alias('ps')
            ->leftJoin('stock s', 's.id = ps.stock_id ')
            ->leftJoin('supply_goods sg', 'sg.id = ps.supply_goods_id')
            ->leftJoin('goods g', 'sg.g_id = g.id')
            ->leftJoin('project p', 'p.id = ps.project_id')
            ->leftJoin('contract c', 'p.contract_id = c.id')
            ->where(function ($query) use ($params, $p_owner) {
                if (!empty($params['search'])) {
                    $query->where('g.name', 'like', '%' . $params['search'] . '%');
                }
                if (!empty($params['project_id'])) {
                    $query->where('p.id', 'neq', $params['project_id']);
                }
                if (!empty($params['supply_goods_id'])) {
                    $query->where('g.id', 'eq', $params['supply_goods_id']);
                }
                $query->where('ps.offer', '>', 0);
                $query->where('c.owner_id', $p_owner['owner_id']);
            })
            ->field('p.id, max(ps.have) as have, max(p.name) as project_name, max(ps.stock_id) as stock_id, max(g.name) as supply_goods_name,max(sg.id) as supply_goods_id, max(p.name) as num, max(s.name) as stock_name, max(c.owner_id) as owner_id, max(c.number) as c_number, max(g.number) as g_number, max(ps.offer) as offer')
            ->order('p.create_time', 'desc')
            ->group('p.id')
            ->select()->toArray();
        $is_type = \Db::table('pw_need')->field('type')->where('project_id',$params['project_id'])->find();
        if($is_type['type'] == 1){
            cookie('is_type', 1, 40);
        }else{
            cookie('is_type', 2, 40);
        }
        foreach ($list as &$val){
            $val['supply_goods_name'] = $val['supply_goods_name'].' / '.$val['g_number'];
        }
        return ['count' => $count, 'list' => $list];
    }

    public function shopping_list($params){
        $list = ShoppingCartModel::useGlobalScope(false)->alias('sc')
            ->leftJoin('Stock s','sc.stock_id = s.id')
            ->leftJoin('project p','sc.project_id = p.id')
            ->leftJoin('project p1','sc.passive_project_id = p1.id')
            ->leftJoin('woker w','sc.passive_woker_id = w.id')
            ->leftJoin('supply_goods sg','sc.supply_goods_id = sg.id')
            ->leftJoin('goods g','g.id = sg.g_id')
            ->leftJoin('unit u','u.id = g.unit_id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){
                    $query->where('w.name|g.name|p1.name|p.name', 'like', '%'.$params['search'].'%');
                }
            })
            ->where('sc.status',1)
            ->field('sc.*, s.name as stock_name, p.name as project_name,p1.name as passive_project_name, w.name as passive_woker_name,g.name as supply_goods_name,u.name as unit_name')
            ->order(['sc.create_time' => 'desc'])
            ->paginate(10, false, [
                'query'     => $params,
            ]);
        if($list) {
            foreach ($list as &$v){
                if($v['type'] == 1){
                    $v['type_name'] = '工程调拨';
                }else if($v['type'] == 3){
                    $v['type_name'] = '工程仓库调拨';
                }else{
                    $v['type_name'] = '仓库调拨';
                }
            }
        }
        return $list;

    }

    //调拨
    public function shopping_set($params){
        $list = ShoppingCartModel::where(['id' => $params['id']])->find();
        $list = $list->toArray();
        if($list['type'] == 2){
            $is_all = self::balance_set($list);
        }elseif ($list['type'] == 3){
            $is_all = self::project_stock_set($list);
        }else{
            $is_all = self::project_set($list);
        }
        if($is_all['code'] == 200){
            ShoppingCartModel::where(['id' => $params['id']])->update(['status' => 2, 'update_time' => time()]);
        }
        return $is_all;
    }

    //批量调拨

    //仓库调拨
    public function balance_set($data){
        $project_info = \Db::table('pw_project_stock')->field('id, have')
            ->where([
                'project_id'       => $data['passive_project_id'],
                'stock_id'         => $data['stock_id'],
                'supply_goods_id'  => $data['supply_goods_id']
            ])
            ->where('delete_time', 0)
            ->find();
        if($data['num'] > $project_info['have']){
            throw new BaseException(
                [
                    'msg' => '超过最大可调拨材料数'.$project_info['have'].'个',
                    'errorCode' => 301
                ]);
        }
        $need = \Db::table('pw_need')->alias('n')
            ->leftJoin('pw_supply_goods sg','n.goods_id = sg.g_id')
            ->field('n.id')
            ->where([
                'n.project_id' => $data['project_id'],
                'sg.id' => $data['supply_goods_id'],
                'type'  => '1'
            ])
            ->find();
        if(!$need){
            throw new BaseException(
                [
                    'msg' => '暂无该材料的需求',
                    'errorCode' => 301
                ]);
        }
        $allocation_list = \Db::table('pw_project_stock')->field('id, num')->where(['supply_goods_id' => $data['supply_goods_id'], 'project_id' => $data['project_id']])->find();
        \Db::startTrans();
        try {
            if(!$allocation_list){
                \Db::table('pw_project_stock')->insert([
                    'stock_id'        => $data['stock_id'],
                    'project_id'      => $data['project_id'],
                    'supply_goods_id' => $data['supply_goods_id'],
                    'num'             => $data['num'],
                    'in'              => $data['num'],
                    'freeze'          => '0',
                    'have'            => '0',
                    'offer'            => '0',
                    'extra'           => '0',
                    'create_time'     => time(),
                    'update_time'     => time(),
                    'delete_time'     => 0,
                ]);
            }else{
                \Db::table('pw_project_stock')->where('id', $allocation_list['id'])->update(['num' => $data['num']+$allocation_list['num']]);
            }
            \Db::table('pw_project_stock')->where('id', $project_info['id'])->update(['have' => $project_info['have']-$data['num']]);
            \Db::commit();
        }catch (\Exception $e){
            throw new BaseException(
                [
                    'msg' => '调拨失败！',
                    'errorCode' => 4005
                ]);
            \Db::rollback();
        }
        ProjectService::allocation($data, 11);
        return [
            'code' => '200',
            'msg'  => '调拨成功',
        ];
    }

    //仓库甲供调拨
    public function balance_type_set($data){
        $project_info = \Db::table('pw_project_stock')->field('id, offer')
            ->where([
                'project_id'       => $data['passive_project_id'],
                'stock_id'         => $data['stock_id'],
                'supply_goods_id'  => $data['supply_goods_id']
            ])
            ->where('delete_time', 0)
            ->find();
        if($data['num'] > $project_info['offer']){
            throw new BaseException(
                [
                    'msg' => '超过最大可调拨材料数'.$project_info['offer'].'个',
                    'errorCode' => 301
                ]);
        }
        $need = \Db::table('pw_need')->alias('n')
            ->leftJoin('pw_supply_goods sg','n.goods_id = sg.g_id')
            ->field('n.id')
            ->where([
                'n.project_id' => $data['project_id'],
                'sg.id' => $data['supply_goods_id'],
                'type'  => '2'
            ])
            ->find();
        if(!$need){
            throw new BaseException(
                [
                    'msg' => '暂无该材料的需求',
                    'errorCode' => 301
                ]);
        }
        $allocation_list = \Db::table('pw_project_stock')->field('id, num')->where(['supply_goods_id' => $data['supply_goods_id'], 'project_id' => $data['project_id']])->find();
        \Db::startTrans();
        try {
            if(!$allocation_list){
                \Db::table('pw_project_stock')->insert([
                    'stock_id'        => $data['stock_id'],
                    'project_id'      => $data['project_id'],
                    'supply_goods_id' => $data['supply_goods_id'],
                    'num'             => $data['num'],
                    'in'              => $data['num'],
                    'freeze'          => '0',
                    'have'           => '0',
                    'offer'           => '0',
                    'extra'           => '0',
                    'create_time'     => time(),
                    'update_time'     => time(),
                    'delete_time'     => 0,
                ]);
            }else{
                \Db::table('pw_project_stock')->where('id', $allocation_list['id'])->update(['num' => $data['num']+$allocation_list['num']]);
            }
            \Db::table('pw_project_stock')->where('id', $project_info['id'])->update(['offer' => $project_info['offer']-$data['num']]);
            \Db::commit();
        }catch (\Exception $e){
            throw new BaseException(
                [
                    'msg' => '调拨失败！',
                    'errorCode' => 4005
                ]);
            \Db::rollback();
        }
        ProjectService::allocation($data, 11);
        return [
            'code' => '200',
            'msg'  => '调拨成功',
        ];
    }

    //工程调拨
    public function project_set($data){
        $project_info = \Db::table('pw_project_woker')->field('id, can, get, not, back')
            ->where([
                'project_id'       => $data['passive_project_id'],
                'woker_id'         => $data['passive_woker_id'],
                'supply_goods_id'  => $data['supply_goods_id']
            ])
            ->where('delete_time', 0)
            ->find();
        if($project_info['not'] < $data['num']){
            throw new BaseException(
                [
                    'msg' => '超过最大可调拨材料数'.$project_info['not'],
                    'errorCode' => 301
                ]);
        }
        $need = \Db::table('pw_need')->alias('n')
            ->leftJoin('pw_supply_goods sg','n.goods_id = sg.g_id')
            ->field('n.id')
            ->where([
                'n.project_id' => $data['project_id'],
                'sg.id' => $data['supply_goods_id'],
                'type'  => '1'
            ])
            ->find();
        if(!$need){
            throw new BaseException(
                [
                    'msg' => '暂无该材料的需求',
                    'errorCode' => 301
                ]);
        }
        $allocation_list = \Db::table('pw_project_stock')->field('id, num, in')->where(['supply_goods_id' => $data['supply_goods_id'], 'project_id' => $data['project_id']])->find();
        $project_stock = \Db::table('pw_project_stock')
            ->field('id,stock_id,freeze,in,num')
            ->where([
                'project_id'      => $data['passive_project_id'],
                'supply_goods_id' => $data['supply_goods_id'],
            ])
            ->find();
        $params['stock_id'] = $project_stock['stock_id'];

        $project_stock_data['num'] = $project_stock['num']-$data['num'];
        $project_info_data['not'] = $project_info['not']-$data['num'];
        $project_stock_data['freeze'] = $project_stock['freeze']-$data['num'];
        $allocation_list_data['num'] = $allocation_list['num']+$data['num'];
        $allocation_list_data['in'] = $allocation_list['in']+$data['num'];
        \Db::startTrans();
        try {
            $res = \Db::table('pw_project_woker')->where('id',$project_info['id'])->update($project_info_data);
            $res1 =\Db::table('pw_project_stock')->where('id', $project_stock['id'])->update($project_stock_data);
            if(!$allocation_list){
                $res2 = \Db::table('pw_project_stock')->insert([
                    'stock_id'        => $data['stock_id'],
                    'project_id'      => $data['project_id'],
                    'supply_goods_id' => $data['supply_goods_id'],
                    'num'             => $data['num'],
                    'in'              => $data['num'],
                    'freeze'          => '0',
                    'have'            => '0',
                    'offer'           => '0',
                    'extra'           => '0',
                    'create_time'     => time(),
                    'update_time'     => time(),
                    'delete_time'     => 0,
                ]);
            }else{
                $res2 = \Db::table('pw_project_stock')->where('id', $allocation_list['id'])->update($allocation_list_data);
            }

            \Db::commit();
        }catch (\Exception $e){
            throw new BaseException(
                [
                    'msg' => '调拨失败！',
                    'errorCode' => 4005
                ]);
            \Db::rollback();
        }
        ProjectService::allocation($data, 9);
        return [
            'code' => '200',
            'msg'  => '调拨成功',
        ];
    }

    //工程库存调拨
    public function project_stock_set($data){
        $project_info = \Db::table('pw_project_stock')->field('id, in')
            ->where([
                'project_id'       => $data['passive_project_id'],
                'supply_goods_id'  => $data['supply_goods_id']
            ])
            ->where('delete_time', 0)
            ->find();
        if($project_info['in'] < $data['num']){
            throw new BaseException(
                [
                    'msg' => '超过最大可调拨材料数'.$project_info['in'],
                    'errorCode' => 301
                ]);
        }
        $need = \Db::table('pw_need')->alias('n')
            ->leftJoin('pw_supply_goods sg','n.goods_id = sg.g_id')
            ->field('n.id')
            ->where([
                'n.project_id' => $data['project_id'],
                'sg.id' => $data['supply_goods_id'],
                'type'  => '1'
            ])
            ->find();
        if(!$need){
            throw new BaseException(
                [
                    'msg' => '暂无该材料的需求',
                    'errorCode' => 301
                ]);
        }
        $allocation_list = \Db::table('pw_project_stock')->field('id, num, in')->where(['supply_goods_id' => $data['supply_goods_id'], 'project_id' => $data['project_id']])->find();
        $project_stock = \Db::table('pw_project_stock')
            ->field('id,stock_id,freeze,in,num')
            ->where([
                'project_id'      => $data['passive_project_id'],
                'supply_goods_id' => $data['supply_goods_id'],
            ])
            ->find();
        $params['stock_id'] = $project_stock['stock_id'];

        $project_stock_data['num'] = $project_stock['num']-$data['num'];
        $project_stock_data['in'] = $project_stock['freeze']-$data['num'];
        $allocation_list_data['num'] = $allocation_list['num']+$data['num'];
        $allocation_list_data['in'] = $allocation_list['in']+$data['num'];
        \Db::startTrans();
        try {
            \Db::table('pw_project_stock')->where('id', $project_stock['id'])->update($project_stock_data);
            if(!$allocation_list){
                $res2 = \Db::table('pw_project_stock')->insert([
                    'stock_id'        => $data['stock_id'],
                    'project_id'      => $data['project_id'],
                    'supply_goods_id' => $data['supply_goods_id'],
                    'num'             => $data['num'],
                    'in'              => $data['num'],
                    'freeze'          => '0',
                    'have'            => '0',
                    'offer'           => '0',
                    'extra'           => '0',
                    'create_time'     => time(),
                    'update_time'     => time(),
                    'delete_time'     => 0,
                ]);
            }else{
                $res2 = \Db::table('pw_project_stock')->where('id', $allocation_list['id'])->update($allocation_list_data);
            }

            \Db::commit();
        }catch (\Exception $e){
            throw new BaseException(
                [
                    'msg' => '调拨失败！',
                    'errorCode' => 4005
                ]);
            \Db::rollback();
        }
        ProjectService::allocation($data, 14);
        return [
            'code' => '200',
            'msg'  => '调拨成功',
        ];
    }

    //批量调拨
    public function shopping_all_set($params){
        $arr = array_filter(explode(',',$params['ids']));
        asort($arr);
        $list = ShoppingCartModel::where(['id' => $arr])->select();
        if($list) {
            $list=$list->toArray();
            \Db::startTrans();
            foreach ($list as &$v) {
                if ($v['type'] == 2) {
                    $is_all = $this->balance_all_set($v);
                } elseif ($v['type'] == 3){
                    $is_all = $this->project_stock_all_set($v);
                }else {
                    $is_all = $this->project_all_set($v);
                }
                if($is_all['code'] != 200){
                    \Db::rollback();
                    throw new BaseException(
                        [
                            'msg' => '编号为'.$v['id'].'的清单'.$is_all['msg'].'个',
                            'errorCode' => 301
                        ]);
                    break;
                }
            }
            ShoppingCartModel::where(['id' => $arr])->update(['status' => 2, 'update_time' => time()]);
            \Db::commit();
        }
        return ['code' => 200];
    }

    //仓库调拨
    public function balance_all_set($data){
        $project_info = \Db::table('pw_project_stock')->field('id, have')
            ->where([
                'project_id'       => $data['passive_project_id'],
                'stock_id'         => $data['stock_id'],
                'supply_goods_id'  => $data['supply_goods_id']
            ])
            ->where('delete_time', 0)
            ->find();
        if($data['num'] > $project_info['have']){
            return [
                'msg' => '编号为'.$data['id'].'最大可调拨材料数'.$project_info['have'],
                'code' => 301
            ];
        }
        $allocation_list = \Db::table('pw_project_stock')->field('id, num, in')->where(['supply_goods_id' => $data['supply_goods_id'], 'project_id' => $data['project_id']])->find();
        if(!$allocation_list){
            \Db::table('pw_project_stock')->insert([
                'stock_id'        => $data['stock_id'],
                'project_id'      => $data['project_id'],
                'supply_goods_id' => $data['supply_goods_id'],
                'num'             => $data['num'],
                'in'              => $data['num'],
                'freeze'          => '0',
                'have'            => '0',
                'extra'           => '0',
                'create_time'     => time(),
                'update_time'     => time(),
                'delete_time'     => 0,
            ]);
        }else{
            \Db::table('pw_project_stock')->where('id', $allocation_list['id'])->update(['num' => $data['num']+$allocation_list['num'], 'in' => $data['num']+$allocation_list['in']]);
        }
        \Db::table('pw_project_stock')->where('id', $project_info['id'])->update(['have' => $project_info['have']-$data['num']]);
        ProjectService::allocation($data, 11);
        return [
            'code' => '200',
            'msg'  => '调拨成功',
        ];
    }

    //工程调拨
    public function project_all_set($data){
        $project_info = \Db::table('pw_project_woker')->field('id, can, get, not, back')
            ->where([
                'project_id'       => $data['passive_project_id'],
                'woker_id'         => $data['passive_woker_id'],
                'supply_goods_id'  => $data['supply_goods_id']
            ])
            ->where('delete_time', 0)
            ->find();
        if($project_info['not'] < $data['num']){
            return [
                'msg' => '编号为'.$data['id'].'超过最大可调拨材料数'.$project_info['not'],
                'code' => 301
            ];
        }
        $need = \Db::table('pw_need')->alias('n')
            ->leftJoin('pw_supply_goods sg','n.goods_id = sg.g_id')
            ->field('n.id')
            ->where([
                'n.project_id' => $data['project_id'],
                'sg.id' => $data['supply_goods_id'],
                'n.type' => '1',
            ])
            ->find();
        if(!$need){
            throw new BaseException(
                [
                    'msg' => '暂无该材料的需求',
                    'errorCode' => 301
                ]);
        }
        $allocation_list = \Db::table('pw_project_stock')->field('id, num,in')->where(['supply_goods_id' => $data['supply_goods_id'], 'project_id' => $data['project_id']])->find();
        $project_stock = \Db::table('pw_project_stock')
            ->field('id,stock_id,freeze,in,num')
            ->where([
                'project_id'      => $data['passive_project_id'],
                'supply_goods_id' => $data['supply_goods_id'],
            ])
            ->find();
        $params['stock_id'] = $project_stock['stock_id'];

        $project_stock_data['num'] = $project_stock['num']-$data['num'];
        $project_info_data['not'] = $project_info['not']-$data['num'];
        $project_stock_data['freeze'] = $project_stock['freeze']-$data['num'];
        $allocation_list_data['num'] = $allocation_list['num']+$data['num'];
        $allocation_list_data['in'] = $allocation_list['in']+$data['num'];
        $res = \Db::table('pw_project_woker')->where('id',$project_info['id'])->update($project_info_data);
        $res1 =\Db::table('pw_project_stock')->where('id', $project_stock['id'])->update($project_stock_data);
        if(!$allocation_list){
            $res2 = \Db::table('pw_project_stock')->insert([
                'stock_id'        => $data['stock_id'],
                'project_id'      => $data['project_id'],
                'supply_goods_id' => $data['supply_goods_id'],
                'num'             => $data['num'],
                'in'              => $data['num'],
                'freeze'          => '0',
                'have'            => '0',
                'extra'           => '0',
                'create_time'     => time(),
                'update_time'     => time(),
                'delete_time'     => 0,
            ]);

        }else{
            $res2 = \Db::table('pw_project_stock')->where('id', $allocation_list['id'])->update($allocation_list_data);
        }

        ProjectService::allocation($data, 9);
        return [
            'code' => '200',
            'msg'  => '调拨成功',
        ];
    }

    public function project_stock_all_set($data){
        $project_info = \Db::table('pw_project_stock')->field('id, in')
            ->where([
                'project_id'       => $data['passive_project_id'],
                'supply_goods_id'  => $data['supply_goods_id']
            ])
            ->where('delete_time', 0)
            ->find();
        if($project_info['in'] < $data['num']){
            return [
                'msg' => '编号为'.$data['id'].'超过最大可调拨材料数'.$project_info['in'],
                'code' => 301
            ];
        }
        $need = \Db::table('pw_need')->alias('n')
            ->leftJoin('pw_supply_goods sg','n.goods_id = sg.g_id')
            ->field('n.id')
            ->where([
                'n.project_id' => $data['project_id'],
                'sg.id' => $data['supply_goods_id'],
                'n.type'  => '1'
            ])
            ->find();
        if(!$need){
            throw new BaseException(
                [
                    'msg' => '暂无该材料的需求',
                    'errorCode' => 301
                ]);
        }
        $allocation_list = \Db::table('pw_project_stock')->field('id, num,in')->where(['supply_goods_id' => $data['supply_goods_id'], 'project_id' => $data['project_id']])->find();
        $project_stock = \Db::table('pw_project_stock')
            ->field('id,stock_id,freeze,in,num')
            ->where([
                'project_id'      => $data['passive_project_id'],
                'supply_goods_id' => $data['supply_goods_id'],
            ])
            ->find();
        $params['stock_id'] = $project_stock['stock_id'];

        $project_stock_data['num'] = $project_stock['num']-$data['num'];
        $project_stock_data['in'] = $project_stock['in']-$data['num'];
        $allocation_list_data['num'] = $allocation_list['num']+$data['num'];
        $allocation_list_data['in'] = $allocation_list['in']+$data['num'];
        \Db::table('pw_project_stock')->where('id', $project_stock['id'])->update($project_stock_data);
        if(!$allocation_list){
            $res2 = \Db::table('pw_project_stock')->insert([
                'stock_id'        => $data['stock_id'],
                'project_id'      => $data['project_id'],
                'supply_goods_id' => $data['supply_goods_id'],
                'num'             => $data['num'],
                'in'              => $data['num'],
                'freeze'          => '0',
                'have'            => '0',
                'extra'           => '0',
                'create_time'     => time(),
                'update_time'     => time(),
                'delete_time'     => 0,
            ]);

        }else{
            $res2 = \Db::table('pw_project_stock')->where('id', $allocation_list['id'])->update($allocation_list_data);
        }

        ProjectService::allocation($data, 14);
        return [
            'code' => '200',
            'msg'  => '调拨成功',
        ];
    }



    public function shopping_cart_add($params){
        if($params['type_id'] == 1) {
            $params['passive_woker_id'] = $params['passive_woker_id'];
            $params['supply_goods_id'] = $params['supply_goods_id'];
            $need = \Db::table('pw_need')->alias('n')
                ->leftJoin('pw_supply_goods sg','n.goods_id = sg.g_id')
                ->field('n.id')
                ->where([
                    'n.project_id' => $params['project_id'],
                    'sg.id' => $params['supply_goods_id'],
                    'type'  => '1'
                ])
                ->find();
            if(!$need){
                throw new BaseException(
                    [
                        'msg' => '暂无该材料的需求',
                        'errorCode' => 301
                    ]);
            }
            $project_info = \Db::table('pw_project_woker')->field('id, can, get, not, back')
                ->where ([
                    'project_id'       => $params['passive_project_id'],
                    'woker_id'        => $params['passive_woker_id'],
                    'supply_goods_id' => $params['supply_goods_id']
                ])
                ->where('delete_time', 0)
                ->find();
            if ($params['num'] == 0 || empty($params['num'])) {
                if ($project_info['can'] - $project_info['back'] < $params['num']) {
                    throw new BaseException(
                        [
                            'msg' => '领取项目材料数大于0！',
                            'errorCode' => 301
                        ]);
                }
            }
            if ($project_info['not'] < $params['num']) {
                throw new BaseException(
                    [
                        'msg' => '超过项目最大可领材料数'.$project_info['not'].'个',
                        'errorCode' => 301
                    ]);
            }
            $project_stock = \Db::table('pw_project_stock')
                ->field('id,stock_id,freeze,in,num')
                ->where([
                    'project_id' => $params['passive_project_id'],
                    'supply_goods_id' => $params['supply_goods_id'],
                ])
                ->find();
            $params['stock_id'] = $project_stock['stock_id'];
            $data = [
                'passive_project_id' => $params['passive_project_id'],
                'passive_woker_id' => $params['passive_woker_id'],
                'supply_goods_id' => $params['supply_goods_id'],
                'stock_id' => $params['stock_id'],
                'project_id' => $params['project_id'],
                'num' => $params['num'],
                'type' => 1,
                'status' => 1,
                'note' => '',
                'user_id' => session('power_user.company_id'),
                'create_time' => time(),
                'update_time' => time(),
            ];
            $res = ShoppingCartModel::insert($data);
        }elseif ($params['type_id'] == 3){
            $params['passive_woker_id'] = $params['passive_woker_id'];
            $params['supply_goods_id'] = $params['supply_goods_id'];
            $need = \Db::table('pw_need')->alias('n')
                ->leftJoin('pw_supply_goods sg','n.goods_id = sg.g_id')
                ->field('n.id')
                ->where([
                    'n.project_id' => $params['project_id'],
                    'sg.id' => $params['supply_goods_id'],
                    'type'  => '1'
                ])
                ->find();
            if(!$need){
                throw new BaseException(
                    [
                        'msg' => '暂无该材料的需求',
                        'errorCode' => 301
                    ]);
            }
            $project_info = \Db::table('pw_project_stock')->field('id, in')
                ->where ([
                    'project_id'       => $params['passive_project_id'],
                    'supply_goods_id' => $params['supply_goods_id']
                ])
                ->where('delete_time', 0)
                ->find();
            if ($params['num'] == 0 || empty($params['num'])) {
                if ($project_info['can'] - $project_info['back'] < $params['num']) {
                    throw new BaseException(
                        [
                            'msg' => '领取项目材料数大于0！',
                            'errorCode' => 301
                        ]);
                }
            }
            if ($project_info['in'] < $params['num']) {
                throw new BaseException(
                    [
                        'msg' => '超过项目最大可领材料数'.$project_info['in'].'个',
                        'errorCode' => 301
                    ]);
            }
            $project_stock = \Db::table('pw_project_stock')
                ->field('id,stock_id,freeze,in,num')
                ->where([
                    'project_id' => $params['passive_project_id'],
                    'supply_goods_id' => $params['supply_goods_id'],
                ])
                ->find();
            $params['stock_id'] = $project_stock['stock_id'];
            $data = [
                'passive_project_id' => $params['passive_project_id'],
                'passive_woker_id' => '',
                'supply_goods_id' => $params['supply_goods_id'],
                'stock_id' => $params['stock_id'],
                'project_id' => $params['project_id'],
                'num' => $params['num'],
                'type' => 3,
                'status' => 1,
                'note' => '',
                'user_id' => session('power_user.company_id'),
                'create_time' => time(),
                'update_time' => time(),
            ];
            $res = ShoppingCartModel::insert($data);
        }else{
            $need = \Db::table('pw_need')->alias('n')
                ->leftJoin('pw_supply_goods sg','n.goods_id = sg.g_id')
                ->field('n.id')
                ->where([
                    'n.project_id' => $params['project_id'],
                    'sg.id' => $params['supply_goods_id'],
                    'type'  => '2'
                ])
                ->find();
            if(!$need){
                throw new BaseException(
                    [
                        'msg' => '暂无该材料的需求',
                        'errorCode' => 301
                    ]);
            }
            $project_info = \Db::table('pw_project_stock')->field('id, have')
                ->where([
                    'project_id'       => $params['passive_project_id'],
                    'stock_id'         => $params['stock_id'],
                    'supply_goods_id'  => $params['supply_goods_id']
                ])
                ->where('delete_time', 0)
                ->find();
            if($params['num'] > $project_info['have']){
                throw new BaseException(
                    [
                        'msg' => '超过可调拨材料数'.$project_info['have'],
                        'errorCode' => 301
                    ]);
            }
            $data = [
                'passive_project_id' => $params['passive_project_id'],
                'passive_woker_id' => '',
                'supply_goods_id' => $params['supply_goods_id'],
                'stock_id' => $params['stock_id'],
                'project_id' => $params['project_id'],
                'num' => $params['num'],
                'type' => 2,
                'status' => 1,
                'note' => '',
                'user_id' => session('power_user.company_id'),
                'create_time' => time(),
                'update_time' => time(),
            ];
            $res = ShoppingCartModel::insert($data);
        }
        if($res){
            $return = [
                'msg' => '添加清单成功',
            ];
            return $return;
        }else{
            throw new BaseException(
                [
                    'msg' => '添加调拨清单失败',
                    'errorCode' => 4001
                ]);
        }
    }

    public function allocation_set($params){
        if($params['type_id'] == 1){
            if(!empty($params['woker_goods_id'])){
                $arr = explode(',', $params['woker_goods_id']);
                $params['passive_woker_id'] = $arr['1'];
                $params['supply_goods_id'] = $arr['0'];
            }
            $project_info = \Db::table('pw_project_woker')->field('id, can, get, not, back')
                                ->where([
                                        'project_id'       => $params['passive_project_id'],
                                         'woker_id'        => $params['passive_woker_id'],
                                         'supply_goods_id' => $params['supply_goods_id']
                                        ])
                                ->where('delete_time', 0)
                                ->find();
            if($params['num'] == 0 || empty($params['num'])){
                    throw new BaseException(
                        [
                            'msg' => '项目材料数大于0！',
                            'errorCode' => 301
                        ]);
            }
            if($project_info['not'] < $params['num']){
                throw new BaseException(
                    [
                        'msg' => '超过项目可领材料数'.$project_info['not'],
                        'errorCode' => 301
                    ]);
            }
            $allocation_list = \Db::table('pw_project_stock')->field('id, num')->where(['supply_goods_id' => $params['supply_goods_id'], 'project_id' => $params['project_id']])->find();
            $project_stock = \Db::table('pw_project_stock')
                    ->field('id,stock_id,freeze,in,num')
                    ->where([
                                'project_id'      => $params['passive_project_id'],
                                'supply_goods_id' => $params['supply_goods_id'],
                            ])
                    ->find();
            $params['stock_id'] = $project_stock['stock_id'];

            $project_stock_data['num'] = $project_stock['num']-$params['num'];
            $project_info_data['not'] = $project_info['not']-$params['num'];
            $project_stock_data['freeze'] = $project_stock['freeze']-$params['num'];
            $allocation_list_data['num'] = $allocation_list['num']+$params['num'];
            $allocation_list_data['in'] = $allocation_list['in']+$params['num'];
            \Db::startTrans();
            try {
                $res = \Db::table('pw_project_woker')->where('id',$project_info['id'])->update($project_info_data);
                $res1 =\Db::table('pw_project_stock')->where('id', $project_stock['id'])->update($project_stock_data);
                if(!$allocation_list){
                    $res2 = \Db::table('pw_project_stock')->insert([
                        'stock_id'        => $params['stock_id'],
                        'project_id'      => $params['project_id'],
                        'supply_goods_id' => $params['supply_goods_id'],
                        'num'             => $params['num'],
                        'in'              => $params['num'],
                        'freeze'          => '0',
                        'have'            => '0',
                        'extra'           => '0',
                        'create_time'     => time(),
                        'update_time'     => time(),
                        'delete_time'     => 0,
                    ]);
                }else{
                    $res2 = \Db::table('pw_project_stock')->where('id', $allocation_list['id'])->update($allocation_list_data);
                }

                \Db::commit();
            }catch (\Exception $e){
                throw new BaseException(
                    [
                        'msg' => '调拨失败！',
                        'errorCode' => 4005
                    ]);
                \Db::rollback();
            }
        }else{
            $params['supply_goods_id'] = $params['woker_goods_id'];
            $project_info = \Db::table('pw_project_stock')->field('id, have')
                ->where([
                    'project_id'       => $params['passive_project_id'],
                    'stock_id'         => $params['stock_id'],
                    'supply_goods_id'  => $params['supply_goods_id']
                ])
                ->where('delete_time', 0)
                ->find();
            if($params['num'] > $project_info['have']){
                throw new BaseException(
                    [
                        'msg' => '超过可调拨材料数'.$project_info['have'],
                        'errorCode' => 301
                    ]);
            }
            $allocation_list = \Db::table('pw_project_stock')->field('id, num')->where(['supply_goods_id' => $params['supply_goods_id'], 'project_id' => $params['project_id']])->find();
            if(!$allocation_list){
                \Db::table('pw_project_stock')->insert([
                    'stock_id'        => $params['stock_id'],
                    'project_id'      => $params['project_id'],
                    'supply_goods_id' => $params['supply_goods_id'],
                    'num'             => $params['num'],
                    'in'              => $params['num'],
                    'freeze'          => '0',
                    'have'            => '0',
                    'extra'           => '0',
                    'create_time'     => time(),
                    'update_time'     => time(),
                    'delete_time'     => 0,
                ]);
            }else{
                \Db::table('pw_project_stock')->where('id', $allocation_list['id'])->update(['num' => $params['num']+$allocation_list['num']]);
            }
            \Db::table('pw_project_stock')->where('id', $project_info['id'])->update(['have' => $project_info['have']-$params['num']]);

        }
        ProjectService::allocation($params, $params['type_id']);
        return [
            'msg' => '调拨成功',
        ];
    }

} 