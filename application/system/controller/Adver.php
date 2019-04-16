<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/9
 * Time: 16:09
 */

namespace app\system\controller;


use think\Db;
use think\File;
use think\Request;

class Adver extends Common
{
    protected $pageNum = 5;

    public function index()
    {
        $adver_list = Db::name("adver_resource")->order("resource_id DESC")
            ->paginate($this->pageNum, false, ["query" => \request()->param()]);
        self::assign("adver_list", $adver_list);
        return self::fetch();
    }

    public function addAdver()
    {
        $request = Request::instance();
        if ($request->isPost()) {
            $input = input("post.");
            $data["resource_name"] = $input["fileName"];
            if ($input["advType"] != 1) {
                $data["resource_adver_type"] = 3;
                $data["resource_img_path"] = $input["imgPath"];
                $data["resource_video_path"] = $input["videoPath"];
                $data["resource_video_path"] = $input["videoSize"];
            } else {
                if ($input["fileType"] == 1) {
                    $data["resource_img_path"] = $input["imgPath"];
                } else {
                    $data["resource_video_path"] = $input["videoPath"];
                    $data["resource_video_size"] = $input["videoSize"];
                }
                $data["resource_adver_type"] = $input["fileType"];
            }
            Db::startTrans();
            try {
                Db::name("adver_resource")->insert($data);
                Db::commit();
                return $this->success('素材上传成功', 'index');
            } catch (\PDOException $e) {
                Db::rollback();
                return $this->success('素材上传失败，网络异常 ');
            }

        } else
            return self::fetch();
    }

    public function editAdver()
    {
        return self::fetch();
    }

    /**
     * 上/下架
     * @return \think\response\Json
     */
    public function resource()
    {
        $id = input("post.id");
        if (intval($id) <= 0) {
            return json(returnState(100, '请提交素材ID'));
        }
        $type = input("post.type");
        switch ($type){
            case "1":
                return $this->downAdver($id);
                break;
            case "2":
                return $this->upAdver($id);
                break;
        }
    }


    /**
     * 下架素材（暂未判断已投放广告）
     * @return \think\response\Json
     */
    public function downAdver($id)
    {
        Db::startTrans();
        try {
            Db::name("adver_resource")->where("resource_id", $id)->setField("resource_status", 2);
            Db::commit();
            return json(returnState(200, '素材下架成功'));
        } catch (\PDOException $e) {
            Db::rollback();
            return json(returnState(100, '素材下架失败，网络异常'));
        }
    }

    /**
     * 上架素材（暂未判断已投放广告）
     * @return \think\response\Json
     */
    public function upAdver($id)
    {
        Db::startTrans();
        try {
            Db::name("adver_resource")->where("resource_id", $id)->setField("resource_status", 1);
            Db::commit();
            return json(returnState(200, '素材上架成功'));
        } catch (\PDOException $e) {
            Db::rollback();
            return json(returnState(100, '素材上架失败，网络异常'));
        }
    }

    /**
     * 删除素材（暂未判断已投放广告）
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delAdver()
    {
        $id = input("post.id");
        if (intval($id) <= 0) {
            return json(returnState(100, '请提交素材ID'));
        }
        $adver = Db::name("adver_resource")->where("resource_id",$id)->find();
        if(!$adver){
            return json(returnState(100, '未找到该素材'));
        }
        Db::startTrans();
        try {
            $res = Db::name("adver_resource")->where("resource_id", $id)->delete();
            if ($res > 0) {
                if ($adver["resource_put_type"] == 1) {
                    if ($adver["resource_adver_type"] == 1) {
                        $picurl = '../public/' . $adver['resource_img_path'];
                        if(file_exists($picurl)){
                            unlink($picurl);
                        }
                    } else {
                        $videourl = '../public/' . $adver['resource_video_path'];
                        if(file_exists($videourl)){
                            unlink($videourl);
                        }

                    }

                } else {
                    $picurl = '../public/' . $adver['resource_img_path'];
                    $videourl = '../public/' . $adver['resource_video_path'];
                    if(file_exists($videourl)){
                        unlink($videourl);
                    }
                    if(file_exists($picurl)){
                        unlink($picurl);
                    }
                }
            }
            Db::commit();
            return json(returnState(100, '素材删除成功'));
        } catch (\PDOException $e) {
            Db::rollback();
            return json(returnState(100, '素材删除失败，网络异常'));
        }
    }

    public function webUpload()
    {
        $file = request()->file("file");
//        print_r($file);
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . '/uploads/adver/');
            if ($info) {
                // 成功上传后 获取上传信息
                $data = [
                    'filePath' => '/uploads/adver/' . $info->getSaveName(),
                    'fileSize' => $info->getInfo()["size"]
                ];
                return json(returnState(200, '上传成功', $data));
            } else {
                // 上传失败获取错误信息
                return json(returnState(100, '上传失败', $file->getError()));
            }
        }
        return json(returnState(100, '请上传文件'));
    }
}