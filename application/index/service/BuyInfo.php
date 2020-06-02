<?php
namespace app\index\service;

use app\index\model\Buy as BuyModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\ContractSupply as ContractSupplyModel;
use app\index\model\Project as ProjectModel;
use think\Db;
use think\Validate;


class BuyInfo{

    // 获取列表
    public function getList($params, $limit = 15){
        $where = [];
        $hasWhere = [];
        if (isset($params['search']) && $params['search']){
            $hasWhere[] = ['name|number', 'like', "%{$params['search']}%"];
        }

        if (isset($params['cate_id']) && $params['cate_id']){
            $hasWhere[] = ['cate_id', '=', $params['cate_id']];
        }

        if (isset($params['project_id']) && $params['project_id']){
            $where[] = ['project_id', '=', $params['project_id']];
        }

        if (isset($params['buy_id']) && $params['buy_id']){
            $where[] = ['buy_id', '=', $params['buy_id']];
        }

        if (isset($params['supply_number']) && $params['supply_number']){
            if ($params['supply_number'] == 'none'){
                $where[] = ['contract_supply_id', '=', 'none'];
            }else{
                $ret = ContractSupplyModel::field('id')->get(['number' => $params['supply_number']]);
                if ($ret){
                    $where[] = ['contract_supply_id', '=', $ret['id']];
                }
            }
        }else{
            $where[] = ['contract_supply_id', '=', 'none'];
        }

        if (isset($params['type']) && $params['type']){
            $where[] = ['type', '=', $params['type']];
        }

        if (isset($params['create_time']) && $params['create_time']){
            $time = explode('至', $params['create_time']);
            $where[] = ['Need.create_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        $list = BuyInfoModel::hasWhere('goods', $hasWhere)->with(['goods' => ['unit', 'cate'], 'need', 'supply', 'project'])->where($where)->order('stock_status asc,create_time desc')->paginate($limit);
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
                'num'        => $v['need'] - $v['buy'],
                'need_id'    => $v['id'],
                'type'       => $v['type'] == '自购'?'1':'2',
            ];

            // 如果是甲供材料，供应商直接为业主对应的供应商
            if ($v['type'] == '甲供'){
                // 获取工程项目对应的业主
                $project = ProjectModel::with(['contract', 'supply'])->get($v['project_id']);
                $data['supply_id'] = $project['supply_id'];
            }

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
            return returnInfo('', 201, '修改错误!<br>原因：' . $validate->getError());
        }
        $data = [
            'id' => $params['id'],
        ];
        if (isset($params['num'])){
            $data['num'] = $params['num'];
        }
        if (isset($params['num'])){
            $data['price'] = $params['price'];
        }
        if (isset($params['supply_id'])){
            $data['supply_id'] = $params['supply_id'];
        }

        $ret = BuyInfoModel::update($data);
        if (!$ret){
            return returnInfo('', 201, '修改错误！');
        }

        return returnInfo($ret, 200, "修改成功！");
    }

    # 删除采购材料
    public function del($params){
        # 验证规则
        $validate = validate('BuyInfoValidate');
        if(!$validate->scene('del')->check($params)){
            return returnJson('', 201, '删除错误！ 原因：' . $validate->getError());
        }

        $ret = BuyInfoModel::destroy($params['id']);
        if (!$ret){
            return returnInfo('', 201, '删除错误！');
        }

        return returnInfo($ret, 200, '删除成功！');
    }

    // 获取采购详情对应工程列表
    public function getProject($params){
        $project = BuyInfoModel::with(['project'])->field('project_id')->distinct(true)->where(['buy_id' => $params['id']])->all()->toArray();
        return $project;
    }

    // 验证是否有值，用于采购单确认判断
    public function check($params){
        $fields = [];
        $validate = Validate::make([
            'num' => 'float|min:0',
        ],[
            'num.number' => '请输入不含负号、特殊符号、大于0 的数字！',
        ]);

        if (!$validate->check(input('post.'))) {
            $fields[] = 'num';
        }

        if ($params['type'] == 1){
            if (!$params['price']){
                $fields[] = 'price';
            }
        }

        if (!$params['supply_id']){
            $fields[] = 'supply_name';
        }

        if ($fields){
            return returnJson($fields, 201, '请检查！');
        }else{
            return returnJson('', 200, '成功');
        }
    }





}