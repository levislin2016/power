<?php
namespace app\index\model;

class Need extends Base{

    protected $insert = [
        'need'    => 0,
        'need_ok' => 0,
        'check'   => 0,
    ];

    public function getTypeAttr($value){
        $arr = config('extra.buy_from');
        return $arr[$value];
    }

//    protected function base($query){
//        $query->where('company_id', session('power_user.company_id'));
//    }

    public function goods(){
        return $this->hasOne('Goods', 'id', 'goods_id')->bind([
            'goods_number' => 'number',
            'goods_name'   => 'name',
            'unit_name'    => 'unit_name',
            'type_name'    => 'type_name',
        ]);
    }
}