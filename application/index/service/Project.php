<?php
namespace app\index\service;

use app\index\model\Project as ProjectModel;
use app\lib\exception\BaseException;

class Project{

    public function select_list($params){ 
        // $list = ProjectModel::with(['contract'=>function($query){
        //     $query->field('id,number');
        // }])->where(function ($query) use($params) {
        //     if(!empty($params['search'])){ 
        //         $query->where('number|name', 'like', '%'.$params['search'].'%');
        //     }
        // })->field('id, company_id, contract_id, name, status')->paginate(10, false, [
        //     'query'     => $params,
        // ]);

        $list = ProjectModel::useGlobalScope(false)->alias('p')
            ->leftJoin('contract c','p.contract_id = c.id')
            ->leftJoin('owner o','c.owner_id = o.id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){ 
                    $query->where('c.number|p.name', 'like', '%'.$params['search'].'%');
                }
                $query->where('c.company_id', session('power_user.company_id'));
                $query->where('p.company_id', session('power_user.company_id'));
            })
            ->field('p.id, p.company_id, p.contract_id, c.number as contract_number, p.name, p.status, p.create_time, o.name as owner_name')
            ->order('p.create_time', 'desc')
            ->paginate(10, false, [
                'query'     => $params,
            ]);

        return $list;
    }

    public function add_project($data){ 
        $data['company_id'] = session('power_user.company_id');
        $data['status'] = 1;
        $project = ProjectModel::create($data);
        if(!$project){ 
            throw new BaseException(
            [
                'msg' => '添加工程错误！',
                'errorCode' => 40004
            ]);
        }
        return [
            'msg' => '添加工程成功',
        ];
    }

    public function save_project($id, $data){
        $res = ProjectModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改工程错误！',
                'errorCode' => 40005
            ]);
        }
        return [
            'msg' => '修改工程成功',
        ];
    }

} 