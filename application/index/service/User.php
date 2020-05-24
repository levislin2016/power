<?php
namespace app\index\service;

use app\index\model\RoleMenu;
use app\index\model\User as UserModel;
use app\index\model\Role as RoleModel;
use app\index\model\RoleMenu as RoleMenuModel;
use app\lib\exception\BaseException;
use think\Db;

class User{ 
    //登陆验证
    public function logincheck($data){ 
        $user = UserModel::where('username', $data['username'])->find();
        if(!$user){
            throw new BaseException(
            [
                'msg' => UserModel::getLastsql(),
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
    public function select_list($params, $limit = 15){
        $list = UserModel::alias('u')->where(function ($query) use($params) {
            if(!empty($params['search'])){ 
                $query->where('username', 'like', '%'.$params['search'].'%');
            }
        })->field('u.id, u.username, r.name as role_name')->leftJoin('role r','u.type = r.id')->paginate($limit);
        return $list;
    }

    public function role_list($params, $limit = 15){
        $list = RoleModel::where(function ($query) use($params) {
            if(!empty($params['search'])){
                $query->where('name', 'like', '%'.$params['search'].'%');
            }
        })->paginate($limit);
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
            'code' => 200
        ];
    }

    public function save_user($id, $data){
        $data = $this->trim_data($data);
        $info = UserModel::where('id', $id)->find();
        if($info['type'] == 1){
            throw new BaseException(
                [
                    'msg' => '无法修改管理员信息！',
                    'errorCode' => 20006
                ]);
        }
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
            'code' => 200
        ];
    }


    public function add_role($data){
        $info = RoleModel::where(['name' => $data['name']])->find();
        if($info){
            throw new BaseException(
                [
                    'msg' => '已存在管理角色！',
                    'errorCode' => 20112
                ]);
        }

        $user = RoleModel::create([
            'name' => $data['name']
        ]);
        if(!$user){
            throw new BaseException(
                [
                    'msg' => '添加管理员角色错误！',
                    'errorCode' => 20004
                ]);
        }
        if($data['menu']) {
            $admin_menu = explode(',', $data['menu']);
            $list = [];
            foreach ($admin_menu as $v) {
                $list[] = [
                    'role_id'     => $user->id,
                    'menu_id'     => $v,
                    'create_time' => time(),
                    'update_time' => time(),
                    'delete_time' => 0,
                ];
            }
            $role = RoleMenuModel::insertAll($list);
            if (!$role) {
                throw new BaseException(
                    [
                        'msg' => '添加管理员角色权限错误！',
                        'errorCode' => 20004
                    ]);
            }
        }
        return [
            'msg' => '添加管理员角色成功',
            'code' => 200
        ];
    }

    public function save_role($id, $data){
        RoleModel::where('id',$id)->update(['name' => $data['name'], 'update_time' => time()]);
        $admin_menu = explode(',', $data['menu']);
        foreach ($admin_menu as $v) {
            $list[] = [
                'role_id'     => $id,
                'menu_id'     => $v,
                'create_time' => time(),
                'update_time' => time(),
                'delete_time' => 0,
            ];
        }
        Db::table('pw_role_menu')->where('role_id',$id)->delete();
        $res = RoleMenuModel::where('id', $id)->insertAll($list);
        if(!$res){
            throw new BaseException(
                [
                    'msg' => '修改权限错误！',
                    'errorCode' => 20015
                ]);
        }
        return [
            'msg' => '修改管理员角色成功',
            'code' => 200
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