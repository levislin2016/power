<?php
namespace app\index\service;

use app\index\model\Cate as CateModel;
use app\lib\exception\BaseException;

class Cate{
    //合同列表
    public function selectList($params, $limit = 15){
        $list = CateModel::where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('name', 'like', '%'.$params['search'].'%');
            }
        })->field('id, pid, name, create_time, lv')->order(['lv','create_time' => 'desc'])->paginate($limit);

//        if($list) {
//            $list = $list->toArray();
//            $pids = array_filter(array_column($list['data'], 'pid'));
//            $cat_list = [];
//            if ($pids) {
//                $cat_list = CateModel::where('id', 'in', $pids)->column('name', 'id');
//            }
//
//            if($list['data']) {
//                foreach ($list['data'] as &$v) {
//                        $v['p_name'] = $v['lv'].'级分类';
//                        $v['p_cate'] = '';
//                        if($v['pid']){
//                            if(isset($cat_list[$v['pid']])) {
//                                $v['p_cate'] = $cat_list[$v['pid']];
//                            }
//                        }
//
//                }
//            }
//        }

        return $list;
    }

    public function add_contract($data){
        $data['lv'] = 1;
        $cate = CateModel::create($data);
        if(!$cate){
            throw new BaseException(
            [
                'msg' => '添加分类错误！',
                'errorCode' => 30004
            ]);
        }
        return [
            'msg' => '添加分类成功',
            'cate' => '200'
        ];
    }

    public function son_add($data){
        $cat_list = CateModel::all()->toArray();
        $pids = self::getParents($cat_list, $data['pid']);
        $data['lv'] = count($pids)+1;
        $cate = CateModel::create($data);
        if(!$cate){
            throw new BaseException(
            [
                'msg' => '添加分类错误！',
                'errorCode' => 30004
            ]);
        }
        return [
            'msg' => '添加分类成功',
            'cate' => '200'
        ];
    }

    public function save_contract($id, $data){
        $res = CateModel::where('id', $id)->add($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改分类错误！',
                'errorCode' => 30005
            ]);
        }
        return [
            'msg' => '更改分类成功',
            'cate' => '200'
        ];
    }


    public function getParents($categorys,$catId)
    {
        $tree = array();
        while ($catId != 0) {
            foreach ($categorys as $item) {
                if ($item['id'] == $catId) {
                    $tree[] = $item;
                    $catId = $item['pid'];
                    break;
                }
            }
        }
        return $tree;

    }
} 