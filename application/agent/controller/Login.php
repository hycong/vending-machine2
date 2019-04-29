<?php
/**
 * Created by PhpStorm.
 * User: agent
 * Date: 2019/4/26
 * Time: 10:53
 */

namespace app\agent\controller;


use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class Login extends Controller
{
    public function login()
    {
        return self::fetch();
    }
    public function check(){
        $username = input('username');
        $password = input('password');
        $post_data = input('post.');
        $result = $this->validate($post_data,'ALogin.login');
        if(true !== $result){
            // 验证失败 输出错误信息
            return returnState(100,$result);
        }
        $user = Db::name('agent')->where('agent_username',$username)->find();
        if(!$user){
            return returnState(100,'用户名不存在，请重新输入');
        }else if($user['agent_password'] != md5($password)) {
            return returnState(100, '密码错误，请重新输入');
        }
        Db::name('agent')->where('agent_id',$user['agent_id'])->update([
            'agent_last_time' => date('Y-m-d H:i:s'),
            'agent_last_ip' => Request::instance()->ip(),
        ]);
        Session::set('agentNickname',$user['agent_name']);
        Session::set('agentId',$user['agent_id']);
        return returnState(200,'验证成功');
    }





    /**

     * 登出系统

     */

    public function logout()
    {
        Session::clear();
        $this->redirect(url('Login/login'));
    }



    public function test(){
        p(cache('agent_path'));
    }
}