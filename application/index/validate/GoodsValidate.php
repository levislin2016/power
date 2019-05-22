<?php

namespace app\index\validate;

class GoodsValidate extends BaseValidate
{
    protected $rule = [
        'number' => 'require|unique:contract',
        'name' => 'require',
    ];

    protected $message = [
        'number.require' => '材料编号不能为空',
        'number.unique' => '材料编号已存在',
        'name.require' => '材料名称不能为空',
    ];
}