<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 15:36
 */

namespace app\system\validate;


use think\Validate;

class VSupplier extends Validate
{

    protected $rule =   [
        'supplier_name'   => 'require',
        'supplier_mobile' => 'require',
        'supplier_password' => 'require',
    ];

    protected $message  =   [
        'supplier_name.require'      => '请输入商品名称',
        'supplier_mobile.require'       => '请输入备注',
        'supplier_password.require'       => '请输入密码',
    ];

    protected $scene = [
        'create'  =>  ['supplier_name','supplier_mobile','goods_supplier_id'],
        'edit'  =>  ['supplier_name','supplier_mobile','goods_supplier_id'],
    ];

}