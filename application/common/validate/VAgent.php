<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 15:36
 */

namespace app\rep\validate;


use think\Validate;

class VAgent extends Validate
{

    protected $rule =   [
        'old_password'  => 'require',
        'password'  => 'require',
        're_password'  => 'require|confirm:password',
    ];

    protected $message  =   [
        'old_password.require'       => '请输入旧密码',
        'password.require'          => '请输入密码',
        're_password.require'       => '请再次输入新的密码',
        're_password.confirm'       => '新密码和确认密码不一至',
    ];

    protected $scene = [
        'update_password'  =>  ['old_password','password','re_password'],
    ];

}