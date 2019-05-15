<?php

namespace app\index\validate;

class UnitValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require',
    ];

    protected $message = [
        'name.require' => '材料计量单位不能为空',
    ];
}