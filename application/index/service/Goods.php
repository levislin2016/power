<?php
namespace app\index\service;

use app\index\model\Goods as GoodsModel;
use app\index\model\SupplyGoods as SupplyGoodsModel;
use app\lib\exception\BaseException;
use think\Db;
use think\facade\App;


class Goods{
    // 获取材料列表
    public function getList($params, $limit = 15){
        $where = [];
        if (isset($params['search']) && $params['search']){
            $where[] = ['name|number', 'like', "%{$params['search']}%"];
        }

        if (isset($params['type_id']) && $params['type_id']){
            $where[] = ['type_id', '=', $params['type_id']];
        }



        if (isset($params['create_time']) && $params['create_time']){
            $time = explode('至', $params['create_time']);
            $where[] = ['create_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        $list = GoodsModel::with(['unit', 'type', 'cate'])->where($where)->order('create_time desc')->paginate($limit);
        return $list;
    }

    //材料列表
    public function selectList($params){

        $list = GoodsModel::with(['company'=>function($query){
            $query->field('id,name');
        },'unit'=>function($query){
            $query->field('id,name');
        },'cate'=>function($query){
            $query->field('id,name');
        }])->where(function ($query) use($params) {
            if(!empty($params['search'])){
                $query->where('number|name', 'like', '%'.$params['search'].'%');
            }
            if(!empty($params['id'])){
                $query->where('id', $params['id']);
            }
        })->field('id, company_id, number, name, cate_id, unit_id, image, create_time')
            ->order('create_time', 'desc')
            ->paginate(10, false, [
                'query'     => $params,
            ]);;
        return $list;
    }

    public function goodsInfo($id){
        $list = GoodsModel::with(['company'=>function($query){
            $query->field('id,name');
        },'unit'=>function($query){
            $query->field('id,name');
        }])
        ->field('id, company_id, number, name, unit_id, supply_id, image create_time')
        ->where('id', $id)
        ->find();
        return $list;
    }

    public function add_contract($data){
        $find = GoodsModel::field('id')->where(['number' => $data['number'],'name' => $data['name']])->find();
        if($find){
            throw new BaseException(
                [
                    'msg' => '材料已存在！',
                    'errorCode' => 30005
                ]);
        }else {
            $data['company_id'] = session('power_user.company_id');
//            $data['image'] = '/static/upload/'.$data['image'];
            $user = GoodsModel::create($data);
            if (!$user) {
                throw new BaseException(
                    [
                        'msg' => '添加材料错误！',
                        'errorCode' => 30004
                    ]);
            }
            return [
                'msg' => '添加材料成功',
                'code' => '200'
            ];
        }
    }

    public function save_contract($id, $data){
        $find = GoodsModel::field('id')->where(['number' => $data['number'], 'name' => $data['name']])->find();
        if($find){
            $find = $find->toArray();
            if($find['id'] == $id){
                $data['update_time'] = time();
//                $data['image'] = '/static/upload/'.$data['image'];
                $res = GoodsModel::where('id', $id)->update($data);
                return [
                    'msg' => '修改材料成功',
                    'code' => '200'
                ];
            }else{
                throw new BaseException(
                    [
                        'msg' => '材料已存在！',
                        'errorCode' => 30005
                    ]);
            }
        }else {
            $res = GoodsModel::where('id', $id)->update($data);
            if (!$res) {
                throw new BaseException(
                    [
                        'msg' => '修改材料错误！',
                        'errorCode' => 30005
                    ]);
            }
            return [
                'msg' => '修改材料成功',
                'code' => '200'
            ];
        }
    }

    public function upload($file){
        if($file){
            $file_name = rand(1,999).'_'.date('YmdHis',time());
            $path = date('Ymd',time());
            $info = $file->move(App::getRootPath().'/public/static/upload/'.$path,$file_name);
            if($info){
                // 成功上传后 获取上传信息
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                return ['code' => 200, 'data' => $path.'/'.$info->getSaveName()];
            }else{
                // 上传失败获取错误信息
                return ['code' => 303, 'data' => $info->getError()];
            }
        }else{
            return ['code' => 303, 'data' => '请上传图片'];
        }
    }

} 