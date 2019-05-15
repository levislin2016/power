<?php
namespace app\index\model;

use think\Model;
use think\model\concern\SoftDelete;

class Base extends Model{
	use SoftDelete;
    protected $deleteTime = 'delete_time';   
    protected $defaultSoftDelete = 0; 
    protected $hidden = ['delete_time'];
    protected $insert = ['delete_time' => 0];
}