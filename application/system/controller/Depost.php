<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/16 0016
 * Time: 15:35
 */

namespace app\system\controller;

use think\Db;
use think\Request;

class Depost extends Common
{
    public function index()
    {
        $agentName = input("agent_name");
        $agentId = input("agent_id");
        $map = [];
        if($agentName){
            $map["ag.agent_name"] = $agentName;
        }
        if(intval($agentId) != 0){
            $map["ag.agent_id"] = $agentId;
        }
        $agent_record = Db::name("agent_extract")->alias('e')
            ->join("agent ag","ag.agent_id = e.extract_agent_id",'left')
            ->where($map)
            ->order("e.extract_time DESC")
            ->field("e.*,ag.agent_name")
            ->paginate($this->pageNum,false,["query"=>request()->param()]);
        $agent_list = Db::name("agent")->select();
        $this->assign("agent_list",$agent_list);
        $this->assign("agent_record",$agent_record);
        return $this->fetch();
    }

    public function depost()
    {
        $data = input();
        $record_info = Db::name("agent_extract")->alias('e')
            ->join("agent ag","ag.agent_id = e.extract_agent_id",'left')
            ->where(['extract_id'=>$data['extract_id']])
            ->field("e.*,ag.agent_name")->find();
        if(Request::instance()->isPost()){
            $status = input('extract_status');
            $remark = input('extract_remark');
            $extract_id = input('extract_id');
            Db::startTrans();
            $set = ['extract_status' => $status, 'extract_result_remark' => $remark, 'extract_deal_time' => date('Y-m-d H:i:s'),'extract_deal_agent'=>-1];
            $result = Db::name('agent_extract')->where(['extract_id' => $extract_id])->setField($set);  // 修改提现信息状态
            if ($result) {   // 修改成功
                $extract = Db::name('agent_extract')->where(['extract_id' => $extract_id])->find();
                $agent_map['agent_id'] = ['eq',$extract['extract_agent_id']];
                $money = $extract['extract_money'] + $extract['extract_tax_money'] + $extract['extract_taxes_dues_money'];
                if($status == 2 ) {  //  已同意
                    Db::name('agent')->where($agent_map)->setInc('agent_extract',$money);  // 加上提现成功金额与手续费
                }else if($status == 3) {   // 已拒绝
                    $agent_info = Db::name('agent')->where($agent_map)->field('agent_balance')->find();
                    $insert_state['statement_agent_id'] = $extract['extract_agent_id'];
                    $insert_state['statement_money'] = $money;
                    $insert_state['statement_type'] = 1;
                    $insert_state['statement_remark'] = '拒绝该申请，【提现申请】：'.$insert_state['statement_money'].'元，提现金额：'.$extract['extract_money'].'，代缴手续费：'.$extract['extract_tax_money'].'，代缴税费：'.$extract['extract_taxes_dues_money'];
                    $insert_state['statement_before'] = $agent_info['agent_balance'];      //  拒绝申请前余额 agent_balancer
                    $insert_state['statement_time'] = date('Y-m-d H:i:s',time());
                    Db::name('agent_statement')->insert($insert_state);  // 插入用户提现处理流水信息
                    Db::name('agent')->where($agent_map)->inc('agent_balance',$money)->update();  // 返回提现金额
                }
                Db::name('agent')->where($agent_map)->setDec('agent_frozen',$money);  // 减去冻结金额
                Db::commit();
                return returnState(200,'处理成功');
            }else{
                Db::rollback();
                return returnState(100,'审核失败');
            }
        }
        return $this->fetch('',['info'=>$record_info]);
    }
}