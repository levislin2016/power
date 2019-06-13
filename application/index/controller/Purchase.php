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

class Purchase extends Base
{
//    protected $beforeActionList = [
//        'checkLogoin'
//    ];

    public function index(){
        $params = input('get.');
        $list = (new StockOrderService)->purchase_list($params);
    	$this->assign('list', $list);
        return $this->fetch();
    }

    public function info(){
        $list = StockOrderInfoModel::alias('soi')
            ->leftJoin('supply_goods sg','soi.supply_goods_id = sg.id')
            ->leftJoin('supply s','sg.s_id = s.id')
            ->leftJoin('goods g','sg.g_id = g.id')
            ->leftJoin('unit u','g.unit_id = u.id')
            ->field('soi.*, soi.num, s.name as supply_name, sg.g_id, sg.s_id, g.name, g.image, g.number, u.name as unit')
            ->where(['soi.stock_order_id' => input('get.id')])
            ->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function add(){
        $stock_list = StockModel::all();
        $this->assign('stock_list', $stock_list);
        return $this->fetch();
    }

    public function excel_purchase(){
        $params = input('post.');
        $file = request()->file('purchase');
        $list = (new StockOrderService)->excelPurchase($params, $file);
        return $list;
    }

}