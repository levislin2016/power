<?php

namespace app\index\validate;

use app\index\model\Need as NeedModel;
use app\index\model\Project;

class ProjectWokerValidate extends BaseValidate
{
    protected $rule = [
        'id'         => 'checkProjectStatus',
        'woker_id'         => 'require|unique:ProjectWoker,woker_id^project_id',
    ];

    protected $message = [
        'woker_id.require'  => '工程编号不能为空',
        'woker_id.unique'   => '工程队已经存在，请勿重复添加！',
    ];

    protected $scene = [
        'add'  => ['woker_id', 'project_id'],
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