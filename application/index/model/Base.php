<?php
namespace app\index\model;

use think\Model;
use think\model\concern\SoftDelete;

class Base extends Model{
	use SoftDelete;
    protected $deleteTime = 'delete_time';   
    protected $hidden = ['delete_time'];

}