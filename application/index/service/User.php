<?php
namespace app\index\service;

use app\index\model\User as UserModel;
use app\lib\exception\BaseException;

class User{ 
    //登陆验证
    public function logincheck($data){ 
        $user = UserModel::where('username', $data['username'])->find();
        if(!$user){
            throw new BaseException(
            [
                'msg' => '用户名不存在！',
                'errorCode' => 20001
            ]);
        }

        if($user['password'] != md5($data['password'])){
            throw new BaseException(
            [
                'msg' => '密码错误，请重试！',
                'errorCode' => 20002
            ]);
        }

        $result = [
                'msg' => '登录成功！',
            ];

        //写入session
        $session_data = [
            'id' => $user->id,
            'username' => $user->username,
            'type' => $user->type,
            'company_id' => $user->company_id,
        ];
        session('power_user',$session_data);

        return $result;
    }

    //管理员列表
    public function select_list($params){ 
        $list = UserModel::where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('username', 'like', '%'.$params['search'].'%');
            }
        })->field('id, username, type')->paginate(10, false, [
            'query'     => $params,
        ]);
        return $list;
    }

    public function add_user($data){ 
        $data = $this->trim_data($data);
        $user = UserModel::create($data);
        if(!$user){ 
            throw new BaseException(
            [
                'msg' => '添加用户错误！',
                'errorCode' => 20004
            ]);
        }
        return [
            'msg' => '添加用户成功',
        ];
    }

    public function save_user($id, $data){
        $data = $this->trim_data($data);
        $res = UserModel::where('id', $id)->update($data);
        if(!$res){ 
            throw new BaseException(
            [
                'msg' => '修改管理员信息错误！',
                'errorCode' => 20005
            ]);
        }
        return [
            'msg' => '添加用户成功',
        ];
    }

    protected function trim_data($data){ 
        unset($data['confirm_password']);
        if($data['password']){ 
            $data['password'] = md5($data['password']);
        }else{ 
            unset($data['password']);
        }
        return $data;
    }
}