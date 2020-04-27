<?php
namespace app\index\model;

class Buy extends Base{

    protected function base($query){
        $query->where('company_id', session('power_user.company_id'));
    }

    public function getStatusAttr($value)
    {
        $status = [
            1 => '采购中',
            2 => '部分入库',
            3 => '已完成',
            4 => '已取消',
        ];
        return $status[$value];
    }

}