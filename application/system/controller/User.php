<?php
namespace app\system\controller;


use think\Db;

class User extends Common
{

    public function index(){
        $where = [];
        $user_name = input('user_name');
        $user_openid = input('user_openid');
        $user_id = input('user_id');
        $user_subscribe = input('user_subscribe');
        if(trim($user_name) != '') $where['user_name'] = ['eq',$user_name];
        if(trim($user_openid) != '') $where['user_openid'] = ['eq',$user_openid];
        if(intval($user_id) != 0) $where['user_id'] = ['eq',$user_id];
        if(intval($user_subscribe) != 0) $where['user_subscribe'] = ['eq',$user_subscribe];
        $userList = Db::name('user')
            ->where($where)
            ->order('user_id DESC')
            ->paginate($this->pageNum, false, ['query' => request()->param()]);
        return $this->fetch('',[
            'userList' => $userList,
        ]);
    }

}