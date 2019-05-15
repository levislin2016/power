<?php
namespace app\index\service;

use app\index\model\Supply as SupplyModel;
use app\lib\exception\BaseException;

class Supply{
    //合同列表
    public function selectList($params){
        $list = SupplyModel::where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('name', 'like', '%'.$params['search'].'%');
            }
        })->field('id, company_id, name, create_time')->paginate(10, false, [
            'query'     => $params,
        ]);
        return $list;
    }

    public function add_contract($data){ 
        $data['company_id'] = session('power_user.company_id');
        $user = SupplyModel::create($data);
        if(!$user){ 
            throw new BaseException(
            [
                'msg' => '添加供应商错误！',
                'errorCode' => 30004
            ]);
        }
        return [
            'msg' => '添加供应商成功',
        ];
    }

    public function save_contract($id, $data){
        $res = SupplyModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改供应商错误！',
                'errorCode' => 30005
            ]);
        }
        return [
            'msg' => '更改供应商成功',
        ];
    }

} 