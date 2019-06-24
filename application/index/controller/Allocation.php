<?php
namespace app\index\controller;

use app\index\service\Project as ProjectService;
use app\index\service\Allocation as AllocationService;
use app\index\model\Project as ProjectModel;
use app\index\model\Contract as ContractModel;
use app\index\model\Woker as WokerModel;
use app\index\service\ProjectWoker as ProjectWokerService;
use app\lib\exception\BaseException;
use app\index\validate\ProjectValidate;
use app\index\validate\AllotValidate;

class Allocation extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $params = input('get.');
        $project_list = (new AllocationService)->project_list($params);
        $balance_list = (new AllocationService)->balance_list($params);
        //dump($list->toArray());
    	$this->assign('project_list', $project_list);
    	$this->assign('balance_list', $balance_list);
        return $this->fetch();
    }

    //调拨材料页面
    public function allocation_goods(){
        $project_id = input('get.project_id', '');
        $project_list = \Db::table('pw_project')->field('id,name')->where('id', 'neq', $project_id)->where('delete_time', 0)->select();
        $goods_list = \Db::table('pw_project_woker')->alias('pw')
                        ->leftJoin('supply_goods sg','sg.id = pw.supply_goods_id')
                        ->leftJoin('goods g','g.id = sg.g_id')
                        ->leftJoin('woker w','w.id = pw.woker_id')
                        ->field('pw.supply_goods_id as id, pw.woker_id, g.name as goods_name,w.name, pw.not')
                        ->where('pw.project_id', 'eq', $project_id)
                        ->where('pw.delete_time', 0)
                        ->select();
        $goods_list = $goods_list->toArray();
        foreach ($goods_list as $k => &$v){
            $v['is_can_num'] = $v['not'];
            if($v['is_can_num'] == 0){
                unset($goods_list[$k]);
            }
        }
        $this->assign('project_list', $project_list);
        $this->assign('supply_goods_list', $goods_list);
        return $this->fetch();
    }

    public function allocation_set(){
        $params = input('post.');
        $projectService = new AllocationService();
        $res = $projectService->allocation_set($params);
        if(!$res){
            throw new BaseException(
                [
                    'msg' => '调拨失败！',
                    'errorCode' => 40009
                ]);
        }
        return [
            'msg' => '调拨成功',
        ];
    }

    //获取项目的工程队
    public function worl_list(){
        $project_id = input('get.project_id', '');
        $woker_list = \Db::table('pw_project_woker')->alias('pw')->field('pw.woker_id, w.name')->leftjoin('pw_woker w','w.id = pw.woker_id')->where('pw.project_id', $project_id)->where('pw.delete_time', 0)->select();
        $woker_list = $woker_list->toArray();
        $arr = [];
        foreach ($woker_list as $k => &$v){
            if(empty($arr[$v['woker_id']])) {
                $arr[$v['woker_id']] = $v['woker_id'];
            }else{
                unset($woker_list[$k]);
            }
        }
        return json_encode(['code' => '200', 'data' => $woker_list]);
    }

}