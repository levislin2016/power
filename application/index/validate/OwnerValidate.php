<?php

namespace app\index\validate;

class OwnerValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require',
    ];

    protected $message = [
        'name.require' => '材料名称不能为空',
    ];
}