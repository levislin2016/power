<?php
namespace app\index\controller;

use app\index\service\Need as NeedService;
use app\index\service\BuyProject as BuyProjectService;
use app\index\service\BuyInfo as BuyInfoService;
use app\index\service\Buy as BuyService;
use app\index\model\Project as ProjectModel;
use app\index\model\Stock as StockModel;
use app\index\model\BuyProject as BuyProjectModel;
use app\index\model\StockAll as StockAllModel;
use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\index\model\Supply as SupplyModel;
use app\index\model\StockOrder as StockOrderModel;
use app\index\model\BuyInfo as BuyInfoModel;
use app\index\model\Buy as BuyModel;
use app\index\model\Goods as GoodsModel;
use app\index\validate\BuyProjectValidate;
use app\lib\exception\BaseException;
use app\index\validate\CreateBuyValidate;
use app\index\validate\CreatePutValidate;
use think\Db;

class Buy extends Base{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    // 显示采购单列表
    public function index(){
        $data['list'] = model('buy', 'service')->getList([], 20);
        return view('index', ['data' => $data]);
    }

    // 创建采购单
    public function add_buy(){
        $ret = model('buy', 'service')->addBuy(input('post.'));
        if ($ret['code'] != 200){
            return returnJson('', 201, $ret['msg']);
        }
        return returnJson($ret['data'], 200, $ret['msg']);
    }

    // 显示采购单编辑
    public function edit(){
        $data['id'] = input('id');
        $data['list'] = model('buyProject', 'service')->getList($data, 20);

        return view('edit', ['data' => $data]);
    }

    // 获取采购单列表
    // 获取采购单关联工程列表
    public function ajax_get_list(){
        if (input('list_type') == 'buy'){
            $list = model('buy', 'service')->getList(input('get.'), input('get.limit'))->toArray();
        }

        if (input('list_type') == 'buyProject'){
            $list = model('buyProject', 'service')->getList(input('get.'), input('get.limit'))->toArray();
        }

        return returnJson($list, 200, '获取成功');
    }

    // 作废采购单
    public function ajax_cancel(){
        $ret = BuyModel::update([
            'status' => 9
        ], ['id' => input('id')]);
        if (!$ret){
            return returnJson('', 201, '修改失败');
        }
        return returnJson('', 200, '修改成功');
    }

    // 添加需要采购的工程项目
    public function add_project(){
        $validate = validate('BuyProjectValidate');
        if(!$validate->check(input('post.'))){
            return returnJson('', 201, $validate->getError());
        }
        $ret = BuyProjectModel::create([
            'buy_id'     => input('buy_id'),
            'project_id' => input('project_id'),
        ]);
        if (!$ret){
            return returnJson('', 202, '添加失败！');
        }
        return returnJson('', 200, '添加成功！');
    }














    // 显示选择工程采购页面
    public function sel_project(){
        return view('sel_project');
    }

    // 获取采购的工程列表
    public function get_buy_project_ajax(){
        $data['list'] = (new BuyProjectService)->getList(input('param.'));
        return view('buy_project_ajax', ['data' => $data]);
    }

    // 显示需要采购的材料
    public function sel_goods(){
        $data['list'] = (new BuyService)->getSelGoods(input('param.ids'));
        $data['buy_id'] = input('param.buy_id');

        return view('sel_goods', ['data' => $data]);
    }



    // 获取根据工程分类的采购单详情
    public function sel_goods_list(){
        $data['list'] = (new BuyinfoService)->getList(input('param.'), 'project');

        return view('sel_goods_list', ['data' => $data]);
    }

    // 获取计算完成的采购单详情
    public function sel_goods_total(){
        $data['list'] = (new BuyinfoService)->getList(input('param.'), 'total');

        return view('sel_goods_total', ['data' => $data]);
    }

    // 修改采购单详情表的数量
    public function edit_info(){
        $ret = BuyInfoModel::update([
            'num' => input('param.num')
        ], ['id' => input('param.id')]);
        if (!$ret){
            return returnJson('', 201, '修改失败！');
        }
        return returnJson('', 200, '数量修改为：'. input('param.num') .'！');
    }

    // 删除采购单中的明细
    public function del_info(){
        $ret = BuyInfoModel::destroy(['id' => input('param.id')]);
        if (!$ret){
            return returnJson('', 201, '删除失败！');
        }
        return returnJson('', 200, '删除成功！');
    }

    public function get_need(){
        $params = input('post.', '');
        $list = (new NeedService())->select_list($params, 2);
        foreach($list as &$vo){
            // $sg_kist = SupplyGoodsModel::alias('sg')
            // ->leftJoin('supply s','s.id = sg.s_id')
            // ->where('sg.g_id', $vo['goods_id'])
            // ->field('sg.price, s.name, s.phone, s.id')
            // ->select();
            // $vo['supply'] = $sg_kist;
            // $have = SupplyGoodsModel::alias('sg')
            // ->leftJoin('stock_all sa','sg.id = sa.supply_goods_id')
            // ->where('sg.g_id', $vo['goods_id'])
            // ->sum('have');
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

//    public function index(){
//        $params = input('param.', '');
//        $buy_status = config('extra.buy_status');
//        $this->assign('buy_status', $buy_status);
//        $project_list = ProjectModel::all();
//        $this->assign('project_list', $project_list);
//        $supply_list = SupplyModel::all();
//        $this->assign('supply_list', $supply_list);
//        $buy_from = config('extra.buy_from');
//        $this->assign('buy_from', $buy_from);
//        $list = BuyModel::useGlobalScope(false)->alias('b')
//                    ->leftJoin('project p','b.project_id = p.id')
//                    ->leftJoin('user u','b.user_id = u.id')
//                    ->leftJoin('supply s','b.supply_id = s.id')
//                    ->where(function ($query) use($params) {
//                        $query->where('b.company_id', session('power_user.company_id'));
//                        if(!empty($params['search'])){
//                            $query->where('b.number|p.name|u.name|b.buy_contract', 'like', '%'.$params['search'].'%');
//                        }
//                        if(!empty($params['status'])){
//                            $query->where('b.status', $params['status']);
//                        }
//                        if(!empty($params['from'])){
//                            $query->where('b.from', $params['from']);
//                        }
//                        if(!empty($params['time'])){
//                            $query->where('b.create_time', 'between time', explode(' ~ ', $params['time']));
//                        }
//                        if(!empty($params['pid'])){
//                            $query->where('b.project_id', $params['pid']);
//                        }
//                        if(!empty($params['sid'])){
//                            $query->where('b.supply_id', $params['sid']);
//                        }
//                    })
//                    ->field('b.*, p.name as project_name, u.name as user_name, s.name as supply_name')
//                    ->order('b.create_time', 'desc')
//                    ->paginate(10, false, [
//                        'query'     => $params,
//                    ]);
//        $this->assign('list', $list);
//        return $this->fetch();
//    }

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