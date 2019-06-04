<?php
namespace app\index\service;

use app\index\model\StockOrder as StockOrderModel;
use app\index\model\StockOrderInfo as StockOrderInfoModel;
use app\index\model\ProjectWoker as ProjectWokerModel;
use app\index\model\ProjectStock as ProjectStockModel;
use app\index\model\Contract as ContractModel;
use app\index\model\StockAll as StockAllModel;
use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\lib\exception\BaseException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Samples\Sample10\MyReadFilter;
use think\Db;

class StockOrder{
    
    public function get_list($params){ 
        $list = StockOrderModel::useGlobalScope(false)->alias('so')
            ->leftJoin('Stock s','so.stock_id = s.id')
            ->leftJoin('project p','so.project_id = p.id')
            ->leftJoin('woker w','so.woker_id = w.id')
            ->leftJoin('user u','so.user_id = u.id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){ 
                    $query->where('so.number|s.name|p.name|w.name|u.name', 'like', '%'.$params['search'].'%');
                }
                $query->where('so.company_id', session('power_user.company_id'));
            })
            ->field('so.*, s.name as stock_name, p.name as project_name, w.name as woker_name, u.name as user_name')
            ->order('so.create_time', 'desc')
            ->paginate(10, false, [
                'query'     => $params,
            ]);

        return $list;
            
    }
    public function purchase_list($params){
        $list = StockOrderModel::useGlobalScope(false)->alias('so')
            ->leftJoin('Stock s','so.stock_id = s.id')
            ->leftJoin('project p','so.project_id = p.id')
            ->leftJoin('woker w','so.woker_id = w.id')
            ->leftJoin('user u','so.user_id = u.id')
            ->leftJoin('contract c','so.contract_id = c.id')
            ->where(function ($query) use($params) {
                if(!empty($params['search'])){
                    $query->where('so.number|s.name|p.name|w.name|u.name', 'like', '%'.$params['search'].'%');
                }
                $query->where(['so.type' => 1]);
            })
            ->field('so.*, s.name as stock_name, p.name as project_name, w.name as woker_name, u.name as user_name, c.name as contract_name')
            ->where(['so.type' => 1])
            ->order('so.create_time', 'desc')
            ->paginate(10, false, [
                'query'     => $params,
            ]);
        return $list;

    }

    public function create_get_order($params){ 
        StockOrderModel::startTrans();
        $stockOrder = StockOrderModel::create([
            'company_id'        =>  session('power_user.company_id'),
            'number'            =>  $this->create_order_no(),
            'stock_id'          =>  $params['stock_id'],
            'project_id'        =>  $params['project_id'],
            'woker_id'          =>  $params['woker_id'],
            'user_id'           =>  session('power_user.id'),
            'type'              =>  7,
            'status'            => 2,
            'note'              => $params['note']
        ]);

        if(!$stockOrder){
            StockOrderModel::rollback();
            throw new BaseException(
                [
                    'msg' => '材料领取创建错误！',
                    'errorCode' => 51001
                ]);
        }

        
        foreach($params['num'] as $num){
            //扣减分配表
            $projectWoker = ProjectWokerModel::get($num['id']);
            if(!$projectWoker){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '非法操作！',
                        'errorCode' => 51002
                    ]);
            }
            $projectWoker->get = $projectWoker->get + $num['val'];
            $projectWoker->not = $projectWoker->not - $num['val'];
            if($projectWoker->not < 0){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '分配数量不足！',
                        'errorCode' => 51003
                    ]);
            }
            $res = $projectWoker->save();
            if(!$res){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '扣减分配数量错误！',
                        'errorCode' => 51004
                    ]);
            }

            //扣减库存
            $projectStock = ProjectStockModel::where('stock_id', $params['stock_id'])->where('project_id', $params['project_id'])->where('supply_goods_id', $projectWoker->supply_goods_id)->find();
            if(!$projectStock){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '非法操作！',
                        'errorCode' => 51005
                    ]);
            }
            $projectStock->num = $projectStock->num - $num['val'];
            $projectStock->freeze = $projectStock->freeze - $num['val'];
            $res = $projectStock->save();
            if(!$res){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '扣减库存错误！',
                        'errorCode' => 51006
                    ]);
            }

            //扣减总库存
            $stockAll = StockAllModel::where('stock_id', $params['stock_id'])->where('supply_goods_id', $projectWoker->supply_goods_id)->find();
            if(!$stockAll){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '非法操作！',
                        'errorCode' => 51007
                    ]);
            }
            $stockAll->num = $stockAll->num - $num['val'];
            $stockAll->freeze = $stockAll->freeze - $num['val'];
            $res = $stockAll->save();
            if(!$res){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '扣减总库存错误！',
                        'errorCode' => 51008
                    ]);
            }

            $supplyGoods = SupplyGoodsModel::get($projectWoker->supply_goods_id);

            //创建订单详情
            $stockOrderInfo = StockOrderInfoModel::create([
                'stock_order_id'    =>  $stockOrder->id,
                'supply_goods_id'   =>  $projectWoker->supply_goods_id,
                'woker_id'          =>  $params['woker_id'],
                'price'             =>  $supplyGoods->price,
                'num'               =>  $num['val'],
                
            ]);
            if(!$stockOrderInfo){
                StockOrderModel::rollback();
                throw new BaseException(
                    [
                        'msg' => '创建详情错误！',
                        'errorCode' => 51009
                    ]);
            }
        }

        StockOrderModel::commit();
        return [
            'msg' => '领取材料成功',
        ];
    }

    protected function create_order_no($str = 'G'){
        return $str .''. date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    public function excelPurchase($params, $file){
        $stock_id = $params['stock_id'];
        if(!$stock_id){
            throw new BaseException(
                [
                    'msg' => '未选择仓库！',
                    'errorCode' => 51011
                ]);
        }
        if($file){
            $list_data = Excel::excel_data($file -> getInfo()['tmp_name']);
            $contract_names     = array_flip(array_flip(array_column($list_data, '1')));
            $project_names      = array_flip(array_flip(array_column($list_data, '2')));
            $woker_names        = array_flip(array_flip(array_column($list_data, '3')));
            $supply_names       = array_flip(array_flip(array_column($list_data, '4')));
            $supply_goods_names = array_flip(array_flip(array_column($list_data, '5')));
            $contract_ids = [];
            $project_ids  = [];
            $woker_ids    = [];
            $s_ids        = [];
            $g_ids        = [];
            if($contract_names){
                $contract_ids = \Db::table('pw_contract')->where('name', 'in', $contract_names)->column('id', 'name');
            }
            if($project_names){
                $project_ids = \Db::table('pw_project')->where('name', 'in', $project_names)->column('id', 'name');
            }
            if($woker_names){
                $woker_ids = \Db::table('pw_woker')->where('name', 'in', $woker_names)->column('id', 'name');
            }
            if($supply_goods_names && $supply_names){
                $s_ids = \Db::table('pw_supply')->where('name', 'in', $supply_names)->column('id', 'name');
                $g_ids = \Db::table('pw_goods')->where('name', 'in', $supply_goods_names)->column('id', 'name');
            }
            $data = [];
            $content = '';
            foreach ($list_data as $k => $v) {
                $supply_goods_id = \Db::table('pw_supply_goods')->where(['s_id' => $s_ids[$v['4']], 'g_id' => $g_ids[$v['5']]])->value('id');
                if ($contract_ids[$v['1']] && $v['0'] && $project_ids[$v['2']] && $woker_ids[$v['3']] && $supply_goods_id && $v['8'] > 0) {
                    $data = [
                        'company_id' => '',
                        'contract_id' => $contract_ids[$v['1']],
                        'number' => $v['0'],
                        'stock_id' => $stock_id,
                        'project_id' => $project_ids[$v['2']],
                        'woker_id' => $woker_ids[$v['3']],
                        'user_id' => session('power_user.company_id'),
                        'type' => 1,
                        'status' => 1,
                        'create_time' => time(),
                        'update_time' => time(),
                        'delete_time' => 0,
                    ];
                    $purchase_id = \Db::table('pw_stock_order')->alias('so')
                        ->leftjoin('stock_order_info soi', 'so.id = soi.stock_order_id')
                        ->where(['so.type' => 1, 'so.number' => $v['0'], 'soi.supply_goods_id' => $supply_goods_id, 'so.delete_time' => 0])
                        ->value('so.id');

                    if ($purchase_id) {
                        $content .= '第' . ($k + 2) . "行数据重复提交<br>";
                        continue;
                    }
                    $stock_order_id = \Db::table('pw_stock_order')->insertGetId($data);
                    \Db::table('pw_stock_order_info')->insert([
                        'stock_order_id'  => $stock_order_id,
                        'supply_goods_id' => $supply_goods_id,
                        'woker_id'        => $woker_ids[$v['3']],
                        'price'           => $v['8'] * 100,
                        'num'             => $v['7'],
                        'create_time'     => time(),
                        'update_time'     => time(),
                        'delete_time'     => 0,
                    ]);
                    $stock_list_id = \Db::table('pw_stock_all')->where(['supply_goods_id' => $supply_goods_id,'stock_id' => $stock_id])->value('id');
                    if($stock_list_id){
                        \Db::table('pw_stock_all')->where(['supply_goods_id' => $stock_list_id])->setInc('num', $v['7']);
                    }else{
                        \Db::table('pw_stock_all')->insert([
                            'company_id'      => '',
                            'stock_id'        => $stock_id,
                            'supply_goods_id' => $supply_goods_id,
                            'num'             => $v['7'],
                            'freeze'          => 0,
                            'have'            => 0,
                            'create_time'     => time(),
                            'update_time'     => time(),
                            'delete_time'     => 0,
                        ]);
                    }
                    $content .= '第' . ($k + 2) . "行数据成功提交<br>";
                } else {
                    $content .= '第' . ($k + 2) . "行";
                    if (!$contract_ids[$v['1']]) {
                        $content .= "提交了不存在的合同, ";
                    }
                    if (!$v['0']) {
                        $content .= "提交了空的采购单号, ";
                    }

                    if (!$project_ids[$v['2']]) {
                        $content .= "提交了不存在的工程项目, ";
                    }

                    if (!$woker_ids[$v['3']]) {
                        $content .= "提交了错误的工程队, ";
                    }

                    if (!$supply_goods_id) {
                        $content .= "提交了不存在的供应商材料, ";
                    }
                    $content = rtrim($content, ',') . "<br>";
                }
            }
            return ['msg' => $content];
        }else{
            throw new BaseException(
                [
                    'msg' => '未上传采购单！',
                    'errorCode' => 51010
                ]);
        }
    }



}