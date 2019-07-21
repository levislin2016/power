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
        $params = input('get.');
        $list = (new ProjectService)->select_list($params);
        //dump($list->toArray());
    	$this->assign('list', $list);
        return $this->fetch();
    }

    public function add(){ 
        $contract_list = ContractModel::all();
        $this->assign('contract_list', $contract_list);

        $id = input('get.id', '');
        if($id){
            $list = ProjectModel::get($id);
            if(!$list){ 
                throw new BaseException(
                [
                    'msg' => '非法错误，请重试！',
                    'errorCode' => 40001
                ]);
            }
            $this->assign('list', $list);
        }
        return $this->fetch();
    }

    public function save(){ 
        $id = input('param.id', '');
        $validate = new ProjectValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $projectService = new ProjectService();
        if($id){ 
            $res = $projectService->save_project($id, $data);
        }else{ 
            $res = $projectService->add_project($data);
        }
        return $res;
    }

    public function del($ids){ 
    	$res = ProjectModel::destroy(rtrim($ids, ','));
    		
    	if(!$res){ 
    		throw new BaseException(
	            [
	                'msg' => '删除工程错误！',
	                'errorCode' => 40006
	            ]);
    	}

    	return [
                'msg' => '操作成功',
            ];
    }

    //开工
    public function start_work(){
        $id = input('param.id', '');
        $project = ProjectModel::get($id);
        if($project->status != 1){
            throw new BaseException(
	            [
	                'msg' => '非法操作！',
	                'errorCode' => 40007
	            ]);
        }
        $project->status = 2;
        $res = $project->save();
        if(!$res){ 
    		throw new BaseException(
	            [
	                'msg' => '开始工程错误！',
	                'errorCode' => 40008
	            ]);
    	}
        return [
            'msg' => '工程开始',
        ];
    }

    //工程队材料分配列表
    public function woker(){
        $params = input('param.');
        $project_list = ProjectModel::get($params['id']);
        $this->assign('project_list', $project_list);
        $list = (new ProjectWokerService)->select_list($params);
        //dump($list->toArray());
    	$this->assign('list', $list);
        return $this->fetch();
    }

    //工程的分配材料显示页面
    public function woker_add(){
        $pid = input('get.pid', '');
        $woker_list = WokerModel::all();
        $this->assign('woker_list', $woker_list);

        $goods_list = (new ProjectWokerService)->allot_goods($pid);
        $this->assign('goods_list', $goods_list);
        return $this->fetch();
    }

    //调拨材料页面
    public function allocation_goods(){
        $project_id = input('get.project_id', '');
        $project_list = \Db::table('pw_project')->field('id,name')->where('id', 'neq', $project_id)->where('delete_time', 0)->select();
        $this->assign('project_list', $project_list);

        return $this->fetch();
    }

    public function allocation_set(){
        $params = input('post.');
        $projectService = new ProjectService();
        $res = $projectService->allocation_set($params);
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

    //工程的分配材料操作
    public function allot(){
        $validate = new AllotValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('param.'));
        $projectWokerService = new ProjectWokerService();
        $res = $projectWokerService->allot($data);
        return $res;
    }

    //工程完成
    public function accomplish_work(){
        $id = input('param.id', '');
        ProjectModel::startTrans();
        $project = ProjectModel::get($id);
        if($project->status != 2){
            ProjectModel::rollback();
            throw new BaseException(
	            [
	                'msg' => '非法操作！',
	                'errorCode' => 40010
	            ]);
        }
        $project->status = 3;
        $res = $project->save();
        if(!$res){ 
            ProjectModel::rollback();
    		throw new BaseException(
	            [
	                'msg' => '完成工程错误！',
	                'errorCode' => 40011
	            ]);
        }

        $res = (new BalanceService)->create_balance($id);
        if($res['errorCode'] > 0){
            ProjectModel::rollback();
    		throw new BaseException($res);
        }
        ProjectModel::commit();
        return [
            'msg' => '工程完成',
        ];
    }

    //结算页面
    public function balance(){
        $id = input('param.id', '');

        //判断工程队结算是否完成
        $is_balance = ProjectWokerModel::where('project_id', $id)->where('is_balance', 1)->count();
        $this->assign('is_balance', $is_balance);

        $project = ProjectModel::get($id);
        $this->assign('project', $project);
        $list = ProjectEndModel::useGlobalScope(false)->alias('pe')
            ->leftJoin('supply_goods sg','pe.supply_goods_id = sg.id')
            ->leftJoin('supply s','sg.s_id = s.id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->where('pe.project_id', $project->id)
            ->field('pe.*, g.number, g.id as goods_id, g.name as goods_name, u.name as unit, s.name as supply_name, s.id as supply_id')
            ->select();

        foreach($list as &$vo){
            $vo->arr = ProjectEndInfoModel::useGlobalScope(false)->alias('pei')
                ->leftJoin('stock s','pei.stock_id = s.id')
                ->where('pei.project_end_id', $vo->id)
                ->field('s.name as stock_name, pei.*')
                ->select();
            $vo->count = count($vo->arr);
        }
        $this->assign('list', $list);
        $buy_from = config('extra.buy_from');
        $this->assign('buy_from', $buy_from);
        return $this->fetch();
    }

    public function balance_save(){
        $validate = new BalanceValidate();
        $validate->goCheck();
        $params = $validate->getDataByRule(input('post.'));
        $params['num'] = json_decode($params['num'], true);
        $res = (new BalanceService)->balance_operation($params);
        return $res;
    }

    public function balance_show(){
        $id = input('param.id', '');
        $project = ProjectModel::get($id);
        $this->assign('project', $project);
        $list = ProjectEndModel::useGlobalScope(false)->alias('pe')
            ->leftJoin('supply_goods sg','pe.supply_goods_id = sg.id')
            ->leftJoin('supply s','sg.s_id = s.id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->where('pe.project_id', $project->id)
            ->field('pe.*, g.number, g.id as goods_id, g.name as goods_name, u.name as unit, s.name as supply_name, s.id as supply_id')
            ->select();

        foreach($list as &$vo){
            $vo->arr = ProjectEndInfoModel::useGlobalScope(false)->alias('pei')
                ->leftJoin('stock s','pei.stock_id = s.id')
                ->where('pei.project_end_id', $vo->id)
                ->field('s.name as stock_name, pei.*')
                ->select();
            $vo->count = count($vo->arr);
        }
        $this->assign('list', $list);
        $buy_from = config('extra.buy_from');
        $this->assign('buy_from', $buy_from);
        return $this->fetch();
    }

    //甲供退回
    public function balance_back(){
        $id = input('param.id', '');
        $project = ProjectModel::get($id);
        $this->assign('project', $project);
        $list = ProjectEndModel::useGlobalScope(false)->alias('pe')
            ->leftJoin('supply_goods sg','pe.supply_goods_id = sg.id')
            ->leftJoin('supply s','sg.s_id = s.id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->where('pe.from', 2)
            ->where('pe.project_id', $project->id)
            ->field('pe.*, g.number, g.id as goods_id, g.name as goods_name, u.name as unit, s.name as supply_name, s.id as supply_id')
            ->select();
        foreach($list as &$vo){
            $info = ProjectEndInfoModel::useGlobalScope(false)->alias('pei')
                ->leftJoin('stock s','pei.stock_id = s.id')
                ->leftJoin('project_stock ps','pei.project_stock_id = ps.id')
                ->where('pei.project_end_id', $vo->id)
                ->field('s.name as stock_name, ps.offer, pei.*')
                ->select();
            $vo->count = count($info);

            foreach($info as $k => $vo2){
                //结余调拨数量  11
                $info[$k]['offer_db'] = StockOrderModel::useGlobalScope(false)->alias('so')
                    ->leftJoin('stock_order_info soi','so.id = soi.stock_order_id')
                    ->where('so.project_id', $project->id)
                    ->where('so.type', 11)
                    ->where('so.status', 2)
                    ->where('soi.supply_goods_id', $vo->supply_goods_id)
                    ->sum('soi.num');
            }

            $vo->arr = $info;
            //dump($vo->arr->toArray());
        }
        //dump($list->toArray());
        $this->assign('list', $list);
        $buy_from = config('extra.buy_from');
        $this->assign('buy_from', $buy_from);
        return $this->fetch();
    }

    public function balance_back_save(){
        $validate = new BalanceValidate();
        $validate->goCheck();
        $params = $validate->getDataByRule(input('post.'));
        $params['num'] = json_decode($params['num'], true);
        $res = (new BalanceService)->balance_back($params);
        return $res;
    }

    public function woker_balance(){
        $id = input('param.id', '');
        $project = ProjectModel::get($id);
        $this->assign('project', $project);
        return $this->fetch();
    }

    public function woker_data(){
        $project_id = input('param.pid', '');
        $woker_name = input('param.woker_name', '');
        $nums = input('param.nums', '');
        
        $list = ProjectWokerModel::useGlobalScope(false)->alias('pw')
            ->leftJoin('woker w','w.id = pw.woker_id')
            ->where('pw.project_id', $project_id)
            ->where('w.name', 'like', '%'.$woker_name.'%')
            ->group('pw.woker_id')
            ->field('w.*, sum(pw.can) as can_s, sum(pw.get) as get_s, sum(pw.not) as not_s, sum(pw.back) as back_s, min(pw.is_balance) as is_balance')
            // ->fetchSql(true)
            // ->select();
            ->paginate($nums, false);
        
            // dump($list);
            // die();
        $list = $list->toArray();
        $list['code'] = 200;
        $list['msg'] = '数据加载成功';
        return json($list);
    }

    public function woker_list_data(){
        $project_id = input('param.pid', '');
        $woker_id = input('param.id', '');
        $nums = input('param.nums', '');
        if(!$woker_id){
            $list['data'] = [];
            $list['code'] = 200;
            $list['msg'] = '数据加载成功';
            return json($list);
        }
        $list = ProjectWokerModel::useGlobalScope(false)->alias('pw')
            ->leftJoin('supply_goods sg','pw.supply_goods_id = sg.id')
            ->leftJoin('supply s','sg.s_id = s.id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->where('pw.project_id', $project_id)
            ->where('pw.woker_id', $woker_id)
            ->field('pw.*, g.number, g.id as goods_id, g.name as goods_name, u.name as unit, s.name as supply_name, s.id as supply_id')
            ->paginate($nums, false);

        $list = $list->toArray();
        $list['code'] = 200;
        $list['msg'] = '数据加载成功';
        return json($list);
    }

    public function woker_balance_save(){
        $project_id = input('param.pid', '');
        $woker_id = input('param.id', '');
        $is_balance = input('param.is_balance', '');
        $res = (new ProjectWokerModel)->save([
            'is_balance'    =>  $is_balance
        ],[
            'project_id'    =>  $project_id,
            'woker_id'      =>  $woker_id
        ]);
        if(!$res){ 
    		throw new BaseException(
	            [
	                'msg' => '工程队结算错误！',
	                'errorCode' => 49001
	            ]);
        }

        return [
            'msg' => '工程队结算完成'
        ];
    }

}