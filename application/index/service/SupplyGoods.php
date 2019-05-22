<?php
namespace app\index\service;

use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\lib\exception\BaseException;

class SupplyGoods{
    //供应商列表
    public function selectList($params){
        $list = SupplyGoodsModel::alias('sg')
            ->leftJoin('goods g','g.id = sg.g_id')
            ->leftJoin('supply s','s.id = sg.s_id')
            ->where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('s.name|g.name', 'like', '%'.$params['search'].'%');
            }
        })->field('sg.id, s.name as supply_name, s.phone as supply_phone, g.name as goods_name, g.image as goods_image, sg.price, sg.note, sg.create_time')->order('sg.create_time', 'desc')->paginate(10, false, [
            'query'     => $params,
        ]);
        return $list;
    }

    public function add_contract($data){
        $find = SupplyGoodsModel::field('id')->where(['s_id' => $data['s_id'], 'g_id' => $data['g_id']])->find();
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
        $find = SupplyGoodsModel::field('id')->where(['s_id' => $data['s_id'], 'g_id' => $data['g_id']])->find();
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