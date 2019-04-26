<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 15:36
 */

namespace app\system\validate;


use think\Validate;

class VAgentGoods extends Validate
{

    protected $rule =   [
        'goods_name'   => 'require',
        'goods_type' => 'require',
        'goods_pic' => 'require',
        'goods_recommend_price'  => 'require|float',
        'goods_price'  => 'require|float',
        'goods_desc'  => 'require',
        'goods_supplier_id'  => 'require',
    ];

    protected $message  =   [
        'goods_name.require'      => '请输入商品名称',
        'goods_type.require'       => '请输入备注',
        'goods_pic.require'       => '请上传商品图',
        'goods_recommend_price.require'       => '请输入建议价格',
        'goods_price.require'       => '请输入加盟商价格',
        'goods_price.float'       => '建议价格只能为数字',
        'goods_desc.require'        => '请输入商品描述',
        'goods_supplier_id.require'        => '请选择供货商',
    ];

    protected $scene = [
        'edit'  =>  ['goods_name','goods_price'],
    ];

}