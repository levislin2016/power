<?php
namespace app\index\model;

class Contract extends Base{

    protected function base($query){
        $query->where('company_id', session('power_user.company_id'));
    }

    public function owner(){
        return $this->hasOne('Owner', 'id', 'owner_id');
    }

}