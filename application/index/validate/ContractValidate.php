<?php

namespace app\index\validate;

class ContractValidate extends BaseValidate
{
    protected $rule = [
        'number' => 'require|unique:contract',
        'name' => 'require',
        'owner_id' => 'require|isPositiveInteger',
    ];

    protected $message = [
        'number.require' => '合同编号不能为空',
        'number.unique' => '合同编号已存在',
        'name.require' => '合同名称不能为空',
        'owner_id.require' => '业主不能为空',
    ];
}