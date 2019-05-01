<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/26
 * Time: 11:27
 */

namespace app\agent\validate;


use think\Validate;

class AAccount extends Validate
{
    protected $rule =   [
        'account_bank'   => 'require',
        'account_open' => 'require',
        'account_code'  => 'require|length:16,19',
        'account_username'  => 'require',
        'account_mobile'  => 'require|number|length:11',
    ];

    protected $message  =   [
        'account_bank.require'       => '银行名称不能为空',
        'account_open.require'       => '开户银行不能为空',
        'account_code.require'       => '提现银行卡号不能为空',
        'account_code.length'        => '提现银行卡号长度为16-19位',
        'account_username.require'   => '提现用户不能为空',
        'account_mobile.require'     => '联系电话不能为空',
        'account_mobile.number'      => '联系电话为11位数字',
        'account_mobile.length'      => '联系电话为11位数字',
    ];

    protected $scene = [
        'create'  =>  ['account_bank','account_open','account_code','account_username','account_mobile'],
        'update'  =>  ['account_bank','account_open','account_code','account_username','account_mobile'],
    ];
}