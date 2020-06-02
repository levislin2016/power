<?php
namespace app\index\service;

use app\index\model\Stock as StockModel;
use app\index\model\StockInfo as StockInfoModel;
use app\index\model\Need as NeedModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\Buy as BuyModel;
use think\Db;

class Stock{

    public function getList($params, $limit = 15){
        $where = [];
        if (isset($params['search']) && $params['search']){
            $where[] = ['number', 'like', "%{$params['search']}%"];
        }

        if (isset($params['create_time']) && $params['create_time']){
            $time = explode('至', $params['create_time']);
            $where[] = ['create_time', 'between time', [trim($time[0]), trim($time[1])]];
        }


        if (isset($params['type']) && $params['type']){
            $where[] = ['type', '=', $params['type']];
        }

        if (isset($params['buy_id']) && $params['buy_id']){
            $where[] = ['buy_id', '=', $params['buy_id']];
        }

        $list = StockModel::where($where)->order('create_time desc')->paginate($limit);
        return $list;
    }

    # 采购入库
    public function in($params){
        if (!isset($params['json_data']) || !$params['json_data']){
            return returnInfo('', 201, '请先搜索需要入库的供货商合同！');
        }
        $buy_id = $params['json_data'][0]['buy_id'];
        $buy = BuyModel::field('status')->get($buy_id);
        if ($buy->getData('status') == 3){
            return returnInfo('', 201, '该合同已全部完成入库，请勿重复入库！');
        }
        # 验证规则
        $validate = validate('StockValidate');
        if(!$validate->scene('in')->check($params)){
            return returnInfo('', 201, $validate->getError());
        }
        Db::startTrans();

        // 判断是否有填写采购数量
        $stock_data = [];
        foreach ($params['json_data'] as $k => $v){
            if (!isset($v['stock_num']) || !$v['stock_num']){
                continue;
            }
            $stock_num = trim($v['stock_num']);
//            if(!preg_match("/^[1-9][0-9]*$/",$stock_num)){
//                return returnInfo('', 201, "{$v['supply_name']} - {$v['goods_name']}<br>采购数量填写错误，请填入正确的数字！");
//            }
            $max = round($v['num'] - $v['num_ok'], 2);
            if ($stock_num > $max){
                return returnInfo('', 201, "{$v['supply_name']} - {$v['goods_name']}<br>入库数量不能 大于 未采购数量 {$max}！");
            }

            $add_data = [
                'store_id'   => $params['store_id'],
                'buy_id'     => $v['buy_id'],
                'goods_id'   => $v['goods_id'],
                'project_id' => $v['project_id'],
                'stock_num'  => $stock_num,
                'type'       => $v['type'],
                'supply_id'  => $v['supply_id'],
                'price'      => $v['price'],
            ];
            // 如果入库数量等于未入库数量，入库状态修改
            if ($max == $v['stock_num']){
                $add_data['stock_status'] = 2;
            }else{
                $add_data['stock_status'] = 1;
            }

            $stock_data[] = $add_data;
        }
        if (!$stock_data){
            Db::rollback();
            return returnInfo('', 201, '请填写入库材料的数量！');
        }

        // 生成入库单
        $stock = StockModel::create([
            'buy_id' => $buy_id,
            'number' => create_order_no('B'),
            'type'   => 1,
        ]);

        if (!$stock){
            Db::rollback();
            return returnInfo('', 201, '生成入库单失败！');
        }

        // 增加材料的入库数量
        foreach ($stock_data as $k => $v){
            $temp = $v;
            $temp['stock_id'] = $stock['id'];
            // 添加入库详情
            $ret = StockInfoModel::create($temp);
            if (!$ret){
                Db::rollback();
                return returnInfo('', 204, "材料入库失败！");
            }

            // 增加工程已采购数量
            $ret = NeedModel::where([
                'goods_id'   => $v['goods_id'],
                'project_id' => $v['project_id'],
                'type'       => $v['type'],
            ])->setInc('in', $v['stock_num']);
            if (!$ret){
                Db::rollback();
                return returnInfo('', 205, "材料入库失败！");
            }
            // 增加采购单已采购数量
            $ret = BuyInfoModel::where([
                'goods_id'   => $v['goods_id'],
                'project_id' => $v['project_id'],
                'type'       => $v['type'],
            ])->setInc('num_ok', $v['stock_num']);
            if (!$ret){
                Db::rollback();
                return returnInfo('', 206, "材料入库失败！");
            }
            // 修改材料的入库状态
            $ret = BuyInfoModel::where([
                'goods_id'   => $v['goods_id'],
                'project_id' => $v['project_id'],
                'type'       => $v['type'],
            ])->update([
                'stock_status' => $v['stock_status']
            ]);

        }

        // 判断采购单是否全部采购完成，修改采购单状态
        $buy_status = BuyInfoModel::field('id')->where(['buy_id' => $buy_id, 'stock_status' => 1])->find();
        if (!$buy_status){
            $ret = BuyModel::update([
                'id'     => $stock_data[0]['buy_id'],
                'status' => 3,
            ]);
            if (!$ret){
                Db::rollback();
                return returnInfo('', 210, "材料入库失败！");
            }
        }

        Db::commit();


        return returnInfo('', 200, "入库成功 入库单号:{$stock['number']}");
    }

} 