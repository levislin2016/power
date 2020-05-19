<?php
namespace app\index\model;

class BuyProject extends Base{
    public function project(){
        return $this->hasOne('Project', 'id', 'project_id')->bind([
            'project_name'      => 'name',
            'project_status'    => 'status',
            'project_open_time' => 'open_time',
            'contract_name'     => 'contract_name',
            'contract_number'   => 'contract_number',
        ]);
    }

    public function project2(){
        return $this->hasOne('Project', 'id', 'project_id')->bind([
            'project_name' => 'name',
        ]);
    }
}