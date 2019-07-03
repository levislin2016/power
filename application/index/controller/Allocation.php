<?php
namespace app\index\controller;

use app\index\service\Project as ProjectService;
use app\index\service\Allocation as AllocationService;
use app\index\model\Project as ProjectModel;
use app\index\model\ShoppingCart as ShoppingCartModel;
use app\index\model\Contract as ContractModel;
use app\index\model\Woker as WokerModel;
use app\index\service\ProjectWoker as ProjectWokerService;
use app\lib\exception\BaseException;
use app\index\validate\ProjectValidate;
use app\index\validate\AllotValidate;

class Allocation extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $params = input('get.');
        $project_list = (new AllocationService)->project_list($params);
        $balance_list = (new AllocationService)->balance_list($params);
        $project_type_list = (new AllocationService)->project_type_list($params);
        $balance_type_list = (new AllocationService)->balance_type_list($params);
        $shopping_list = (new AllocationService)->shopping_list($params);
    	$this->assign('project_list', $project_list);
    	$this->assign('balance_list', $balance_list);
        $this->assign('project_type_list', $project_type_list);
        $this->assign('balance_type_list', $balance_type_list);
    	$this->assign('shopping_list', $shopping_list);

        $project_all_list = \Db::table('pw_project')->alias('p')
            ->leftJoin('contract c','c.id = p.contract_id')
            ->field('p.id,p.name')
            ->where('p.delete_time', 0)
            ->select();
        if(!empty($params['supply_goods_id'])){
            $goods_name = \Db::table('pw_goods')->field('id','name')->where('id',$params['supply_goods_id'])->find();
        }else{
            $goods_name['name'] = '';
        }

        $this->assign('project_all_list', $project_all_list);
        $this->assign('goods_name', $goods_name);

        return $this->fetch();
    }

    //调拨材料页面
    public function allocation_goods(){
        $project_id = input('get.project_id');
        $goods_list = \Db::table('pw_project_woker')->alias('pw')
                        ->leftJoin('supply_goods sg','sg.id = pw.supply_goods_id')
                        ->leftJoin('goods g','g.id = sg.g_id')
                        ->leftJoin('woker w','w.id = pw.woker_id')
                        ->leftJoin('contract c','c.id = pw.woker_id')
                        ->field('pw.supply_goods_id as id, pw.woker_id, g.name as goods_name,w.name, pw.not')
                        ->where('pw.project_id', 'eq', $project_id)
                        ->where('pw.delete_time', 0)
                        ->select();
        $goods_list = $goods_list->toArray();
        foreach ($goods_list as $k => &$v){
            $v['is_can_num'] = $v['not'];
            if($v['is_can_num'] == 0){
                unset($goods_list[$k]);
            }
        }
        $this->assign('supply_goods_list', $goods_list);
        return $this->fetch();
    }

    //添加购物清单
    public function shopping_cart_add(){
        $params = input('post.');
        $allocationService = new AllocationService();
        $res = $allocationService->shopping_cart_add($params);
        if(!$res){
            throw new BaseException(
                [
                    'msg' => '添加清单失败！',
                    'errorCode' => 4001
                ]);
        }
        return [
            'msg' => '添加清单成功',
        ];
    }

    public function allocation_set(){
        $params = input('post.');
        $allocationService = new AllocationService();
        $res = $allocationService->allocation_set($params);
        if(!$res){
            throw new BaseException(
                [
                    'msg' => '调拨失败！',
                    'errorCode' => 40009
                ]);
        }
        return [
            'msg' => '调拨成功',
        ];
    }


    public function shopping_set(){
        $params = input('get.');
        $allocationService = new AllocationService();
        $res = $allocationService->shopping_set($params);
        if(!$res){
            throw new BaseException(
                [
                    'msg' => '调拨失败！',
                    'errorCode' => 40009
                ]);
        }
        return [
            'msg' => '调拨成功',
        ];
    }

    public function shopping_all_set(){
        $params = input('get.');
        $allocationService = new AllocationService();
        $res = $allocationService->shopping_all_set($params);
        if(!$res){
            throw new BaseException(
                [
                    'msg' => '调拨失败！',
                    'errorCode' => 40009
                ]);
        }
        return [
            'msg' => '调拨成功',
        ];
    }


    //获取项目的工程队
    public function worl_list(){
        $project_id = input('get.project_id', '');
        $woker_list = \Db::table('pw_project_woker')->alias('pw')->field('pw.woker_id, w.name')->leftjoin('pw_woker w','w.id = pw.woker_id')->where('pw.project_id', $project_id)->where('pw.delete_time', 0)->select();
        $woker_list = $woker_list->toArray();
        $arr = [];
        foreach ($woker_list as $k => &$v){
            if(empty($arr[$v['woker_id']])) {
                $arr[$v['woker_id']] = $v['woker_id'];
            }else{
                unset($woker_list[$k]);
            }
        }
        return json_encode(['code' => '200', 'data' => $woker_list]);
    }

    //删除
    public function del($ids){
        $res = ShoppingCartModel::destroy(rtrim($ids, ','));

        if(!$res){
            throw new BaseException(
                [
                    'msg' => '删除调拨清单错误！',
                    'errorCode' => 30006
                ]);
        }

        return [
            'msg' => '操作成功',
        ];
    }

}