<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 15:36
 */

namespace app\system\validate;


use think\Validate;

class VWxkeyword extends Validate
{

    protected $rule =   [
        'keyword_title'   => 'require',
        'keyword_reply' => 'require',
        'keyword_url'  => 'require',
        'keyword_responseType'  => 'require',
    ];

    protected $message  =   [
        'keyword_title.require'      => '请输入关键字',
        'keyword_reply.require'       => '请输入回复内容',
        'keyword_url.require'       => '请输入链接地址',
        'keyword_responseType.require'        => '请选择回复类型',
    ];

    protected $scene = [
        'add'  =>  ['keyword_title','keyword_reply','keyword_responseType'],
        'edit'  =>  ['keyword_title','keyword_reply','keyword_responseType'],
    ];

}