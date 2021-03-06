<?php

namespace app\index\validate;

class WokerValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require',
        'leader' => 'require',
    ];

    protected $message = [
        'name.require' => '仓库名称不能为空',
        'leader.require' => '联系人不能为空',
    ];
}