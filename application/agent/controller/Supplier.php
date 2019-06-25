<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/27
 * Time: 10:07
 */

namespace app\agent\controller;


use think\Db;

class Supplier extends Common
{
    /**
     * 供货商列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $where = [
            'supplier_agent_id' => ['eq',$this->agentId],
        ];
        $supplierList = Db::name("supplier")
            ->where($where)
            ->order("supplier_id DESC")
            ->paginate($this->pageNum,false,["query"=>$this->request->param()]);
        self::assign("supplierList",$supplierList);
        return self::fetch();
    }


    public function restPassword()
    {
        $supplier_id = input('supplier_id');
        $info  = Db::name('supplier')->where(['supplier_id'=>$supplier_id,'supplier_agent_id' => ['eq',$this->agentId]])->find();
        if($this->request->isPost()){
            if(!$info) return returnState(100,'没有供货商信息');
            $input = $this->request->only(['supplier_id','supplier_password'],'post');
            $result = $this->validate($input,'ASupplier.restPassword');
            if(true !== $result){
                return returnState(100,$result);
            }
            $input["supplier_password"] = md5($input["supplier_password"]);
            Db::startTrans();
            try{
                Db::name("supplier")->update($input);
                Db::commit();
                return returnState(200,"修改供货商信息成功");
            }catch (\PDOException $e){
                Db::rollback();
                return returnState(100,'网络异常，修改供货商信息失败');
            }
        }
        return returnState(110,'请求错误');
    }

    /**
     * 添加供货商
     * @return mixed
     */
    public function createSupplier()
    {
        if($this->request->isPost()){
            $input = $this->request->only(['supplier_name','supplier_mobile','supplier_password','supplier_status'],'post');
            $result = $this->validate($input,'ASupplier.create');
            if(true !== $result){
               return returnState(100,$result);
            }
            $input["supplier_password"] = md5($input["supplier_password"]);
            $input["supplier_agent_id"] = $this->agentId;
            $res = Db::name("supplier")->insert($input);
            if($res > 0){
                return returnState(200,'创建供货商成功');
            }
            return returnState(100,"网络异常,创建供货商失败");
        }
        return self::fetch();
    }

    /**
     * 修改供货商页面
     * @return mixed|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editSupplier()
    {
        $supplierId = input("supplier_id");
        $info  = Db::name('supplier')->where(['supplier_id'=>$supplierId,'supplier_agent_id' => ['eq',$this->agentId]])->find();
        if($this->request->isPost()){
            if(!$info) return returnState(100,'没有供货商信息');
            $input = $this->request->only(['supplier_id','supplier_name','supplier_mobile','supplier_status'],'post');
            $checkName = Db::name("supplier")->where([
                "supplier_id"=>["neq",$input["supplier_id"]],
                "supplier_name"=>['eq',$input["supplier_name"]],
            ])->value("supplier_id");
            $checkMobile = Db::name("supplier")->where([
                "supplier_id"=>["neq",$input["supplier_id"]],
                "supplier_mobile"=>['eq',$input["supplier_mobile"]],
            ])->value("supplier_id");
            if($checkName){
                return returnState(100,'该昵称已存在');
            }
            if($checkMobile){
                return returnState(100,'该联系电话已存在');
            }
            Db::startTrans();
            try{
                Db::name("supplier")->update($input);
                Db::commit();
                return returnState(200,"修改供货商信息成功");
            }catch (\PDOException $e){
                Db::rollback();
                return returnState(100,'网络异常，修改供货商信息失败');
            }
        }
        if(intval($supplierId) == 0){
            return self::error("未接收到供货商ID");
        }
        if(!$info) return $this->error('没有供货商信息');
        $supplier = Db::name("supplier")->where("supplier_id",$supplierId)->find();
        self::assign("supplier",$supplier);
        return self::fetch();
    }

    /**
     * 禁用/启用供货商
     */
    public function changeStaus()
    {
        $supplierId = input("post.supplier_id");
        if(intval($supplierId) == 0){
            return self::error("未接收到供货商ID");
        }
        $type = input("post.type",1);
        switch ($type){
            case "1":
                return $this->downSupplier($supplierId);
                break;
            case "2":
                return $this->upSupplier($supplierId);
                break;
        }
    }

    /**
     * 禁用供货商
     * @param $supplierId
     * @return array
     */
    public function downSupplier($supplierId)
    {
        Db::startTrans();
        try{
            Db::name("supplier")->where(["supplier_id"=>$supplierId,'supplier_agent_id'=>$this->agentId])->setField("supplier_status",2);
            Db::commit();
            return returnState(200,'禁用供货商成功');
        }catch (\PDOException $e){
            Db::rollback();
            return returnState(100,'禁用供货商失败');
        }
    }

    /**
     * 启用供货商
     * @param $supplierId
     * @return array
     */
    public function upSupplier($supplierId)
    {
        Db::startTrans();
        try{
            Db::name("supplier")->where(["supplier_id"=>$supplierId,'supplier_agent_id'=>$this->agentId])->setField("supplier_status",1);
            Db::commit();
            return returnState(200,'启用供货商成功');
        }catch (\PDOException $e){
            Db::rollback();
            return returnState(100,'启用供货商失败');
        }
    }


    /**
     * 删除供货商
     * @return array|void
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delSupplier()
    {
        $supplierId = input("post.supplier_id");
        if(intval($supplierId) == 0){
            return self::error("未接收到供货商ID");
        }
        Db::startTrans();
        try{
            Db::name("supplier")->where(["supplier_id"=>$supplierId,'supplier_agent_id'=>$this->agentId])->delete();
            Db::commit();
            return returnState(200,'删除供货商成功');
        }catch (\PDOException $e){
            Db::rollback();
            return returnState(100,'删除供货商失败');
        }
    }
}