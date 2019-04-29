<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/26
 * Time: 10:53
 */

namespace app\agent\controller;


class Index extends Common
{
    public function index()
    {
        return self::fetch();
    }
}