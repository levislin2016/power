<?php
namespace app\index\controller;

use app\index\model\RoleMenu;
use app\index\service\User as UserService;
use app\index\model\User as UserModel;
use app\index\model\Role as RoleModel;
use app\index\model\Menu as MenuModel;
use app\index\model\RoleMenu as RoleMenuModel;
use app\lib\exception\BaseException;
use app\index\validate\UserValidate;
use function PHPSTORM_META\type;

class User extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        return view('index');
    }

    public function ajax_get_list(){
    	$list = model('user', 'service')->select_list(input('get.'), input('limit'));
        return returnJson($list, 200, '获取成功!');

    }

    public function role(){
        return view('role');

    }

    public function ajax_get_role_list(){
        $list = model('user', 'service')->role_list(input('get.'), input('limit'));
        return returnJson($list, 200, '获取成功!');

    }


    public function add(){ 
        $type_name = RoleModel::all();
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

    public function role_add(){
        $id = input('get.id', '');
        if($id){
            $list = RoleModel::get($id);
            if(!$list){
                throw new BaseException(
                    [
                        'msg' => '非法错误，请重试！',
                        'errorCode' => 20003
                    ]);
            }
            $this->assign('list', $list);
            //获取对应权限
            $role_menu = RoleMenuModel::where(['role_id' => $id])->column('menu_id');
            $menu =MenuModel::field('id, name, parent_id')->all();
            $menu_list = [];

            if($menu) {
                $menu = $menu->toArray();
                foreach ($menu as &$v){
                    if(in_array($v['id'],$role_menu)){
                        $is_check = true;
                    }else{
                        $is_check = false;
                    }
                    $data = [
                        "id"      => $v['id'],
                        "pId"     => $v['parent_id'],
                        "name"     => $v['name'],
                        "checked" => $is_check,
                    ];
                    if($v["parent_id"] == 0){
                        $data["open"] = true;
                    }
                    $menu_list[] = $data;
                }
            }
            $menu_list = json_encode($menu_list);
            $this->assign('menu_list', $menu_list);
        }else{
            $menu =MenuModel::field('id, name, parent_id')->all();
            $menu_list = [];

            if($menu) {
                $menu = $menu->toArray();
                foreach ($menu as &$v){
                    $data = [
                        "id"      => $v['id'],
                        "pId"     => $v['parent_id'],
                        "name"     => $v['name'],
                        "checked" => false,
                    ];
                    if($v["parent_id"] == 0){
                        $data["open"] = true;
                    }
                    $menu_list[] = $data;
                }
            }
            $menu_list = json_encode($menu_list);
            $this->assign('menu_list', $menu_list);
        }
        return $this->fetch();
    }

    public function role_save(){
        $id = input('param.id', '');
        $data = input('post.');
        $userService = new UserService();
        if($id){
            $res = $userService->save_role($id, $data);
        }else{
            $res = $userService->add_role($data);
        }
        return $res;
    }

    public function role_del($ids){
        $id = explode(',', rtrim($ids, ','));
        $res = RoleModel::destroy($id);
        RoleMenuModel::where('role_id', 'in',$id)->update(['delete_time' => time()]);
        if(!$res){
            throw new BaseException(
                [
                    'msg' => '非法错误，请重试！',
                    'errorCode' => 20016
                ]);
        }

        return [
            'msg' => '操作成功',
            'code' => 200
        ];
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
                'code' => 200
            ];
    }
    

}
