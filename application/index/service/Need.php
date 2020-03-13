<?php
namespace app\index\service;

use app\index\model\Need as NeedModel;
use app\index\model\Goods as GoodsModel;
use app\index\service\Type as TypeService;
use app\lib\exception\BaseException;
use think\Db;

class Need{

    // 获取材料列表
    public function getList($params, $limit = 20, $order = 'desc'){

        $list = NeedModel::useGlobalScope(false)->alias('n')
                            ->leftJoin('goods g','n.goods_id = g.id')
                            ->leftJoin('unit u','g.unit_id = u.id')

                            ->where(function ($query) use($params) {
                                $query->where('n.project_id', $params['project_id']);
                            })
                            ->where(function ($query) use($params) {
                                $query->where('g.name', 'like', '%' . $params['keywords'] . '%');
                                $query->whereOr('g.number', 'like', '%' . $params['keywords'] . '%');
                            })
                            ->field('n.id, n.company_id, n.project_id, n.goods_id, n.need, n.need_ok, n.type, n.check, n.note, g.type_id, n.create_time, g.number, g.name, g.unit_id, g.image, u.name as unit')
                            ->order('n.create_time', $order)
                            ->paginate($limit, false, ['path' => "javascript:ajaxPage([PAGE]);"]);

        if (!empty($params['debug'])) {
            dump(Db::getLastSql());
        }
        return $list;
    }

    // 获取带分类的列表
    public function getListWithType($list){
        if (!$list){
            return $list;
        }

        // 获取分类
        $type_list = (new TypeService())->getList();

        $arr = [];
        foreach ($list as $k => $v){
            $arr[$v['type_id']]['type_name'] = $type_list[$v['type_id']];
            $arr[$v['type_id']]['list'][] = $v;
        }

        return $arr;
    }

    public function add($data){
        // 验证 登录 场景
        $validate = validate('NeedValidate');
        if (!$validate->scene('add')->check($data)){
            return returnInfo('', 201, $validate->getError());
        }

        $ret_add = NeedModel::create($data);
        if (!$ret_add){
            return returnInfo('', 201, '添加预算材料错误！');
        }

        $goods = GoodsModel::with('unit')->get($data['goods_id']);

        $ret_data = [
            'id'     => $ret_add['id'],
            'name'   => $goods['name'],
            'number' => $goods['number'],
            'unit'   => $goods['unit']['name'],
            'create_time' => $ret_add['create_time'],
        ];
        // 获取商品序号
        $ret_data['sort'] = NeedModel::where('project_id', '=', $data['project_id'])->count();

        return returnInfo($ret_data, 200, "已添加 {$ret_data['number']} {$ret_data['name']}");
    }

    public function add_need($data){ 
        $data['company_id'] = session('power_user.company_id');
        $need = NeedModel::create($data);
        if(!$need){ 
            throw new BaseException(
            [
                'msg' => '添加需求材料错误！',
                'errorCode' => 50004
            ]);
        }
        return [
            'msg' => '添加需求材料成功',
        ];
    }

    public function save_need($id, $data){
        $res = NeedModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改需求材料错误！',
                'errorCode' => 50005
            ]);
        }
        return [
            'msg' => '修改需求材料成功',
        ];
    }

} 