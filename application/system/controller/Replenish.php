<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/14 0014
 * Time: 17:09
 */

namespace app\system\controller;


use think\Db;

class Replenish extends Common
{

    public function index(){
        $name = input("name");
        $username = input("username");
        $status = input("status");
        $map = [];
        if(intval($status) != 0) $map['r.rep_status'] = $status;
        if(trim($name) != '') $map['r.rep_name'] = ["like","%$name%"];
        if(trim($username) != '') $map['r.rep_username'] = ["like","%$username%"];
        $rep_list = Db::name('rep')->alias('r')
            ->join('agent a','r.rep_agent_id=a.agent_id','left')
            ->order('r.rep_id DESC')
            ->field('r.rep_id,r.rep_name,r.rep_username,r.rep_status,a.agent_name,a.agent_createTime')
            ->where($map)
            ->paginate($this->pageNum,false,['query'=>request()->param()]);
        $this->assign('rep_list',$rep_list);
        return $this->fetch();
    }

}