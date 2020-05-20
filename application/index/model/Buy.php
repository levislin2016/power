<?php
namespace app\index\model;

class Buy extends Base{

    protected $insert = [
        'status' => 1,
    ];
    protected function base($query){
//        $query->where('company_id', session('power_user.company_id'));
    }

    public function getStatusAttr($value)
    {
        $statusName = config('extra.buy_status');
        return $statusName[$value];
    }

    // [修改器] 获取采购单对应的工程列表
    public function setBuyProjectAttr($value)
    {
        $project_arr = [];
        foreach ($value as $k => $v){
            $project_arr[] = $v['project_name'];
        }
        foreach ($project_arr as $k => &$v){
            $v = "<span class=\"layui-badge-rim\">{$v}</span>";
        }
        $project_str = implode(' ', $project_arr);
        return $project_str;
    }

    public function buyProject()
    {
        return $this->hasMany('BuyProject', 'buy_id');
    }

}