<?php
namespace app\index\service;

use app\index\model\Need as NeedModel;
use app\index\model\Goods as GoodsModel;
use app\index\service\Type as TypeService;
use app\lib\exception\BaseException;
use think\Db;

class Need{

    // 获取列表
    public function getList($params, $limit = 15){
        $where = [];
        $hasWhere = [];
        if (isset($params['search']) && $params['search']){
            $hasWhere[] = ['name|number', 'like', "%{$params['search']}%"];
        }

        if (isset($params['project_id']) && $params['project_id']){
            $where[] = ['project_id', '=', $params['project_id']];
        }

        if (isset($params['type']) && $params['type']){
            $where[] = ['type', '=', $params['type']];
        }

        if (isset($params['create_time']) && $params['create_time']){
            $time = explode('至', $params['create_time']);
            $where[] = ['Need.create_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        $list = NeedModel::hasWhere('goods', $hasWhere)->with(['goods' => ['unit', 'type']])->where($where)->order('create_time desc')->paginate($limit);
        return $list;
    }

    // 新增预算材料
    public function add($params){
        if (!isset($params['goods_list']) || !$params['goods_list']){
            return returnInfo('', 201, '请勾选要添加的材料！！');
        }
        foreach ($params['goods_list'] as $k => $v){
            $data = [
                'project_id' => $params['id'],
                'type'       => $params['type'],
                'goods_id'   => $v['id'],
            ];

            // 验证场景
            $validate = validate('NeedValidate');
            if (!$validate->scene('add')->check($data)){
                return returnInfo('', 201, "材料：{$v['name']} 添加失败 <br>原因：" . $validate->getError());
            }

            $ret_add = NeedModel::create($data);
            if (!$ret_add){
                return returnInfo('', 201, '添加预算材料错误！');
            }
        }
        return returnInfo($ret_add, 200, "添加成功！");
    }

    // 修改预算材料
    public function edit($params){
        // 验证 登录 场景
        $validate = validate('NeedValidate');
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
        $validate = validate('NeedValidate');
        if(!$validate->scene('del')->check($params)){
            return returnJson('', 201, $validate->getError());
        }

        $ret = NeedModel::destroy($params['id']);
        if (!$ret){
            return returnInfo('', 201, '删除错误！');
        }

        return returnInfo($ret, 200, '删除成功！');
    }

    // 核对预算材料
    public function check($params){
        $ret = NeedModel::update([
            'check' => $params['check'],
        ], ['id' => $params['id']]);
        if (!$ret){
            return returnInfo('', 201, '修改错误！');
        }

        return returnInfo('', 200, "修改成功！");
    }

} 