<?php
namespace app\index\service;

use app\index\model\Need as NeedModel;
use app\lib\exception\BaseException;

class Need{

    public function select_list($params){ 
        $list = NeedModel::useGlobalScope(false)->alias('n')
            ->leftJoin('goods g','n.goods_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->leftJoin('supply s','g.supply_id = s.id')
            ->where(function ($query) use($params) {
                $query->where('n.company_id', session('power_user.company_id'));
                $query->where('g.company_id', session('power_user.company_id'));
                $query->where('u.company_id', session('power_user.company_id'));
                $query->where('s.company_id', session('power_user.company_id'));
            })
            ->field('n.id, n.company_id, n.project_id, n.goods_id, n.need, n.buy, n.have, n.note, g.number, g.name, g.unit_id, g.supply_id, g.image, g.price, u.name as unit, s.name as supply_name')
            ->select();

        return $list;
    }

    public function add_need($data){ 
        $data['company_id'] = session('power_user.company_id');
        $need = NeedModel::create($data);
        if(!$need){ 
            throw new BaseException(
            [
                'msg' => '添加需求材料错误！',
                'errorCode' => 50004
            ]);
        }
        return [
            'msg' => '添加需求材料成功',
        ];
    }

    public function save_need($id, $data){
        $res = NeedModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改需求材料错误！',
                'errorCode' => 50005
            ]);
        }
        return [
            'msg' => '修改需求材料成功',
        ];
    }

} 