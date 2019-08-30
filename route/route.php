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
Route::get('user_role', 'user/role');
//后台管理员添加/修改页
Route::get('user_add', 'user/add');
//后台管理员角色添加/修改页
Route::get('role_add', 'user/role_add');
//后台管理员添加/修改操作
Route::post('user_save', 'user/save');
//后台管理员删除操作
Route::get('user_del', 'user/del');
Route::get('role_del', 'user/role_del');

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
//工程开工
Route::post('start_work', 'project/start_work');
//工程完成
Route::post('accomplish_work', 'project/accomplish_work');
//工程结算
Route::get('balance', 'project/balance');
Route::post('balance_save', 'project/balance_save');
Route::get('balance_show', 'project/balance_show');
Route::get('balance_back', 'project/balance_back');
Route::post('balance_back_save', 'project/balance_back_save');
//工程工程队
Route::get('project_woker', 'project/woker');
//工程分配
Route::get('project_woker_add', 'project/woker_add');
Route::post('project_allot', 'project/allot');
Route::get('project_allocation', 'project/allocation_goods');
Route::get('woker_balance', 'project/woker_balance');
Route::get('woker_data', 'project/woker_data');
Route::get('woker_list_data', 'project/woker_list_data');
Route::post('woker_balance_save', 'project/woker_balance_save');
//添加调拨清单
//Route::get('allocation_shopping_cart_add', 'allocation/shopping_cart_add');

//调拨需求删除操作
Route::get('allocation_shopping_del', 'allocation/del');
Route::get('allocation_shopping_index', 'allocation/shopping_list');
Route::get('allocation_shopping_all_set', 'allocation/shopping_all_set');
Route::get('allocation_shopping_set', 'allocation/shopping_set');

Route::get('need_del', 'need/del');

Route::get('project_worl_list', 'project/worl_list');

//调拨管理
Route::get('allocation', 'allocation/index');
Route::get('allocation_banlance_list', 'allocation/banlance_list');
Route::get('allocation_banlance_type_list', 'allocation/banlance_type_list');
Route::get('allocation_project_list', 'allocation/project_list');
Route::get('allocation_project_stock_list', 'allocation/project_stock_list');
Route::get('allocation_excl', 'allocation/excl');
Route::get('allocation_goods', 'allocation/allocation_goods');


//材料列表页
Route::get('goods', 'goods/index');
//材料添加页
Route::get('goods_add', 'goods/add');
//材料修改页
Route::get('goods_edit', 'goods/edit');
//材料修改操作
Route::any('goods_save', 'goods/save');
//材料删除操作
Route::get('goods_del', 'goods/del');

//业主列表页
Route::get('owner', 'owner/index');
//业主添加/修改页
Route::get('owner_add', 'owner/add');
//业主添加/修改操作
Route::post('owner_save', 'owner/save');
//业主删除操作
Route::get('owner_del', 'owner/del');


//业主列表页
Route::get('company', 'company/index');
Route::get('company_list', 'company/getData');
Route::get('company_add', 'company/add');
Route::post('company_save', 'company/save');
Route::get('company_del', 'company/del');


//计量单位列表页
Route::get('unit', 'unit/index');
//计量单位添加/修改页
Route::get('unit_add', 'unit/add');
//计量单位添加/修改操作
Route::post('unit_save', 'unit/save');
//计量单位删除操作
Route::get('unit_del', 'unit/del');

//供应商列表页
Route::get('supply', 'supply/index');
//供应商添加/修改页
Route::get('supply_add', 'supply/add');
//供应商添加/修改操作
Route::post('supply_save', 'supply/save');
//供应商删除操作
Route::get('supply_del', 'supply/del');

//供应商材料列表页
Route::get('supplyGoods', 'supply_goods/index');
//供应商添加/修改材料页
Route::get('supplyGoods_add', 'supply_goods/add');
//供应商添加/修改材料操作
Route::post('supplyGoods_save', 'supply_goods/save');
//供应商材料删除操作
Route::get('supplyGoods_del', 'supply_goods/del');

//仓库
Route::get('stock', 'stock/index');
Route::get('stock_add', 'stock/add');
Route::post('stock_save', 'stock/save');
Route::get('stock_del', 'stock/del');

//库存
Route::get('stock_all', 'stock_all/index');
Route::get('stock_all_data', 'stock_all/get_data');
Route::get('stock_all_allot', 'stock_all/allot');
Route::get('stock_all_record', 'stock_all/record');

//工程队
Route::get('woker', 'woker/index');
Route::get('woker_add', 'woker/add');
Route::post('woker_save', 'woker/save');
Route::get('woker_del', 'woker/del');
Route::post('project_to_woker', 'woker/project_to_woker');


//领取材料
Route::get('get_index', 'stock_order/get_index');
Route::get('get_add', 'stock_order/get_add');
Route::get('get_show', 'stock_order/get_show');
Route::post('get_woker_goods', 'stock_order/get_woker_goods');
Route::post('create_get_order', 'stock_order/create_get_order');


//材料退还
Route::get('back_index', 'stock_order/back_index');
Route::get('back_add', 'stock_order/back_add');
Route::get('back_show', 'stock_order/back_show');
Route::post('back_woker_goods', 'stock_order/back_woker_goods');
Route::post('create_back_order', 'stock_order/create_back_order');


//导入采购
Route::get('purchase', 'purchase/index');
Route::get('purchase_add', 'purchase/add');
Route::get('purchase_info', 'purchase/info');
Route::post('purchase_excel', 'purchase/excel_purchase');

//领取和退还记录
Route::any('order_get_back', 'stock_order/order_get_back');
Route::any('order_water', 'stock_order/order_water');
Route::any('order_water_data', 'stock_order/order_water_data');
Route::any('order_water_data_info', 'stock_order/order_water_data_info');

//调拨记录
Route::any('stock_order_index', 'stock_order/stock_order_index');
//调拨记录excel导出
Route::any('stock_order_index_excel', 'stock_order/stock_order_index_excel');

//创建采购订单页面
Route::get('buy_add', 'buy/add');
Route::post('get_need', 'buy/get_need');
Route::post('create_buy', 'buy/create_buy');
Route::any('buy', 'buy/index');
Route::get('buy_show', 'buy/show');
Route::get('buy_put', 'buy/put');
Route::post('create_put', 'buy/create_put');
Route::get('buy_cancel', 'buy/cancel');
Route::post('get_have', 'buy/get_have');
Route::get('buy_apply_for', 'buy/apply_for');
Route::post('buy_select_supply', 'buy/select_supply');
Route::get('buy_status', 'buy/save_status');



