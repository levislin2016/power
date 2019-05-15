<?php
namespace app\index\controller;

use app\index\service\Contract as ContractService;
use app\index\model\Contract as ContractModel;
use app\index\model\Owner as OwnerModel;
use app\lib\exception\BaseException;
use app\index\validate\ContractValidate;

class Contract extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $params = input('get.');
        $list = (new ContractService)->select_list($params);
        //dump($list->toArray());
    	$this->assign('list', $list);
        return $this->fetch();
    }

    public function add(){ 
        $owner_list = OwnerModel::all();
        $this->assign('owner_list', $owner_list);

        $id = input('get.id', '');
        if($id){
            $list = ContractModel::get($id);
            if(!$list){ 
                throw new BaseException(
                [
                    'msg' => '非法错误，请重试！',
                    'errorCode' => 30001
                ]);
            }
            $this->assign('list', $list);
        }
        return $this->fetch();
    }

    public function save(){ 
        $id = input('param.id', '');
        $validate = new ContractValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $contractService = new ContractService();
        if($id){ 
            $res = $contractService->save_contract($id, $data);
        }else{ 
            $res = $contractService->add_contract($data);
        }
        return $res;
    }

    public function del($ids){
    	$res = ContractModel::destroy(rtrim($ids, ','));
    		
    	if(!$res){ 
    		throw new BaseException(
	            [
	                'msg' => '删除合同错误！',
	                'errorCode' => 30006
	            ]);
    	}

    	return [
                'msg' => '操作成功',
            ];
    }


}