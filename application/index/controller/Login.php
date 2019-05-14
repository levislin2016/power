<?php
namespace app\index\controller;

use app\index\service\User as UserService;
use app\index\validate\LoginValidate;

class Login extends Base
{

    public function index()
    {
        session('power_user', null);
        //dump(md5('123456'));
        return $this->fetch();
    }

    public function gologin(){ 
    	(new LoginValidate())->goCheck();
    	$data = $this->request->param();
    	$result = (new UserService)->logincheck($data);
    	return $result;
    }

}