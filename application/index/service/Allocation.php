<?php
namespace app\index\service;

use app\index\model\Project as ProjectModel;
use app\index\model\ProjectStock as ProjectStockModel;
use app\index\service\Project as ProjectService;
use app\index\model\StockOrder as StockOrderModel;
use app\index\model\StockOrderInfo as StockOrderInfoModel;
use app\index\model\ProjectWoker as ProjectWokerModel;
use app\index\model\StockOrderInfo;
use app\lib\exception\BaseException;
use think\Db;

class Allocation{

    public function project_list($params){
        $list = ProjectModel::useGlobalScope(false)->alias('p')
            ->leftJoin('contract c','p.contract_id = c.id')
            ->leftJoin('owner o','c.owner_id = o.id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){
                    $query->where('c.number|p.name', 'like', '%'.$params['search'].'%');
                }
                $query->where('status', 2);
            })
            ->field('p.id, p.company_id, p.contract_id, c.number as contract_number, p.name, p.status, p.create_time, o.name as owner_name')
            ->order('p.create_time', 'desc')
            ->paginate(10, false, [
                'query'     => $params,
            ]);
        foreach ($list as &$v){
            $project_list = \Db::table('pw_project_woker')->alias('pw')
                ->leftJoin('woker w','w.id = pw.woker_id')
                ->leftJoin('supply_goods sg','sg.id = pw.supply_goods_id')
                ->leftJoin('goods g','g.id = sg.g_id')
                ->field('pw.id, pw.project_id, pw.woker_id, w.name as woker_name, pw.supply_goods_id, pw.not as not_num, g.name as goods_name')
                ->where([
                    'pw.project_id'  => $v['id'],
                    'pw.delete_time' => 0
                ])
                ->select();
            $project_list = $project_list->toArray();
            foreach ($project_list as &$val){
                $stock_list = \Db::table('pw_stock_order')->alias('so')
                    ->leftJoin('stock_order_info soi','so.id = soi.stock_order_id')
                    ->field('so.supply_goods_id, soi.num')
                    ->where([
                        'so.woker_id'         => $val['woker_id'],
                        'so.project_id'       => $val['project_id'],
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

    public function balance_list($params){
        $list = ProjectStockModel::useGlobalScope(false)->alias('ps')
            ->leftJoin('stock s','s.id = ps.stock_id ')
            ->leftJoin('supply_goods sg','sg.id = ps.supply_goods_id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('project p','p.id = ps.project_id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){
                    $query->where('p.name', 'like', '%'.$params['search'].'%');
                }
                $query->where('ps.have', '>', 0);
            })
            ->field('p.id, ps.have, p.name as project_name, ps.stock_id, g.name as supply_goods_name,sg.id as supply_goods_id, p.name, s.name as stock_name')
            ->order('p.create_time', 'desc')
            ->paginate(10, false, [
                'query'     => $params,
            ]);

        return $list;
    }

    public function allocation_set($params){
        if($params['type_id'] == 9){
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
                if($project_info['can']-$project_info['back'] < $params['num']){
                    throw new BaseException(
                        [
                            'msg' => '项目材料数大于0！',
                            'errorCode' => 301
                        ]);
                }
            }
            if($project_info['not'] < $params['num']){
                throw new BaseException(
                    [
                        'msg' => '超过项目可领材料数！',
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
                        'msg' => '超过可调拨材料数！',
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