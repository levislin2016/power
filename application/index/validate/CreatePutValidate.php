<?php

namespace app\index\validate;

class CreatePutValidate extends BaseValidate
{
    protected $rule = [
        'stock_id' => 'isNotEmpty|isPositiveInteger',
        'buy_id' => 'isNotEmpty|isPositiveInteger',
        'num' => 'isNotEmpty|is_num_json',
    ];

    protected function is_num_json($value, $rule='', $data='', $field=''){
        $num = json_decode($value, true);
        if(is_null($num)){
            return $field . '必须是json格式';
        }
        foreach($num as $v){
            if(empty($v['id']) || empty($v['val'])){
                return $field . ':json数据错误';
            }
        }
        return true;
    }

}