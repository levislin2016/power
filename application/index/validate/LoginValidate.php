<?php
/**
 * Created by PhpStorm.
 * User: Office
 * Date: 2018/4/4
 * Time: 13:49
 */

namespace app\index\validate;

class LoginValidate extends BaseValidate
{
    protected $rule = [
        'username' => 'require',
        'password' => 'require',
        'captcha'=>'require|captcha',
    ];

    protected $message = [
        'username' => '用户名不能为空',
        'password' => '密码不能为空',
        'captcha' => '验证码错误'
    ];
}