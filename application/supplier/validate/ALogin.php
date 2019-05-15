<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/26
 * Time: 11:27
 */

namespace app\supplier\validate;


use think\Validate;

class ALogin extends Validate
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
        'login'  =>  ['username','password','captcha'],
    ];
}