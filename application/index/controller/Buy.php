<?php
namespace app\index\controller;

use app\index\service\Need as NeedService;
use app\index\service\Buy as BuyService;
use app\index\model\Project as ProjectModel;
use app\index\model\Stock as StockModel;
use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\lib\exception\BaseException;
use app\index\validate\CreateBuyValidate;

class Buy extends Base{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function add(){
        $project_list = ProjectModel::all();
        $stock_list = StockModel::all();
        $this->assign('project_list', $project_list);
        $this->assign('stock_list', $stock_list);
        return $this->fetch();
    }

    public function get_need(){
        $params = input('post.', '');
        $list = (new NeedService())->select_list($params);
        foreach($list as &$vo){
            $sg_kist = SupplyGoodsModel::alias('sg')
            ->leftJoin('supply s','s.id = sg.s_id')
            ->where('sg.g_id', $vo['goods_id'])
            ->field('sg.price, s.name, s.phone, s.id')
            ->select();
            $vo['supply'] = $sg_kist;
        }
        return $list;
    }

    public function create_buy(){
        $validate = new CreateBuyValidate();
        $validate->goCheck();
        $params = $validate->getDataByRule(input('post.'));
        $params['num'] = json_decode($params['num'], true);
        $res = (new BuyService())->create_buy_order($params);
        return $res;
    }
}