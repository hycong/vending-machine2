<?php/** * Created by PhpStorm. * User: Administrator * Date: 2018/4/8 0008 * Time: 14:36 */namespace app\system\controller;use agent\AgentPath;use app\common\model\AgentConfigModel;use app\common\model\AgentModel;use think\Db;use think\Request;class Agent extends Common{    /**     * 加盟商列表     */    public function index(){        $name = input("name");        $username = input("username");        $status = input("status");        $level = input("level");        $parent_id = input("parent_id");        $agent_id = input("agent_id");        $map = [];        if($status != 0) $map['agent_status'] = ['eq',$status];        if($agent_id) $map['agent_id'] = ['eq',$agent_id];        if(trim($name) != '') $map['agent_name'] = ['like',"%$name%"];        if(trim($username) != '') $map['agent_username'] = ["like","%$username%"];        if(trim($level) != 0) $map['agent_level'] = ['eq',$level];        if(trim($parent_id) != '') $map['agent_parent_id'] = ['eq',$parent_id];        $agent_list = Db::name('agent')            ->order('agent_id DESC')            ->where($map)            ->paginate(20,false,['query'=>request()->param()]);        return $this->fetch('',[            'agent_list' => $agent_list        ]);    }    /**     * 创建加盟商     */    public function createAgent(){        if(Request::instance()->isPost()){            $data = input('post.');            $result = $this->validate($data,'app\system\validate\VAgent.create');            if(true !== $result){                // 验证失败 输出错误信息                return returnState(100,$result);            }            if(Db::name('agent')->where(['agent_username'=>$data['agent_username']])->value('agent_id')){                return returnState(100,'登录账号已存在');            }            $data['agent_level'] = 1;            $data['agent_password'] = md5($data['agent_password']);            $data['agent_status'] = 1;            $data['agent_balance'] = 0;            $data['agent_createTime'] = date('y-m-d H:i:s',time());            Db::startTrans();            try {                $AgentModel = new AgentModel();                $AgentModel->allowField(true)->data($data)->save();                $agent_id = $data['agent_id'] = $AgentModel->agent_id;                $AgentConfigModel = new AgentConfigModel();                $AgentConfigModel->allowField(true)->data($data)->save();                AgentPath::close_path();                Db::commit();                return returnState(200,'新增成功',url('index'));            } catch (\PDOException $e) {                Db::rollback();                return returnState(100,'新增失败，请重试');            }        }        return $this->fetch();    }    /**     * 编辑加盟商     */    public function editAgent(){        $agent_id = input('agent_id');        $agent = Db::name('agent')->alias('a')            ->join('agent_config c','a.agent_id=c.agent_id','left')            ->where(['a.agent_id' => $agent_id])->find();        if(Request::instance()->isPost()){            if(!$agent) return returnState(100,'没有加盟商信息');            $data = input('post.');            $result = $this->validate($data,'app\system\validate\VAgent.edit');            if(true !== $result){                // 验证失败 输出错误信息                return returnState(100,$result);            }            $map[] = ['agent_id','<>',$data['agent_id']];            $agent_list = Db::name('agent')->where($map)->select();            foreach ($agent_list as $agent){                if($agent['agent_name'] == $data['agent_name']){                    return returnState(100,'该加盟商名称已存在');                }else if($agent['agent_username'] == $data['agent_username']){                    return returnState(100,'该登录账号已存在');                }else if($agent['agent_contacts'] == $data['agent_contacts']){                    return returnState(100,'该联系人已存在');                }else if($agent['agent_mobile'] == $data['agent_mobile']){                    return returnState(100,'该联系人电话已存在');                }            }            if($data['agent_password']) $data['agent_password'] = md5($data['agent_password']);            Db::startTrans();            try {                $AgentModel = new AgentModel();                $AgentModel->allowField(true)->save($data,['agent_id' => $data['agent_id']]);                $AgentConfigModel = new AgentConfigModel();                $AgentConfigModel->allowField(true)->save($data,['agent_id' => $data['agent_id']]);                Db::commit();                return returnState(200,'编辑成功','index');            } catch (\PDOException $e) {                Db::rollback();                return returnState(100,'编辑失败，请重试');            }        }        if(!$agent) return $this->error('没有加盟商信息');        return $this->fetch('',[            'info' => $agent,        ]);    }    /**     *删除加盟商     */    public function delAgentStatus(){        $agent_id = input('agent_id');        $map['agent_id'] = $agent_id;        $map['agent_status'] = 2;        Db::startTrans();        try {            Db::name('agent')->update($map);            Db::commit();            return returnState(200,'禁用成功','index');        } catch (\PDOException $e) {            Db::rollback();            return returnState(100,'禁用失败，请重试');        }    }    /**     * 恢复加盟商     */    public function changeAgentStatus(){        $agent_id = input('agent_id');        $map['agent_id'] = $agent_id;        $map['agent_status'] = 1;        Db::startTrans();        try {            Db::name('agent')->update($map);            Db::commit();            return returnState(200,'恢复成功','index');        } catch (\PDOException $e) {            Db::rollback();            return returnState(100,'恢复失败，请重试');        }    }    /**     * 注销加盟商     */    public function delAgent(){        $agent_id = input('agent_id');        $map['agent_id'] = $agent_id;        $info = Db::name('agent')->where(['agent_parent_id'=>$agent_id])->find();        if(!$info) return returnState(100,'没有加盟商信息');        if(Db::name('agent')->where(['agent_parent_id'=>$agent_id])->find()){            return returnState(100,'请先删除其下级代理');        }        Db::startTrans();        try {            Db::name('agent')->delete($map);            AgentPath::del_level_path($agent_id,$info['agent_parent_id'],$info['agent_level']); // 清除加盟商下级配置            Db::commit();            return returnState(200,'删除成功','index');        } catch (\PDOException $e) {            Db::rollback();            return returnState(100,'删除失败，请重试');        }    }}