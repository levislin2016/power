<?php

namespace app\index\validate;

use app\index\model\Need as NeedModel;
use app\index\model\Project as ProjectModel;

class ProjectValidate extends BaseValidate
{
    protected $rule = [
        'id'          => 'checkNeed',
        'contract_id' => 'require|isPositiveInteger',
        'name'        => 'require|unique:Project',
    ];

    protected $message = [
        'contract_id.require' => '请选择合同编号！',
        'name.require'        => '工程名称不能为空！',
        'name.unique'         => '工程名称已存在，请重新填写！',
    ];

    protected $scene = [
        'add'  => ['contract_id','name'],
        'del'  => ['id'],
        'edit' => ['name'],
    ];

    // 设定开始工程的验证场景
    public function sceneStart(){
        return $this->only(['id'])->remove('id', 'checkNeed')->append('id', 'checkStart');
    }

    // 验证工程项目下是否设定了预算
    protected function checkNeed($value,$rule,$data=[])
    {
        $ret = NeedModel::get(['project_id' => $value]);
        if ($ret){
            return '请先删除工程下的预算，才能删除该工程！';
        }
        return true;
    }

    // 验证开始工程项目 预算设定
    protected function checkStart($value,$rule,$data=[])
    {
        $need = NeedModel::with(['goods' => ['unit', 'type']])->order('create_time desc')->all(['project_id' => $value])->toArray();
        if (!$need){
            return '请先添加预算材料！';
        }
        foreach ($need as $k => $v){
            if ($v['need'] <= 0){
                return "材料：{$v['goods_name']} 数量未填写";
            }
        }

        $project = ProjectModel::get($value);
        if ($project->getData('status') == 2){
            return "工程当前已为 [已开工] 状态，请勿重复开始工程！";
        }
        return true;
    }
}