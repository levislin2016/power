<?php
namespace app\index\controller;

use app\index\service\StockOrder as StockOrderService;
use app\index\model\StockOrder as StockOrderModel;
use app\index\model\Project as ProjectModel;
use app\index\model\ProjectWoker as ProjectWokerModel;
use app\index\model\ProjectStock as ProjectStockModel;
use app\index\model\Stock as StockModel;
use app\index\model\Woker as WokerModel;
use app\index\model\StockOrderInfo as StockOrderInfoModel;
use app\lib\exception\BaseException;
use app\index\validate\StockOrderValidate;
use app\index\validate\GetWokerGoodsValidate;
use app\index\validate\CreateGetOrderValidate;

class StockOrder extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function get_index(){
        $params = input('get.');
        $list = (new StockOrderService)->get_list($params);
    	$this->assign('list', $list);
        return $this->fetch();
    }

    public function get_add(){
        $project_list = ProjectModel::all();
        $stock_list = StockModel::all();
        $this->assign('project_list', $project_list);
        $this->assign('stock_list', $stock_list);
        return $this->fetch();
    }

    public function get_show(){
        $list = StockOrderInfoModel::alias('soi')
            ->leftJoin('supply_goods sg','soi.supply_goods_id = sg.id')
            ->leftJoin('supply s','sg.s_id = s.id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->field('soi.*, s.name as supply_name, sg.g_id, sg.s_id, g.name, g.image, g.number, u.name as unit')
            ->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function get_woker_goods(){
        $validate = new GetWokerGoodsValidate();
        $validate->goCheck();
        $params = $validate->getDataByRule(input('post.'));
        $projectWoker_list = ProjectWokerModel::useGlobalScope(false)->alias('pw')
                ->leftJoin('supply_goods sg','pw.supply_goods_id = sg.id')
                ->leftJoin('supply s','sg.s_id = s.id')
                ->leftJoin('goods g','sg.g_id = g.id')
                ->leftJoin('unit u','g.unit_id = u.id')
                ->where('pw.project_id', $params['project_id'])
                ->where('pw.woker_id', $params['woker_id'])
                ->field('pw.*, s.name as supply_name, sg.g_id, sg.s_id, g.name, g.image, g.number, sg.price, u.name as unit')
                ->select();
        foreach($projectWoker_list as $key=>$projectWoker){
            $projectStock = ProjectStockModel::where('supply_goods_id', $projectWoker->supply_goods_id)->where('project_id', $params['project_id'])->where('stock_id', $params['stock_id'])->find();
            $freeze = 0;
            if($projectStock){ 
                $freeze = $projectStock->freeze;
            }
            $projectWoker_list[$key]['freeze'] = $freeze;
        }
        return $projectWoker_list;
    }

    public function create_get_order(){
        $validate = new CreateGetOrderValidate();
        $validate->goCheck();
        $params = $validate->getDataByRule(input('post.'));
        $params['num'] = json_decode($params['num'], true);
        $res = (new StockOrderService)->create_get_order($params);
        return $res;
    }

}