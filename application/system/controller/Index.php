<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/8 0008
 * Time: 14:58
 */

namespace app\system\controller;


class Index extends Common
{

    public function index(){
        return $this->fetch();
    }

}