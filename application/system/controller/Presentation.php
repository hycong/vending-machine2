<?php


namespace app\system\controller;


use think\Db;

class Presentation extends Common
{

    public function showdata(){
        $input_date = input('date',date('Y-m-d',mktime(0,0,0,date('m'),date('d')-1,date('Y'))));
        $orders_count = Db::name('orders')->where(['o_date'=> $input_date,'o_pay_status' => 1,'o_out_status' => 1])->count();
        $extract = Db::name('agent_extract')->where([
            'extract_status' => 1,
            'extract_deal_time' => ['between',[$input_date.' 00:00:00',$input_date.' 23:59:59']]
            ])->field('sum(extract_money) as money,count(extract_id) as count')->find();
        $machine_market_sum = Db::name('orders')->where(['o_date'=>$input_date,'o_pay_status' => 1,'o_out_status' => 1])->sum('o_money');
        $refund = Db::name('orders')->where(['o_date'=>$input_date,'o_pay_status' => 5])->field('sum(o_money) as money,count(o_id) as count')->find();
        $orders_data_list = [
            'all' => Db::name('orders')->where(['o_date'=>$input_date,'o_pay_status' => ['in',[1,4]],'o_out_status' => 1])->sum('o_money'),
            'money' => Db::name('orders')->where(['o_date'=>$input_date,'o_pay_status' => 1,'o_out_status' => 1])->sum('o_money'),
            'refund_count' => $refund['count'] ? $refund['count'] : 0,
            'refund_money' => $refund['money'] ? $refund['money'] : 0,
        ];
        $date = [];
        $date_data = [];
        for ($i=6;$i>=0;$i--){
            $this_date = date('Y-m-d',mktime(0,0,0,date('m',strtotime($input_date)),date('d',strtotime($input_date))-$i,date('Y',strtotime($input_date))));
            $date[] = $this_date;
            $this_money = 0;
            $this_money = Db::name('orders')->where(['o_date' => $this_date,'o_pay_status' => ['in',[1,4,5]],'o_out_status' => 1])->sum('o_money');
            $date_data[] = $this_money ? $this_money : 0;
        }
        $goods_orders = Db::name('orders')->alias('o')
            ->join('goods g','o.o_goods_id = g.goods_id')
            ->where([
                'o_date' => $input_date,'o_pay_status' => ['in',[1,4,5]],'o_out_status' => 1
            ])->field('o.o_money,g.goods_name,count(o.o_id) as count')
            ->group('o.o_goods_id')->order('count')->limit('50')->select();
        $agent_orders = Db::name('orders')->alias('o')
            ->join('agent g','o.o_agent_id = g.agent_id')
            ->where([
                'o_date' => $input_date,'o_pay_status' => ['in',[1,4,5]],'o_out_status' => 1
            ])->field('g.agent_name,count(o.o_money) as count')
            ->group('o.o_agent_id')->order('count')->limit('50')->select();
        $fetch_data = [
            'extract_count' => $extract['count'],
            'extract_money' => $extract['money'],
            'input_date' => $input_date,
            'orders_count' => $orders_count,
            'machine_market_sum' => $machine_market_sum,
            'orders_data_list' => $orders_data_list,
            'date' => $date,
            'date_data' => $date_data,
            'goods_orders' => $goods_orders,
            'agent_orders' => $agent_orders,
        ];
        return $this->fetch('',$fetch_data);
        return $this->fetch();
    }

}