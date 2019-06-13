<?php

namespace app\index\validate;

class StockValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require',
    ];

    protected $message = [
        'name.require' => '仓库名称不能为空',
    ];
}