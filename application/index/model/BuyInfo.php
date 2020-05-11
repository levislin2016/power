<?php
namespace app\index\model;

class BuyInfo extends Base{
    protected $insert = [
        'num_ok' => 0
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
            'need_need'    => 'need',
            'need_need_ok' => 'need_ok',
            'need_type'    => 'type',
        ]);
    }

    public function project(){
        return $this->hasOne('Project', 'id', 'project_id')->bind([
            'project_name' => 'name',
        ]);
    }




}