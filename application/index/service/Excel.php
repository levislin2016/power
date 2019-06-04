<?php
namespace app\index\service;

use app\index\model\Unit as UnitModel;
use app\lib\exception\BaseException;

class Unit{
    //合同列表
    public function selectList($params){
        $list = UnitModel::where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('name', 'like', '%'.$params['search'].'%');
            }
        })->field('id, company_id, name, create_time')->order('create_time', 'desc')->paginate(10, false, [
            'query'     => $params,
        ]);
        return $list;
    }

    public function add_contract($data){ 
        $data['company_id'] = session('power_user.company_id');
        $user = UnitModel::create($data);
        if(!$user){ 
            throw new BaseException(
            [
                'msg' => '添加计量单位错误！',
                'errorCode' => 30004
            ]);
        }
        return [
            'msg' => '添加计量单位成功',
        ];
    }

    public function save_contract($id, $data){
        $res = UnitModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改计量单位错误！',
                'errorCode' => 30005
            ]);
        }
        return [
            'msg' => '更改计量单位成功',
        ];
    }

} 