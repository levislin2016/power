<?php
namespace app\index\controller;

use app\index\service\Need as NeedService;
use app\index\model\Need as NeedModel;
use app\index\model\Cate as CateModel;
use app\index\validate\NeedValidate;

class ProjectWoker extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $data['type'] = config('extra.buy_from');
        return view('index', ['data' => $data]);
    }

    // 获取工程对应工程队
    public function ajax_get_list(){
        $list = model('ProjectWoker', 'service')->getList(input('get.'), input('limit'));
        return returnJson($list, 200, '获取成功!');
    }

    // 显示工程队列表
    public function woker(){
        return view('woker');
    }

    // 显示工程 工程队选购材料列表
    public function need(){

        $data['type'] = config('extra.buy_from');
        return view('need', ['data' => $data]);
    }

    // 显示工程 工程队选购材料列表
    public function goods(){
        $data['type'] = config('extra.buy_from');
        return view('goods', ['data' => $data]);
    }

    // 获取工程队列表数据
    public function ajax_get_goods_list(){
        $list = model('ProjectWokerInfo', 'service')->getGoodsList(input('get.'), input('limit'));
        return returnJson($list, 200, '获取成功!');
    }

    // 获取工程队列表数据
    public function ajax_get_goods(){
        $list = model('ProjectWoker', 'service')->getWokerList(input('get.'), input('limit'));
        return returnJson($list, 200, '获取成功!');
    }

    // 删除工程对应工程队
    public function ajax_del(){
        $list = model('ProjectWoker', 'service')->del(input('post.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    // 删除工程对应工程队
    public function ajax_goods_del(){
        $list = model('ProjectWokerInfo', 'service')->del(input('post.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    // 增加工程队
    public function ajax_add(){
        $list = model('ProjectWoker', 'service')->add(input('post.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }


    // 增加工程队预算材料
    public function ajax_goods_add(){
        $list = model('ProjectWokerInfo', 'service')->addInfo(input('post.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    // 编辑预算的材料
    public function ajax_edit(){
        $list = model('ProjectWoker', 'service')->edit(input('post.'));
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