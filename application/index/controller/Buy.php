<?php
namespace app\index\controller;

use app\index\service\Need as NeedService;
use app\index\service\Buy as BuyService;
use app\index\model\Project as ProjectModel;
use app\index\model\Stock as StockModel;
use app\index\model\StockAll as StockAllModel;
use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\index\model\StockOrder as StockOrderModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\Buy as BuyModel;
use app\index\model\Goods as GoodsModel;
use app\lib\exception\BaseException;
use app\index\validate\CreateBuyValidate;
use app\index\validate\CreatePutValidate;

class Buy extends Base{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function add(){
        $project_list = ProjectModel::all();
        $stock_list = StockModel::all();
        $this->assign('project_list', $project_list);
        $this->assign('stock_list', $stock_list);

        $buy_from = config('extra.buy_from');
        $this->assign('buy_from', $buy_from);
        return $this->fetch();
    }

    public function get_need(){
        $params = input('post.', '');
        $list = (new NeedService())->select_list($params);
        foreach($list as &$vo){
            $sg_kist = SupplyGoodsModel::alias('sg')
            ->leftJoin('supply s','s.id = sg.s_id')
            ->where('sg.g_id', $vo['goods_id'])
            ->field('sg.price, s.name, s.phone, s.id')
            ->select();
            $vo['supply'] = $sg_kist;
            $have = SupplyGoodsModel::alias('sg')
            ->leftJoin('stock_all sa','sg.id = sa.supply_goods_id')
            ->where('sg.g_id', $vo['goods_id'])
            ->sum('have');
            $stock_num = StockOrderModel::alias('so')
                ->leftJoin('stock_order_info soi', 'soi.stock_order_id = so.id')
                ->where('soi.supply_goods_id', $vo['goods_id'])
                ->where('so.project_id', $params['id'])
                ->where('so.type', 'in',['10','12'])
                ->sum('soi.num');
            $vo['have'] = $have+$stock_num;
        }
        return $list;
    }

    public function get_have(){
        $supply_goods_id = input('post.id', '');
        $have = StockAllModel::where('supply_goods_id', $supply_goods_id)->sum('have');
        return [
            'have'  =>  $have
        ];
    }

    public function create_buy(){
        $validate = new CreateBuyValidate();
        $validate->goCheck();
        $params = $validate->getDataByRule(input('post.'));
        $params['num'] = json_decode($params['num'], true);
        $res = (new BuyService())->create_buy_order($params);
        return $res;
    }

    public function index(){
        $params = input('param.', '');
        $buy_status = config('extra.buy_status');
        $this->assign('buy_status', $buy_status);
        $project_list = ProjectModel::all();
        $this->assign('project_list', $project_list);
        $list = BuyModel::useGlobalScope(false)->alias('b')
                    ->leftJoin('project p','b.project_id = p.id')
                    ->leftJoin('user u','b.user_id = u.id')
                    ->where(function ($query) use($params) {
                        $query->where('b.company_id', session('power_user.company_id'));
                        if(!empty($params['search'])){ 
                            $query->where('b.number|p.name|u.name', 'like', '%'.$params['search'].'%');
                        }
                        if(!empty($params['status'])){
                            $query->where('b.status', $params['status']);
                        }
                        if(!empty($params['time'])){
                            $query->where('b.create_time', 'between time', explode(' ~ ', $params['time']));
                        }
                        if(!empty($params['pid'])){
                            $query->where('b.project_id', $params['pid']);
                        }
                    })
                    ->field('b.*, p.name as project_name, u.name as user_name')
                    ->order('b.create_time', 'desc')
                    ->paginate(10, false, [
                        'query'     => $params,
                    ]);
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function show(){
        $buy = BuyModel::get(input('get.id', ''));
        $this->assign('buy', $buy);
        $list = BuyInfoModel::alias('bi')
            ->leftJoin('supply s','bi.supply_id = s.id')
            ->leftJoin('goods g','bi.goods_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->where('bi.buy_id', input('get.id', ''))
            ->field('bi.*, s.name as supply_name, g.name, g.image, g.number, u.name as unit')
            ->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function put(){
        $id = input('get.id', '');

        $stock_list = StockModel::all();
        $this->assign('stock_list', $stock_list);

        $buy = BuyModel::get($id);
        $project = ProjectModel::get($buy->project_id);
        $this->assign('project', $project);

        $list = BuyInfoModel::alias('bi')
            ->leftJoin('supply s','bi.supply_id = s.id')
            ->leftJoin('goods g','bi.goods_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->where('bi.buy_id', $id)
            ->field('bi.*, s.name as supply_name, g.name, g.image, g.number, u.name as unit')
            ->select();
        $this->assign('list', $list);

        return $this->fetch();
    }

    public function create_put(){
        $validate = new CreatePutValidate();
        $validate->goCheck();
        $params = $validate->getDataByRule(input('post.'));
        $params['num'] = json_decode($params['num'], true);
        $res = (new BuyService)->create_put($params);
        return $res;
    }

    public function cancel(){
        $id = input('get.id', '');
        $buy = BuyModel::get($id);
        if($buy->status != 1){
            throw new BaseException(
                [
                    'msg' => '非法操作！',
                    'errorCode' => 63001
                ]);
        }
        $buy->status = 4;
        $res = $buy->save();
        if(!$res){
            throw new BaseException(
                [
                    'msg' => '操作失败！',
                    'errorCode' => 63002
                ]);
        }
        return [
            'msg' => '操作成功',
        ];
    }

    public function apply_for(){
        $project_list = ProjectModel::all();
        $this->assign('project_list', $project_list);
        $goods_list = GoodsModel::all();
        $this->assign('goods_list', $goods_list);
        $buy_from = config('extra.buy_from');
        $this->assign('buy_from', $buy_from);
        return $this->fetch();
    }

    public function select_supply(){
        $goods_id = input('post.id', '');
        $sg_kist = SupplyGoodsModel::alias('sg')
            ->leftJoin('supply s','s.id = sg.s_id')
            ->where('sg.g_id', $goods_id)
            ->field('sg.price, s.name, s.phone, s.id')
            ->select();
        return $sg_kist;
    }

    public function save_status(){
        $id = input('get.id', '');
        $buy = BuyModel::get($id);
        if($buy->status != 5){
            throw new BaseException(
                [
                    'msg' => '非法操作！',
                    'errorCode' => 64001
                ]);
        }
        $type = input('get.type', 'n');
        if($type == 'y'){
            $buy->status = 1;
        }else{
            $buy->status = 6;
        }
        $res = $buy->save();
        if(!$res){
            throw new BaseException(
                [
                    'msg' => '操作失败！',
                    'errorCode' => 64002
                ]);
        }
        return [
            'msg' => '操作成功',
        ];
    }

}