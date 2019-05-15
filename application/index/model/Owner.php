<?php
namespace app\index\model;

class Owner extends Base{

    protected function base($query){
        $query->where('company_id', session('power_user.company_id'));
    }

    public function company(){
        return $this->hasOne('Company', 'id', 'company_id');
    }


}