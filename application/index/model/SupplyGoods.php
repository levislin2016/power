<?php
namespace app\index\model;

class SupplyGoods extends Base{
//    protected function base($query){
//        $query->where('company_id', session('power_user.company_id'));
//    }

    public function supply(){
        return $this->hasOne('Supply', 'id', 's_id');
    }

    public function goods(){
        return $this->hasOne('Goods', 'id', 'g_id');
    }

}