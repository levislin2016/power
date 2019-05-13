<?php
namespace app\index\model;

class Project extends Base{

    protected function base($query){
        $query->where('company_id', session('power_user.company_id'));
    }

    public function getStatusNameAttr($value,$data){
        $statusName = config('extra.project_status');
        return $statusName[$data['status']];
    }

    public function contract(){
        return $this->hasOne('contract', 'id', 'contract_id');
    }

}