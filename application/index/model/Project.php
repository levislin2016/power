<?php
namespace app\index\model;

class Project extends Base{
    protected $insert = [
        'status' => 1
    ];

//    protected function base($query){
//        $query->where('company_id', session('power_user.company_id'));
//    }

    public function getStatusNameAttr($value,$data){
        $statusName = config('extra.project_status');
        return $statusName[$data['status']];
    }

    public function getStatusAttr($value,$data){
        $statusName = config('extra.project_status');
        return $statusName[$data['status']];
    }

    public function getOpenTimeAttr($value,$data){
        if ($value){
            $value = date('Y-m-d H:i:s', $value);
        }else{
            $value = '-';
        }
        return $value;
    }

    public function contract(){
        return $this->hasOne('contract', 'id', 'contract_id')->bind([
            'contract_name'   => 'name',
            'contract_number' => 'number',
        ]);
    }

}