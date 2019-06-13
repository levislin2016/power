<?php
namespace app\index\controller;

use app\index\service\SupplyGoods as SupplyGoodsService;
use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\index\model\Supply as SupplyModel;
use app\index\model\Goods as GoodsModel;
use app\lib\exception\BaseException;
use app\index\validate\SupplyGoodsValidate;

class SupplyGoods extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $params = input('get.');
        $list = (new SupplyGoodsService)->selectList($params);
    	$this->assign('list', $list);
        return $this->fetch();
    }

    public function add(){
        $supply_list = SupplyModel::where(['company_id' => session('power_user.company_id')])->all();
        $this->assign('supply_list', $supply_list);
        $supply_list = GoodsModel::where(['company_id' => session('power_user.company_id')])->all();
        $this->assign('goods_list', $supply_list);
        $id = input('get.id', '');
        if($id){
            $list = SupplyGoodsModel::get($id);
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
        $validate = new SupplyGoodsValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $SupplyService = new SupplyGoodsService();
        if($id){ 
            $res = $SupplyService->save_contract($id, $data);
        }else{ 
            $res = $SupplyService->add_contract($data);
        }
        return $res;
    }

    public function del($ids){
    	$res = SupplyGoodsModel::destroy(rtrim($ids, ','));
    		
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