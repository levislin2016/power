<?php
namespace app\index\controller;

use app\index\service\Project as ProjectService;
use app\index\service\Balance as BalanceService;
use app\index\model\Project as ProjectModel;
use app\index\model\ProjectEnd as ProjectEndModel;
use app\index\model\ProjectEndInfo as ProjectEndInfoModel;
use app\index\model\StockOrder as StockOrderModel;
use app\index\model\Contract as ContractModel;
use app\index\model\Woker as WokerModel;
use app\index\model\ProjectWoker as ProjectWokerModel;
use app\index\service\ProjectWoker as ProjectWokerService;
use app\lib\exception\BaseException;
use app\index\validate\ProjectValidate;
use app\index\validate\AllotValidate;
use app\index\validate\BalanceValidate;

class Project extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $data['contract'] = ContractModel::all();
        $data['status'] = config('extra.project_status');
        return view('index', ['data' => $data]);
    }

    // 获取工程列表
    public function ajax_get_list(){
        $list = model('project', 'service')->getList(input('get.'), input('limit'));
        return returnJson($list, 200, '获取成功!');
    }

    // 显示添加页面
    public function add(){ 
        $data['contract'] = ContractModel::all();
        if (input('?get.id')){
            $data['info'] = ProjectModel::get(input('id'));
        }
        return view('add', ['data' => $data]);
    }

    // 添加 | 编辑 工程
    public function ajax_add(){
        $ret = model('project', 'service')->add(input('param.'));

        return returnJson($ret['data'], $ret['code'], $ret['msg']);
    }

    // 删除工程
    public function ajax_del(){
        $ret = model('project', 'service')->del(input('post.'));

        return returnJson($ret['data'], $ret['code'], $ret['msg']);
    }

    // 开始工程
    public function ajax_start(){
        $ret = model('project', 'service')->start(input('post.'));

        return returnJson($ret['data'], $ret['code'], $ret['msg']);
    }

}