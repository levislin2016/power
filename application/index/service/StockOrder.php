<?php
namespace app\index\service;

use app\index\model\StockOrder as StockOrderModel;
use app\index\model\StockOrderInfo as StockOrderInfoModel;
use app\index\model\ProjectWoker as ProjectWokerModel;
use app\index\model\ProjectStock as ProjectStockModel;
use app\index\model\StockAll as StockAllModel;
use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\lib\exception\BaseException;

class StockOrder{
    
    public function get_list($params){ 
        $list = StockOrderModel::useGlobalScope(false)->alias('so')
            ->leftJoin('Stock s','so.stock_id = s.id')
            ->leftJoin('project p','so.project_id = p.id')
            ->leftJoin('woker w','so.woker_id = w.id')
            ->leftJoin('user u','so.user_id = u.id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){ 
                    $query->where('so.number|s.name|p.name|w.name|u.name', 'like', '%'.$params['search'].'%');
                }
                $query->where('so.company_id', session('power_user.company_id'));
            })
            ->field('so.*, s.name as stock_name, p.name as project_name, w.name as woker_name, u.name as user_name')
            ->order('so.create_time', 'desc')
            ->paginate(10, false, [
                'query'     => $params,
            ]);

        return $list;
            
    }

    public function create_get_order($params){ 
        StockOrderModel::startTrans();
        $stockOrder = StockOrderModel::create([
            'company_id'        =>  session('power_user.company_id'),
            'number'            =>  $this->create_order_no(),
            'stock_id'          =>  $params['stock_id'],
            'project_id'        =>  $params['project_id'],
            'woker_id'          =>  $params['woker_id'],
            'user_id'           =>  session('power_user.id'),
            'type'              =>  7,
            'status'            => 2,
            'note'              => $params['note']
        ]);

        if(!$stockOrder){
            StockOrderModel::rollback();
            throw new BaseException(
                [
                    'msg' => '材料领取创建错误！',
                    'errorCode' => 51001
                ]);
        }

        
        foreach($params['num'] as $num){
            //扣减分配表
            $projectWoker = ProjectWokerModel::get($num['id']);
            if(!$projectWoker){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '非法操作！',
                        'errorCode' => 51002
                    ]);
            }
            $projectWoker->get = $projectWoker->get + $num['val'];
            $projectWoker->not = $projectWoker->not - $num['val'];
            if($projectWoker->not < 0){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '分配数量不足！',
                        'errorCode' => 51003
                    ]);
            }
            $res = $projectWoker->save();
            if(!$res){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '扣减分配数量错误！',
                        'errorCode' => 51004
                    ]);
            }

            //扣减库存
            $projectStock = ProjectStockModel::where('stock_id', $params['stock_id'])->where('project_id', $params['project_id'])->where('supply_goods_id', $projectWoker->supply_goods_id)->find();
            if(!$projectStock){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '非法操作！',
                        'errorCode' => 51005
                    ]);
            }
            $projectStock->num = $projectStock->num - $num['val'];
            $projectStock->freeze = $projectStock->freeze - $num['val'];
            $res = $projectStock->save();
            if(!$res){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '扣减库存错误！',
                        'errorCode' => 51006
                    ]);
            }

            //扣减总库存
            $stockAll = StockAllModel::where('stock_id', $params['stock_id'])->where('supply_goods_id', $projectWoker->supply_goods_id)->find();
            if(!$stockAll){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '非法操作！',
                        'errorCode' => 51007
                    ]);
            }
            $stockAll->num = $stockAll->num - $num['val'];
            $stockAll->freeze = $stockAll->freeze - $num['val'];
            $res = $stockAll->save();
            if(!$res){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '扣减总库存错误！',
                        'errorCode' => 51008
                    ]);
            }

            $supplyGoods = SupplyGoodsModel::get($projectWoker->supply_goods_id);

            //创建订单详情
            $stockOrderInfo = StockOrderInfoModel::create([
                'stock_order_id'    =>  $stockOrder->id,
                'supply_goods_id'   =>  $projectWoker->supply_goods_id,
                'woker_id'          =>  $params['woker_id'],
                'price'             =>  $supplyGoods->price,
                'num'               =>  $num['val'],
                
            ]);
            if(!$stockOrderInfo){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '创建详情错误！',
                        'errorCode' => 51009
                    ]);
            }
        }

        StockOrderModel::commit();
        return [
            'msg' => '领取材料成功',
        ];
    }

    protected function create_order_no($str = 'G'){
        return $str .''. date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

}