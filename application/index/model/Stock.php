<?php
namespace app\index\model;

class Stock extends Base{

    protected function base($query){
        $query->where('company_id', session('power_user.company_id'));
    }


}