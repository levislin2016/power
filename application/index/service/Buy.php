<?php
namespace app\index\service;

use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\index\model\Need as NeedModel;
use app\index\model\Buy as BuyModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\Project as ProjectModel;

use think\Db;

class Buy{

    public function getList($params, $limit = 10){
        $where = [];
        $where2 = [];
        $hasWhere = [];
        if (isset($params['search']) && $params['search']){
            $where[] = ['number', 'like', "%{$params['search']}%"];
        }

        if (isset($params['search2']) && $params['search2']){
            $where2[] = ['name', 'like', "%{$params['search2']}%"];
            $project = ProjectModel::where($where2)->field('id')->all()->toArray();
            $project_ids = array_column($project, 'id');
            if ($project_ids){
                $hasWhere[] = ['project_id', 'in', $project_ids];
            }
        }

        if (isset($params['create_time']) && $params['create_time']){
            $time = explode('至', $params['create_time']);
            $where[] = ['create_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        if (isset($params['status']) && $params['status']){
            $where[] = ['status', '=', $params['status']];
        }

        if (isset($params['search2']) && $params['search2']) {
            $list = BuyModel::hasWhere('buyProject', $hasWhere)->with(['buyProject' => ['project2']])->where($where)->order('create_time desc')->paginate($limit);
        }else{
            $list = BuyModel::with(['buyProject' => ['project2']])->where($where)->order('create_time desc')->paginate($limit);
        }
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
        ];
        $ret = BuyModel::create($data);
        if (!$ret){
            return returnInfo('', 201, '创建采购单失败！');
        }

        return returnInfo($ret, 200, "创建采购单成功！编号:{$ret['number']}");
    }

    # 确认生成采购单
    public function sure($params){
        # 验证规则
        $validate = validate('BuyInfoValidate');
        if(!$validate->scene('sure')->check($params)){
            return returnJson('', 201, $validate->getError());
        }

        $ret = BuyModel::update([
            'id'     => $params['buy_id'],
            'status' => 2,
        ]);
        if (!$ret){
            return returnInfo('', 201, '生成错误！');
        }

        $buy_info_list = BuyInfoModel::all(['buy_id' => $params['buy_id']]);
        foreach ($buy_info_list as $k => $v){
            NeedModel::where([
                'goods_id'   => $v['goods_id'],
                'project_id' => $v['project_id'],
                'type'       => $v['type'],
            ])->setInc('buy', $v['num']);
        }

        return returnInfo($ret, 200, '确认生成采购单成功, 采购单修改为 [采购中]！');
    }

    # 获取采购单清单明细
    public function total($param){
        $where = [];
        $where[] = ['buy_id', '=', $param['buy_id']];
        if (isset($param['supply_id']) && $param['supply_id']){
            $where[] = ['supply_id', '=', $param['supply_id']];
        }
        $ret = BuyInfoModel::with(['goods' => ['type', 'unit'], 'need', 'project', 'supply'])->where($where)->paginate($param['limit']);
        if (!$ret){
            return returnInfo([], 200, '获取成功');
        }

        $arr = [];
        foreach ($ret as $k => $v){
            $key = 'k' . $v['goods_id'] . '_' . $v['supply_id'];
            if (!isset($arr[$key])){
                $arr[$key] = [
                    'goods_name'   => $v['goods_name'],
                    'goods_number' => $v['goods_number'],
                    'price'        => $v['price'],
                    'unit_name'    => $v['unit_name'],
                    'type_name'    => $v['type_name'],
                    'need_type'    => $v['need_type'],
                    'supply_name'  => $v['supply_name']
                ];
            }
            if (!isset($arr[$key]['num'])){
                $arr[$key]['num'] = 0;
            }
            $arr[$key]['num'] += $v['num'];

            if (!isset($arr[$key]['num_ok'])){
                $arr[$key]['num_ok'] = 0;
            }
            $arr[$key]['num_ok'] += $v['num_ok'];

            if (!isset($arr[$key]['need_need'])){
                $arr[$key]['need_need'] = 0;
            }
            $arr[$key]['need_need'] += $v['need_need'];

            if (!isset($arr[$key]['need_buy'])){
                $arr[$key]['need_buy'] = 0;
            }
            $arr[$key]['need_buy'] += $v['need_buy'];
        }
        $new_arr = [];
        foreach ($arr as $k => $v){
            $new_arr[] = $v;
        }

        return returnInfo($new_arr, 200, '获取成功');
    }

    // 获取采购单中的所有供应商
    public function getSupply($param){
        $where = [];
        $where[] = ['buy_id', '=', $param['buy_id']];
        $ret = BuyInfoModel::with(['supply'])->field('supply_id')->distinct(true)->where($where)->all()->toArray();
        return $ret;
    }


}