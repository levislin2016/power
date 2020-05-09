<?php

namespace app\index\validate;

class ProjectValidate extends BaseValidate
{
    protected $rule = [
        'contract_id' => 'require|isPositiveInteger',
        'name'        => 'require',
    ];

    protected $message = [
        'contract_id.require' => '请选择合同编号！',
        'name.require'        => '工程名称不能为空！',
    ];

    protected $scene = [
        'add'  =>  ['contract_id','name'],
    ];
}