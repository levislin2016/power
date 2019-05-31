<?php
namespace app\index\controller;

use app\index\service\Storage as StorageService;
use app\index\model\Storage as StorageModel;
use app\lib\exception\BaseException;
use app\index\validate\StorageValidate;

class StockNum extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        return $this->fetch();
    }

}