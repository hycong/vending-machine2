<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/29
 * Time: 15:22
 */

namespace app\agent\controller;


use think\Db;

class Depost extends Common
{
    /**
     * 提现记录
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $check["extract_agent_id"] = ['eq',$this->agentId];
        $depositList = Db::name("agent_extract")
            ->where($check)
            ->order("extract_id DESC")
            ->paginate($this->pageNum,false,["query"=>$this->request->param()]);
        self::assign('depositList',$depositList);
        return self::fetch();
    }

    /**
     * 提现账号设置
     * @return mixed|void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function depositAccount()
    {
        if($this->request->isPost()){
            $input = $this->request->only(["account_bank","account_open","account_code","account_username","account_mobile"],'post');
            $agentAccount = Db::name("agent_account")->where("agent_id",$this->agentId)->value("agent_id");
            Db::startTrans();
            try {
                if ($agentAccount) {
                    $result = $this->validate($input, 'AAccount.update');
                    if (true !== $result) {
                        return returnState(100,$result);
                    }
                    Db::name("agent_account")->where("agent_id",$this->agentId)->update($input);
                } else {
                    $input["agent_id"] = $this->agentId;
                    $result = $this->validate($input, 'AAccount.create');
                    if (true !== $result) {
                        return $this->error($result);
                    }
                    Db::name("agent_account")->insert($input);
                }
                Db::commit();
                return returnState(200,'保存配置成功');
            }catch (\PDOException $e){
                Db::rollback();
                return returnState(100,'网络异常，保存配置失败');
            }
        }
        $account = Db::name("agent_account")->where("agent_id",$this->agentId)->find();
        self::assign("account",$account);
        return self::fetch();
    }


    /**
     * 撤销提现
     * @return array
     */
    public function repealDeposit()
    {
        if($this->request->isPost()){
            $id = input("post.id");
            if(intval($id) == 0){
                return returnState(100,'未获取到提现记录');
            }
            Db::startTrans();
            try{
                Db::name('agent_extract')->where("extract_id",$id)->setField('extract_status',4);
                Db::commit();
                return returnState(200,'撤销订单成功');
            }catch (\PDOException $e){
                Db::rollback();
                return returnState(100,'网络异常，撤销提现失败');
            }

        }
        return returnState(100,'网络异常，撤销提现失败');
    }
}