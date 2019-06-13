<?php

namespace app\index\validate;

class CompanyValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require',
    ];

    protected $message = [
        'name.require' => '公司名称不能为空',
    ];
}