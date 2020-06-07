<?php
namespace app\index\model;

class ProjectWokerInfo extends Base{

    public function goods(){
        return $this->hasOne('goods', 'id', 'goods_id')->bind([
            'goods_name'   => 'name',
            'goods_number'   => 'number',
            'cate_name',
        ]);
    }
}