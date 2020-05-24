<?php
namespace app\index\controller;

use app\index\model\StockInfo as StockModel;
use app\index\model\StockAll as StockAllModel;
use app\index\service\StockAll as StockAllService;
use app\index\model\ProjectWoker as ProjectWokerModel;
use app\index\model\StockOrder as StockOrderModel;
use app\lib\exception\BaseException;
use app\index\validate\StorageValidate;

class StockAll extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        return $this->fetch();
    }

    public function get_data(){
        $stock_type = input('get.stock_type', 1);
        if($stock_type == 2){
            $list = (new StockAllService())->project_num();
        }else{
            $list = (new StockAllService())->stock_num();
        }
        
        $list = $list->toArray();
        $list['code'] = 200;
        $list['msg'] = '数据加载成功';
        return json($list);
    }

    public function allot(){
        $supply_goods_id = input('get.sg', '');
        $stock_id = input('get.sid', '');
        $project_id = input('get.pid', '');

        $list = ProjectWokerModel::useGlobalScope(false)->alias('pw')
            ->leftJoin('woker w','w.id = pw.woker_id')
            ->leftJoin('project p','pw.project_id = p.id')
            ->leftJoin('supply_goods sg','sg.id = pw.supply_goods_id')
            ->leftJoin('goods g','g.id = sg.g_id')
            ->leftJoin('supply s','s.id = sg.s_id')
            ->where('pw.supply_goods_id', $supply_goods_id)
            ->where('pw.project_id', $project_id)
            ->field('pw.*, w.name as woker_name, p.name as project_name, g.name as goods_name, g.number, s.name as supply_name')
            ->paginate(15, false, [
                'query'     => input('get.'),
            ]);
        
        //dump($list->toArray());
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function record(){
        $supply_goods_id = input('get.sg', '');
        $stock_id = input('get.sid', '');
        $project_id = input('get.pid', '');

        $list = StockOrderModel::useGlobalScope(false)->alias('so')
            ->leftJoin('stock_order_info soi','so.id = soi.stock_order_id')
            ->leftJoin('woker w','w.id = so.woker_id')
            ->leftJoin('project p','so.project_id = p.id')
            ->leftJoin('supply_goods sg','sg.id = soi.supply_goods_id')
            ->leftJoin('goods g','g.id = sg.g_id')
            ->leftJoin('supply s','s.id = sg.s_id')
            ->leftJoin('user u','u.id = so.user_id')
            ->where('so.project_id', $project_id)
            ->where('so.stock_id', $stock_id)
            ->where('so.status', 2)
            ->where('soi.supply_goods_id', $supply_goods_id)
            ->order('so.update_time desc')
            ->field('so.number, so.woker_id, w.name as woker_name, so.type, so.user_id, so.note, so.create_time, soi.supply_goods_id, soi.price, soi.num, p.name as project_name, g.name as goods_name, g.number as goods_number, s.name as supply_name, u.name as user_name')
            ->paginate(15, false, [
                'query'     => input('get.'),
            ]);
        
        //dump($list->toArray());
        $this->assign('list', $list);
        return $this->fetch();
    }
    
}