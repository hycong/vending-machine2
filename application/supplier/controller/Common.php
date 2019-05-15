<?php
/**
 * Created by PhpStorm.
 * User: supplier
 * Date: 2019/4/26
 * Time: 10:53
 */

namespace app\supplier\controller;


use cache\GetCache;
use think\Controller;
use think\Db;
use think\Request;

class Common extends Controller
{
    protected $supplierId;
    protected $supplierNickname;
    protected $pageNum = 15;
    protected $request;
    public function __construct()
    {
        parent::__construct();
        $this->supplierId = session('supplierId');
        $this->supplierNickname = session('supplierNickname');
        if(intval($this->supplierId) == 0){
            return $this->redirect(url('login/login'));
        }
        $this->assign('supplierId',$this->supplierId);
        $this->assign('supplierNickname',$this->supplierNickname);
        $this->request = Request::instance();
    }



}