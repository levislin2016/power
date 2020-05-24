<?php
namespace app\index\model;

class StockInfo extends Base{

//    protected function base($query){
//        $query->where('company_id', session('power_user.company_id'));
//    }
    protected $insert = [
        'num'     => 1,
    ];


    public function goods(){
        return $this->hasOne('Goods', 'id', 'goods_id')->bind([
            'goods_number' => 'number',
            'goods_name'   => 'name',
            'unit_name'    => 'unit_name',
            'type_name'    => 'type_name',
        ]);
    }

    public function need(){
        return $this->hasOne('Need', 'id', 'need_id')->bind([
            'need_need' => 'need',
            'need_buy'  => 'buy',
            'need_type' => 'type',
        ]);
    }

    public function project(){
        return $this->hasOne('Project', 'id', 'project_id')->bind([
            'project_name' => 'name',
        ]);
    }

    public function supply(){
        return $this->hasOne('supply', 'id', 'supply_id')->bind([
            'supply_name' => 'name',
        ]);
    }



}