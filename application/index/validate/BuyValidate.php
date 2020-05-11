<?php

namespace app\index\validate;

use app\index\model\Buy as BuyModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\BuyProject;
use app\index\model\Project as ProjectModel;

class BuyValidate extends BaseValidate
{
    protected $rule = [
        'id'         => 'checkBuyStatus',
    ];

    protected $message = [
    ];

    protected $scene = [
//        'add' => ['buy_id', 'project_id'],
        'cancel' => ['id']
    ];

    // 判断采购单的状态， 判断是否有工程
    protected function checkBuyStatus($value,$rule,$data=[])
    {
        # 判断采购单的状态是否可以删除工程
        $buy = BuyModel::get($data['id']);
        if ($buy->getData('status') != 1){
            return '只能作废 [待确认] 状态的采购单！';
        }

        return true;
    }

}