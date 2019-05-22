<?php

namespace app\index\validate;

class SupplyGoodsValidate extends BaseValidate
{
    protected $rule = [
        's_id' => 'require',
        'g_id' => 'require',
        'price' => 'require',
    ];

    protected $message = [
        's_id.require' => '请选择供应商',
        'g_id.require' => '请选择一种材料',
        'price.require' => '请输入进货价',
    ];
}