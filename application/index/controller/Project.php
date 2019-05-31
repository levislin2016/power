<?php
namespace app\index\controller;

use app\index\service\Project as ProjectService;
use app\index\model\Project as ProjectModel;
use app\index\model\Contract as ContractModel;
use app\index\model\Woker as WokerModel;
use app\index\service\ProjectWoker as ProjectWokerService;
use app\lib\exception\BaseException;
use app\index\validate\ProjectValidate;
use app\index\validate\AllotValidate;

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

    //工程的分配材料操作
    public function allot(){
        $validate = new AllotValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('param.'));
        $projectWokerService = new ProjectWokerService();
        $res = $projectWokerService->allot($data);
        return $res;
    }

}