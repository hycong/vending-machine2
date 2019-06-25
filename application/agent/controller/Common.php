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
use think\Db;
use think\Request;

class Common extends Controller
{
    protected $agentId;
    protected $agentNickname;
    protected $pageNum = 15;
    protected $request;
    protected $agentLevel;
    public function __construct()
    {
        parent::__construct();
        $this->agentId = session('agentId');
        $this->agentNickname = session('agentNickname');
        $this->agentLevel = session("agentLevel");
        if(intval($this->agentId) == 0){
            return $this->redirect(url('login/login'));
        }
        $this->assign('agentId',$this->agentId);
        $this->assign('agentNickname',$this->agentNickname);
        $this->assign("agentLevel",$this->agentLevel);
        $this->request = Request::instance();
        $this->assign('webconfig',GetCache::getCache('webconfig'));
    }


    /**
     * 获取提现账户
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getAgentAccount($id)
    {
        $agentAccount = Db::name("agent_account")->where("agent_id",$id)->find();
        return $agentAccount;
    }


    protected function errorFetch($msg = ''){
        return $this->fetch('common/error_fetch',[
            'msg'=>$msg
        ]);
    }
}