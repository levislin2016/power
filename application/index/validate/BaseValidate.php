<?php

namespace app\index\validate;

use think\facade\Request;
use think\Validate;
use app\lib\exception\BaseException;

/**
 * Class BaseValidate
 * 验证类的基类
 */
class BaseValidate extends Validate
{
    /**
     * 检测所有客户端发来的参数是否符合验证类规则
     * 基类定义了很多自定义验证方法
     * 这些自定义验证方法其实，也可以直接调用
     * @throws ParameterException
     * @return true
     */
    public function goCheck()
    {
        //必须设置contetn-type:application/json
        $params = Request::param();

        if (!$this->check($params)) {

            throw new BaseException(
            [
                'msg' => is_array($this->error) ? implode(
                        ';', $this->error) : $this->error,
                'errorCode' => 10001
            ]);
        }
        return true;
    }

    /**
     * @param array $arrays 通常传入request.post变量数组
     * @return array 按照规则key过滤后的变量数组
     * @throws ParameterException
     */
    public function getDataByRule($arrays)
    {
        // if (array_key_exists('user_id', $arrays) | array_key_exists('uid', $arrays)) {
        //     // 不允许包含user_id或者uid，防止恶意覆盖user_id外键
        //     throw new ParameterException([
        //         'msg' => '参数中包含有非法的参数名user_id或者uid'
        //     ]);
        // }
        $newArray = [];
        foreach ($this->rule as $key => $value) {
            $newArray[$key] = $arrays[$key];
        }
        return $newArray;
    }

    protected function isPositiveInteger($value, $rule='', $data='', $field='')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        }
        return $field . '必须是正整数';
    }

    protected function isNotEmpty($value, $rule='', $data='', $field='')
    {
        if (empty($value)) {
            return $field . '不允许为空';
        } else {
            return true;
        }
    }

    //没有使用TP的正则验证，集中在一处方便以后修改
    //不推荐使用正则，因为复用性太差
    //手机号的验证规则
    protected function isMobile($value)
    {
        $rule = '^1(3|4|5|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            //return false;
            return '手机号码不正确';
        }
    }

    //整百 100 200
    protected function is100($value, $rule='', $data='', $field=''){ 
        if(($value % 100) == 0){ 
            return true;
        }
        return '必须是整百';
    }
    

    /**
     * 判断两次密码是否一致
     */
    protected function confirmPassword($value,$rule,$data)
    {
        return $data[$rule] == $value ? true : '两次密码不一致';
    }


    //无规则
    protected function ok($value, $rule='', $data='', $field=''){ 
        return true;
    }
}