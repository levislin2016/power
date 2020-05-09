<?php

namespace app\index\validate;

use app\index\model\Need as NeedModel;

class NeedValidate extends BaseValidate
{
    protected $rule = [
        'goods_id'   => 'require|unique:Need,goods_id^project_id^type',
        'project_id' => 'require|unique:Need,goods_id^project_id^type',
        'type'       => 'require|unique:Need,goods_id^project_id^type',
        'need'       => 'number',
    ];

    protected $message = [
        'goods_id.require'  => '材料编号不能为空',
        'goods_id.unique'   => ' 材料已经存在，请勿重复添加！',
        'project_id.unique' => ' 材料已经存在，请勿重复添加！',
        'type.unique'       => ' 材料已经存在，请勿重复添加！',
        'need.number'       => ' 请填写数字（不含小数点）！',
    ];

    protected $scene = [
        'edit' => ['need']
    ];
}