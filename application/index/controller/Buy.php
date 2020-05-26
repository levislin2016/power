<?php
namespace app\index\controller;

use app\index\model\Contract as ContractModel;
use app\index\model\Store as StoreModel;
use think\Db;
use think\Validate;

class Buy extends Base{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    // 显示采购单列表
    public function index(){
        $data['from'] = config('extra.buy_from');
        $data['status'] = config('extra.buy_status');
        return view('index', ['data' => $data]);
    }

    // 获取采购单列表
    public function ajax_get_list(){
        $list = model('buy', 'service')->getList(input('get.'), input('get.limit'))->toArray();
        return returnJson($list, 200, '获取成功');
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
        $data['list'] = model('buyInfo', 'service')->getList($data, 20);
        $data['contract'] = ContractModel::all();
        $data['project'] = model('buyInfo', 'service')->getProject($data);

        return view('edit', ['data' => $data]);
    }

//    // 获取采购单关联工程列表
//    public function ajax_get_project_list(){
//        $list = model('buyProject', 'service')->getList(input('get.'), input('get.limit'))->toArray();
//        return returnJson($list, 200, '获取成功');
//    }

    // 作废采购单
    public function ajax_cancel(){
        $ret = model('buy', 'service')->cancel(input('post.'));

        return returnJson($ret['data'], $ret['code'], $ret['msg']);
    }

//    // 添加需要采购的工程
//    public function ajax_add_project(){
//        $ret = model('buyProject', 'service')->add(input('post.'));
//
//        return returnJson($ret['data'], $ret['code'], $ret['msg']);
//    }

//    // 删除采购的工程
//    public function ajax_del_project(){
//        $list = model('buyProject', 'service')->del(input('post.'));
//        return returnJson($list['data'], $list['code'], $list['msg']);
//    }

//    // 添加采购工程 弹框页面
//    public function project(){
//        $data['status'] = config('extra.project_status');
//        $data['contract'] = ContractModel::all();
//        return view('project', ['data' => $data]);
//    }

//    // 显示工程项目的采购材料
//    public function buy_info(){
//        $data['type'] = config('extra.buy_from');
//        return view('buy_info', ['data' => $data]);
//    }

    // 获取采购工程的采购材料
    public function ajax_get_buyinfo_list(){
        $list = model('buyInfo', 'service')->getList(input('get.'));
        return returnJson($list, 200, '获取成功');
    }

    // 显示工程的预算材料，选择采购
    public function need(){
        $data['type'] = config('extra.buy_from');
        $data['project'] = model('project', 'service')->getList([
            'status' => 2
        ]);

        return view('need', ['data' => $data]);
    }

    // 添加需要采购的材料
    public function ajax_add_buyInfo(){
        $list = model('buyInfo', 'service')->add(input('post.'));

        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    // 修改 采购材料的数量
    public function ajax_edit_buyInfo(){
        $list = model('buyInfo', 'service')->edit(input('post.'));

        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    // 删除预算的材料
    public function ajax_del_buyinfo(){
        $list = model('buyInfo', 'service')->del(input('post.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    // 确认生成采购单
    public function ajax_sure(){
        $list = model('buy', 'service')->sure(input('post.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    // 显示供应商列表
    public function supply(){

        return view('supply', ['data' => []]);
    }

    // 显示采购清单
    public function total(){
        $data['supply'] = model('buy', 'service')->getSupply(input('param.'));

        return view('total', ['data' => $data]);
    }

    // 采购单清单明细
    public function ajax_get_total(){
        $list = model('buy', 'service')->total(input('param.'));
        return returnJson($list, 200, '获取成功');
    }

    // 显示采购入库
    public function stock(){
        $data['store'] = StoreModel::all();
        return view('stock', ['data' => $data]);
    }

    // 进行采购入库
    public function ajax_stock_in(){
        $list = model('stock', 'service')->in(input('param.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }


    // 检查是否为正整数
    public function ajax_check_num(){
        $validate = Validate::make([
            'stock_num'  => 'number|min:0',
        ],[
            'stock_num.number'  => '请输入不含负号、特殊符号、小于0 的数字',
        ]);

        if (!$validate->check(input('post.'))) {
            return returnJson('', 201, $validate->getError());
        }

        return returnJson('', 200, '成功');
    }

    // 显示采购入库的历史记录
    public function stock_list(){
        // 获取入库类别
        $data['type'] = config('extra.stock_type');
        return view('stock_list', ['data' => $data]);
    }

    // 获取入库单
    public function ajax_get_stock(){
        $list = model('stock', 'service')->getList(input('get.'), input('get.limit'))->toArray();
        return returnJson($list, 200, '获取成功');
    }

    // 显示采购入库的详情
    public function stock_info(){
        $data = [];
        return view('stock_info', ['data' => $data]);
    }

    // 获取入库单详情
    public function ajax_get_stock_info(){
        $list = model('stockInfo', 'service')->getList(input('get.'), input('get.limit'))->toArray();
        return returnJson($list, 200, '获取成功');
    }






}