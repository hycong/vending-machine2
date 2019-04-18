<?php
namespace app\system\controller;


use app\common\model\GoodsModel;
use think\Db;
use think\Request;

class Goods extends Common
{

    public function index(){
        return returnState(200, '上传成功', [],'');
        $map['g.goods_status'] = ['<>', 3];
        $goods_list = Db::name('goods')->alias('g')
            ->join('supplier s', 's.supplier_id = g.goods_supplier_id', 'left')
            ->where($map)
            ->field('g.*,s.supplier_name')
            ->order('g.goods_id DESC')
            ->paginate($this->pageNum, false, ['query' => request()->param()]);
        return $this->fetch('',[
            'goods_list' => $goods_list
        ]);
    }

    public function create_goods(){
        if(Request::instance()->isPost()){
            $post_data = input('post.');

            $file = request()->file('goods_pic');
            $info = $file->validate(['ext'=>'png'])->move('./uploads/goods','default_goods_pic.png');
            if(!$info) {
                return $this->error($file->getError());
                $post_data['goods_pic'] = $info->getSaveName();
            }

            $post_data['goods_status'] = 1;
            $post_data['goods_createTime'] = date('Y-m-d H:i:s');
            $GoodsModel = new GoodsModel();
            $GoodsModel->allowField(true)->data($post_data)->save();
            $id = $GoodsModel->goods_id;
            if(!$id) returnState(100,'添加失败');
            return returnState(200,'添加成功',url('index'));
        }
        return $this->fetch();
    }

    public function goodsUpload()
    {
        $file = request()->file("file");
//        print_r($file);
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . '/uploads/goods/');
            if ($info) {
                // 成功上传后 获取上传信息
                $data = [
                    'filePath' => '/uploads/goods/' . $info->getSaveName(),
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