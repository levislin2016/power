<?php

namespace app\index\validate;

class GoodsValidate extends BaseValidate
{
    protected $rule = [
        'number' => 'require|unique:contract',
        'name' => 'require',
        'supply_id' => 'require|isPositiveInteger',
        'unit_id' => 'require|isPositiveInteger',
        'price' => 'require',
    ];

    protected $message = [
        'number.require' => '材料编号不能为空',
        'number.unique' => '材料编号已存在',
        'name.require' => '材料名称不能为空',
        'price.require' => '材料进货价不能为空',
        'supply_id.require' => '供应商不能为空',
        'unit_id.require' => '供应商不能为空',
    ];
}