<?php
namespace app\index\service;

use app\index\model\Cate as CateModel;
use app\lib\exception\BaseException;

class Cate{
    //合同列表
    public function selectList($params){
        $list = CateModel::where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('name', 'like', '%'.$params['search'].'%');
            }
        })->field('id, name, create_time')->order('create_time', 'desc')->paginate(10, false, [
            'query'     => $params,
        ]);
        return $list;
    }

    public function add_contract($data){ 
        $cate = CateModel::create($data);
        if(!$cate){
            throw new BaseException(
            [
                'msg' => '添加分类错误！',
                'errorCode' => 30004
            ]);
        }
        return [
            'msg' => '添加分类成功',
        ];
    }

    public function save_contract($id, $data){
        $res = CateModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改分类错误！',
                'errorCode' => 30005
            ]);
        }
        return [
            'msg' => '更改分类成功',
        ];
    }

} 