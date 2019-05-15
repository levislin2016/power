<?php
namespace app\index\controller;

use think\Controller;

class Base extends Controller
{
    //检查是否登录
    protected function checkLogoin()
    {
    	if(!session('?power_user')){
    		return $this->redirect('login/index');
    	}
    }
}