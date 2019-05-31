<?php
namespace app\index\service;

use app\index\model\Stock as StockModel;
use app\lib\exception\BaseException;

class Stock{
    //仓库列表
    public function select_list($params){ 
        $list = StockModel::where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('name', 'like', '%'.$params['search'].'%');
            }
        })->field('id, company_id, name, create_time')
        ->order('create_time', 'desc')
        ->paginate(10, false, [
            'query'     => $params,
        ]);
        return $list;
    }

    public function add_stock($data){ 
        $data['company_id'] = session('power_user.company_id');
        $stock = StockModel::create($data);
        if(!$stock){ 
            throw new BaseException(
            [
                'msg' => '添加仓库错误！',
                'errorCode' => 80004
            ]);
        }
        return [
            'msg' => '添加仓库成功',
        ];
    }

    public function save_stock($id, $data){
        $res = StockModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改仓库错误！',
                'errorCode' => 80005
            ]);
        }
        return [
            'msg' => '修改仓库成功',
        ];
    }

} 