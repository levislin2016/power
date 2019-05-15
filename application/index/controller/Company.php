<?php
namespace app\index\controller;

use app\index\service\User as UserService;
use app\index\model\Company as CompanyModel;
use app\lib\exception\BaseException;
use app\index\validate\CompanyValidate;

class Company extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){ 
        return $this->fetch();
    }

    public function getData(){ 
        $params = input('get.');

        $field = input('get.field', 'create_time');
        $order = input('get.order', 'desc');
        if($order == 'null'){ 
            $field = 'id';
            $order = 'asc';
        }

        $list = CompanyModel::where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('name', 'like', '%'.$params['search'].'%');    
            }
        })->order($field, $order)->paginate($params['nums'], false);
        $list = $list->toArray();
        $list['code'] = 200;
        $list['msg'] = '数据加载成功';
        return json($list);
    }

    public function add(){ 
        $id = input('get.id', '');
        if($id){
            $list = CompanyModel::get($id);
            if(!$list){ 
                throw new BaseException(
                [
                    'msg' => '非法错误，请重试！',
                    'errorCode' => 60001
                ]);
            }
            $this->assign('list', $list);
        }
        return $this->fetch();
    }

    public function save(){ 
        $id = input('param.id', '');
        $validate = new CompanyValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        if($id){ 
            $res = CompanyModel::where('id', $id)->save($data);
        }else{ 
            $res = CompanyModel::create($data);
        }
        if(!$res){ 
            throw new BaseException(
                [
                    'msg' => '操作错误！',
                    'errorCode' => 60004
                ]);
        }
        return [
            'msg' => '操作成功',
        ];
    }

    public function del($ids){ 
    	$res = CompanyModel::destroy(rtrim($ids, ','));
    		
    	if(!$res){ 
    		throw new BaseException(
	            [
	                'msg' => '删除公司错误！',
	                'errorCode' => 60006
	            ]);
    	}

    	return [
                'msg' => '操作成功',
            ];
    }

}