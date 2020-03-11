<?php
namespace app\index\service;

use app\index\model\Type as TypeModel;
use app\lib\exception\BaseException;

class Type{
    public function getList(){
        $list = TypeModel::all();

        $arr = [];
        foreach ($list as $k => $v){
            $arr[$v['id']] = $v['name'];
        }

        return $arr;
    }
} 