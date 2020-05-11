<?php
namespace app\index\service;

use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\index\model\Goods as GoodsModel;
use app\index\model\Buy as BuyModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\Need as NeedModel;
use app\index\model\StockOrder as StockOrderModel;
use app\index\model\StockOrderInfo as StockOrderInfoModel;
use app\index\model\Project as ProjectModel;
use app\index\model\StockAll as StockAllModel;
use app\index\model\ProjectStock as ProjectStockModel;
use app\lib\exception\BaseException;
use think\Db;

class Buy{

    public function getList($params, $limit = 10){
        $where = [];
        if (isset($params['search']) && $params['search']){
            $where[] = ['number', 'like', "%{$params['search']}%"];
        }

        if (isset($params['create_time']) && $params['create_time']){
            $time = explode('至', $params['create_time']);
            $where[] = ['create_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        if (isset($params['status']) && $params['status']){
            $where[] = ['status', '=', $params['status']];
        }

        if (isset($params['from']) && $params['from']){
            $where[] = ['from', '=', $params['from']];
        }

        $list = BuyModel::where($where)->order('create_time desc')->paginate($limit);
        return $list;
    }

    # 删除采购单
    public function cancel($params){
        # 验证规则
        $validate = validate('BuyValidate');
        if(!$validate->scene('cancel')->check($params)){
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

    // 创建采购单
    public function addBuy($params){
        $data = [
            'number' => create_order_no('C'),
            'from'   => $params['from'],
        ];
        $ret = BuyModel::create($data);
        if (!$ret){
            return returnInfo('', 201, '创建采购单失败！');
        }

        return returnInfo($ret, 200, "创建采购单成功！编号:{$ret['number']}");
    }


}