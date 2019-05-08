<?php
namespace app\index\controller;

use app\index\service\User as UserService;
use app\index\model\User as UserModel;
use app\lib\exception\BaseException;
use app\index\validate\UserValidate;

class User extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $params = input('get.');
    	$list = (new UserService)->select_list($params);
    	$this->assign('list', $list);
        return $this->fetch();
    }

    public function add(){ 
        $type_name = config('extra.user_type');
        $this->assign('type_name', $type_name);

        $id = input('get.id', '');
        if($id){
            $list = UserModel::get($id);
            if(!$list){ 
                throw new BaseException(
                [
                    'msg' => '非法错误，请重试！',
                    'errorCode' => 20003
                ]);
            }
            $this->assign('list', $list);
        }
        return $this->fetch();
    }

    public function save(){ 
        $id = input('param.id', '');
        $validate = new UserValidate();
        $validate->goCheck();
        $data = $validate->getDataByRule(input('post.'));
        $userService = new UserService();
        if($id){ 
            $res = $userService->save_user($id, $data);
        }else{ 
            $res = $userService->add_user($data);
        }
        return $res;
    }
    
    public function del($ids){ 
    	$res = UserModel::destroy(rtrim($ids, ','));
    		
    	if(!$res){ 
    		throw new BaseException(
	            [
	                'msg' => '删除管理员错误！',
	                'errorCode' => 20006
	            ]);
    	}

    	return [
                'msg' => '操作成功',
            ];
    }
    

}
