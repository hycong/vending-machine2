<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26 0026
 * Time: 10:17
 */

namespace app\rep\controller;


use cache\GetCache;
use EasyWeChat\Factory;
use think\Config;
use think\Db;
use think\Request;

class Replenishment extends Common
{

    public function index(){
        $page = input('page',1);
        $where = [
            'r.mr_rep_id' => $this->rep_id,
        ];
        $list = Db::name('rep_replenishment')->alias('r')
            ->join('machine m','r.mr_machine_id=m.machine_id','left')
            ->where($where)
            ->field('sum(mr_goods_stock) stock,r.*,m.machine_name,m.machine_code')
            ->group('mr_trade_no')
            ->order('mr_createTime desc')
            ->limit(($page-1)*$this->pageNum,$this->pageNum)->select();
        if(!$list) return returnState(100,'没有数据！');
        return returnState(200,'获取成功',$list);
    }

    /**
     * 补货处理
     */
    public function replenish(){
        $machine_id = input('machine_id');
        if (intval($machine_id) == 0) {
            return returnState(100,"设备ID获取失败，请重新进入页面");
        }
        $map = [
            'mc.channel_machine_id' => $machine_id,
            'mc.channel_status' => 1,
            'm.machine_rep_id' => $this->rep_id,
            'mc.channel_goods_id' => ['<>',0],
        ];
        $channel_list = Db::name('machine_channel')
            ->alias('mc')
            ->join('machine m','mc.channel_machine_id = m.machine_id','left')
            ->join('goods g', 'g.goods_id = mc.channel_goods_id', 'left')
            ->where($map)
            ->field('mc.*,g.goods_name goods_name,g.goods_id goods_id,g.goods_type goods_type')
            ->order('channel_name asc')
            ->column(null,'channel_id');
        if(Request::instance()->isPost()){
            $post_data = input('channel_id');
            $mr_trade_no = 'REP'.date('YmdHis').rand(10000,99999).rand(10,99);
            $mr_rep_ip = Request::instance()->ip();
            $mr_createTime = date('Y-m-d H:i:s');
            $machine_info = Db::name('machine')->alias('m')
                ->join('agent a','m.machine_agent_id = a.agent_id','left')
                ->where(['m.machine_id'=>$machine_id])->field('m.*,a.agent_openid')->find();
            $template = [
                'touser' => $machine_info['agent_openid'],
                'template_id' => Config::get('weixin_template')['replenishment_successful'],
                'url' => '',
                'data' => [
                    'machine_name' => $machine_info['machine_name'],
                    'machine_code' => $machine_info['machine_code'],
                    'all_count' => 0,
                    'channel_count' => 0,
                    'rep_name' => $this->rep_name,
                    'time' => $mr_createTime,
                ],
            ];
            foreach ($post_data as $key => $value){
                if($value > $channel_list[$key]['channel_capacity'] - $channel_list[$key]['channel_stock']){
                    $value = $channel_list[$key]['channel_capacity'] - $channel_list[$key]['channel_stock'];
                }
                $template['data']['all_count'] += $value;
                $template['data']['channel_count'] += 1;
                Db::name('rep_replenishment')->insertGetId([
                    'mr_trade_no' => $mr_trade_no,
                    'mr_machine_id' => $channel_list[$key]['channel_machine_id'],
                    'mr_channel_id' => $channel_list[$key]['channel_id'],
                    'mr_goods_id' => $channel_list[$key]['channel_goods_id'],
                    'mr_agent_id' => $channel_list[$key]['machine_agent_id'],
                    'mr_rep_id' => $channel_list[$key]['machine_rep_id'],
                    'mr_rep_ip' => $mr_rep_ip,
                    'mr_goods_stock' => $value,
                    'mr_goods_before_stock' => $channel_list[$key]['channel_stock'],
                    'mr_createTime' => $mr_createTime,
                ]);
                Db::name('machine_channel')->where(['channel_id'=>$channel_list[$key]['channel_id']])->inc('channel_stock',$value)->update(); // 货道添加库存
            }
            if($machine_info['agent_openid'] != '' && $template['template_id'] != ''){
                $app = Factory::officialAccount(GetCache::getCache('wechat'));
                $app->template_message->send($template);
            }
            return returnState(200,'补货成功！');
        }
        return returnState(200,'获取成功！',[
            'channel_list' => $channel_list,
            'machine_id' => $machine_id,
        ]);
    }

    public function replenish_all(){
        $machine_id = input('machine_id');
        if (intval($machine_id) == 0) {
            return returnState(100,"设备ID获取失败，请重新进入页面");
        }
        $map = [
            ['mc.channel_machine_id','=',$machine_id],
            ['mc.channel_status','=',1],
            ['m.machine_rep_id','=',$this->rep_id],
            ['mc.channel_goods_id','<>',0]
        ];
        $channel_list = Db::name('machine_channel')
            ->alias('mc')
            ->join('machine m','mc.channel_machine_id = m.machine_id','left')
            ->join('goods g', 'g.goods_id = mc.channel_goods_id', 'left')
            ->where($map)
            ->field('mc.*,g.goods_name goods_name,g.goods_id goods_id,g.goods_type goods_type')
            ->order('channel_name asc')
            ->column(null,'channel_id');
        $mr_trade_no = 'REP'.date('YmdHis').rand(10000,99999).rand(10,99);
        $mr_rep_ip = Request::instance()->ip();
        $mr_createTime = date('Y-m-d H:i:s');
        foreach ($channel_list as $key => $value){
            $num = $value['channel_capacity'] - $value['channel_stock'];
            if($num <= 0) continue;
            Db::name('rep_replenishment')->insertGetId([
                'mr_trade_no' => $mr_trade_no,
                'mr_machine_id' => $value['channel_machine_id'],
                'mr_channel_id' => $value['channel_id'],
                'mr_goods_id' => $value['channel_goods_id'],
                'mr_agent_id' => $value['machine_agent_id'],
                'mr_rep_id' => $value['machine_rep_id'],
                'mr_rep_ip' => $mr_rep_ip,
                'mr_goods_stock' => $num,
                'mr_goods_before_stock' => $value['channel_stock'],
                'mr_createTime' => $mr_createTime,
            ]);
            Db::name('machine_channel')->where(['channel_id'=>$value['channel_id']])->inc('channel_stock',$num)->update(); // 货道添加库存
        }
        return returnState(200,'补货成功！');
    }

    /**
     * 补货详情
     */
    public function details(){
        $trade_no = input('trade_no');
        $where = [
            'mr_trade_no' => $trade_no,
            'mr_rep_id' => $this->rep_id,
        ];
        $list = Db::name('rep_replenishment')->alias('r')
            ->join('goods g','r.mr_goods_id=g.goods_id','left')
            ->where($where)
            ->select();
        return returnState(200,'补货成功！',['list'=>$list]);
    }

}