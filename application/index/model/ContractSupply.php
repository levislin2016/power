<?php
namespace app\index\model;

class ContractSupply extends Base{

    protected $insert = [
        'status' => 1

    ];

    public function getStatusAttr($value,$data){
        $statusName = config('extra.contract_status');
        return $statusName[$data['status']];
    }

    public function supply(){
        return $this->hasOne('supply', 'id', 'supply_id')->bind([
            'supply_name' => 'name',
        ]);
    }
}