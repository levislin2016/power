<?php

namespace app\index\validate;

class GetWokerGoodsValidate extends BaseValidate
{
    protected $rule = [
        'stock_id' => 'isNotEmpty|isPositiveInteger',
        'project_id' => 'isNotEmpty|isPositiveInteger',
        'woker_id' => 'isNotEmpty|isPositiveInteger',
    ];

}