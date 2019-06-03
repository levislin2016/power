<?php
namespace app\index\controller;

use app\index\model\Stock as StockModel;
use app\index\model\StockAll as StockAllModel;
use app\index\service\StockAll as StockAllService;
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

}