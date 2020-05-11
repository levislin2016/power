<?php
namespace app\index\service;

use app\index\model\Goods as GoodsModel;
use app\index\model\BuyProject as BuyProjectModel;
use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\lib\exception\BaseException;
use think\Db;
use think\facade\App;


class BuyProject{
    // 获取采购对应的工程列表
    public function getList($params, $limit = 10, $order = 'desc'){
        $where = [];
        $hasWhere = [];
        if (isset($params['search']) && $params['search']){
            $hasWhere[] = ['Project.name', 'like', "%{$params['search']}%"];
        }

        if (isset($params['buy_id']) && $params['buy_id']){
            $where[] = ['buy_id', '=', $params['buy_id']];
        }

        if (isset($params['contract_id']) && $params['contract_id']){
            $where[] = ['Project.contract_id', '=', $params['contract_id']];
        }

        if (isset($params['open_time']) && $params['open_time']){
            $time = explode('至', $params['open_time']);
            $where[] = ['Project.open_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        $list = BuyProjectModel::hasWhere('project', $hasWhere)->with(['project' => ['contract']])->where($where)->paginate($limit);

        return $list;
    }

    // 新增采购工程
    public function add($params){
        if (!isset($params['json_arr']) || !$params['json_arr']){
            return returnInfo('', 201, '请勾选要采购的工程！！');
        }
        foreach ($params['json_arr'] as $k => $v){
            $data = [
                'buy_id'     => $params['id'],
                'project_id' => $v['id'],
            ];

            // 验证场景
            $validate = validate('BuyProjectValidate');
            if (!$validate->scene('add')->check($data)){
                return returnInfo('', 201, "添加失败 <br>原因：" . $validate->getError());
            }

            $ret_add = BuyProjectModel::create($data);
            if (!$ret_add){
                return returnInfo('', 201, '添加错误！');
            }
        }
        return returnInfo('', 200, "添加成功！");
    }

    // 修改预算材料
    public function edit($params){
        // 验证 登录 场景
        $validate = validate('BuyProjectValidate');
        if (!$validate->scene('edit')->check($params)){
            return returnInfo('', 201, $validate->getError());
        }

        $ret = NeedModel::update([
            'need' => $params['need'],
        ], ['id' => $params['id']]);
        if (!$ret){
            return returnInfo('', 201, '修改数量错误！');
        }

        return returnInfo('', 200, "数量成功修改为{$params['need']}！");
    }

    # 删除预算材料
    public function del($params){
        # 验证规则
        $validate = validate('BuyProjectValidate');
        if(!$validate->scene('del')->check($params)){
            return returnJson('', 201, $validate->getError());
        }

        $ret = BuyProjectModel::destroy($params['id']);
        if (!$ret){
            return returnInfo('', 201, '删除错误！');
        }

        return returnInfo($ret, 200, '删除成功！');
    }

} 