<?php
/**
 * Created by PhpStorm.
 * User: agent
 * Date: 2019/4/26
 * Time: 10:53
 */

namespace app\agent\controller;


use cache\GetCache;
use think\Controller;
use think\Request;

class Common extends Controller
{
    protected $agentId;
    protected $agentNickname;
    protected $pageNum = 15;
    protected $request;
    public function __construct()
    {
        parent::__construct();
        $this->agentId = session('agentId');
        $this->agentNickname = session('agentNickname');
        if(intval($this->agentId) == 0){
            return $this->redirect(url('login/login'));
        }
        $this->assign('agentId',$this->agentId);
        $this->assign('agentNickname',$this->agentNickname);
        $this->request = Request::instance();
        $this->assign('webconfig',GetCache::getCache('webconfig'));
    }
}