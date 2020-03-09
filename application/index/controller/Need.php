<?php
namespace app\index\controller;

use app\index\service\Need as NeedService;
use app\index\model\Project as ProjectModel;
use app\index\model\Need as NeedModel;
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
        $data['project_id'] = input('param.id');
        $data['goods'] = GoodsModel::all();

        return view('index2', ['data' => $data]);

        $params = input('param.');
        $project_list = ProjectModel::get($params['id']);
        $this->assign('project_list', $project_list);

        $list = (new NeedService)->select_list($params);
        //dump($list->toArray());
        $this->assign('list', $list);
        $buy_from = config('extra.buy_from');
        $this->assign('buy_from', $buy_from);
        return $this->fetch();
    }

    public function index2(){
        $data['project_id'] = input('param.project_id');
        $data['goods'] = GoodsModel::all();

        return view('index2', ['data' => $data]);
    }

    # 获取工程的预算列表
    public function ajaxNeedList(){
        $project_id = input('param.project_id');
        $list = NeedModel::all(['project_id' => $project_id], 'goods');

        $arr = [];
        foreach ($list as $k => $v){
            $sort = $k+100;
            $arr[] = [
                'sort'        => $sort,
                'goods_id'    => $v['goods_id'],
                'unit'        => '个',
                'need'        => $v['need'],
                'note'        => $v['note'],
                'create_time' => $v['create_time'],
                'check'       => $v['check'],
            ];
        }

        return json([
            'data'  => $arr,
            'code'  => 0,
            'msg'   => '获取预算列表成功',
            'count' => count($list),
        ]);
    }



    public function add(){ 
        //$goods_list = SupplyGoodsModel::with(['supply', 'goods'])->select();
        $goods_list = GoodsModel::all();
        $this->assign('goods_list', $goods_list);

        $id = input('get.id', '');
        if($id){
            $list = NeedModel::get($id);
            if(!$list){ 
                throw new BaseException(
                [
                    'msg' => '非法错误，请重试！',
                    'errorCode' => 50001
                ]);
            }
            $this->assign('list', $list);
        }
        $buy_from = config('extra.buy_from');
        $this->assign('buy_from', $buy_from);
        return $this->fetch();
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

    public function del($ids){ 
    	$res = NeedModel::destroy(rtrim($ids, ','));
    		
    	if(!$res){ 
    		throw new BaseException(
	            [
	                'msg' => '删除需求材料错误！',
	                'errorCode' => 50006
	            ]);
    	}

    	return [
                'msg' => '操作成功',
            ];
    }


}