<?php
namespace app\index\controller;

use app\index\service\Stock as StockService;
use app\index\model\Stock as StockModel;
use app\lib\exception\BaseException;
use app\index\validate\StockValidate;

class Stock extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $params = input('get.');
        $list = (new StockService)->select_list($params);
        //dump($list->toArray());
    	$this->assign('list', $list);
        return $this->fetch();
    }

    public function add(){ 
        $id = input('get.id', '');
        if($id){
            $list = StockModel::get($id);
            if(!$list){ 
                throw new BaseException(
                [
                    'msg' => '非法错误，请重试！',
                    'errorCode' => 80001
                ]);
            }
            $this->assign('list', $list);
        }
        return $this->fetch();
    }

    public function save(){ 
        $id = input('param.id', '');
        $validate = new StockValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $stockService = new StockService();
        if($id){ 
            $res = $stockService->save_stock($id, $data);
        }else{ 
            $res = $stockService->add_stock($data);
        }
        return $res;
    }

    public function del($ids){
    	$res = StockModel::destroy(rtrim($ids, ','));
    		
    	if(!$res){ 
    		throw new BaseException(
	            [
	                'msg' => '删除仓库错误！',
	                'errorCode' => 80006
	            ]);
    	}

    	return [
                'msg' => '操作成功',
            ];
    }


}