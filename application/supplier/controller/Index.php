<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/5/14
 * Time: 11:23
 */

namespace app\supplier\controller;


class Index extends Common
{
    public function index()
    {
        return self::fetch();
    }
}