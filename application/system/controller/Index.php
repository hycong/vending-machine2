<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/8 0008
 * Time: 14:58
 */

namespace app\system\controller;


use think\Db;

class Index extends Common
{

    public function index(){
//        $info = Db::table('nw_citys')->where(['city_level'=>['in',[1,2]]])->select();
//        array_multisort ($info);
//        p($info);
        $yesterday = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-1,date('Y')));
//        $yesterday = '2019-03-20';
//        p($yesterday);
        $machine_sum = Db::name('machine')->where(['machine_status'=>['<>',3]])->count();
        $machine_onLine_sum = Db::name('machine')->where(['machine_status'=>['<>',3],'machine_online' => 1])->count();
        $orders_count = Db::name('orders')->where(['o_date'=>date('Y-m-d'),'o_pay_status' => 1,'o_out_status' => 1])->count();
        $machine_market_sum = Db::name('orders')->where(['o_date'=>date('Y-m-d'),'o_pay_status' => 1,'o_out_status' => 1])->sum('o_money');
        $refund = Db::name('orders')->where(['o_date'=>date('Y-m-d'),'o_pay_status' => 5])->field('sum(o_money) as money,count(o_id) as count')->find();
        $orders_data_list = [
            'all' => Db::name('orders')->where(['o_date'=>date('Y-m-d'),'o_pay_status' => ['in',[1,4]],'o_out_status' => 1])->sum('o_money'),
            'money' => Db::name('orders')->where(['o_date'=>date('Y-m-d'),'o_pay_status' => 1,'o_out_status' => 1])->sum('o_money'),
            'refund_count' => $refund['count'] ? $refund['count'] : 0,
            'refund_money' => $refund['money'] ? $refund['money'] : 0,
        ];
        $date = [];
        $date_data = [];
        for ($i=1;$i<=7;$i++){
            $this_date = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-$i,date('Y')));
            $date[] = $this_date;
            $this_money = 0;
            $this_money = Db::name('orders')->where(['o_date' => $this_date,'o_pay_status' => ['in',[1,4,5]],'o_out_status' => 1])->sum('o_money');
            $date_data[] = $this_money ? $this_money : 0;
        }
        $goods_orders = Db::name('orders')->alias('o')
            ->join('goods g','o.o_goods_id = g.goods_id')
            ->where([
                'o_date' => $yesterday,'o_pay_status' => ['in',[1,4,5]],'o_out_status' => 1
            ])->field('o.o_money,g.goods_name,count(o.o_id) as count')
            ->group('o.o_goods_id')->order('count')->limit('50')->select();
        $agent_orders = Db::name('orders')->alias('o')
            ->join('agent g','o.o_agent_id = g.agent_id')
            ->where([
                'o_date' => $yesterday,'o_pay_status' => ['in',[1,4,5]],'o_out_status' => 1
            ])->field('g.agent_name,count(o.o_money) as count')
            ->group('o.o_agent_id')->order('count')->limit('50')->select();
        $fetch_data = [
            'machine_sum' => $machine_sum,
            'machine_onLine_sum' => $machine_onLine_sum,
            'orders_count' => $orders_count,
            'machine_market_sum' => $machine_market_sum,
            'orders_data_list' => $orders_data_list,
            'date' => $date,
            'date_data' => $date_data,
            'goods_orders' => $goods_orders,
            'agent_orders' => $agent_orders,
        ];
//        p($fetch_data);
        return $this->fetch('',$fetch_data);
    }

}