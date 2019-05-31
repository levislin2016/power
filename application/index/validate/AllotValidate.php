<?php
namespace app\index\validate;

class AllotValidate extends BaseValidate
{
    protected $rule = [
        'pid' => 'isNotEmpty|isPositiveInteger',
        'woker_id' => 'isNotEmpty|isPositiveInteger',
        'supply_goods_id' => 'isNotEmpty|isPositiveInteger',
        'num' => 'isNotEmpty|isPositiveInteger',
    ];
}