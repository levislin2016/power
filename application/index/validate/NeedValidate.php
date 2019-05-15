<?php

namespace app\index\validate;

use app\index\model\Need as NeedModel;

class NeedValidate extends BaseValidate
{
    protected $rule = [
        'goods_id' => 'require|isPositiveInteger|isGoodsId',
        'project_id' => 'require|isPositiveInteger',
        'need' => 'require|isPositiveInteger',
        'buy' => 'require|isPositiveInteger',
        'have' => 'require|isPositiveInteger',
        'note' => 'ok',
    ];

    protected $message = [
        'goods_id.require' => '材料编号不能为空',
        'need.require' => '需求数量不能为空',
        'buy.require' => '购买数量不能为空',
        'have.require' => '平衡利库数量不能为空',
    ];

    //材料是否已存在
    protected function isGoodsId($value, $rule='', $data='', $field=''){ 
        $id = input('param.id', '');
        if(!$id){ 
            $need = NeedModel::where('project_id', $data['project_id'])->where('goods_id', $value)->find();
            if($need){ 
                return '该材料已存在';
            }
        }
        return true;
    }
}