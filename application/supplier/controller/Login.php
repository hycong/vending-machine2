<?php
/**
 * Created by PhpStorm.
 * User: agent
 * Date: 2019/4/26
 * Time: 10:53
 */

namespace app\supplier\controller;


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
        $post_data = input('post.');
        $result = $this->validate($post_data,'ALogin.login');
        if(true !== $result){
            // 验证失败 输出错误信息
            return returnState(100,$result);
        }
        $user = Db::name('supplier')->where('supplier_mobile',$post_data["username"])->find();
        if(!$user){
            return returnState(100,'用户名不存在，请重新输入',$post_data);
        }else if($user['supplier_password'] != md5($post_data["password"])) {
            return returnState(100, '密码错误，请重新输入');
        }
//        Db::name('supplier')->where('supplier_id',$user['supplier_id'])->update([
//            'agent_last_time' => date('Y-m-d H:i:s'),
//            'agent_last_ip' => Request::instance()->ip(),
//        ]);
        Session::set('supplierNickname',$user['supplier_mobile']);
        Session::set('supplierId',$user['supplier_id']);
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