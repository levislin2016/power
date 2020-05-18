<?php
namespace app\index\service;

use app\index\model\Supply as SupplyModel;
use app\lib\exception\BaseException;

class Supply{
    // 获取列表
    public function getList($params, $limit = 15){
        $where = [];
        if (isset($params['search']) && $params['search']){
            $where[] = ['name', 'like', "%{$params['search']}%"];
        }

        $list = SupplyModel::where($where)->order('create_time desc')->paginate($limit);
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