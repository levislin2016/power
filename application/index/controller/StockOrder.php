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
use app\index\validate\BackWokerGoodsValidate;

class StockOrder extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function get_index(){
        $params = input('get.');
        $list = (new StockOrderService)->order_list($params, 7);
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
            ->where('soi.stock_order_id', input('get.id', ''))
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

    public function back_index(){
        $params = input('get.');
        $list = (new StockOrderService)->order_list($params, 8);
    	$this->assign('list', $list);
        return $this->fetch();
    }

    public function back_add(){
        $project_list = ProjectModel::all();
        $stock_list = StockModel::all();
        $this->assign('project_list', $project_list);
        $this->assign('stock_list', $stock_list);
        return $this->fetch();
    }

    public function back_woker_goods(){
        $validate = new BackWokerGoodsValidate();
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
        return $projectWoker_list;
    }

    public function create_back_order(){
        $validate = new CreateGetOrderValidate();
        $validate->goCheck();
        $params = $validate->getDataByRule(input('post.'));
        $params['num'] = json_decode($params['num'], true);
        $res = (new StockOrderService)->create_back_order($params);
        return $res;
        
    }

    public function order_get_back(){
        $params = input('param.');
        $list = (new StockOrderService)->order_list($params, array(7,8));
        $this->assign('list', $list);
        $project_list = ProjectModel::all();
        $this->assign('project_list', $project_list);
        return $this->fetch();
    }

    public function stock_order_index(){
        $params = input('param.');
        $list = (new StockOrderService)->allocation_order_list($params, array(9,10,11,12));
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function stock_order_index_excel(){
        $params = input('param.');
        $list = (new StockOrderService)->allocation_order_list_excle($params, array(9,10,11,12));
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function order_water(){
        $tl = $params = input('param.tl', '');
        $project_list = ProjectModel::all();
        $this->assign('project_list', $project_list);
        $woker_list = WokerModel::all();
        $this->assign('woker_list', $woker_list);
        $stock_list = StockModel::all();
        $this->assign('stock_list', $stock_list);
        $type_list = config('extra.order_type');
        if($tl){
            
        }
        $this->assign('type_list', $type_list);
        return $this->fetch();
    }

    public function order_water_data(){
        $params = input('param.');

        $field = input('param.field', 'id');
        $order = input('param.order', 'desc');
        if($order == 'null'){ 
            $field = 'id';
            $order = 'asc';
        }
        $list = StockOrderModel::useGlobalScope(false)->alias('so')
            ->leftJoin('Stock s','so.stock_id = s.id')
            ->leftJoin('project p','so.project_id = p.id')
            ->leftJoin('woker w','so.woker_id = w.id')
            ->leftJoin('user u','so.user_id = u.id')
            ->where(function ($query) use($params) {
                $query->where('so.company_id', session('power_user.company_id'));
                $query->where('so.status', 2);
                if($params['number']){
                    $query->where('so.number', 'like' , '%'.$params['number'].'%');
                }
                if($params['pid']){
                    $query->where('so.project_id', $params['pid']);
                }
                if($params['wid']){
                    $query->where('so.woker_id', $params['wid']);
                }
                if($params['sid']){
                    $query->where('so.stock_id', $params['sid']);
                }
                if($params['tid']){
                    $query->where('so.type', 'in', $params['tid']);
                }
            })
            ->field('so.*, s.name as stock_name, p.name as project_name, w.name as woker_name, u.name as user_name')
            ->order('so.create_time', 'desc')
            ->order($field, $order)
            ->paginate($params['nums'], false);
        
        foreach($list as &$v){
            $v->type_name = $v->type_name;
        }
        $list = $list->toArray();
        $list['code'] = 200;
        $list['msg'] = '数据加载成功';
        return json($list);
    }

    public function order_water_data_info(){
        $id = input('param.id', '');
        $nums = input('param.nums', '');

        if(!$id){
            $list['data'] = [];
            $list['code'] = 200;
            $list['msg'] = '数据加载成功';
            return json($list);
        }

        $field = input('param.field', 'id');
        $order = input('param.order', 'desc');
        if($order == 'null'){ 
            $field = 'id';
            $order = 'asc';
        }

        $list = StockOrderInfoModel::alias('soi')
            ->leftJoin('supply_goods sg','soi.supply_goods_id = sg.id')
            ->leftJoin('supply s','sg.s_id = s.id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->where('soi.stock_order_id', $id)
            ->field('soi.*, s.name as supply_name, sg.g_id, sg.s_id, g.name, g.image, g.number, u.name as unit')
            ->order($field, $order)
            ->paginate($nums, false);
        
        $list = $list->toArray();
        $list['code'] = 200;
        $list['msg'] = '数据加载成功';
        return json($list);
    }

}