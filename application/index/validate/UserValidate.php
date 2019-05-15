<?php

namespace app\index\validate;

class UserValidate extends BaseValidate
{
    protected $rule = [
        'username' => 'require|min:2|unique:user',
        'password' => 'length:6,16',
        'confirm_password' => 'confirmPassword:password',
        'type' => 'require|isPositiveInteger',
    ];

    protected $message = [
        'username.require' => '用户名不能为空',
        'username.min' => '用户名小于2位',
        'username.unique' => '用户名已存在',
        'password.length' => '密码小于6位大于16位',
        'confirm_password.confirmPassword' => '两次密码不一致',
        'nickname.require' => '名字不能为空',
        'type.require' => '类型不能为空',
    ];
}