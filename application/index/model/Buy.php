<?php
namespace app\index\model;

class Buy extends Base{

    protected $insert = [
        'status' => 0,
    ];
    protected function base($query){
//        $query->where('company_id', session('power_user.company_id'));
    }

    public function getStatusAttr($value)
    {
        $status = [
            0 => '<span class="layui-badge layui-bg-cyan">未确认</span>',
            1 => '<span class="layui-badge">采购中</span>',
            2 => '<span class="layui-badge layui-bg-orange">采购中</span>',
            3 => '<span class="layui-badge layui-bg-green">已完成</span>',
            4 => '<span class="layui-badge layui-bg-gray">已作废</span>',
        ];
        return $status[$value];
    }

    public function getFromAttr($value)
    {
        $status = [
            1 => '自购',
            2 => '甲供',
        ];
        return $status[$value];
    }

}