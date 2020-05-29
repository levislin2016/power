<?php
namespace app\index\service;

use app\index\model\Cate as CateModel;
use app\lib\exception\BaseException;

class Cate{
    //合同列表
    public function getList(){
        $list = CateModel::field('id,pid, name')->order(['lv','create_time' => 'desc'])->select()->toArray();
        if(!$list) {
            return $list;
        }

        $tree_list = $this->getCateTree($list);

        return $tree_list;
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

    // 获取树状结构分类
    public function getCateTree($list, $pid =0){
        $tree = [];
        if ($pid == 0){
//            $tree[] = [
//                'id'       => '',
//                'name'     => '全部',
//                'checked'  => True,
//                'open'     => False,
//                'children' => [],
//            ];
        }
        foreach($list as $k => $v){
            if($v['pid'] == $pid){
                $v['children'] = $this->getCateTree($list, $v['id']);
                $v['open'] = False;
                $v['checked'] = False;
                $tree[] = $v;
                unset($list[$k]);
            }
        }
        return $tree;
    }
} 