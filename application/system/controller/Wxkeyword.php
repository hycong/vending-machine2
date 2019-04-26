<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/11 0011
 * Time: 11:50
 */
namespace app\system\controller;


use app\common\model\KeywordModel;
use cache\GetCache;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\Image;
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
        ])->order('keyword_id desc')->paginate($this->pageNum,false,['query'=>request()->param()]);
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
            if($postData['keyword_pic']){
                $app = Factory::officialAccount(GetCache::getCache('wechat'));
                $result = $app->material->uploadImage('.'.$postData['keyword_pic']);
                if($result){
                    $postData['keyword_media_id'] = $result['media_id'];
                    $postData['keyword_media_url'] = $result['url'];
                }
            }

            $KeywordModel = new KeywordModel();
            $KeywordModel->allowField(true)->data($postData)->save();
            $keyword_id = $KeywordModel->keyword_id;
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
    public function delKeyword(){
        if(Request::instance()->isPost()){
            $keyword_id = input('keyword_id');
            Db::name('keyword')->where(['keyword_id'=>$keyword_id])->delete();
            return returnState(200,'删除成功');
        }
    }



    /**
     * 图片上传
     */
    public function goodsUpload()
    {
        $file = request()->file("file");
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . '/uploads/wxkeyword/');
            if ($info) {
                // 成功上传后 获取上传信息
                $data = [
                    'filePath' => '/uploads/wxkeyword/' . $info->getSaveName(),
                    'fileSize' => $info->getInfo()["size"]
                ];
                return returnState(200, '上传成功', $data);
            } else {
                // 上传失败获取错误信息
                return returnState(100, '上传失败', $file->getError());
            }
        }
        return returnState(100, '请上传文件');
    }


    public function listMaterial(){
        $wechat_option = GetCache::getCache('wechat');
        $app = Factory::officialAccount($wechat_option);
        $res = $app->material->list('image', 0, 10);
        p($res);
    }


    public function delMaterial(){
        $mediaId = input('media_id');
        $wechat_option = GetCache::getCache('wechat');
        $app = Factory::officialAccount($wechat_option);
        $app->material->delete($mediaId);
    }

}