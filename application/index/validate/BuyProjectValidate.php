<?php

namespace app\index\validate;

class BuyProjectValidate extends BaseValidate
{
    protected $rule = [
        'buy_id'     => 'require|unique:BuyProject,buy_id^project_id',
        'project_id' => 'require|unique:BuyProject,buy_id^project_id',
    ];

    protected $message = [
        'buy_id.unique'      => '请勿重复添加该工程项目！',

        'project_id.unique'  => '请勿重复添加该工程项目！',
    ];

    protected $scene = [
    ];

}