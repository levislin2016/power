<?php
namespace app\index\service;

use app\index\model\Project as ProjectModel;
use app\index\model\ProjectEnd as ProjectEndModel;
use app\index\model\ProjectEndInfo as ProjectEndInfoModel;
use app\index\model\Need as ProjectStockModel;
use app\index\model\ProjectWoker as ProjectWokerModel;
use app\index\model\StockAll as StockAllModel;
use app\index\model\StockOrder as StockOrderModel;
use app\index\model\StockOrderInfo as StockOrderInfoModel;
use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\index\model\Need as NeedModel;
use app\index\model\Buy as BuyModel;
use app\lib\exception\BaseException;

class Balance{
    public function create_balance($id){
        $list = $this->select_balance($id);
        foreach($list as $vo){
            $project_end = ProjectEndModel::create([
                'project_id'        =>  $id,
                'supply_goods_id'   =>  $vo['supply_goods_id'],
                'need'              =>  $vo['need'],
                'from'              =>  $vo['from'],
                'buy_num'           =>  $vo['buy_num'],
                'project_num'       =>  $vo['project_num'],
                'have_num'          =>  $vo['have_num'],
                'get'               =>  $vo['get'],
                'status'            =>  1,
                'back_time'         =>  0
            ]);
            if(!$project_end){
                return [
                    'msg' => '结算信息创建失败',
                    'errorCode' => 44001
                ];
            }
            foreach($vo['arr'] as $vo2){
                $project_end_info = ProjectEndInfoModel::create([
                    'project_end_id'    =>  $project_end->id,
                    'project_stock_id'  =>  $vo2['id'],
                    'stock_id'          =>  $vo2['stock_id'],
                    // 'buy_num1'          =>  $vo2['buy_num_1'],
                    // 'buy_num2'          =>  $vo2['buy_num_2'],
                    // 'project_num'       =>  $vo2['project_num'],
                    // 'have_num'          =>  $vo2['have_num'],
                    'have'              =>  $vo2['num'],
                    'num'               =>  0
                ]);
                if(!$project_end_info){
                    return [
                        'msg' => '结算详情创建失败',
                        'errorCode' => 44002
                    ];
                }
            }
        }
        return [
            'msg' => '结算信息创建成功',
            'errorCode' => 0
        ];
    }

