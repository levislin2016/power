<?php

namespace app\index\validate;

class StoreValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require|unique:Store',
    ];

    protected $message = [
        'name.require' => '仓库名称不能为空',
        'name.unique'  => '该仓库名称已存在，请重新输入！',
    ];
}