<?php
namespace app\index\model;

class Need extends Base{

    protected $insert = [
        'need'       => 0,
        'buy'        => 0,
        'in'         => 0,
        'adjust_in'  => 0,
        'adjust_out' => 0,
        'get'        => 0,
        'freeze'     => 0,
        'ok'         => 0,
        'check'      => 1,

        'buy_status' => 1,
    ];

    public static function init()
    {

    }

    // [修改器] 对数字进行保留两位小数
    public function setNeedAttr($value,$data)
    {
        return round(trim($value), 2);
    }

    public function getTypeAttr($value){
        $arr = config('extra.buy_from');
        return $arr[$value];
    }

//    protected function base($query){
//        $query->where('company_id', session('power_user.company_id'));
//    }
    public function project(){
        return $this->hasOne('Project', 'id', 'project_id')->bind([
            'project_name' => 'name',
        ]);
    }

    public function goods(){
        return $this->hasOne('Goods', 'id', 'goods_id')->bind([
            'goods_number' => 'number',
            'goods_name'   => 'name',
            'unit_name'    => 'unit_name',
            'cate_name'    => 'cate_name',
        ]);
    }
}