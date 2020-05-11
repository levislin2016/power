<?php

namespace app\index\validate;

use app\index\model\Buy as BuyModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\BuyProject;
use app\index\model\Project as ProjectModel;

class BuyInfoValidate extends BaseValidate
{
    protected $rule = [
        'id'         => 'checkBuyStatus',
        'num'        => 'checkNum',
        'buy_id'     => 'require|unique:BuyInfo,buy_id^project_id^goods_id',
        'project_id' => 'require|unique:BuyInfo,buy_id^project_id^goods_id',
        'goods_id'   => 'require|unique:BuyInfo,buy_id^project_id^goods_id',
    ];

    protected $message = [
        'num'                => '该材料已采购完成，无需再次采购',
        'buy_id.unique'      => '请勿重复采购该材料！',
        'project_id.unique'  => '请勿重复采购该材料！',
        'goods_id.unique'    => '请勿重复采购该材料！',

    ];

    protected $scene = [
        'add'  => ['num.>', 'buy_id', 'project_id', 'goods_id'],
        'edit' => ['id', 'num'],
        'del'  => ['id'],
    ];

    // 设定确认生成采购单验证场景
    public function sceneSure(){
        return $this->only(['buy_id'])
            ->remove('buy_id', 'require')
            ->remove('buy_id', 'unique')
            ->append('buy_id', 'checkSure');
    }

    // 判断采购单的状态， 判断是否为待确认状态
    protected function checkBuyStatus($value,$rule,$data=[])
    {
        # 判断采购单的状态是否可以删除工程
        $buy = BuyModel::get($data['buy_id']);
        if ($buy->getData('status') != 1){
            return '采购单状态必须为 [待确认]！';
        }

        return true;
    }

    // 判断采购数量不能大于预算数量
    protected function checkNum($value,$rule,$data=[])
    {
        $max = $data['need_need'] - $data['need_need_ok'];
        if ($data['num'] > $max){
            return "采购数量不得大于 未采购数量：{$max}";
        }

        return true;
    }

    // 判断采购单能否生成
    protected function checkSure($value,$rule,$data=[])
    {
        $list = BuyInfoModel::with(['project', 'goods' => ['unit', 'type']])->all(['buy_id' => $value])->toArray();
        if (!$list){
            return "请先添加需要采购的工程！";
        }

        $buy = BuyModel::get($value);
        if ($buy->getData('status') != 1){
            return "采购单状态必须为 [待确认] 才能确认生成采购单！";
        }

        foreach ($list as $k => $v){
            if ($v['num'] == 0){
                return "生成失败！<br>工程：{$v['project_name']}<br>材料：{$v['goods_name']} 数量不能为 0！";
            }
        }

        return true;
    }





}