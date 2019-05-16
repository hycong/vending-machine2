<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 15:36
 */

namespace app\system\validate;


use think\Validate;

class VActivity extends Validate
{

    protected $rule =   [
        'activity_name'  => 'require',
        'activity_start'  => 'require',
        'activity_end'  => 'require',
        'activity_supplier_id'  => 'require|gt:1',
        'activity_goods_id'  => 'require|gt:1',
        'activity_machineList'  => 'require',
        'coupon_title'  => 'require',
        'coupon_money'  => 'number',
        'coupon_type'  => 'require',
        'coupon_sum_number'  => 'require|number',
        'coupon_probability'  => 'require|number',
    ];

    protected $message  =   [
        'activity_name.require'       => '请输入活动名称',
        'activity_start.require'       => '请选择开始时间',
        'activity_end.require'       => '请选择结束时间',
        'activity_goods_id.require'       => '请选择商品',
        'activity_supplier_id.gt'       => '请选择供货商',
        'activity_supplier_id.require'       => '请选择供货商',
        'activity_agent_id.gt'       => '请选择加盟商',
        'activity_machineList.require'       => '请选择机器',
        'coupon_title.require'       => '请输入优惠卷名称',
        'coupon_money.require'       => '请输入优惠卷金额',
        'coupon_money.number'       => '优惠卷金额为数字',
        'coupon_type.require'       => '请输入优惠卷金额',
        'coupon_sum_number.require'       => '请输入优惠卷数量',
        'coupon_sum_number.number'       => '优惠卷数量只能是数字',
        'coupon_probability.require'       => '优惠卷数量只能是数字',
        'coupon_probability.number'       => '优惠卷数量只能是数字',
    ];

    protected $scene = [
        'createActivity'  =>  ['activity_name','activity_start','activity_end','activity_agent_id','activity_goods_id','activity_machineList'],
        'editActivity'  =>  ['activity_name','activity_machineList'],
        'createActivityCoupon'  =>  ['coupon_title','coupon_money','coupon_type','coupon_probability','coupon_sum_number'],
    ];

}