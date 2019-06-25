<?php
namespace app\agent\controller;

use think\Db;
use think\Request;

class Replenish extends Common
{

    public function index(){
        $name = input("name");
        $username = input("username");
        $status = input("status");
        $map = [];
        $map["rep_agent_id"] = ["eq",$this->agentId];
        if(intval($status) != 0) $map['rep_status'] = ['eq',$status];
        if(trim($name) != '') $map['rep_name'] = ["like","%$name%"];
        if(trim($username) != '') $map['rep_username'] = ["like","%$username%"];
        $rep_list = Db::name('rep')
            ->order('rep_id DESC')
            ->where($map)
            ->paginate($this->pageNum,false,['query'=>request()->param()]);
        $this->assign('rep_list',$rep_list);
        return $this->fetch();
    }

    /**
     * 分配机器
     */
    public function allocatingMachine(){
        $rep_id = input('rep_id');
        $info = Db::name('rep')->where(['rep_id'=>$rep_id,'rep_agent_id'=>$this->agentId])->find();
        if(Request::instance()->isPost()){
            if(!$info) return returnState(100,'没有货道信息');
            $machine_id = input('machine_id/a');
            Db::name('machine')->where(['machine_rep_id'=>$rep_id])->setField('machine_rep_id',0);
            Db::name('machine')->where(['machine_id'=>['in',$machine_id]])->setField('machine_rep_id',$rep_id);
            return returnState(200,'设置成功');
        }
        if(!$info) return $this->error('没有货道信息');
        return $this->fetch('',[
            'info' => $info,
            'machineList' => Db::name('machine')->where(['machine_agent_id'=>$info['rep_agent_id']])->select(),
        ]);
    }

}