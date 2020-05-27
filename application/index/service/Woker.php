<?php
namespace app\index\service;

use app\index\model\Woker as WokerModel;
use app\lib\exception\BaseException;

class Woker{
    //工程队列表
    public function select_list($params, $limit = 15){
        $list = WokerModel::where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('name|leader|phone', 'like', '%'.$params['search'].'%');
            }
        })->field('id, company_id, name, leader, phone, create_time')
        ->order('create_time', 'desc')
        ->paginate($limit);
        return $list;
    }

    public function add_woker($data){ 
        $data['company_id'] = session('power_user.company_id');
        $woker = WokerModel::create($data);
        if(!$woker){ 
            throw new BaseException(
            [
                'msg' => '添加工程队错误！',
                'errorCode' => 90004
            ]);
        }
        return [
            'msg' => '添加工程队成功',
            'code' => 200
        ];
    }

    public function save_woker($id, $data){
        $data['update_time'] = time();
        $res = WokerModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改工程队错误！',
                'errorCode' => 90005
            ]);
        }
        return [
            'msg' => '修改工程队成功',
            'code' => 200
        ];
    }

} 