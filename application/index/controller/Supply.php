<?php
namespace app\index\controller;

use app\index\service\Supply as SupplyService;
use app\index\model\Supply as SupplyModel;
use app\lib\exception\BaseException;
use app\index\validate\SupplyValidate;

class Supply extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        return view('index');
    }

    // 获取材料对应的供应商列表
    public function ajax_get_list(){
        $list = model('supply', 'service')->getList(input('get.'), input('get.limit'))->toArray();
        return returnJson($list, 200, '获取成功');
    }

    // 获取供应商合同编号对应的合同
    public function ajax_get_contract_list(){
        $list = model('supply', 'service')->getContractList(input('get.'), input('get.limit'))->toArray();
        return returnJson($list, 200, '获取成功');
    }

    public function add(){ 
        $id = input('get.id', '');
        if($id){
            $list = SupplyModel::get($id);
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
        $validate = new SupplyValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $SupplyService = new SupplyService();
        if($id){ 
            $res = $SupplyService->save_contract($id, $data);
        }else{ 
            $res = $SupplyService->add_contract($data);
        }
        return $res;
    }

    public function del($ids){
    	$res = SupplyModel::destroy(rtrim($ids, ','));
    		
    	if(!$res){ 
    		throw new BaseException(
	            [
	                'msg' => '删除业主错误！',
	                'errorCode' => 30006
	            ]);
    	}

    	return [
                'msg' => '操作成功',
                'code' => '200'
            ];
    }


}