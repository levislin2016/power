<?php
namespace app\index\model;

class Goods extends Base{

//    protected function base($query){
//        $query->where('company_id', session('power_user.company_id'));
//    }

    public function company(){
        return $this->hasOne('Company', 'id', 'company_id');
    }

//    public function supply(){
//        return $this->hasOne('Supply', 'id', 'supply_id');
//    }

    public function cate(){
        return $this->hasOne('Cate', 'id', 'cate_id')->bind(['cate_name' => 'name']);
    }

    public function unit(){
        return $this->hasOne('Unit', 'id', 'unit_id')->bind(['unit_name' => 'name',]);
    }

    public function type(){
        return $this->hasOne('Type', 'id', 'type_id')->bind(['type_name' => 'name',]);
    }


}