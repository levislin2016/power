<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//系统框架
Route::get('index', 'index/index');
//首页
Route::get('welcome', 'index/welcome');

//登陆页
Route::get('login', 'login/index');
//登陆操作
Route::post('gologin', 'login/gologin');

//后台管理员列表页
Route::get('user_index', 'user/index');
//后台管理员添加/修改页
Route::get('user_add', 'user/add');
//后台管理员添加/修改操作
Route::post('user_save', 'user/save');
//后台管理员删除操作
Route::get('user_del', 'user/del');


