<?php
namespace app\index\service;

use app\index\model\Supply as SupplyModel;
use app\lib\exception\BaseException;

class Supply{
    //供应商列表
    public function selectList($params){
        $list = SupplyModel::where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('name', 'like', '%'.$params['search'].'%');
            }
        })->field('id, company_id, name, phone, note,create_time')->order('create_time', 'desc')->paginate(10, false, [
            'query'     => $params,
        ]);
        return $list;
    }

    public function add_contract($data){
        $find = SupplyModel::field('id')->where(['name' => $data['name']])->find();
        if($find){
            throw new BaseException(
                [
                    'msg' => '供应商已存在！',
                    'errorCode' => 30005
                ]);
        }else {
            $data['company_id'] = session('power_user.company_id');
            $user = SupplyModel::create($data);
            if (!$user) {
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
    }

    public function save_contract($id, $data){
        $find = SupplyModel::field('id')->where(['name' => $data['name']])->find();
        if($find){
            $find = $find->toArray();
            if($find['id'] == $id){
                return [
                    'msg' => '更改供应商成功',
                ];
            }else{
                throw new BaseException(
                    [
                        'msg' => '供应商已存在！',
                        'errorCode' => 30005
                    ]);
            }
        }else {
            $res = SupplyModel::where('id', $id)->update($data);
            if (!$res) {
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

} 