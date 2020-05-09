<?php
namespace app\index\service;

use app\index\model\Project as ProjectModel;
use app\index\model\StockOrder as StockOrderModel;
use app\index\model\StockOrderInfo as StockOrderInfoModel;
use app\index\model\StockOrderInfo;
use app\lib\exception\BaseException;

class Project{

    public function getList($params, $limit = 15){
        $where = [];
        if (isset($params['search']) && $params['search']){
            $where[] = ['name', 'like', "%{$params['search']}%"];
        }

        if (isset($params['create_time']) && $params['create_time']){
            $time = explode('至', $params['create_time']);
            $where[] = ['create_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        if (isset($params['status']) && $params['status']){
            $where[] = ['status', '=', $params['status']];
        }

        $list = ProjectModel::with('contract')->where($where)->order('create_time desc')->paginate($limit);
        return $list;
    }

    # 添加工程
    public function add($data){
        # 判断验证肠镜
        $scene = 'add';
        if (isset($data['id']) && $data['id']){
            $scene = 'edit';
            $model = model('Project')::get($data['id']);
        }else{
            $model = model('Project');
        }
        # 验证规则
        $validate = validate('ProjectValidate');
        if(!$validate->scene($scene)->check($data)){
            return returnJson('', 201, $validate->getError());
        }

        $ret = $model->save($data);
        if(!$ret){
            return returnInfo('', 202, '操作错误！');
        }

        return returnInfo($ret, 200, '操作成功！');
    }

    # 删除工程
    public function del($id){
        $ret = ProjectModel::destroy($id);
        if (!$ret){
            return returnInfo('', 201, '删除错误！');
        }

        return returnInfo($ret, 200, '删除成功！');
    }

    public function save_project($id, $data){
        $res = ProjectModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改工程错误！',
                'errorCode' => 40005
            ]);
        }
        return [
            'msg' => '修改工程成功',
        ];
    }

    public function allocation_set($params){
        $project_info = \Db::table('pw_project_woker')->field('id, can, get, not, back')
                            ->where([
                                    'project_id'       => $params['passive_project_id'],
                                     'woker_id'        => $params['passive_woker_id'],
                                     'supply_goods_id' => $params['supply_goods_id']
                                    ])
                            ->where('delete_time', 0)
                            ->find();
        if($params['num'] == 0 || empty($params['num'])){
            if($project_info['can']-$project_info['back'] < $params['num']){
                throw new BaseException(
                    [
                        'msg' => '项目材料数大于0！',
                        'errorCode' => 301
                    ]);
            }
        }
        if($project_info['not'] < $params['num']){
            throw new BaseException(
                [
                    'msg' => '超过项目可领材料数！',
                    'errorCode' => 301
                ]);
        }
        $allocation_list = \Db::table('pw_project_stock')->field('id, num')->where(['supply_goods_id' => $params['supply_goods_id'], 'project_id' => $params['project_id']])->find();
        if(!$allocation_list){
            throw new BaseException(
                [
                    'msg' => '调拨项目需求材料不存在！',
                    'errorCode' => 302
                ]);
        }
        $project_stock = \Db::table('pw_project_stock')
                ->field('id,stock_id,freeze,in,num')
                ->where([
                            'project_id'      => $params['passive_project_id'],
                            'supply_goods_id' => $params['supply_goods_id'],
                        ])
                ->find();
        $params['stock_id'] = $project_stock['stock_id'];

        $project_stock_data['num'] = $project_stock['num']-$params['num'];
        $project_info_data['not'] = $project_info['not']-$params['num'];
        $project_stock_data['freeze'] = $project_stock['freeze']-$params['num'];
        $allocation_list_data['num'] = $allocation_list['num']+$params['num'];
        \Db::startTrans();
        try {
            $res = \Db::table('pw_project_woker')->where('id',$project_info['id'])->update($project_info_data);
            $res1 =\Db::table('pw_project_stock')->where('id', $project_stock['id'])->update($project_stock_data);
            if(!$allocation_list){
                $res2 = \Db::table('pw_project_stock')->insert([
                    'stock_id'        => $params['stock_id'],
                    'project_id'      => $params['project_id'],
                    'supply_goods_id' => $params['supply_goods_id'],
                    'num'             => $params['num'],
                    'in'              => $params['num'],
                    'freeze'          => '0',
                    'have'            => '0',
                    'extra'           => '0',
                    'create_time'     => time(),
                    'update_time'     => time(),
                    'delete_time'     => 0,
                ]);
            }else{
                $res2 = \Db::table('pw_project_stock')->where('id', $allocation_list['id'])->update($allocation_list_data);
            }
            \Db::commit();
        }catch (\Exception $e){
            throw new BaseException(
                [
                    'msg' => '调拨失败！',
                    'errorCode' => 4005
                ]);
            \Db::rollback();
        }
        self::allocation($params, 9);
        return [
            'msg' => '调拨成功',
        ];
    }

    //调拨入库
    public static function allocation($params, $type){
        $sid = 'A'.date('YmdHi', time()).rand(100,999);
        $data_passive['order'] = [
            'company_id'  => '',
            'contract_id' => '',
            'stock_id'    => !empty($params['stock_id']) ? $params['stock_id'] : 0,
            'project_id'  => $params['passive_project_id'] ?: 0,
            'woker_id'    => $params['passive_woker_id'] ?: 0,
            'user_id'     => session('power_user.company_id'),
            'type'        => $type,
            'create_time' => time(),
            'update_time' => time(),
            'note'        => $params['note'] ?: '',
            'delete_time' => 0,
        ];
        $data_passive['info'] = [
            'supply_goods_id' => $params['supply_goods_id'],
            'woker_id'        => $params['passive_woker_id'] ?: 0,
            'price'           => 0,
            'num'             => $params['num'],
            'create_time'     => time(),
            'update_time'     => time(),
            'delete_time'     => 0,
        ];
        self::allocationRecord($sid, $data_passive);
        $data['order'] = [
            'company_id'  => '',
            'contract_id' => '',
            'stock_id'    => !empty($params['stock_id']) ? $params['stock_id'] : 0,
            'project_id'  => $params['project_id'],
            'woker_id'    => '',
            'user_id'     => session('power_user.company_id'),
            'type'        => $type+1,
            'note'        => $params['note'],
            'create_time' => time(),
            'update_time' => time(),
            'delete_time' => 0,
        ];
        $data['info'] = [
            'supply_goods_id' => $params['supply_goods_id'],
            'woker_id'        => '',
            'price'           => 0,
            'num'             => $params['num'],
            'create_time'     => time(),
            'update_time'     => time(),
            'delete_time'     => 0,
        ];
        self::allocationRecord($sid, $data);
        return ['code' => 200];
    }


    public static function allocationRecord($sid, $data){
        $data['order']['number'] = $sid;
        $data['order']['status'] = 1;
        $res = StockOrderModel::insertGetId($data['order']);
        $data['info']['stock_order_id'] = $res;
        $res1 = StockOrderInfoModel::insertGetId($data['info']);
        if($res && $res1){
            return ['code' => 200, 'msg' => '成功'];
        }else{
            return ['code' => 300, 'msg' => '添加记录失败'];
        }
    }

} 