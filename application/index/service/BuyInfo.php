<?php
namespace app\index\service;

use app\index\model\Buy as BuyModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\Need as NeedModel;
use think\Db;


class BuyInfo{

    // 获取列表
    public function getList($params, $limit = 15){
        $where = [];
        $hasWhere = [];
        if (isset($params['search']) && $params['search']){
            $hasWhere[] = ['Goods.name|number', 'like', "%{$params['search']}%"];
        }

        if (isset($params['project_id']) && $params['project_id']){
            $where[] = ['project_id', '=', $params['project_id']];
        }

        if (isset($params['buy_id']) && $params['buy_id']){
            $where[] = ['buy_id', '=', $params['buy_id']];
        }

        if (isset($params['create_time']) && $params['create_time']){
            $time = explode('至', $params['create_time']);
            $where[] = ['Need.create_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        $list = BuyInfoModel::hasWhere('goods', $hasWhere)->with(['goods' => ['unit', 'type'], 'need'])->where($where)->order('create_time desc')->paginate($limit);
        return $list;
    }

    // 新增采购材料
    public function add($params){
        if (!isset($params['json_arr']) || !$params['json_arr']){
            return returnInfo('', 201, '请勾选要添加的材料！！');
        }
        $validate = validate('BuyInfoValidate');
        Db::startTrans();
        foreach ($params['json_arr'] as $k => $v){
            $data = [
                'buy_id'     => $params['buy_id'],
                'project_id' => $v['project_id'],
                'goods_id'   => $v['goods_id'],
                'num'        => $v['need'] - $v['need_ok'],
                'need_id'    => $v['id'],
            ];

            // 验证场景
            if (!$validate->scene('add')->check($data)){
                Db::rollback();
                return returnInfo('', 201, "材料：{$v['goods_name']} 添加失败 <br>原因：" . $validate->getError());
            }

            $ret_add = BuyInfoModel::create($data);
            if (!$ret_add){
                Db::rollback();
                return returnInfo('', 201, '添加采购材料错误！');
            }
        }
        Db::commit();
        return returnInfo('', 200, "添加成功！");
    }

    // 修改采购材料数量
    public function edit($params){
        // 验证 登录 场景
        $validate = validate('BuyInfoValidate');
        if (!$validate->scene('edit')->check($params)){
            return returnInfo('', 201, '修改错误 原因：' . $validate->getError());
        }

        $ret = BuyInfoModel::update([
            'num' => $params['num'],
        ], ['id' => $params['id']]);
        if (!$ret){
            return returnInfo('', 201, '修改数量错误！');
        }

        return returnInfo('', 200, "数量成功修改为{$params['num']}！");
    }

    # 删除采购材料
    public function del($params){
        # 验证规则
        $validate = validate('BuyInfoValidate');
        if(!$validate->scene('del')->check($params)){
            return returnJson('', 201, $validate->getError());
        }

        $ret = BuyInfoModel::destroy($params['id']);
        if (!$ret){
            return returnInfo('', 201, '删除错误！');
        }

        return returnInfo($ret, 200, '删除成功！');
    }

    # 确认生成采购单
    public function sure($params){
        # 验证规则
        $validate = validate('BuyInfoValidate');
        if(!$validate->scene('sure')->check($params)){
            return returnJson('', 201, $validate->getError());
        }

        $ret = BuyModel::update([
            'status' => 2,
        ],['id' => $params['buy_id']]);
        if (!$ret){
            return returnInfo('', 201, '生成错误！');
        }

        return returnInfo($ret, 200, '生成成功！');
    }



}