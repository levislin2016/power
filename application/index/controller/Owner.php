<?php
namespace app\index\controller;

use app\index\service\Owner as OwnerService;
use app\index\model\Owner as OwnerModel;
use app\lib\exception\BaseException;
use app\index\validate\OwnerValidate;

class Owner extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $params = input('get.');
        $list = (new OwnerService)->selectList($params);
        //dump($list->toArray());
    	$this->assign('list', $list);
        return $this->fetch();
    }

    public function add(){ 
        $id = input('get.id', '');
        if($id){
            $list = OwnerModel::get($id);
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
        $validate = new OwnerValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $ownerService = new OwnerService();
        if($id){ 
            $res = $ownerService->save_contract($id, $data);
        }else{ 
            $res = $ownerService->add_contract($data);
        }
        return $res;
    }

    public function del($ids){
    	$res = OwnerModel::destroy(rtrim($ids, ','));
    		
    	if(!$res){ 
    		throw new BaseException(
	            [
	                'msg' => '删除业主错误！',
	                'errorCode' => 30006
	            ]);
    	}

    	return [
                'msg' => '操作成功',
            ];
    }


}