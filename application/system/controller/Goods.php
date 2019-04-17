<?php
namespace app\system\controller;


use app\common\model\GoodsModel;
use think\Db;
use think\Request;

class Goods extends Common
{

    public function index(){
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

}