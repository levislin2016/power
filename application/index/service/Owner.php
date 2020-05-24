<?php
namespace app\index\service;

use app\index\model\Owner as OwnerModel;
use app\lib\exception\BaseException;

class Owner{
    //合同列表
    public function selectList($params, $limit = 15){
        $list = OwnerModel::where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('name', 'like', '%'.$params['search'].'%');
            }
        })->field('id, company_id, name, create_time')
        ->order('create_time', 'desc')
        ->paginate($limit);
        return $list;
    }

    public function add_contract($data){
        $find = OwnerModel::field('id')->where(['name' => $data['name']])->find();
        if($find){
            throw new BaseException(
                [
                    'msg' => '业主已存在！',
                    'errorCode' => 30005
                ]);
        }else {
            $data['company_id'] = session('power_user.company_id');
            $user = OwnerModel::create($data);
            if (!$user) {
                throw new BaseException(
                    [
                        'msg' => '添加业主错误！',
                        'errorCode' => 30004
                    ]);
            }
            return [
                'msg' => '添加业主成功',
                'code' => 200
            ];
        }
    }

    public function save_contract($id, $data){
        $find = OwnerModel::field('id')->where(['name' => $data['name']])->find();
        if($find){
            $find = $find->toArray();
            if($find['id'] == $id){
                return [
                    'msg' => '修改业主成功',
                    'code' => 200
                ];
            }else{
                throw new BaseException(
                    [
                        'msg' => '业主已存在！',
                        'errorCode' => 30005
                    ]);
            }
        }else {
            $res = OwnerModel::where('id', $id)->update($data);
            if (!$res) {
                throw new BaseException(
                    [
                        'msg' => '修改业主错误！',
                        'errorCode' => 30005
                    ]);
            }
            return [
                'msg' => '更改业主成功',
                'code' => 200
            ];
        }
    }

} 