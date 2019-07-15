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
use think\Cookie;
use think\Db;
use think\Session;
use app\index\service\Need as NeedService;
use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\index\model\Buy as BuyModel;

class Allocation extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        cookie('check_shop', '');
        $params = input('get.');

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

    public function shopping_list(){
        $params = input('get.');
        $shopping_list = (new AllocationService)->shopping_list($params);
        $this->assign('shopping_list', $shopping_list);
        return $this->fetch();
    }

    public function project_list(){
        $params = input('get.');
        cookie('is_type', '');
        $project_list = (new AllocationService)->project_list($params);
        cookie('check_shop', '');
        return json(["code"=>"0","msg"=>"","count" => $project_list['count'], "data"=>$project_list['list']]);
    }

    public function banlance_list(){
        $params = input('get.');
        cookie('is_type', '');
        $balance_list = (new AllocationService)->balance_list($params);
        cookie('check_shop', '');
        return json(["code"=>"0","msg"=>"","count" => $balance_list['count'], "data"=>$balance_list['list']]);
    }

    public function banlance_type_list(){
        $params = input('get.');
        cookie('is_type', '');
        $project_type_list = (new AllocationService)->balance_type_list($params);
        cookie('check_shop', '');
        return json(["code"=>"0","msg"=>"","count" => $project_type_list['count'], "data"=>$project_type_list['list']]);
    }


    //调拨材料页面
    public function allocation_goods(){
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
        cookie('check_shop', 2, 5);
        return [
            'msg' => '操作成功',
        ];
    }



    public function excl()
    {
        $params = input('get.');
//        header("Content-type:application/vnd.ms-excel");
//        header("Content-Disposition:filename=采购单.xls");
        $list = (new NeedService())->select_list($params, 2);
        foreach($list as &$vo){
            $sg_kist = SupplyGoodsModel::alias('sg')
                ->leftJoin('supply s','s.id = sg.s_id')
                ->where('sg.g_id', $vo['goods_id'])
                ->field('sg.price, s.name, s.phone, s.id')
                ->select();
            $vo['supply'] = $sg_kist;
            $stock_nums = Db::table('pw_stock_order')->alias('so')
                ->leftJoin('stock_order_info soi', 'soi.stock_order_id = so.id')
                ->leftJoin('supply_goods sg', 'sg.id = soi.supply_goods_id')
                ->where('sg.g_id', $vo['goods_id'])
                ->where('so.project_id', $params['id'])
                ->where('so.type', 'in',['10','12'])
                ->group('so.type')
                ->field('so.type, sum(soi.num) as num')
                ->select();
            $stock_nums = $stock_nums->toArray();
            $vo['have_num'] = $vo['project_num'] = 0;
            foreach($stock_nums as $stock_num){
                if($stock_num['type'] == 10){
                    $vo['project_num'] = $stock_num['num'];
                }
                if($stock_num['type'] == 12){
                    $vo['have_num'] = $stock_num['num'];
                }
            }

            //已采购数量（非已入库）
            $vo['buy_num'] = BuyModel::useGlobalScope(false)->alias('b')
                ->leftJoin('buy_info bi','b.id = bi.buy_id')
                ->where('b.status', 'in', '1,2,3')
                ->where('b.project_id', $params['id'])
                ->where('bi.goods_id', $vo['goods_id'])
                ->sum('num');
        }
        $strexport = "材料编号\t材料名称\t价格\t预算\t已采购\t结余调拨数量\t工程调拨数量\r";
        foreach ($list as $row) {
            $strexport .= $row['number'] . "\t";
            $strexport .= $row['name'] . "\t";
            $strexport .= $row['need'] . "\t";
            $strexport .= $row['buy_num'] . "\t";
            $strexport .= $row['have_num'] . "\t";
            $strexport .= $row['project_num'] . "\r";
        }
        $strexport = iconv('UTF-8', "GB2312//IGNORE", $strexport);
        exit($strexport);
    }


}