<?php
namespace app\index\service;

use app\index\model\Store as StoreModel;
use app\lib\exception\BaseException;

class Store{

    // 获取列表
    public function getList($params, $limit = 15){
        $where = [];
        if (isset($params['search']) && $params['search']){
            $where[] = ['name', 'like', "%{$params['search']}%"];
        }

        if (isset($params['create_time']) && $params['create_time']){
            $time = explode('至', $params['create_time']);
            $where[] = ['create_time', 'between time', [trim($time[0]), trim($time[1])]];
        }

        $list = StoreModel::where($where)->order('create_time desc')->paginate($limit);
        return $list;
    }

    // 获取列表
    public function save($params){
        // 验证 登录 场景
        $validate = validate('StoreValidate');
        if (!$validate->check($params)){
            return returnInfo('', 201, '修改错误 原因：' . $validate->getError());
        }

        $data = [
            'name' => $params['name']
        ];

        if (isset($params['id'])){
            $model = StoreModel::get($params['id']);
            $data['id'] = $params['id'];
        }else{
            $model = model('store', 'model');
        }

        $ret = $model->save($data);
        if (!$ret){
            return returnInfo('', 202, '失败！');
        }

        return returnInfo('', 200, '成功！');
    }

} 