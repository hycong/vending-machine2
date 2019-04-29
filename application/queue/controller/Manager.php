<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/25
 * Time: 15:44
 */

namespace app\queue\controller;

use think\Controller;

class Manager extends Controller
{
    protected $redis;
    public function _initialize()
    {
        parent::_initialize();
        $this->redis = new \Redis();
        $this->redis->connect("127.0.0.1",'6379');
    }
    public function managerCmd()
    {
        while(true) {
            $list = $this->redis->lRange("cmd_list", 0, -1);
            $num = count($list);
            if ($num > 0) {
                $data = $this->redis->rPop("cmd_list");
                if($data != '') {
                    Log::write($data,'cmdList');
                    $data = json_decode($data, true);
                    $temp_str = $data["message"];                               //指令
                    $machine_code = $data["code"];  //设备号
                    $orders = strtoupper($temp_str);
                    $check_header = substr($orders,0,4);
                    $check_header = strtoupper($check_header);
                    switch ($check_header){
                        case "CONN":        //连接，发送获取设备序号
                            break;
                    }
                }
            }
            usleep(100);
        }
    }
}