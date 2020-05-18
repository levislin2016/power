<?php
namespace app\index\service;

use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\lib\exception\BaseException;

class SupplyGoods{
    //供应商列表
    public function getList($params, $limit = 15){
        $where = [];
        if (isset($params['search']) && $params['search']){
            $where[] = ['name', 'like', "%{$params['search']}%"];
        }

        if (isset($params['goods_id']) && $params['goods_id']){
            $where[] = ['goods_id', '=', $params['goods_id']];
        }

        if (isset($params['supply_id']) && $params['supply_id']){
            $where[] = ['supply_id', '=', $params['supply_id']];
        }



        $list = SupplyGoodsModel::with(['supply', 'goods'])->where($where)->order('create_time desc')->paginate($limit);
        return $list;
    }

    public function add_contract($data){
        $find = SupplyGoodsModel::field('id')->where(['supply_id' => $data['supply_id'], 'g_id' => $data['g_id']])->find();
        if($find){
            throw new BaseException(
                [
                    'msg' => '供应商材料已存在！',
                    'errorCode' => 30005
                ]);
        }else {
            $data['company_id'] = session('power_user.company_id');
            $user = SupplyGoodsModel::create($data);
            if (!$user) {
                throw new BaseException(
                    [
                        'msg' => '添加供应商材料错误！',
                        'errorCode' => 30004
                    ]);
            }
            return [
                'msg' => '添加供应商材料成功',
            ];
        }
    }

    public function save_contract($id, $data){
        $find = SupplyGoodsModel::field('id')->where(['supply_id' => $data['supply_id'], 'goods_id' => $data['goods_id']])->find();
        if($find){
            $find = $find->toArray();
            if($find['id'] == $id){
                return [
                    'msg' => '更改供应商材料成功',
                ];
            }else{
                throw new BaseException(
                    [
                        'msg' => '更改供应商材料已存在！',
                        'errorCode' => 30005
                    ]);
            }
        }else {
            $res = SupplyGoodsModel::where('id', $id)->update($data);
            if (!$res) {
                throw new BaseException(
                    [
                        'msg' => '修改供应商材料错误！',
                        'errorCode' => 30005
                    ]);
            }
            return [
                'msg' => '更改供应商材料成功',
            ];
        }
    }

} 