<?php
namespace app\index\service;

use app\index\model\ContractSupply as ContractSupplyModel;
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

    // 获取供应商合同编号对应的合同
    public function getContractList($params, $limit = 15){
        $where = [];
        $whereOr = [];
        if (isset($params['search']) && $params['search']){
            $where[] = ['number', 'like', "%{$params['search']}%"];
            $supply_ids = SupplyModel::where('name', 'like', "%{$params['search']}%")->field('id')->distinct(true)->all()->toArray();
            if ($supply_ids){
                $supply_ids = array_column($supply_ids,'id');
                $whereOr[] = ['supply_id', 'in', $supply_ids];
            }
        }

        $list = ContractSupplyModel::with(['supply'])->where($where)->whereOr($whereOr)->order('create_time desc')->paginate($limit);
        if ($list){
            foreach ($list as $k => $v){
                $list[$k]['search_show'] = "时间：{$v['create_time']} 编号：{$v['number']} 供应商：{$v['supply_name']}";
            }
        }
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
                'code' => '200'
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
                    'code' => '200'
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
                'code' => '200'
            ];
        }
    }



} 