<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/11 0011
 * Time: 17:12
 */

namespace app\system\controller;


use think\Db;
use think\Request;
use think\Session;

class Center extends Common
{

    public function update_password(){
        if(Request::instance()->isPost()){
            $data = input('post.');
            $result = $this->validate($data,'VAdmin.update_password');
            if(true !== $result){
                // 验证失败 输出错误信息
                return returnState(100,$result);
            }
            $old_info = Db::name('admin')->find($this->adminId);
            if($old_info['admin_password'] != md5($data['old_password'])){
                return returnState(100,'原密码错误');
            }else{
                $map['admin_id'] = $this->adminId;
                $res = Db::name('admin')->where($map)->update(['admin_password'=>md5($data['password'])]);
                if(false !== $res){
//                    Session::clear();
                    return returnState(200,'更新密码成功',[],url('Login/logout'));
                }else{
                    return returnState(100,'更新密码失败');
                }
            }
        }
        return $this->fetch();
    }

}