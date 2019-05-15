<?php
namespace app\index\service;

use app\index\model\Owner as OwnerModel;
use app\lib\exception\BaseException;

class Owner{
    //合同列表
    public function selectList($params){
        $list = OwnerModel::with(['company'=>function($query){
            $query->field('id,name');
        }])->where(function ($query) use($params) {
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
        $user = OwnerModel::create($data);
        if(!$user){ 
            throw new BaseException(
            [
                'msg' => '添加业主错误！',
                'errorCode' => 30004
            ]);
        }
        return [
            'msg' => '添加业主成功',
        ];
    }

    public function save_contract($id, $data){
        $res = OwnerModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改业主错误！',
                'errorCode' => 30005
            ]);
        }
        return [
            'msg' => '更改业主成功',
        ];
    }

} 