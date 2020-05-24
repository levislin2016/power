<?php

namespace app\index\validate;

class StockValidate extends BaseValidate
{
    protected $rule = [
        'store_id' => 'require',
    ];

    protected $message = [
        'store_id.require' => '请选择入库仓库！',
    ];
}