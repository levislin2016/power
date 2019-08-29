<?php
namespace app\index\service;

use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\index\model\Goods as GoodsModel;
use app\index\model\Buy as BuyModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\StockOrder as StockOrderModel;
use app\index\model\StockOrderInfo as StockOrderInfoModel;
use app\index\model\Project as ProjectModel;
use app\index\model\StockAll as StockAllModel;
use app\index\model\ProjectStock as ProjectStockModel;
use app\lib\exception\BaseException;

class Buy{

    public function create_buy_order($params){

        BuyModel::startTrans();
        
        $project = ProjectModel::get($params['project_id']);
        if($project->status > 2){
            BuyModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '非法操作！',
                        'errorCode' => 61001
                    ]);
        }
        $status = 1;
        $type = 1;
        if($params['type'] == 2){
            $status = 5;
            $type = 2;
        }

        $params['supply_id'] = $params['supply_id'] ? $params['supply_id'] : 0;

        //创建采购订单
        $buy = BuyModel::create([
            'company_id'    =>  session('power_user.company_id'),
            'user_id'       =>  session('power_user.id'),
            'number'        =>  create_order_no('C'),
            'buy_contract'  =>  $params['buy_contract'],
            'supply_id'     =>  $params['supply_id'],
            'project_id'    =>  $params['project_id'],
            'status'        =>  $status,
            'type'          =>  $type,
            'from'          =>  $params['from'],
            'note'          =>  $params['note']
        ]);
        if(!$buy){
            BuyModel::rollback();
            throw new BaseException(
                [
                    'msg' => '订单创建错误！',
                    'errorCode' => 61001
                ]);
        }

        foreach($params['num'] as $num){
            //创建采购订单详情
            $buyInfo = BuyInfoModel::create([
                'buy_id'        =>  $buy->id,
                'goods_id'      =>  $num['id'],
                // 'supply_id'     =>  $num['sp'],
                // 'price'         =>  SupplyGoodsModel::where('s_id', $num['sp'])->where('g_id', $num['id'])->value('price'),
                'supply_id'     =>  $params['supply_id'],
                'price'         =>  $num['price'] * 100,
                'num'           =>  $num['val'],
                'num_ok'        =>  0
            ]);
            if(!$buyInfo){
                BuyModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '订单详情创建错误！',
                        'errorCode' => 61002
                    ]);
            }

            //创建供应商材料关联
            $supplyGoods = SupplyGoodsModel::where('s_id', $params['supply_id'])->where('g_id', $num['id'])->find();
            if(!$supplyGoods){
                $supplyGoods_res = SupplyGoodsModel::create([
                    's_id'      =>  $params['supply_id'],
                    'g_id'      =>  $num['id'],
                    'price'     =>  $num['price'] * 100,
                    'note'      =>  ''
                    
                ]);
                if(!$supplyGoods_res){
                    BuyModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '创建供应商材料关联错误！',
                        'errorCode' => 61003
                    ]);
                }
            }
            
        }


        BuyModel::commit();
        return [
            'msg' => '创建采购单成功',
        ];
    }

    public function create_put($params){
        BuyModel::startTrans();
        $buy = BuyModel::get($params['buy_id']);
        if($buy->status > 2){
            BuyModel::rollback();
            throw new BaseException(
                [
                    'msg' => '非法操作',
                    'errorCode' => 62001
                ]);
        }
        
        //创建入库单
        $stockOrder = StockOrderModel::create([
            'contract_id'       =>  ProjectModel::where('id', $buy->project_id)->value('contract_id'),
            'company_id'        =>  session('power_user.company_id'),
            'number'            =>  $buy->number,
            'stock_id'          =>  $params['stock_id'],
            'project_id'        =>  $buy->project_id,
            'woker_id'          =>  0,
            'user_id'           =>  session('power_user.id'),
            'type'              =>  1,
            'status'            =>  2,
            'note'              =>  ''
        ]);

        if(!$stockOrder){
            BuyModel::rollback();
            throw new BaseException(
                [
                    'msg' => '入库单创建错误',
                    'errorCode' => 62002
                ]);
        }

        foreach($params['num'] as $num){
            //修改采购详情累计入库数量
            $buyInfo = BuyInfoModel::where('id', $num['id'])->where('buy_id', $buy->id)->find();
            $buyInfo->num_ok = $buyInfo->num_ok + $num['val'];
            if($buyInfo->num_ok > $buyInfo->num){
                BuyModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '累计入库数量大于采购数量',
                        'errorCode' => 62003
                    ]);
            }
            $res = $buyInfo->save();
            if(!$res){
                BuyModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '累计入库数量错误',
                        'errorCode' => 62004
                    ]);
            }

            $supplyGoods = SupplyGoodsModel::where('s_id', $buyInfo->supply_id)->where('g_id', $buyInfo->goods_id)->find();
            // $supplyGoods = GoodsModel::get($buyInfo->goods_id);

            //创建入库单详情
            $stockOrderInfo = StockOrderInfoModel::create([
                'stock_order_id'    =>  $stockOrder->id,
                'supply_goods_id'   =>  $supplyGoods->id,
                'woker_id'          =>  0,
                'price'             =>  $buyInfo->price,
                'num'               =>  $num['val'],
                
            ]);
            if(!$stockOrderInfo){
                BuyModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '创建入库单详情错误',
                        'errorCode' => 62005
                    ]);
            }

            //修改库存
            $projectStock = ProjectStockModel::where('stock_id', $params['stock_id'])->where('project_id', $buy->project_id)->where('supply_goods_id', $supplyGoods->id)->find();
            if(!$projectStock){
                $extra = 0;
                if($buy->type == 2){
                    $extra = $num['val'];
                }
                //新建库存
                $projectStock = ProjectStockModel::create([
                    'stock_id'          =>  $params['stock_id'],
                    'project_id'        =>  $buy->project_id,
                    'supply_goods_id'   =>  $supplyGoods->id,
                    'num'               =>  $num['val'],
                    'in'                =>  $num['val'],
                    'freeze'            =>  0,
                    'have'              =>  0,
                    'extra'             =>  $extra,
                    
                ]);
                if(!$projectStock){
                    BuyModel::rollback();
                    throw new BaseException(
                        [
                            'msg' => '修改库存错误！',
                            'errorCode' => 62006
                        ]);
                }
            }else{
                $projectStock->num = $projectStock->num + $num['val'];
                $projectStock->in = $projectStock->in + $num['val'];
                if($buy->type == 2){
                    $projectStock->extra = $projectStock->extra + $num['val'];
                }
                $res = $projectStock->save();
                if(!$res){
                    BuyModel::rollback();
                    throw new BaseException(
                        [
                            'msg' => '修改库存错误！',
                            'errorCode' => 62007
                        ]);
                }
            }

            //修改总库存
            $stockAll = StockAllModel::where('stock_id', $params['stock_id'])->where('supply_goods_id', $supplyGoods->id)->find();
            if(!$stockAll){
                $stockAll = StockAllModel::create([
                    'company_id'    =>  session('power_user.company_id'),
                    'stock_id'    =>  $params['stock_id'],
                    'supply_goods_id'    =>  $supplyGoods->id,
                    'num'    =>  $num['val'],
                    'freeze'    =>  0,
                    'have'    =>  0,
                ]);
                if(!$stockAll){
                    BuyModel::rollback();
                    throw new BaseException(
                        [
                            'msg' => '修改总库存错误！',
                            'errorCode' => 62008
                        ]);
                }
            }else{
                $stockAll->num = $stockAll->num + $num['val'];
                $res = $stockAll->save();
                if(!$res){
                    BuyModel::rollback();
                    throw new BaseException(
                        [
                            'msg' => '修改总库存错误！',
                            'errorCode' => 62009
                        ]);
                }
            }

        }
        $status_ok = 0;
        $buyInfoList = BuyInfoModel::where('buy_id', $buy->id)->select();
        foreach ($buyInfoList as $buyInfo) {
            if($buyInfo->num > $buyInfo->num_ok){
                $status_ok++;
            }
        }
        $status = 2;
        if($status_ok == 0){
            $status = 3;
        }
        if($buy->status != $status){
            $buy->status = $status;
            $res = $buy->save();
            if(!$res){
                BuyModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '修改采购订单状态错误！',
                        'errorCode' => 62010
                    ]);
            }
        }
        BuyModel::commit();
        return [
            'msg' => '采购入库成功',
        ];
    }

}