<?php 
namespace app\lib\exception;
use think\Exception;
class BaseException extends Exception {

	public $code = 200;		//HTTP状态码  例如404 502等

	public $msg = '参数错误';		//错误的具体信息

	public $errorCode = 10000;  //自定义的错误码  例如10001等

    /**
     * @return int
     */
    public function __construct($params=[])
    {
        if(!is_array($params)){
            return;   //若无强制规定条件为数组  可直接返回
            //若条件强制规定数组   throw new Exception('参数必须为数组');
        }
        if(array_key_exists('code',$params)){
            $this->code = $params['code'];
        }
        if(array_key_exists('msg',$params)){
            $this->msg = $params['msg'];
        }
        if(array_key_exists('errorCode',$params)){
            $this->errorCode = $params['errorCode'];
        }
    }
}