<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/18 0018
 * Time: 15:37
 */

namespace payment;


use think\Db;

class Payment
{

    /**
     * 生成外部订单号
     * @return string
     */
    public static function genOutTrade(){
        while(1){
            $code = time().self::gen_rand_string(6,'hex');
            if(!Db::name('orders')->where(['o_trade_no'=>$code])->find()){
                return $code;
            }
        }
    }



    /**
     * 生成退款单号
     * @return string
     */
    public static function genRefundNo(){
        while(1){
            $code = 'REFUND'.time().self::gen_rand_string(6,'hex');
            if(!Db::name('orders')->where(['o_refund_no'=>$code])->find()){
                return $code;
            }
        }
    }

    /**
     * 生成随机数
     * @param int $length
     * @param string $type
     * @return string
     */
    public static function gen_rand_string($length=0,$type='chars_num'){
        $num = '0123456789';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $hex = 'ABCDEF';
        $string = '';
        $key = '';
        switch($type){
            case 'chars':
                $string = $chars;
                break;
            case 'chars_num':
                $string = $num.$chars;
                break;
            case 'num':
                $string = $num;
                break;
            case 'hex':
                $string = $num.$hex;
                break;
        }
        for($i=0;$i<$length;$i++){
            $key .= $string[mt_rand(0,strlen($string)-1)];
        }
        return $key;
    }

}