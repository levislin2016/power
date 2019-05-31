<?php
namespace app\index\controller;

use app\index\service\Woker as WokerService;
use app\index\model\Woker as WokerModel;
use app\index\model\ProjectWoker as ProjectWokerModel;
use app\lib\exception\BaseException;
use app\index\validate\WokerValidate;

class Woker extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $params = input('get.');
        $list = (new WokerService)->select_list($params);
        //dump($list->toArray());
    	$this->assign('list', $list);
        return $this->fetch();
    }

    public function add(){ 
        $id = input('get.id', '');
        if($id){
            $list = WokerModel::get($id);
            if(!$list){ 
                throw new BaseException(
                [
                    'msg' => '非法错误，请重试！',
                    'errorCode' => 90001
                ]);
            }
            $this->assign('list', $list);
        }
        return $this->fetch();
    }

    public function save(){ 
        $id = input('param.id', '');
        $validate = new WokerValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $wokerService = new WokerService();
        if($id){ 
            $res = $wokerService->save_woker($id, $data);
        }else{ 
            $res = $wokerService->add_woker($data);
        }
        return $res;
    }

    public function del($ids){
    	$res = WokerModel::destroy(rtrim($ids, ','));
    		
    	if(!$res){ 
    		throw new BaseException(
	            [
	                'msg' => '删除仓库错误！',
	                'errorCode' => 90006
	            ]);
    	}

    	return [
                'msg' => '操作成功',
            ];
    }

    public function project_to_woker(){
        $project_id = input('post.id', '');
        $list = ProjectWokerModel::useGlobalScope(false)->alias('pw')
            ->leftJoin('woker w','pw.woker_id = w.id')
            ->where('pw.project_id', $project_id)
            ->group('w.id')
            ->field('w.*')
            ->select();
        return $list;
    }

}