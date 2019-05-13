<?php
namespace app\index\controller;

use app\index\service\Project as ProjectService;
use app\index\model\Project as ProjectModel;
use app\index\model\Contract as ContractModel;
use app\lib\exception\BaseException;
use app\index\validate\ProjectValidate;

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


}