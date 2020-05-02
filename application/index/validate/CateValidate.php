<?php

namespace app\index\validate;

class CateValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require',
    ];

    protected $message = [
        'name.require' => '分类名称不能为空',
    ];
}