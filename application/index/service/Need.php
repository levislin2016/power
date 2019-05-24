<?php
namespace app\index\service;

use app\index\model\Need as NeedModel;
use app\lib\exception\BaseException;

class Need{

    public function select_list($params){ 
        $list = NeedModel::useGlobalScope(false)->alias('n')
            //->leftJoin('supply_goods sg','n.goods_id = sg.id')
            //->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('goods g','n.goods_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            //->leftJoin('supply s','sg.s_id = s.id')
            ->where(function ($query) use($params) {
                $query->where('n.company_id', session('power_user.company_id'));
                $query->where('n.project_id', $params['id']);
                $query->where('g.company_id', session('power_user.company_id'));
                $query->where('u.company_id', session('power_user.company_id'));
                //$query->where('s.company_id', session('power_user.company_id'));
            })
            ->field('n.id, n.company_id, n.project_id, n.goods_id, n.need, n.note, n.create_time, g.number, g.name, g.unit_id, g.image, u.name as unit')
            //->field('n.id, n.company_id, n.project_id, n.goods_id, n.need, n.note, n.create_time, g.number, g.name, g.unit_id, s.id as supply_id, g.image, sg.price, u.name as unit, s.name as supply_name')
            ->order('n.create_time', 'desc')
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