<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 15:36
 */

namespace app\system\validate;


use think\Validate;

class VGoods extends Validate
{

    protected $rule =   [
        'goods_name'   => 'require',
        'goods_type' => 'require',
        'goods_pic' => 'require',
        'goods_price'  => 'require|float',
        'goods_desc'  => 'require',
    ];

    protected $message  =   [
        'goods_name.require'      => '请输入商品名称',
        'goods_type.require'       => '请输入备注',
        'goods_pic.require'       => '请上传商品图',
        'goods_price.require'       => '请输入建议价格',
        'goods_price.float'       => '建议价格只能为数字',
        'goods_desc.require'        => '请输入商品描述',
    ];

    protected $scene = [
        'create'  =>  ['goods_name','goods_price'],
        'edit'  =>  ['goods_name','goods_price'],
    ];

}