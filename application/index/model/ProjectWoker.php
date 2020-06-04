<?php
namespace app\index\model;

class ProjectWoker extends Base{

    public function woker(){
        return $this->hasOne('Woker', 'id', 'woker_id')->bind([
            'woker_name'   => 'name',
            'woker_leader'   => 'leader',
        ]);
    }
}