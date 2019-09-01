<?php

namespace app\index\validate;

class CreateBuyValidate extends BaseValidate
{
    protected $rule = [
        'project_id' => 'isNotEmpty|isPositiveInteger',
        'buy_contract' => 'ok',
        'supply_id' => 'ok',
        'num' => 'isNotEmpty|is_num_json',
        'note' => 'ok',
        'type' => 'isNotEmpty|in:1,2',
        'from' => 'isNotEmpty|isPositiveInteger',
    ];

    protected function is_num_json($value, $rule='', $data='', $field=''){
        $num = json_decode($value, true);
        if(is_null($num)){
            return $field . '必须是json格式';
        }
        foreach($num as $v){
            if(empty($v['id']) || empty($v['val']) || empty($v['price'])){
                return $field . ':json数据错误';
            }
        }
        return true;
    }

}