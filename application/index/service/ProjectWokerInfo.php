<?php
namespace app\index\service;

use app\index\model\Project as ProjectModel;
use app\index\model\ProjectWoker as ProjectWokerModel;
use app\index\model\ProjectWokerInfo as ProjectWokerInfoModel;
use app\index\model\Need as ProjectStockModel;
use app\index\model\StockAll as StockAllModel;
use app\lib\exception\BaseException;
use app\index\model\Woker as WokerModel;


class ProjectWokerInfo{


    // 获取列表
    public function getGoodsList($params, $limit = 15){
        $where = [];
        $hasWhere = [];
        if (isset($params['search']) && $params['search']){
            $hasWhere[] = ['name|number', 'like', "%{$params['search']}%"];
        }

        if (isset($params['search']) && $params['search']){
            $hasWhere[] = ['cate_id', '=', $params['cate_id']];
        }
        if (isset($params['project_woker_id']) && $params['project_woker_id']){
            $where[] = ['project_woker_id', '=', $params['project_woker_id']];
        }
        $list = ProjectWokerInfoModel::hasWhere('goods', $hasWhere)->with(['goods' => ['cate']])->where($where)->paginate($limit);
        return $list;
    }


    # 删除工程对应工程队
    public function del($params){
        $ret = ProjectWokerInfoModel::destroy($params['id']);
        if (!$ret){
            return returnInfo('', 201, '删除错误！');
        }

        return returnInfo($ret, 200, '删除成功！');
    }

    // 添加工程队对应预算材料
    public function addInfo($params){

        if (!isset($params['goods_list']) || !$params['goods_list']){
            return returnInfo('', 201, '请勾选要添加的工程队！！');
        }

        foreach ($params['goods_list'] as $k => $v){
            $data = [
                'project_woker_id' => $params['project_woker_id'],
                'goods_id' => $v['goods_id'],
                'type'     =>1,
                'num'      =>0,
                'note'     =>'',
            ];
            $validate = validate('ProjectWokerValidate');
            if (!$validate->scene('addInfo')->check($data)){
                return returnInfo('', 201, "材料：{$v['goods_name']} 添加失败 <br>原因：" . $validate->getError());
            }
            $ret_add = ProjectWokerInfoModel::create($data);
            if (!$ret_add){
                return returnInfo('', 201, '添加材料错误！');
            }
        }
        return returnInfo($ret_add, 200, "添加成功！");
    }






    public function select_list($params){ 
        $list = ProjectWokerModel::useGlobalScope(false)->alias('pw')
            ->leftJoin('woker w','pw.woker_id = w.id')
            ->leftJoin('supply_goods sg','pw.supply_goods_id = sg.id')
            ->leftJoin('supply s','sg.s_id = s.id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){ 
                    $query->where('w.name|g.number|g.name|s.name', 'like', '%'.$params['search'].'%');
                }
                $query->where('pw.project_id', $params['id']);
            })
            ->field('pw.*, w.name as woker_name, s.name as supply_name, sg.g_id, sg.s_id, g.name, g.image, g.number, sg.price, u.name as unit')
            ->order('pw.create_time', 'desc')
            ->paginate(10, false, [
                'query'     => $params,
            ]);

        return $list;
            
    }

    //可分配材料
    public function allot_goods($project_id){
        $list = ProjectStockModel::useGlobalScope(false)->alias('ps')
            ->field('ps.supply_goods_id, sum(ps.in) as num, s.name as supply_name, g.name, g.number, u.name as unit')
            ->group('ps.supply_goods_id')
            ->having('sum(ps.in) > 0')
            ->leftJoin('supply_goods sg','ps.supply_goods_id = sg.id')
            ->leftJoin('supply s','sg.s_id = s.id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->where('ps.project_id', $project_id)
            ->select();
        
        return $list;
    }

    //分配材料
    public function allot($params){ 
        //查询需要分配的材料
        $project_stock_list = ProjectStockModel::where('project_id', $params['pid'])->where('supply_goods_id', $params['supply_goods_id'])->where('in', '>', 0)->select();
        //判断可分配数量是否足够
        $stock_save = $this->allot_project_stock_data($params, $project_stock_list);
        //开启事务
        ProjectStockModel::startTrans();
        //查询是否已有分配记录，有则修改，无则添加
        $projectWoker = ProjectWokerModel::where('project_id', $params['pid'])->where('supply_goods_id', $params['supply_goods_id'])->where('woker_id', $params['woker_id'])->find();
        if($projectWoker){ 
            $projectWoker->can = $projectWoker->can + $params['num'];
            $projectWoker->not = $projectWoker->not + $params['num'];
            $res = $projectWoker->save();
        }else{ 
            $res = ProjectWokerModel::create([
                    'project_id'        =>  $params['pid'],
                    'supply_goods_id'   =>  $params['supply_goods_id'],
                    'woker_id'          =>  $params['woker_id'],
                    'can'               =>  $params['num'],
                    'get'               =>  0,
                    'not'               =>  $params['num'],
                    'back'              =>  0,
                ]);

        }
        if(!$res){
            ProjectStockModel::rollback();
            throw new BaseException(
                [
                    'msg' => '分配库存错误！',
                    'errorCode' => 41002
                ]);
        }

        //项目库存表的未分配数量扣减，已分配数量添加
        foreach($stock_save as $vo){
            $projectStock = ProjectStockModel::get($vo['id']);
            $projectStock->freeze = $projectStock->freeze + $vo['num'];
            $projectStock->in = $projectStock->in - $vo['num'];
            $res = $projectStock->save();
            if(!$res){ 
                ProjectStockModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '分配库存错误！',
                        'errorCode' => 41003
                    ]);
            }
            $stockAll = StockAllModel::where('stock_id', $vo['stock_id'])->where('supply_goods_id', $vo['supply_goods_id'])->find();
            $stockAll->freeze = $stockAll->freeze + $vo['num'];
            $res = $stockAll->save();
            if(!$res){ 
                ProjectStockModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '分配库存错误！',
                        'errorCode' => 41004
                    ]);
            }
        }
        ProjectStockModel::commit();
        return [
            'msg' => '分配成功',
        ];

    }

    //判断可分配数量,整理项目库存扣减数据
    public function allot_project_stock_data($params, $project_stock_list){
        $stock_in = 0;
        $stock_save = array();
        $stock_save_num = $params['num'];
        foreach($project_stock_list as $project_stock){
            $stock_in += $project_stock['in'];
            if($stock_save_num > 0){
                $stock_save[] = array(
                    'id'                =>  $project_stock['id'],
                    'stock_id'          =>  $project_stock['stock_id'],
                    'supply_goods_id'   =>  $project_stock['supply_goods_id'],
                    'num'               =>  $project_stock['in'] < $stock_save_num?$project_stock['in']:$stock_save_num
                );
            }
            $stock_save_num -= $project_stock['in'];
        }
        if($stock_in < $params['num']){
            throw new BaseException(
                [
                    'msg' => '可分配的数量不足！',
                    'errorCode' => 41001
                ]);
        }
        return $stock_save;
    }
}