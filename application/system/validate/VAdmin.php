<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 15:36
 */

namespace app\system\validate;


use think\Validate;

class VAdmin extends Validate
{

    protected $rule =   [
        'password'  => 'require',
        'old_password'  => 'require',
        're_password'  => 'require|confirm:password',
    ];

    protected $message  =   [
        'password.require'       => '请输入密码',
        'old_password.require'       => '请输入旧密码',
        're_password.require'       => '请再次输入新的密码',
        're_password.confirm'       => '新密码和确认密码不一至',
    ];

    protected $scene = [
        'update_password'  =>  ['old_password','password','re_password'],
    ];

}