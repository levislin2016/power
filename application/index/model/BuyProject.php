<?php
namespace app\index\model;

class BuyProject extends Base{

    public function project(){
        return $this->hasOne('Project', 'id', 'project_id');
    }
}