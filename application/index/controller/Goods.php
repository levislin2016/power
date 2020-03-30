<?php
namespace app\index\controller;

use app\index\service\Goods as GoodsService;
use app\index\model\Goods as GoodsModel;
use app\index\model\Supply as SupplyModel;
use app\index\model\Unit as UnitModel;
use app\index\model\Cate as CateModel;
use app\lib\exception\BaseException;
use app\index\validate\GoodsValidate;

class Goods extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $params = input('get.');
        $list = (new GoodsService)->selectList($params);
        $this->assign('list', $list);
        return $this->fetch();
    }

    # ajax 获取页面数据
    public function ajax_page(){
        $data['list'] = (new GoodsService)->getList(input('get.'));

        return view('ajax_page', ['data' => $data]);
    }

    # 弹框获取商品列表
    public function get_list(){
        return view('list');
    }

    public function add(){
        //供应商列表
        $supply_list = SupplyModel::all();
        $this->assign('supply_list', $supply_list);

        //材料单位列表
        $unit_list = UnitModel::all();
        $this->assign('unit_list', $unit_list);

        //材料分类列表
        $cate_list = CateModel::all();
        $this->assign('cate_list', $cate_list);
        return $this->fetch();
    }

    public function edit(){
        //供应商列表
        $supply_list = SupplyModel::all();
        $this->assign('supply_list', $supply_list);

        //材料单位列表
        $unit_list = UnitModel::all();
        $this->assign('unit_list', $unit_list);

        //材料分类列表
        $cate_list = CateModel::all();
        $this->assign('cate_list', $cate_list);
        $id = input('get.id', '');
        if($id){
            $list = GoodsModel::get($id);
            if(!$list){
                throw new BaseException(
                    [
                        'msg' => '无该材料，请重试！',
                        'errorCode' => 2001
                    ]);
            }
            $this->assign('list', $list);
        }
        return $this->fetch();
    }

    public function save(){
        $id = input('param.id', '');
        $validate = new GoodsValidate();
        $validate->goCheck();
        $data = input('post.');
        unset($data['file']);
        $GoodsService = new GoodsService();
        if($id){
            $res = $GoodsService->save_contract($id, $data);
        }else{
            $res = $GoodsService->add_contract($data);
        }
        return $res;
    }

    public function upload(){
        $file = request()->file('file');
        $data = (new GoodsService)->upload($file);
        return $data;
    }
    public function del($ids){
    	$res = GoodsModel::destroy(rtrim($ids, ','));

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