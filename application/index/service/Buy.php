<?php
namespace app\index\service;

use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\index\model\Buy as BuyModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\lib\exception\BaseException;

class Buy{

    public function create_buy_order($params){

        BuyModel::startTrans();
        
        //创建采购订单
        $buy = BuyModel::create([
            'company_id'    =>  session('power_user.company_id'),
            'user_id'       =>  session('power_user.id'),
            'number'        =>  create_order_no('C'),
            'project_id'    =>  $params['project_id'],
            'status'        =>  1
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
                'supply_id'     =>  $num['sp'],
                'price'         =>  SupplyGoodsModel::where('s_id', $num['sp'])->where('g_id', $num['id'])->value('price'),
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
        }


        BuyModel::commit();
        return [
            'msg' => '创建采购单成功',
        ];
    }

}