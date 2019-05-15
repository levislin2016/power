<?php

namespace app\index\validate;

class SupplyValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require',
    ];

    protected $message = [
        'name.require' => '材料名称不能为空',
    ];
}