<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/11 0011
 * Time: 11:50
 */
namespace app\system\controller;


use think\Db;
use think\Request;

class Wxkeyword extends Common
{
    /**
     * 关键字列表
     */
    public function index(){
        $list = Db::name('keyword')->where([
            'keyword_triggerType' => 2,
        ])->paginate(20,false,['query'=>request()->param()]);
        return $this->fetch('',['list' => $list]);
    }

    public function addkeyword(){
        if(Request::instance()->isPost()){
            $postData = input('post.');
            $result = $this->validate($postData,'VWxkeyword.add');
            if(true !== $result){
                // 验证失败 输出错误信息
                return returnState(100,$result);
            }
            $postData['keyword_triggerType'] = 2;
            $postData['keyword_createTime'] = date('Y-m-d H:i:s');
            $keyword_id = Db::name('keyword')->insertGetId($postData);
            if(!$keyword_id) return returnState(100,'添加失败');
            return returnState(200,'添加成功');
        }
        return $this->fetch();
    }

    public function editkeyword(){
        $keyword_id = input('keyword_id');
        if(Request::instance()->isPost()){
            $postData = input('post.');
            $result = $this->validate($postData,'VWxkeyword.edit');
            if(true !== $result){
                // 验证失败 输出错误信息
                return returnState(100,$result);
            }
            $handle = Db::name('keyword')->where(['keyword_id'=>$keyword_id])->update($postData);
            if(!$handle) return returnState(100,'修改失败');
            return returnState(200,'修改成功');
        }
        $info = Db::name('keyword')->where(['keyword_id'=>$keyword_id])->find();
        return $this->fetch('',['info' => $info]);
    }

    /**
     * 删除关键字
     */
    public function delkeyword(){
        if(Request::instance()->isPost()){
            $keyword_id = input('keyword_id');
            Db::name('keyword')->where(['keyword_id'=>$keyword_id])->delete();
            return returnState(200,'删除成功');
        }
    }

}