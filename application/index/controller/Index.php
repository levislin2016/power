<?php
namespace app\index\controller;

use app\index\model\Menu;
use app\index\model\RoleMenu;

class Index extends Base
{
    protected $beforeActionList = [
        'checkLogoin'
    ];

    public function index(){
        $left_list = RoleMenu::alias('r')
            ->field('id, name, graphical, child_id, url, description')
            ->LeftJoin('menu m', 'r.menu_id = m.id')
            ->where('r.role_id', session('power_user')['type'])
            ->where('m.parent_id',0)
            ->select();
        $left_list = $left_list->toArray();
        foreach ($left_list as &$v){
            if($v['url']) {
                $v['url'] = url($v['url']);
            }
            $child_list=[];
            $childs = explode(',', $v['child_id']);
            $child_list = Menu::field('id, name, graphical, url, description')->where('id', 'in', $childs)->select();
            foreach ($child_list as &$va){
                if($va['url']) {
                    $va['url'] = url($va['url']);
                }
            }
            $v['child_list'] = $child_list->toArray();
            unset($v['child_id']);
        }
        $this->assign('list', $left_list);
        return $this->fetch();
    }




    public function unicode_encode($name)
    {
        $name = iconv('UTF-8', 'UCS-2', $name);
        $len = strlen($name);
        $str = '';
        for ($i = 0; $i < $len - 1; $i = $i + 2)
        {
            $c = $name[$i];
            $c2 = $name[$i + 1];
            if (ord($c) > 0)
            {   //两个字节的文字
                $str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
            }
            else
            {
                $str .= $c2;
            }
        }
        return $str;
    }

    public function welcome(){
        return $this->fetch();
    }
}
