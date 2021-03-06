<?php
namespace app\index\service;

use app\index\model\Need as NeedModel;
use app\index\model\Project as ProjectModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\ProjectWokerInfo as ProjectWokerInfoModel;
use app\index\model\Goods as GoodsModel;
use app\index\service\Type as TypeService;
use app\lib\exception\BaseException;
use think\Db;

class Need{

    // 获取列表
    public function getList($params, $limit = 15){
        $where = [];
        $hasWhere = [];
        $projectWhere = [];
        if (isset($params['search']) && $params['search']){
            $hasWhere[] = ['name|number', 'like', "%{$params['search']}%"];
        }

        if (isset($params['project_id']) && $params['project_id']){
            $where[] = ['project_id', '=', $params['project_id']];
        }

        if (isset($params['cate_id']) && $params['cate_id']){
            $ids_arr = explode(',', $params['cate_id']);
            $where[] = ['cate_id', 'in', $ids_arr];
        }

        if (isset($params['type']) && $params['type']){
            $where[] = ['type', '=', $params['type']];
        }

        if (isset($params['buy_status']) && $params['buy_status']){
            $where[] = ['buy_status', '=', $params['buy_status']];
        }

        // 解决如果是采购单进来的，已经在采购单添加的预算 不再显示
        if (isset($params['buy_id']) && $params['buy_id']){
            $buyinfo_ids = BuyInfoModel::field('need_id')->where(['buy_id' => $params['buy_id']])->all()->toArray();
            if ($buyinfo_ids){
                $buyinfo_ids = array_column($buyinfo_ids, 'need_id');
                $where[] = ['Need.id', 'not in', $buyinfo_ids];
            }
        }

        if (isset($params['project_woker_id']) && $params['project_woker_id']){
            $project_woker_ids = ProjectWokerInfoModel::where(['project_woker_id' => $params['project_woker_id']])->column('goods_id');
            if ($project_woker_ids){
                $where[] = ['Need.goods_id', 'not in', $project_woker_ids];
            }
        }

        if (isset($params['project_status']) && $params['project_status']){
            $project_ids = ProjectModel::field('id')->where(['status' => 2])->all()->toArray();
            $project_ids = array_column($project_ids, 'id');
            $where[] = ['project_id', 'in', $project_ids];
        }

        if (isset($params['create_time']) && $params['create_time']){
            $time = explode('至', $params['create_time']);
            $where[] = ['create_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        $list = NeedModel::hasWhere('goods', $hasWhere)->with(['goods' => ['unit', 'cate'], 'project'])->where($where)->order('create_time desc')->paginate($limit);
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
                'status'     => 1,
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
        return returnInfo($ret, 200, "数量成功修改为{$params['need']}！");
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