<?php
namespace app\index\model;

class Stock extends Base{

    public function getTypeAttr($value,$data){
        $statusName = config('extra.stock_type');
        return $statusName[$data['type']];
    }

}