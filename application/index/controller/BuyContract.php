<?php
namespace app\index\controller;

use app\index\service\Need as NeedService;
use app\index\model\Project as ProjectModel;
use app\index\model\Need as NeedModel;
use app\index\model\Goods as GoodsModel;
// use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\lib\exception\BaseException;
use app\index\validate\NeedValidate;

class BuyContract extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
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
}