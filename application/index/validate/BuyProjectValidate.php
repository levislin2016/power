<?php

namespace app\index\validate;

use app\index\model\Buy as BuyModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\Project as ProjectModel;

class BuyProjectValidate extends BaseValidate
{
    protected $rule = [
        'id'         => 'checkDel',
        'buy_id'     => 'require|unique:BuyProject,buy_id^project_id|checkBuyStatus',
        'project_id' => 'require|unique:BuyProject,buy_id^project_id',
    ];

    protected $message = [
        'buy_id.unique'      => '请勿重复添加该工程项目！',

        'project_id.unique'  => '请勿重复添加该工程项目！',
    ];

    protected $scene = [
        'add' => ['buy_id', 'project_id'],
        'del' => ['id']
    ];

    // 判断工程处于已开工才可以添加
    protected function checkProjectStatus($value,$rule,$data=[])
    {
        # 判断采购单的状态是否可以删除工程
        $project = ProjectModel::get($value);
        if ($project->getData('status') != 2){
            return '只能添加 [已开工] 状态的工程！';
        }

        return true;
    }

    // 删除采购工程，判断采购工程是否处于待确认状态， 判断是否有采购的详情
    protected function checkBuyStatus($value,$rule,$data=[])
    {
        # 判断采购单的状态是否可以删除工程
        $buy = BuyModel::get($data['buy_id']);
        if ($buy->getData('status') != 1){
            return '采购单必须为 [待确认] 状态！才能进行操作！';
        }
        return true;
    }

    // 删除采购工程,判断是否有采购的详情
    protected function checkDel($value,$rule,$data=[])
    {
        # 判断是否有采购材料
        $ret = BuyInfoModel::get([
            'buy_id'     => $data['buy_id'],
            'project_id' => $data['project_id'],
        ]);
        if ($ret){
            return '请先删除该工程下的采购材料，才能进行工程的删除操作！';
        }
        return true;
    }

}