<?php

namespace app\index\validate;

class BackWokerGoodsValidate extends BaseValidate
{
    protected $rule = [
        'project_id' => 'isNotEmpty|isPositiveInteger',
        'woker_id' => 'isNotEmpty|isPositiveInteger',
    ];

}