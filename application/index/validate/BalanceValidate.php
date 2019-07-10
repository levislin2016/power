<?php

namespace app\index\validate;

class BalanceValidate extends BaseValidate
{
    protected $rule = [
        'project_id' => 'isNotEmpty|isPositiveInteger',
        'num' => 'isNotEmpty|is_num_json'
    ];

    protected function is_num_json($value, $rule='', $data='', $field=''){
        $num = json_decode($value, true);
        if(is_null($num)){
            return $field . '必须是json格式';
        }
        foreach($num as $v){
            if(empty($v['id']) || $v['val'] < 0){
                return $field . ':json数据错误'.$v['id'];
            }
        }
        return true;
    }

}