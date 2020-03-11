<?php
namespace app\index\model;

class Need extends Base{

//    protected function base($query){
//        $query->where('company_id', session('power_user.company_id'));
//    }

    public function goods(){
        return $this->hasOne('Goods', 'id', 'goods_id');
    }

    public function unit(){
        return $this->hasManyThrough('Unit', 'Goods', 'id', 'unit_id');
    }




}