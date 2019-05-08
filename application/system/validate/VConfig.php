<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 15:36
 */

namespace app\system\validate;


use think\Validate;

class VConfig extends Validate
{

    protected $rule =   [
        'extract_tax'  => 'require|number|between:0,99',
        'extract_taxes_dues'  => 'require|number|between:0,99',
        'min'  => 'number',
        'max'  => 'number',
        'app_id'  => 'require',
        'secret'  => 'require',
        'token'  => 'require',
        'aes_key'  => 'require',
    ];

    protected $message  =   [
        'extract_tax.require'       => '请输入手续费(%)',
        'extract_tax.number'       => '手续费(%)必须为数字',
        'extract_tax.between'       => '手续费(%)为 0~99',
        'extract_taxes_dues.require'       => '请输入税费(%)',
        'extract_taxes_dues.number'       => '税费(%)必须为数字',
        'extract_taxes_dues.between'       => '税费(%)为 0~99',
        'app_id.require'       => '请输入微信APPID',
        'secret.require'       => '请输入微信appsecret',
        'token.require'       => '请输入微信token',
        'aes_key.require'       => '请输入微信encodingaeskey',
    ];

    protected $scene = [
        'depostConf'  =>  ['extract_tax','extract_taxes_dues','min','max'],
        'wechat'  =>  ['app_id','secret','token','aes_key'],
    ];

}