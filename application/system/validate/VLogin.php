<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 15:36
 */

namespace app\system\validate;


use think\Validate;

class VLogin extends Validate
{

    protected $rule =   [
        'username'   => 'require',
        'password' => 'require',
        'captcha'  => 'require|number|max:4|captcha',
    ];

    protected $message  =   [
        'username.require'      => '请输入用户名',
        'password.require'       => '请输入密码',
        'captcha.require'       => '请输入验证码',
        'captcha.number'        => '验证码只能为数字',
        'captcha.max'            => '验证码最多4位数字',
        'captcha.captcha'       => '验证码错误',
    ];

    protected $scene = [
        'login'  =>  ['username','password'],
    ];

}