<?php

namespace app\index\validate;

class ProjectValidate extends BaseValidate
{
    protected $rule = [
        'contract_id' => 'require|isPositiveInteger',
        'name' => 'require',
    ];

    protected $message = [
        'number.require' => '合同编号不能为空',
        'name.require' => '工程名称不能为空',
    ];
}