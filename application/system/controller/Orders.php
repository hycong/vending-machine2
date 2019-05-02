<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/21 0021
 * Time: 15:24
 */

namespace app\system\controller;

use cache\GetCache;
use EasyWeChat\Factory;
use payment\Payment;
use think\Db;
use think\db\Where;

class Orders extends Common
{
    public function index()
    {
        $machine_code = input('machineCode','');
        $payType = input("payType",0);
        $payStatus = input("payStatus",0);
        $outStatus = input("outStatus",0);
        $goods_id = input("goodsId",0);
        $start_time = input('start_time');
        $end_time = input('end_time');
        if ($start_time && $end_time) {
            $map['o.o_createTime'] = ['between', [$start_time, $end_time]];
        }
        if(intval($goods_id) > 0){
            $map["o.o_goods_id"] = ['eq',$goods_id];
        }
        if(intval($payType) > 0){
            $map["o.o_pay_type"] = ['eq',$payType];
        }
        if(intval($payStatus) > 0){
            $map["o.o_pay_status"] = ['eq',$payStatus];
        }else{
            $map['o.o_pay_status'] = ['<>', 3];
        }
        if(intval($outStatus) > 0){
            $map["o.o_out_status"] = ['eq',$outStatus];
        }
        if ($machine_code) {
            $map['o.o_machine'] = ["like","%$machine_code%"];
        }
        $order_list = Db::name('orders')->alias('o')
            ->join('agent a', 'a.agent_id=o.o_agent_id', 'left')
            ->where($map)
            ->field('o.*,a.agent_name')
            ->order('o.o_id DESC')
            ->paginate($this->pageNum, false, ['query' => request()->param()]);
        $goodsList = Db::name("goods")
            ->field("goods_id,goods_name,goods_type")
            ->select();
        return $this->fetch('',[
            'order_list' => $order_list,
            'goodsList' => $goodsList,
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
        $o_refund_no = $orders_info['o_refund_no'] ? $orders_info['o_refund_no'] : Payment::genRefundNo();
        if($orders_info){
            if(time() - strtotime($orders_info['o_pay_notify_time']) < 60*5) return returnState(100,'订单支付在5分钟内，不可退款！');
            $wechat_option = GetCache::getCache('wechat');
            $config = [
                // 必要配置
                'app_id'             => $wechat_option['app_id'],
                'mch_id'             => $wechat_option['mch_id'],
                'key'                => $wechat_option['key'],   // API 密钥
                // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
                'cert_path'          => './cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
                'key_path'           => './cert/apiclient_key',      // XXX: 绝对路径！！！！
                'notify_url'         => url('system/payment/refund_notify','','',true),     // 你也可以在下单时单独设置来想覆盖它
            ];
            Db::name('orders')->where(['o_id' => $orders_id,])->update(['o_refund_no' => $o_refund_no]);
            $app = Factory::payment($config);
            $result = $app->refund->byOutTradeNumber($orders_info['o_trade_no'], $o_refund_no, $orders_info['o_money']*100, $orders_info['o_money']*100, [
                // 可在此处传入其他参数，详细参数见微信支付文档
                'refund_desc' => '订单退款',
            ]);
            if($result['return_code'] != 'SUCCESS'){
                return returnState(100,$result['return_msg']);
            }
            if($result['result_code'] != 'SUCCESS'){
                return returnState(100,$result['err_code_des	']);
            }
            return returnState(200,'退款申请已提交。');
        }

    }


}