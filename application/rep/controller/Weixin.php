<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/1 0001
 * Time: 11:36
 */

namespace app\rep\controller;


use cache\GetCache;
use EasyWeChat\Factory;
use think\Db;

class Weixin extends Common
{
    public $app;
    public function __construct()
    {
        $wechat_option = GetCache::getCache('wechat');
        $this->app = Factory::officialAccount($wechat_option);
    }

    public function set_openid(){
        return $response = $this->app->oauth->scopes(['snsapi_base'])->redirect(url('rep/Weixin/update_openid','','',true));
    }

    public function update_openid(){
        $user_info = $this->app->oauth->user();
        if(!$user_info || $user_info==null){
            die("登陆错误");
        }else{
            Db::name('rep')->where(['rep_id'=>$this->rep_id])->update([
                'rep_open_id' => $user_info['openid'],
            ]);
        }
    }

}