<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/26
 * Time: 11:27
 */

namespace app\agent\validate;


use think\Validate;

class ASupplier extends Validate
{
    protected $rule =   [
        'supplier_id'=>'require',
        'supplier_name'   => 'require',
        'supplier_mobile' => 'require|number|min:11',
        'supplier_password'  => 'require|min:6',
    ];

    protected $message  =   [
        'supplier_name.require'      => '请输入供货商昵称名',
        'supplier_mobile.require'       => '请输入供货商联系电话',
        'supplier_mobile.number'       =>'电话号码为11位数字',
        'supplier_mobile.min'       =>'电话号码为11位数字',
        'supplier_password.require'       => '请输入供货商登录密码',
        'supplier_password.min'            => '登录密码最少6位',
        'supplier_id'               =>'未接收到供货商ID',
    ];

    protected $scene = [
        'create'  =>  ['supplier_name','supplier_mobile','supplier_password'],
        'restPassword'  =>  ['supplier_id','supplier_password',],
    ];
}