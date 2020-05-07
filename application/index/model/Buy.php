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
        $statusName = config('extra.buy_status');
        return $statusName[$value];
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