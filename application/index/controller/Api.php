<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/5/13
 * Time: 10:25
 */

namespace app\index\controller;


use think\Controller;
use think\controller\Rest;
use think\Db;
use think\Log;
use think\Request;

class Api extends Rest
{
    public function index()
    {
        $request = Request::instance();
        $input = $request->post();
        if(trim($input["api_type"]) == ''){
            return returnState(100,'请求错误');
        }
        switch ($input["api_type"]){
            case "adv_list":
                $result = self::getAdvList($input);
                break;
            case "channel_list":
                break;
            default:
                $result = returnState(101,'请求类型错误');
                break;
        }
        return $result;
    }

    /**
     * 获取设备广告列表接口
     * @param $input
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function getAdvList($input)
    {
        if(trim($input["machine_code"]) == ''){
            return returnState(102,'请求参数错误');
        }
        $machine = Db::name("machine")->where("machine_code",$input["machine_code"])->field("machine_id,machine_status,machine_online")->find();
        if(!$machine){
            return returnState(103,'请求失败，机器未录入');
        }else if($machine["machine_status"] == 2){
            return returnState(103,'请求失败，机器已禁用');
        }else if($machine["machine_online"] != 2){
            return returnState(104,'请求失败，机器已离线');
        }
        $nowTime = date("Y-m-d H:i:s",time());
        $advList = Db::name("adver_put")
            ->where([
                "put_machine_id"=>["eq",$machine["machine_id"]],
                "put_status"=>["in",[1,2]],
                "put_start_datetime"=>["elt",$nowTime],
                "put_end_datetime"=>["egt",$nowTime]
            ])
            ->select();
        self::changeAdvStatus($machine["machine_id"]);  //更新广告状态
        return returnState(200,'获取成功',$advList);
    }

    /**
     * 更新广告状态
     * @param $machine_id
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function changeAdvStatus($machine_id)
    {
        $nowTime = date("Y-m-d H:i:s",time());
        Db::name("adver_put")->where([
                "put_machine_id"=>["eq",$machine_id],
                "put_status"=>["eq",1],
                "put_start_datetime"=>["elt",$nowTime],
                "put_end_datetime"=>["egt",$nowTime]
            ])->update(["put_status"=>2]);
        Db::name("adver_put")->where([
            "put_machine_id"=>["eq",$machine_id],
            "put_status"=>["eq",2],
            "put_end_datetime"=>["lt",$nowTime]
        ])->update(["put_status"=>4]);
        return true;
    }
}