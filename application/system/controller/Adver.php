<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/9
 * Time: 16:09
 */

namespace app\system\controller;



use think\File;

class Adver extends Common
{
    public function index()
    {
        return self::fetch();
    }

    public function addAdver()
    {
        $advType = input("advType");
        if(intval($advType) == 0)
            return $this->error('请选择投放类型');
        else if($advType == 1)
            return self::fetch("add_adver");
        else
            return self::fetch("add_split_adver");

    }

    public function editAdver()
    {
        return self::fetch();
    }

    public function webUpload()
    {
        $file = request()->file("file");
//        print_r($file);
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . '/uploads/adver/');
            if($info){
                // 成功上传后 获取上传信息
                return json(returnState(200, '上传成功', '/uploads/adver/'.$info->getSaveName()));
            }else{
                // 上传失败获取错误信息
                return json(returnState(100, '上传失败', $file->getError()));
            }
        }
        return json(returnState(100, '请上传问文件'));
    }
}