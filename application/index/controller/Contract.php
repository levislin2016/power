<?php
namespace app\index\controller;

use app\index\service\Contract as ContractService;
use app\index\model\Contract as ContractModel;
use app\lib\exception\BaseException;
use app\index\validate\UserValidate;

class Contract extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $params = input('get.');
        $list = (new ContractService)->select_list($params);
        dump($list);
    	// $this->assign('list', $list);
        // return $this->fetch();
    }
}