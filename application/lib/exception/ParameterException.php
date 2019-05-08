<?php

namespace app\lib\exception;

/**
 * Class ParameterException
 * 通用参数类异常错误
 */
class ParameterException extends BaseException
{
    public $code = 200;
    public $errorCode = 10000;
    public $msg = "invalid parameters";
}