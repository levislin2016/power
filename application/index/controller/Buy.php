<?php
namespace app\index\controller;

use app\index\service\Need as NeedService;
use app\index\service\BuyProject as BuyProjectService;
use app\index\service\BuyInfo as BuyInfoService;
use app\index\service\Buy as BuyService;
use app\index\model\Project as ProjectModel;
use app\index\model\Stock as StockModel;
use app\index\model\Contract as ContractModel;
use app\index\model\StockAll as StockAllModel;
use app\index\model\SupplyGoods as SupplyGoodsModel;
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
        $data['list'] = model('buyProject', 'service')->getList($data, 20);
        $data['contract'] = ContractModel::all();

        return view('edit', ['data' => $data]);
    }



    // 获取采购单关联工程列表
    public function ajax_get_project_list(){
        $list = model('buyProject', 'service')->getList(input('get.'), input('get.limit'))->toArray();
        return returnJson($list, 200, '获取成功');
    }

    // 作废采购单
    public function ajax_cancel(){
        $ret = model('buy', 'service')->cancel(input('post.'));

        return returnJson($ret['data'], $ret['code'], $ret['msg']);
    }

    // 添加需要采购的工程
    public function ajax_add_project(){
        $ret = model('buyProject', 'service')->add(input('post.'));

        return returnJson($ret['data'], $ret['code'], $ret['msg']);
    }

    // 删除采购的工程
    public function ajax_del_project(){
        $list = model('buyProject', 'service')->del(input('post.'));
        return returnJson($list['data'], $list['code'], $list['msg']);
    }

    // 添加采购工程 弹框页面
    public function project(){
        $data['status'] = config('extra.project_status');
        $data['contract'] = ContractModel::all();
        return view('project', ['data' => $data]);
    }

    // 显示工程项目的采购材料
    public function buy_info(){
        $data['type'] = config('extra.buy_from');
        return view('buy_info', ['data' => $data]);
    }

    // 获取采购工程的采购材料
    public function ajax_get_buyinfo_list(){
        $list = model('buyInfo', 'service')->getList(input('get.'));
        return returnJson($list, 200, '获取成功');
    }

    // 显示工程的预算材料，选择采购
    public function need(){
        $data['type'] = config('extra.buy_from');
        $list = model('need', 'service')->getList(input('get.'));

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




}