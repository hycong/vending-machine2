<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/29
 * Time: 15:22
 */

namespace app\agent\controller;


use agent\AgentPath;
use app\common\model\ConfModel;
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

    /**
     * 我的余额
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function myBalance()
    {
        $agent = Db::name("agent")->where("agent_id",$this->agentId)->find();
        $conf = ConfModel::getConf('depost_conf');
        self::assign('conf',$conf);
        self::assign('agent',$agent);
        return self::fetch();
    }


    /**
     * 申请提现
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function applyDeposit()
    {
        if($this->request->isPost()){
//            $money  = input("post.money");
//            $remark = input("post.remark");
            $input = input("post.");
            $result = $this->validate($input,'AAccount.applyMoney');
            if(true !== $result){
                // 验证失败 输出错误信息
                return returnState(100,$result);
            }
            $conf = ConfModel::getConf('depost_conf');
            if(floatval($input["money"]) < $conf["min"]){
                return returnState(100,'提现金额不能小于'.$conf["min"].'元');
            }else if(floatval($input["money"]) > $conf["max"]){
                return returnState(100,'提现金额不能大于'.$conf["max"].'元');
            }
            $agent = Db::name("agent")->where("agent_id",$this->agentId)->find();
            $tax_money = $input["money"] * $conf["extract_tax"] * 0.01;             //手续费
            $tax_dues_money = $input["money"] * $conf["extract_taxes_dues"] * 0.01;       //税收费
            $total_moeny = $input["money"] + $tax_money + $tax_dues_money;
            if(floatval($total_moeny) > $agent["agent_balance"]){
                return returnState(100,'余额不足，所扣余额为'.$total_moeny.'元，提现申请失败');
            }
            $agent_account = $this->getAgentAccount($this->agentId);
            if(!$agent_account){
                return returnState(100,"未设置提现账户，提现申请失败");
            }
            $data = [
                "extract_agent_id"=>$this->agentId,
                "extract_agent"=>AgentPath::getLastParentAgent($this->agentId),
                "extract_name"=>$agent_account["account_username"],
                "extract_bank"=>$agent_account["account_bank"],
                "extract_card"=>$agent_account["account_code"],
                "extract_mobile"=>$agent_account["account_mobile"],
                "extract_money"=>$input["money"],
                "extract_tax"=>$conf["extract_tax"],
                "extract_tax_money"=>$tax_money,
                "extract_taxes_dues"=>$conf["extract_taxes_dues"],
                "extract_taxes_dues_money"=>$tax_dues_money,
                "extract_total_money"=>$total_moeny,
                "extract_status"=>1,
                "extract_remark"=>$input["remark"],
                "extract_time"=>date("Y-m-d H:i:s",time())
            ];
            Db::startTrans();
            try{
                Db::name("agent")
                    ->where("agent_id",$this->agentId)
                    ->dec("agent_balance",$total_moeny)
                    ->inc("agent_frozen",$total_moeny)
                    ->update();
                Db::name("agent_extract")->insert($data);
                Db::commit();
                return returnState(200,"提现申请成功，请等待后台审核");
            }catch (\PDOException $e){
                Db::rollback();
                return returnState(100,"网络异常，提现申请失败");
            }
            return returnState(100,'网络异常，提现申请失败');
        }
        return returnState(100,'请求错误');
    }


    public function getAgent()
    {
        $getAgent = AgentPath::getLastParentAgent($this->agentId);
        print_r($getAgent);
    }
}