    protected function select_balance($id){
        $project = ProjectModel::get($id);
        $list = ProjectStockModel::useGlobalScope(false)->alias('ps')
            ->leftJoin('supply_goods sg','ps.supply_goods_id = sg.id')
            ->leftJoin('supply s','sg.s_id = s.id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->group('ps.supply_goods_id')
            ->where('ps.project_id', $project->id)
            ->field('count(ps.id) as count, sum(ps.num) as num, ps.supply_goods_id, g.number, g.id as goods_id, s.id as supply_id')
            ->select();

        foreach($list as &$vo){
            //各个仓库结余
            $vo->arr = ProjectStockModel::useGlobalScope(false)->alias('ps')
                ->where('ps.project_id', $project->id)
                ->where('ps.supply_goods_id', $vo->supply_goods_id)
                ->select();

            //需求数
            $need = NeedModel::where('project_id', $project->id)->where('goods_id',$vo->goods_id)->find();
            $vo->need = $need->need;
            $vo->from = $need->type;

            //订货数
            $vo->buy_num = StockOrderModel::useGlobalScope(false)->alias('so')
                ->leftJoin('stock_order_info soi','so.id = soi.stock_order_id')
                ->leftJoin('buy b','so.number = b.number')
                ->where('so.type', 1)
                ->where('soi.supply_goods_id', $vo->supply_goods_id)
                ->where('so.project_id', $project->id)
                ->where('b.from', $vo->from)
                ->sum('soi.num');
            
            
            //平衡利库存
            $project_nums = StockOrderModel::useGlobalScope(false)->alias('so')
                ->leftJoin('stock_order_info soi','so.id = soi.stock_order_id')
                ->where('so.project_id', $project->id)
                ->where('so.type', 'in', '9,10,11,12')
                ->where('so.status', 2)
                ->where('soi.supply_goods_id', $vo->supply_goods_id)
                ->group('so.type')
                ->field('so.type, sum(soi.num) as num')
                ->select();
            $project_num['9'] = $project_num['10'] = $project_num['11'] = $project_num['12'] = 0;
            foreach($project_nums as $project_num_v){
                $project_num[$project_num_v->type] = $project_num_v->num;
            }
            $vo->project_num = $project_num['10'] - $project_num['9'];
            $vo->have_num = $project_num['12'] - $project_num['11'];

            //已使用
            $get = ProjectWokerModel::where('project_id', $project->id)->where('supply_goods_id', $vo->supply_goods_id)->sum('get');
            $back = ProjectWokerModel::where('project_id', $project->id)->where('supply_goods_id', $vo->supply_goods_id)->sum('back');
            $vo->get = $get - $back;
        }

        return $list;
    }

    public function balance_operation($params){
        ProjectModel::startTrans();
        $pk_list = array();
        foreach($params['num'] as $num){
            //修改项目结算详情表
            $project_end_info = ProjectEndInfoModel::get($num['id']);
            $project_end_info->num = $num['val'];
            $res = $project_end_info->save();
            if(!$res){
                ProjectModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '修改项目结算详情错误',
                        'errorCode' => 44003
                    ]);
            }
            $project_end = ProjectEndModel::get($project_end_info->project_end_id);

            //判断结余库存是自购还是结余
            $have = $offer = 0;
            if($project_end->from == 1){
                $have = $num['val'];
            }
            if($project_end->from == 2){
                $offer = $num['val'];
            }
            
            //修改项目库存表
            $project_stock = ProjectStockModel::get($project_end_info->project_stock_id);
            $project_stock->have = $have;
            $project_stock->offer = $offer;
            $project_stock->in = 0;
            $pk_freeze = $project_stock->freeze;
            $project_stock->freeze = 0;
            $pk_num = $num['val'] - $project_stock->num;
            $project_stock->num = $num['val'];
            $res = $project_stock->save();
            if(!$res){
                ProjectModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '修改项目库存错误',
                        'errorCode' => 44004
                    ]);
            }

            

            //盘库出入库
            if($pk_num != 0){
                if($pk_num > 0){
                    $pk_type = 5;
                }else{
                    $pk_type = 6;
                }
                $pk_list[$pk_type][$project_stock->stock_id][] = array(
                    'supply_goods_id'   =>  $project_stock->supply_goods_id,
                    'price'             =>  SupplyGoodsModel::where('id', $project_stock->supply_goods_id)->value('price'),
                    'num'               =>  abs($pk_num)
                );      
            }

            //修改总库存表
            if($pk_freeze > 0 || ($pk_num != 0 && $num['val'] > 0)){
                $stock_all = StockAllModel::where('company_id', session('power_user.company_id'))->where('stock_id', $project_stock->stock_id)->where('supply_goods_id', $project_stock->supply_goods_id)->find();
                $stock_all->freeze = $stock_all->freeze - $pk_freeze;
                $stock_all->have = $stock_all->have + $num['val'];
                $stock_all->num = $stock_all->num + $pk_num;
                $res = $stock_all->save();
                if(!$res){
                    ProjectModel::rollback();
                    throw new BaseException(
                        [
                            'msg' => '修改总库存错误',
                            'errorCode' => 44005
                        ]);
                }
            }
    
        }

        //创建盘库出入库记录
        foreach($pk_list as $k => $pks){
            foreach($pks as $j => $pk){
                $stock_order = StockOrderModel::create([
                    'contract_id'       =>  ProjectModel::where('id', $params['project_id'])->value('contract_id'),
                    'company_id'        =>  session('power_user.company_id'),
                    'number'            =>  create_order_no('D'),
                    'stock_id'          =>  $j,
                    'project_id'        =>  $params['project_id'],
                    'woker_id'          =>  0,
                    'user_id'           =>  session('power_user.id'),
                    'type'              =>  $k,
                    'status'            =>  2,
                    'note'              =>  ''
                ]);
                
                if(!$stock_order){
                    ProjectModel::rollback();
                    throw new BaseException(
                        [
                            'msg' => '订单创建错误！',
                            'errorCode' => 44006
                        ]);
                }

                foreach($pk as $v){
                    $stock_order_info = StockOrderInfoModel::create([
                        'stock_order_id'    =>  $stock_order->id,
                        'supply_goods_id'   =>  $v['supply_goods_id'],
                        'woker_id'          =>  0,
                        'price'             =>  $v['price'],
                        'num'               =>  $v['num']
                    ]);
                    if(!$stock_order_info){
                        ProjectModel::rollback();
                        throw new BaseException(
                            [
                                'msg' => '订单详情创建错误！',
                                'errorCode' => 44007
                            ]);
                    }
                }
            }
        }

        //修改项目结算表
        $res = ProjectEndModel::where('project_id', $params['project_id'])->update([
            'status'    =>  2,
            'back_time' =>  time()
        ]);
        if(!$res){
            ProjectModel::rollback();
            throw new BaseException(
                [
                    'msg' => '修改项目结算错误！',
                    'errorCode' => 44008
                ]);
        }

        //修改项目状态
        $res = ProjectModel::where('id', $params['project_id'])->update([
            'status'    =>  4
        ]);
        if(!$res){
            ProjectModel::rollback();
            throw new BaseException(
                [
                    'msg' => '修改项目状态错误！',
                    'errorCode' => 44009
                ]);
        }
        ProjectModel::commit();
        return [
            'msg' => '结算盘库成功'
        ];
    }

    public function balance_back($params){
        ProjectModel::startTrans();
        $pk_list = array();
        foreach($params['num'] as $num){
            //修改结算详情表的退回数量
            $project_end_info = ProjectEndInfoModel::get($num['id']);
            $project_end_info->back = $project_end_info->back + $num['val'];
            $res = $project_end_info->save();
            if(!$res){
                ProjectModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '修改结算详情表的退回数量错误',
                        'errorCode' => 44010
                    ]);
            }

            //修改项目库存的数量
            $project_stock = ProjectStockModel::get($project_end_info->project_stock_id);
            $project_stock->offer = $project_stock->offer - $num['val'];
            $project_stock->num = $project_stock->num - $num['val'];
            if($project_stock->offer < 0){
                ProjectModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '库存数量不足',
                        'errorCode' => 44011
                    ]);
            }
            $res = $project_stock->save();
            if(!$res){
                ProjectModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '修改项目库存的数量错误',
                        'errorCode' => 44012
                    ]);
            }

            $pk_list[$project_stock->stock_id][] = array(
                'supply_goods_id'   =>  $project_stock->supply_goods_id,
                'price'             =>  SupplyGoodsModel::where('id', $project_stock->supply_goods_id)->value('price'),
                'num'               =>  $num['val']
            );

            //修改总库存数量
            $stock_all = StockAllModel::where('company_id', session('power_user.company_id'))->where('stock_id', $project_stock->stock_id)->where('supply_goods_id', $project_stock->supply_goods_id)->find();
            $stock_all->have = $stock_all->have - $num['val'];
            $stock_all->num = $stock_all->num - $num['val'];
            $res = $stock_all->save();
            if(!$res){
                ProjectModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '修改总库存错误',
                        'errorCode' => 44013
                    ]);
            }
        }

        //创建退还记录
        foreach($pk_list as $k => $pk){
            $stock_order = StockOrderModel::create([
                'contract_id'       =>  ProjectModel::where('id', $params['project_id'])->value('contract_id'),
                'company_id'        =>  session('power_user.company_id'),
                'number'            =>  create_order_no('K'),
                'stock_id'          =>  $k,
                'project_id'        =>  $params['project_id'],
                'woker_id'          =>  0,
                'user_id'           =>  session('power_user.id'),
                'type'              =>  13,
                'status'            =>  2,
                'note'              =>  ''
            ]);
            
            if(!$stock_order){
                ProjectModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '订单创建错误！',
                        'errorCode' => 44014
                    ]);
            }

            foreach($pk as $v){
                $stock_order_info = StockOrderInfoModel::create([
                    'stock_order_id'    =>  $stock_order->id,
                    'supply_goods_id'   =>  $v['supply_goods_id'],
                    'woker_id'          =>  0,
                    'price'             =>  $v['price'],
                    'num'               =>  $v['num']
                ]);
                if(!$stock_order_info){
                    ProjectModel::rollback();
                    throw new BaseException(
                        [
                            'msg' => '订单详情创建错误！',
                            'errorCode' => 44015
                        ]);
                }
            }
        }

        ProjectModel::commit();
        return [
            'msg' => '退还成功'
        ];
    }

}