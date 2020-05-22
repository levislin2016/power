<?php
namespace app\index\service;

use app\index\model\Stock as StockModel;
use app\lib\exception\BaseException;

class Stock{

    # 采购入库
    public function in($params){
        // 判断是否有填写采购数量
        $stock_data = [];
        foreach ($params['json_data'] as $k => $v){
            if (!isset($v['stock_num'])){
                continue;
            }
            $stock_num = trim($v['stock_num']);
            if(!preg_match("/^[1-9][0-9]*$/",$stock_num)){
                return returnInfo('', 201, "{$v['goods_name']} - {$v['supply_name']}<br>采购数量填写错误，请填入正确的数字！");
            }
            if ($stock_num > $v['num']){
                return returnInfo('', 201, "{$v['goods_name']} - {$v['supply_name']}<br>入库数量不能 大于 采购数量 {$v['num']}！");
            }

            $stock_data[] = [
                'buy_id'     => $v['buy_id'],
                'goods_id'   => $v['goods_id'],
                'project_id' => $v['project_id'],
                'stock_num'  => $stock_num,
                'type'       => $v['type'],
                'price'      => $v['price'],
            ];
        }
        if (!$stock_data){
            return returnInfo('', 201, '请填写入库材料的数量！');
        }
        # 验证规则
        $validate = validate('StockValidate');
        if(!$validate->scene('in')->check($params)){
            return returnInfo('', 201, $validate->getError());
        }



        $ret = BuyModel::update([
            'status' => 9
        ], ['id' => input('id')]);
        if (!$ret){
            return returnInfo('', 201, '修改失败');
        }
        return returnInfo('', 200, '修改成功');
    }

    //仓库列表
    public function select_list($params){ 
        $list = StockModel::where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('name', 'like', '%'.$params['search'].'%');
            }
        })->field('id, company_id, name, create_time')
        ->order('create_time', 'desc')
        ->paginate(10, false, [
            'query'     => $params,
        ]);
        return $list;
    }

    public function add_stock($data){ 
        $data['company_id'] = session('power_user.company_id');
        $stock = StockModel::create($data);
        if(!$stock){ 
            throw new BaseException(
            [
                'msg' => '添加仓库错误！',
                'errorCode' => 80004
            ]);
        }
        return [
            'msg' => '添加仓库成功',
        ];
    }

    public function save_stock($id, $data){
        $res = StockModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改仓库错误！',
                'errorCode' => 80005
            ]);
        }
        return [
            'msg' => '修改仓库成功',
        ];
    }

} 