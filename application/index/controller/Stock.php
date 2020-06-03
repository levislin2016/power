<?php
namespace app\index\controller;

use app\index\model\Store as StoreModel;
use think\Validate;

class Stock extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    // 显示采购入库
    public function index(){
        $data['store'] = StoreModel::all();
        return view('index', ['data' => $data]);
    }

    // 进行采购入库
    public function ajax_stock_in(){
        $list = model('stock', 'service')->in(input('param.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    // 修改 采购材料的数量
    public function ajax_check_buyInfo(){
        $list = model('buyInfo', 'service')->check(input('post.'));

        return returnJson($list['data'], $list['code'], $list['msg']);
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

    // 检查是否为正整数
    public function ajax_check_num(){
        $validate = Validate::make([
            'stock_num'  => 'float|>=:0',
        ]);

        if (!$validate->check(input('post.'))) {
            return returnJson('', 201, '请输入不含负号、特殊符号、小于0 的数字');
        }

        $stock_num = round(input('post.stock_num'), 2);

        return returnJson($stock_num, 200, '成功');
    }

    // 显示库存列表
    public function need(){
        $data['type'] = config('extra.buy_from');
        return view('need', ['data' => $data]);
    }


}