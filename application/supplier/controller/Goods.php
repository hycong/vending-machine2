<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/29
 * Time: 9:16
 */

namespace app\supplier\controller;


use app\common\model\GoodsModel;
use think\Db;
use think\Request;

class Goods extends Common
{
    /**
     * 商品列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $check = [];
        $goodsName = input("goods_name");
        $status = input("status",0);
        if(trim($goodsName) != ''){
            $check["goods_name"] = ["like","%$goodsName%"];
        }
        if(intval($status) != 0){
            $check["goods_status"] = ["eq",$status];
        }
        $check["goods_supplier_id"] = ["eq",$this->supplierId];
        $goodsList = Db::name("goods")
            ->where($check)
            ->order("goods_id DESC")
            ->paginate($this->pageNum,false,["query"=>$this->request->param()]);
        self::assign("goodsList",$goodsList);
        self::assign('goods_name',$goodsName);
        self::assign("status",$status);
        return self::fetch();
    }


    /**
     * 下架
     */
    public function downGoods(){
        $goods_id = input('goods_id');
        $GoodsModel = new GoodsModel();
        if(Request::instance()->isPost()){
            $handle = $GoodsModel->allowField(['goods_status'])->save(['goods_status' => 2],['goods_id'=>$goods_id,'goods_status' => 1]);
            if(!$handle) return returnState(100,'下架失败');
            return returnState(200,'下架成功',url('index'));
        }
        return returnState(100, '非法请求！');
    }

    /**
     * 上架
     */
    public function upGoods(){
        $goods_id = input('goods_id');
        $GoodsModel = new GoodsModel();
        if(Request::instance()->isPost()){
            $handle = $GoodsModel->allowField(['goods_status'])->save(['goods_status' => 1],['goods_id'=>$goods_id,'goods_status' => 2]);
            if(!$handle) return returnState(100,'上架失败');
            return returnState(200,'上架成功',url('index'));
        }
        return returnState(100, '非法请求！');
    }

    /**
     * 创建商品
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create_goods(){
        if(Request::instance()->isPost()){
            $post_data = input('post.');
            $map["goods_name"] = $post_data["goods_name"];
            $map["goods_type"] = $post_data["goods_type"];
            $post_data["goods_supplier_id"] = $this->supplierId;
            $map["goods_supplier_id"] = $this->supplierId;;
            if(Db::name("goods")->where($map)->value("goods_id")){
                return returnState(100,"该类型商品已存在");
            }
            $result = $this->validate($post_data,'VGoods.create');
            if(true !== $result){
                // 验证失败 输出错误信息
                return returnState(100,$result);
            }
            $post_data['goods_status'] = 1;
            $post_data['goods_createTime'] = date('Y-m-d H:i:s');
            $GoodsModel = new GoodsModel();
            $GoodsModel->allowField(true)->data($post_data)->save();
            $id = $GoodsModel->goods_id;
            if(!$id) return returnState(100,'添加失败');
            return returnState(200,'添加成功',url('index'));
        }
        return $this->fetch();
    }

    /**
     * 修改商品
     */
    public function edit_goods(){
        $goods_id = input('goods_id');
        $info = Db::name('goods')->where(['goods_id'=>$goods_id])->find();
        if(Request::instance()->isPost()){
            $post_data = input('post.');
            $GoodsModel = new GoodsModel();
            $result = $this->validate($post_data,'VGoods.edit');
            if(true !== $result){
                // 验证失败 输出错误信息
                return returnState(100,$result);
            }
            $handle = $GoodsModel->allowField(true)->save($post_data,['goods_id'=>$goods_id]);
            if(!$handle) return returnState(100,'修改失败');
            return returnState(200,'修改成功',url('index'));
        }
        $supplier_list = Db::name('supplier')->where(['supplier_status'=>1])->select();
        return $this->fetch('',[
            'info' => $info,
            'supplier_list' => $supplier_list,
        ]);
    }

    /**
     * 图片上传
     */
    public function goodsUpload()
    {
        $file = request()->file("file");
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . '/uploads/goods/');
            if ($info) {
                // 成功上传后 获取上传信息
                $data = [
                    'filePath' => '/uploads/goods/' . $info->getSaveName(),
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

    /**
     * 删除
     */
    public function delGoods(){
        $goods_id = input('goods_id');
        $GoodsModel = new GoodsModel();
        if(Request::instance()->isPost()){
            $handle = $GoodsModel->where(['goods_id'=>$goods_id,'goods_status' => 2])->delete();
            if(!$handle) return returnState(100,'删除失败');
            return returnState(200,'删除成功',url('index'));
        }
        return returnState(100, '非法请求！');
    }
}