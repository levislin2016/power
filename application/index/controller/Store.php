<?php
namespace app\index\controller;

use app\index\service\StockInfo as StockService;
use app\index\model\StockInfo as StockModel;
use app\lib\exception\BaseException;
use app\index\validate\StockValidate;

class Store extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){

        return view('index', ['data' => []]);
    }

    // 获取仓库列表
    public function ajax_get_list(){
        $list = model('store', 'service')->getList(input('get.'), input('get.limit'))->toArray();
        return returnJson($list, 200, '获取成功');
    }

    // 显示仓库添加页面
    public function add(){

        return view('add', ['data' => []]);
    }

    // 创建采购单
    public function ajax_add(){
        $ret = model('store', 'service')->save(input('post.'));
        return returnJson($ret['data'], $ret['code'], $ret['msg']);
    }



}