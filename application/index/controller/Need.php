<?php
namespace app\index\controller;

use app\index\service\Need as NeedService;
use app\index\model\Need as NeedModel;
use app\index\model\Type as TypeModel;
use app\index\validate\NeedValidate;

class Need extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $data['type'] = config('extra.buy_from');
        return view('index', ['data' => $data]);
    }

    // 获取预算列表
    public function ajax_get_list(){
        $list = model('need', 'service')->getList(input('get.'), input('limit'));
        return returnJson($list, 200, '获取成功!');
    }

    // 显示材料列表
    public function goods(){
        $data['type'] = TypeModel::all();
        return view('goods', ['data' => $data]);
    }

    // 获取材料列表数据
    public function ajax_get_goods(){
        $list = model('goods', 'service')->getList(input('get.'), input('limit'));
        return returnJson($list, 200, '获取成功!');
    }

    // 增加预算的材料
    public function ajax_add(){
        $list = model('need', 'service')->add(input('post.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    // 删除预算的材料
    public function ajax_del(){
        $list = model('need', 'service')->del(input('post.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    // 编辑预算的材料
    public function ajax_edit(){
        $list = model('need', 'service')->edit(input('post.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    // 核对预算的材料
    public function ajax_check(){
        $list = model('need', 'service')->check(input('post.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    public function ajax_page(){
        $data['list'] = (new NeedService)->getList(input('get.'));
        $data['list2'] = (new NeedService)->getListWithType($data['list']);

        return view('ajax_page', ['data' => $data]);
    }
}