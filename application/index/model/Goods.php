<?php
namespace app\index\model;

class Goods extends Base{

    protected function base($query){
        $query->where('company_id', session('power_user.company_id'));
    }

    public function supply(){ 
        return $this->hasOne('Supply', 'id', 'supply_id');
    }

}