<?php
namespace app\index\model;

class ContractSupply extends Base{

    public function supply(){
        return $this->hasOne('supply', 'id', 'supply_id')->bind([
            'supply_name' => 'name',
        ]);
    }
}