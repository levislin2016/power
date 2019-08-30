<?php
namespace app\index\model;

class User extends Base{
    public function getTypeNameAttr($value,$data){
        $typeName = config('extra.user_type');
        return $typeName[$data['type']];
    }

    public function role(){
        return $this->hasOne('Role', 'id', 'type');
    }
}