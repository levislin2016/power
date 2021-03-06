<?php
namespace app\index\service;

use app\index\model\Contract as ContractModel;
use app\lib\exception\BaseException;

class Contract{
    //合同列表
    public function select_list($params, $limit = 15){
        $list = ContractModel::with(['owner'])->where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('number|name', 'like', '%'.$params['search'].'%');
            }
        })->field('id, company_id, number, name, owner_id, create_time')
        ->order('create_time', 'desc')
        ->paginate($limit);
        return $list;
    }

    public function add_contract($data){ 
        $data['company_id'] = session('power_user.company_id');
        $user = ContractModel::create($data);
        if(!$user){ 
            throw new BaseException(
            [
                'msg' => '添加合同错误！',
                'errorCode' => 30004
            ]);
        }
        return [
            'msg' => '添加合同成功',
            'code' => 200
        ];
    }

    public function save_contract($id, $data){
        $res = ContractModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改合同错误！',
                'errorCode' => 30005
            ]);
        }
        return [
            'msg' => '添加合同成功',
            'code' => 200
        ];
    }

} 