<?php
namespace app\index\model;

class StockOrder extends Base{

    protected function base($query){
        $query->where('company_id', session('power_user.company_id'));
    }

    public function getTypeNameAttr($value,$data){
        $typeName = config('extra.order_type');
        return $typeName[$data['type']];
    }

}