<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/17
 * Time: 9:29
 */

namespace app\system\controller;


use think\Db;
use think\Request;

class Advertising extends Common
{
    /**
     * 投放列表
     * @return mixed
     */
    public function index()
    {
        return self::fetch();
    }

    /**
     * 投放广告页面
     * @return mixed
     */
    public function putAdver()
    {
        $request = Request::instance();
        if($request->isPost()){
            $input = input("post.");
            Db::startTrans();
            try{


                Db::commit();
                return self::success("广告投放成功","index");
            }catch (\PDOException $e){
                Db::rollback();
                return self::error("网络异常，投放广告失败！");
            }
        }else{
            return self::fetch();
        }
    }

    /**
     * 获取素材列表（默认不分屏）
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAdverList()
    {
        $type = input("post.type",1);
        $check["resource_put_type"] = $type;
        $adverList = Db::name("adver_resource")->where($check)->order("resource_id")->select();
        return json(returnState(200,'获取成功',$adverList));
    }
}