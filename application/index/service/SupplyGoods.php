<?php
namespace app\index\service;

use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\index\model\Goods as GoodsModel;
use app\lib\exception\BaseException;

class SupplyGoods{
    //供应商列表
    public function getList($params, $limit = 15){
        $where = [];
        if (isset($params['search']) && $params['search']){
            $where[] = ['name', 'like', "%{$params['search']}%"];
        }

        if (isset($params['goods_id']) && $params['goods_id']){
            $where[] = ['goods_id', '=', $params['goods_id']];
        }

        if (isset($params['supply_id']) && $params['supply_id']){
            $where[] = ['supply_id', '=', $params['supply_id']];
        }



        $list = SupplyGoodsModel::with(['supply', 'goods'])->where($where)->order('create_time desc')->paginate($limit);
        return $list;
    }

    //供应商列表
    public function getGoodsList($params, $limit = 15){
        $where = [];
        if (isset($params['search']) && $params['search']){
            $where[] = ['name|number', 'like', "%{$params['search']}%"];
        }

        if (isset($params['type_id']) && $params['type_id']){
            $where[] = ['type_id', '=', $params['type_id']];
        }
        $goods_ids = SupplyGoodsModel::field('id')->where(['supply_id' => $params['supply_id']])->column('goods_id');

        if($goods_ids){
            $where[] = ['id', 'not in', $goods_ids];
        }
        if (isset($params['create_time']) && $params['create_time']){
            $time = explode('至', $params['create_time']);
            $where[] = ['create_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        $list = GoodsModel::with(['unit', 'type', 'cate'])->where($where)->order('create_time desc')->paginate($limit);
        return $list;
    }

    public function add_contract($data){

        $id = $data['id'];
        $data_add = [];
        if (!isset($data['goods_list']) || !$data['goods_list']){
            return returnInfo('', 201, '请勾选要添加的材料！！');
        }
        foreach ($data['goods_list'] as &$v){
            $find = SupplyGoodsModel::where(['supply_id' => $id, 'goods_id' => $v['id']])->value('id');

            if ($find){
                return returnInfo('', 201, "材料：{$v['name']} 添加失败 <br>原因：材料已存在");
            }
            if(!isset($v['5'])) {
                return returnInfo('', 201, "材料：{$v['name']} 添加失败 <br>原因：请填写进货价");
            }

            if(!isset($v['6'])) {
                $v['6'] = '';
            }
            if (!is_numeric($v['5'])) {
                return returnInfo('', 201, "材料：{$v['name']} 添加失败 <br>原因：请填写正确的进货价");
            }
            $data_add[] = [
                'supply_id'  => $id,
                'goods_id'   => $v['id'],
                'price'      => $v['5'],
                'note'      => $v['6'],
                'create_time' => time(),
                'update_time' => time(),
            ];
        }
        $ret_add = SupplyGoodsModel::insertAll($data_add);
        if (!$ret_add){
            return returnInfo('', 201, '添加预算材料错误！');
        }else {
            return returnInfo('', 200, '添加供应商材料成功！');
        }
    }



    public function save_contract($id, $data){
        $find = SupplyGoodsModel::field('id')->where(['supply_id' => $data['s_id'], 'goods_id' => $data['g_id']])->find();
        if($find){
            $find = $find->toArray();
            if($find['id'] == $id){
                return [
                    'msg' => '更改供应商材料成功',
                    'code' => '200'
                ];
            }else{
                throw new BaseException(
                    [
                        'msg' => '更改供应商材料已存在！',
                        'errorCode' => 30005
                    ]);
            }
        }else {
            $data_save = [
                'supply_id' => $data['s_id'], 'goods_id' => $data['g_id'], 'price' => $data['price']
            ];
            $res = SupplyGoodsModel::where('id', $id)->update($data_save);
            if (!$res) {
                throw new BaseException(
                    [
                        'msg' => '修改供应商材料错误！',
                        'errorCode' => 30005
                    ]);
            }
            return [
                'msg' => '更改供应商材料成功',
                'code' => '200'
            ];
        }
    }

} 