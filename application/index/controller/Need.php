<?php
namespace app\index\controller;

use app\index\service\Need as NeedService;
use app\index\service\Type as TypeService;
use app\index\model\Project as ProjectModel;
use app\index\model\Need as NeedModel;
use app\index\model\Type as TypeModel;
use app\index\model\Goods as GoodsModel;
// use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\lib\exception\BaseException;
use app\index\validate\NeedValidate;

class Need extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $data['project_id'] = input('param.project_id');
        $data['project'] = ProjectModel::get($data['project_id']);
        $data['type_list'] = TypeModel::all();

        return view('index', ['data' => $data]);
    }

    public function ajax_page(){
        $data['list'] = (new NeedService)->getList(input('get.'));
        $data['list2'] = (new NeedService)->getListWithType($data['list']);

        return view('ajax_page', ['data' => $data]);
    }

    // 新增预算
    public function add(){
        $ret = (new NeedService)->add(input('post.'));
        if ($ret['code'] != 200){
            return returnJson('', 201, $ret['msg']);
        }

        return returnJson($ret['data'], 200, $ret['msg']);
    }

    // 修改预算
    public function edit(){
        $ret = NeedModel::update(input('param.'), ['id' => input('param.id')]);
        if (!$ret){
            return returnJson('', 201, '修改数量失败！');
        }

        return returnJson('', 200, '数量修改为：' . input('param.need'));
    }

    public function save(){ 
        $id = input('param.id', '');
        $validate = new NeedValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $needService = new NeedService();
        if($id){ 
            $res = $needService->save_need($id, $data);
        }else{ 
            $res = $needService->add_need($data);
        }
        return $res;
    }

    public function del(){
    	$res = NeedModel::destroy(input('post.id'));
    	if (!$res){
    	    return returnJson('', 201, '删除错误！');
        }

    	return returnJson('', 200, '删除成功！');
    }


}