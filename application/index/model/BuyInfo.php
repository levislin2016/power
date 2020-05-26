<?php
namespace app\index\model;

class BuyInfo extends Base{
    protected $insert = [
        'num_ok' => 0
    ];

    // [修改器] 对数字进行保留两位小数
    public function setNumAttr($value,$data)
    {
        return round(trim($value), 2);
    }

    // [修改器] 对价格进行保留三位小数
    public function setPriceAttr($value,$data)
    {
        return round(trim($value), 3);
    }



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