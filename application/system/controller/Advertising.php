<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/17
 * Time: 9:29
 */

namespace app\system\controller;


use think\Db;
use think\Request;

class Advertising extends Common
{
    protected $pageNum = 20;
    /**
     * 投放列表
     * @return mixed
     */
    public function index()
    {
        $code = input("code");
        $name = input("name");
        $check = [];
        if(trim($code) != '') $check['m.machine_code']=['like',"%$code%"];
        if(trim($name) != '') $check['ar.resource_name']=['like',"%$name%"];
        $putAdverList = Db::name("adver_put")
            ->alias('put')
            ->join("adver_resource ar",'ar.resource_id = put.put_adver_id','left')
            ->join('machine m','m.machine_id = put.put_machine_id','left')
            ->field("put.*,ar.resource_name resource_name,m.machine_code machine_code,m.machine_name machine_name")
            ->where($check)
            ->order("put_id DESC")
            ->paginate($this->pageNum,false,['query'=>\request()->param()]);
        self::assign("putAdverList",$putAdverList);
        self::assign('code',$code);
        self::assign('name',$name);
        return self::fetch();
    }

    /**
     * 上/下架
     * @return \think\response\Json
     */
    public function changeStatus()
    {
        $id = input("post.id");
        if (intval($id) <= 0) {
            return returnState(100, '请提交素材ID');
        }
        $put = Db::name("adver_put")->where("put_id",$id)->find();
        if(!$put){
            return returnState(100, '未找到该投放记录');
        }
        $type = input("post.type");
        switch ($type){
            case "1":
                return $this->downAdver($put);
                break;
            case "2":
                return $this->upAdver($put);
                break;
        }
    }

    /**
     * 下架投放广告
     * @return \think\response\Json
     */
    public function downAdver($put)
    {
        Db::startTrans();
        try {
            Db::name("adver_put")->where("put_id", $put["put_id"])->setField("put_status", 3);
            Db::commit();
            return returnState(200, '投放广告下架成功',3);
        } catch (\PDOException $e) {
            Db::rollback();
            return returnState(100, '投放广告下架失败，网络异常');
        }
    }

    /**
     * 上架投放广告
     * @return \think\response\Json
     */
    public function upAdver($put)
    {
        $nowTime = date("Y-m-d H:i:s",time());
        $type = 3;
        if($nowTime < $put["put_start_datetime"]){
            $type = 1;
        }else if($put["put_start_datetime"] <= $nowTime && $nowTime <=$put["put_end_datetime"]){
            $type = 2;
        }else if($nowTime > $put["put_end_datetime"]){
            $type = 4;
        }
        Db::startTrans();
        try {
            Db::name("adver_put")->where("put_id", $put["put_id"])->setField("put_status", $type);
            Db::commit();
            return returnState(200, '投放广告上架成功',$type);
        } catch (\PDOException $e) {
            Db::rollback();
            return returnState(100, '投放广告上架失败，网络异常');
        }
    }

    /**
     * 投放广告页面
     * @return mixed
     */
    public function putAdver()
    {
        $request = Request::instance();
        if($request->isPost()){
            $input = input("post.");
            Db::startTrans();
            try{

                Db::commit();
                return self::success("广告投放成功","index");
            }catch (\PDOException $e){
                Db::rollback();
                return self::error("网络异常，投放广告失败！");
            }
        }else{
            return self::fetch();
        }
    }

    /**
     * 获取素材列表（默认不分屏）
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAdverList()
    {
        $type = input("post.type",1);
        $check["resource_put_type"] = $type;
        $adverList = Db::name("adver_resource")->where($check)->order("resource_id")->select();
        return returnState(200,'获取成功',$adverList);
    }

    /**
     * 查找机器
     * @return array
     * @throws \think\exception\DbException
     */
    public function getMachineList()
    {
        $type = input("post.type",1);
        if(intval($type) == 1){
            $check["type_machine_type"] = ['in',[1,2]];
        }else if(intval($type) == 2){
            $check["type_machine_type"] = ['in',[3,4]];
        }
        $machineType = Db::name("machine_type")->where($check)->column('type_id');
        if(!$machineType){
            return returnState(100,'获取失败，未找到该类型屏幕的机器',$check);
        }
        $machineList = Db::name('machine')
            ->where("machine_type_id",'in',$machineType)
            ->field("machine_id,machine_name,machine_code")
            ->order("machine_id DESC")
            ->select();
        return returnState(200,'获取成功',$machineList);
    }


    public function putAdverByMachine()
    {
        if($this->request->isPost()){
            $input = input("post.");
            $machineArr = explode(',',$input["machineId"]);
            $data = [];
            foreach ($machineArr as $id){
                foreach ($input["resourceId"] as $rId){
                    $checkPut = Db::name("adver_put")->where([
                        "put_adver_id"=>['eq',$rId],
                        "put_machine_id"=>['eq',$id],
                        'put_status'=>['in',[1,2]]
                    ])->value('put_id');
                    if($checkPut){
                        $backData["resource_name"] = Db::name("adver_resource")->where("resource_id",$rId)->value("resource_name");
                        $backData["machine_name"] = Db::name("machine")->where("machine_id",$id)->value("machine_name");
                        return returnState(100,'投放失败,【'. $backData["resource_name"].'】素材已投放到机器:'. $backData["machine_name"].'',$backData);
                    }
                    $data[] = [
                        "put_type"=>$input['putType'],
                        "put_adver_id"=>$rId,
                        "put_machine_id"=>$id,
                        "put_start_datetime"=>$input['start_date'],
                        "put_end_datetime"=>$input['end_date'],
                        'put_status'=>1
                    ];
                }
            }
            $resUpdate = Db::name("adver_put")->insertAll($data);
            return returnState(200,'广告投放成功',$resUpdate);
        }
        return returnState(100,'请求错误');
    }
}