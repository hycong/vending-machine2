<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/17
 * Time: 9:29
 */

namespace app\system\controller;


use think\Db;

class Advertising extends Common
{
    public function index()
    {
        return self::fetch();
    }

    public function putAdver()
    {
        return self::fetch();
    }

    public function getAdverList()
    {
        $type = input("post.type",1);
        $check["resource_put_type"] = $type;
        $adverList = Db::name("adver_resource")->where($check)->order("resource_id")->select();
        return json(returnState(200,'获取成功',$adverList));
    }
}