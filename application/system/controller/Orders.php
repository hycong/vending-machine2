<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/21 0021
 * Time: 15:24
 */

namespace app\system\controller;

use think\Db;
use think\db\Where;

class Orders extends Common
{
    public function index()
    {
        $machine_code = input('machine_code');
        $start_time = input('start_time');
        $end_time = input('end_time');
        $agent_name = input('agent_name');
        $id = input('id');
        if ($start_time && $end_time) {
            $map['o.o_createTime'] = ['between', [$start_time, $end_time]];
        }
        if ($agent_name) {
            $map['a.agent_name'] = ['like', "%$agent_name%"];
        }
        if ($id) {
            $map['a.o_agent'] = $id;
        }
        if ($machine_code) {
            $map['o.o_machine'] = $machine_code;
        }
        $map['o.o_pay_status'] = ['<>', 3];
        $order_list = Db::name('orders')->alias('o')
            ->join('agent a', 'a.agent_id=o.o_agent_id', 'left')
            ->where($map)
            ->field('o.*,a.agent_name')
            ->order('o.o_id DESC')
            ->paginate($this->pageNum, false, ['query' => request()->param()]);
        return $this->fetch('',[
            'order_list' => $order_list
        ]);
    }

    /**
     * 导出excel表格
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function excel_export()
    {
        $machine_code = input('machine_code');
        $start_time = input('start_time');
        $end_time = input('end_time');
        $nick_name = input('nick_name');
        if ($start_time && $end_time) {
            $map[] = ['so_createTime', 'between', [$start_time, $end_time]];
        }
        if ($nick_name) {
            $map_agent[] = ['agent_nickname','like', "%$nick_name%"];
            $agent = Db::name('agent')->where($map_agent)->field('agent_id')->select();
            $agent_id = indexs($agent, 'agent_id');
            $map[] = ['so_agent','in', $agent_id];
        }
        if ($machine_code) {
            $map[] = ['so_machine','like', "%$machine_code%"];
        }
        $map[] = ['so_pay_status','<>', 3];
        $map[] = ['so_machine','like', "%$machine_code%"];
        $saleorder_list = Db::name('saleorders')
            ->where($map)
            ->field('so_id,so_machine,so_channel,so_user,so_goods,so_money,so_pay_type,so_trade_no,so_mch_no,so_pay_status,so_out_status,so_createTime')
            ->order('so_id DESC')
            ->select();
        foreach ($saleorder_list as $k => $v) {
            switch ($v['so_pay_type']) {
                case 1:
                    $saleorder_list[$k]['so_pay_type'] = "微信支付";
                    break;
                case 2:
                    $saleorder_list[$k]['so_pay_type'] = "支付宝支付";
                    break;
                case 3:
                    $saleorder_list[$k]['so_pay_type'] = "VIP支付";
                    break;
                case 4:
                    $saleorder_list[$k]['so_pay_type'] = "抽奖";
                    break;
                case 5:
                    $saleorder_list[$k]['so_pay_type'] = "秘钥兑换";
                    break;
                default:
                    $saleorder_list[$k]['so_pay_type'] = "支付类型异常";
                    break;
            }
            switch ($v['so_pay_status']) {
                case 1:
                    $saleorder_list[$k]['so_pay_status'] = "支付成功";
                    break;
                case 2:
                    $saleorder_list[$k]['so_pay_status'] = "支付失败";
                    break;
                case 3:
                    $saleorder_list[$k]['so_pay_status'] = "未支付";
                    break;
                case 4:
                    $saleorder_list[$k]['so_pay_status'] = "无需支付";
                    break;
                default:
                    $saleorder_list[$k]['so_pay_status'] = "支付状态异常";
                    break;
            }
            switch ($v['so_out_status']) {
                case 1:
                    $saleorder_list[$k]['so_out_status'] = "出货成功";
                    break;
                case 2:
                    $saleorder_list[$k]['so_out_status'] = "出货失败";
                    break;
                case 3:
                    $saleorder_list[$k]['so_out_status'] = "未出货";
                    break;
                default:
                    $saleorder_list[$k]['so_out_status'] = "出货状态异常";
                    break;
            }
            if (is_numeric($v['so_trade_no'])) {
                $v['so_trade_no'] = strval($v['so_trade_no']);
            }
            if (is_numeric($v['so_mch_no'])) {
                $v['so_mch_no'] = strval($v['so_mch_no']);
            }
        }
        $name = "消费订单数据";
        $header = ['消费ID', '消费机器', '消费货道', '消费用户', '消费商品', '消费金额', '支付类型', '支付订单', '商户订单号', '支付状态', '出货状态', '生成订单时间'];
        $data = $saleorder_list;
        excelExport($name, $header, $data);
    }

    /**
     * 补货列表
     * @return mixed
     */
    public function replenishment()
    {
        $machine_code = input('machine_code');
        $agent_name = input('agent_name');
        $where = [];
        if($machine_code) $where[] = ['m.machine_code','=', $machine_code];
        if($agent_name) $where[] = ['a.agent_name','like', "%$agent_name%"];
        $rep_list = Db::name('rep_replenishment')->alias('r')
            ->join('machine m','r.mr_machine_id=m.machine_id','left')
            ->join('goods g','r.mr_goods_id=g.goods_id','left')
            ->join('agent a','r.mr_agent_id=a.agent_id','left')
            ->join('rep','r.mr_rep_id=rep.rep_id','left')
            ->where($where)
            ->field('sum(r.mr_goods_stock) stock,r.*,m.machine_name,m.machine_code,a.agent_name,rep.rep_name,g.goods_name')
            ->group('mr_trade_no')
            ->order('mr_createTime desc')
            ->paginate(20, false, ['query' => request()->param()]);
        $this->assign('rep_list',$rep_list);
        return $this->fetch();
    }

    public function details(){
        $trade_no = input('trade_no');
        $where = [
            ['mr_trade_no','=',$trade_no],
        ];
        $list = Db::name('rep_replenishment')->alias('r')
            ->join('goods g','r.mr_goods_id=g.goods_id','left')
            ->where($where)
            ->select();
        return $this->fetch('',['list'=>$list]);
    }


    public function wechat_refund(){
        $orders_id = input('orders_id');
        $orders_info = Db::name('orders')->where([
            'o_id' => $orders_id,
            'o_pay_status' => 1,
            'o_out_status' => ['in',[2,3]],
        ])->find();
    }


}