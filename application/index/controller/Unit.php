<?php
namespace app\index\controller;

use app\index\service\Unit as UnitService;
use app\index\model\Unit as UnitModel;
use app\lib\exception\BaseException;
use app\index\validate\UnitValidate;

class Unit extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $params = input('get.');
        $list = (new UnitService)->selectList($params);
        //dump($list->toArray());
    	$this->assign('list', $list);
        return $this->fetch();
    }

    public function add(){ 
        $id = input('get.id', '');
        if($id){
            $list = UnitModel::get($id);
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
        $validate = new UnitValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $unitService = new UnitService();
        if($id){ 
            $res = $unitService->save_contract($id, $data);
        }else{ 
            $res = $unitService->add_contract($data);
        }
        return $res;
    }

    public function del($ids){
    	$res = UnitModel::destroy(rtrim($ids, ','));
    		
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