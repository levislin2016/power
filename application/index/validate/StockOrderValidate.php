<?php

namespace app\index\validate;

class StockOrderValidate extends BaseValidate
{
    protected $rule = [
        'stock_id' => 'isNotEmpty|isPositiveInteger',
        'project_id' => 'isNotEmpty|isPositiveInteger',
        'note'      => 'ok',
        'info'      => 'isNotEmpty'
    ];

}