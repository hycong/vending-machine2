<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 15:36
 */

namespace app\system\validate;


use think\Validate;

class VMachine extends Validate
{

    protected $rule =   [
        'machine_name'   => 'require',
        'machine_type_id' => 'require',
        'machine_agent_id'  => 'require',
        'machine_code'  => 'require|unique:machine,machine_code,,machine_id',
        'machine_address'  => 'require',
    ];

    protected $message  =   [
        'machine_name.require'      => '请输入设备名称',
        'machine_type_id.require'       => '请选择设备类型',
        'machine_agent_id.require'       => '请选择品牌方',
        'machine_code.require'        => '请输入设备序列号',
        'machine_code.unique'        => '设备序列号已存在',
        'machine_address.require'        => '请输入设备地址',
    ];

    protected $scene = [
        'create'  =>  ['machine_name','machine_type_id','machine_agent_id','machine_code','machine_address'],
        'edit'  =>  ['machine_name','machine_agent_id','machine_address'],
    ];

}