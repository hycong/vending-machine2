<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 15:36
 */

namespace app\system\validate;


use think\Validate;

class VMachineType extends Validate
{

    protected $rule =   [
        'type_name'   => 'require',
        'channel_level' => 'require',
        'channel_sum'  => 'require',
        'sum_store'  => 'require',
    ];

    protected $message  =   [
        'type_name.require'      => '请输入类型名称',
        'channel_level.require'       => '请输入总层数',
        'channel_sum.require'       => '请输入货道容量(单)',
        'sum_store.require'        => '请输入贷道数量(单层)',
    ];

    protected $scene = [
        'create'  =>  ['type_name','channel_level','channel_sum','sum_store'],
    ];

}