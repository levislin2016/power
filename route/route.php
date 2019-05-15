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

//合同列表页
Route::get('contract', 'contract/index');
//合同添加/修改页
Route::get('contract_add', 'contract/add');
//合同添加/修改操作
Route::post('contract_save', 'contract/save');
//合同删除操作
Route::get('contract_del', 'contract/del');

//工程列表页
Route::get('project', 'project/index');
//工程添加/修改页
Route::get('project_add', 'project/add');
//工程添加/修改操作
Route::post('project_save', 'project/save');
//工程删除操作
Route::get('project_del', 'project/del');
//工程需求列表页
Route::get('need', 'need/index');
//工程需求添加/修改页
Route::get('need_add', 'need/add');
//工程需求添加/修改操作
Route::post('need_save', 'need/save');
//工程需求删除操作
Route::get('need_del', 'need/del');

//材料列表页
Route::get('goods', 'goods/index');
//材料添加页
Route::get('goods_add', 'goods/add');
//材料修改页
Route::get('goods_edit', 'goods/edit');
//材料修改操作
Route::any('goods_save', 'goods/save');
//合同删除操作
Route::get('goods_del', 'goods/del');

//业主列表页
Route::get('owner', 'owner/index');
//业主添加/修改页
Route::get('owner_add', 'owner/add');
//业主添加/修改操作
Route::post('owner_save', 'owner/save');
//业主删除操作
Route::get('owner_del', 'owner/del');

