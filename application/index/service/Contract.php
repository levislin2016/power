<?php
namespace app\index\service;

use app\index\model\Contract as ContractModel;
use app\lib\exception\BaseException;

class Contract{
    //合同列表
    public function select_list($params){ 
        $list = ContractModel::with(['owner'=>function($query){
            $query->field('id,name');
        }])->where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('number|name', 'like', '%'.$params['search'].'%');
            }
        })->field('id, company_id, number, name, owner_id, create_time')->paginate(10, false, [
            'query'     => $params,
        ]);
        return $list;
    }
} 