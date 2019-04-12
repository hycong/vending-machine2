<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 15:36
 */

namespace app\system\validate;


use think\Validate;

class VAgent extends Validate
{

    protected $rule =   [
        'agent_name'  => 'require',
        'agent_contacts' => 'require',
        'agent_contacts_tel' => 'require',
        'agent_username'   => 'require',
        'agent_password'  => 'require',
    ];

    protected $message  =   [
        'agent_name.require'       => '请输入加盟商登录账号',
        'agent_contacts.require'       => '请输入加盟商联系人',
        'agent_contacts_tel.require'       => '加盟商联系人电话',
        'agent_username.require'      => '请输入加盟商名称',
        'agent_password.require'       => '请输入加盟商登录密码',
    ];

    protected $scene = [
        'create'  =>  ['agent_name','agent_contacts','agent_contacts_tel','agent_username','agent_password'],
        'edit'  =>  ['agent_name','agent_contacts','agent_contacts_tel','agent_username'],
    ];

}