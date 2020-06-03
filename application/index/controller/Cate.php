<?php
namespace app\index\controller;

use app\index\service\Cate as CateService;
use app\index\model\Cate as CateModel;
use app\index\validate\CateValidate;
use app\lib\exception\BaseException;

class Cate extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        return view('index');
    }

    // 获取材料对应的供应商列表
    public function ajax_get_list(){
        $list = model('cate', 'service')->getList();
        return returnJson($list, 200, '获取成功！');
    }

    public function add(){ 
        $id = input('get.id', '');
        if($id){
            $list = CateModel::get($id);
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
    public function sonadd(){
        $id = input('get.id', '');
        if($id){
            $list = CateModel::get($id);
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
        $validate = new CateValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $cateService = new CateService();
        $find = CateModel::where('name', '=',$data['name'])->value('id');
        if($find){
            throw new BaseException(
                [
                    'msg' => '该分类已存在！',
                    'errorCode' => 30016
                ]);
        }
        if($id){
            $res = $cateService->save_contract($id, $data);
        }else{ 
            $res = $cateService->add_contract($data);
        }
        return $res;
    }


    public function cateAdd(){
        $id = input('param.id', '');
        $validate = new CateValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $data['pid'] = $id;
        $cateService = new CateService();

//        $find = CateModel::where('name', '=',$data['name'])->value('id');
//        if($find){
//            throw new BaseException(
//                [
//                    'msg' => '该分类已存在！',
//                    'errorCode' => 30016
//                ]);
//        }
        $res = $cateService->son_add($data);
        return json($res);

    }




    public function del($ids){
    	$res = cateModel::destroy(rtrim($ids, ','));
    		
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