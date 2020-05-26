<?php

namespace app\index\validate;

use app\index\model\Need as NeedModel;
use app\index\model\Project;

class NeedValidate extends BaseValidate
{
    protected $rule = [
        'id'         => 'checkProjectStatus',
        'goods_id'   => 'require|unique:Need,goods_id^project_id^type',
        'project_id' => 'require|unique:Need,goods_id^project_id^type|checkProjectStatus',
        'type'       => 'require|unique:Need,goods_id^project_id^type',
        'need'       => 'float|checkProjectStatus|>=:0',
    ];

    protected $message = [
        'goods_id.require'  => '材料编号不能为空',
        'goods_id.unique'   => '材料已经存在，请勿重复添加！',
        'project_id.unique' => '材料已经存在，请勿重复添加！',
        'type.unique'       => '材料已经存在，请勿重复添加！',
        'need'              => '请填写正确的数字<br>（不含负数，自动保留两位小数）！',
    ];

    protected $scene = [
        'add'  => ['goods_id', 'project_id', 'type'],
        'edit' => ['need'],
        'del'  => ['id'],
    ];

    // 添加材料 验证工程项目是否为 待开工 状态
    protected function checkProjectStatus($value,$rule,$data=[])
    {
        $project = Project::get($data['project_id']);
        if ($project->getData('status') != 1){
            return ' 工程项目必须为 [待开工] 状态！';
        }
        return true;
    }

}