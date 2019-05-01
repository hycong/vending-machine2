<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/29
 * Time: 9:16
 */

namespace app\agent\controller;


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
        $goodsType = input("goodsType",'1');
        $check = [];
        $goodsName = input("goods_name");
        $goodsId = input("goods_id");
        if(trim($goodsName) != ''){
            $check["goods_name"] = ["like","%$goodsName%"];
        }
        if(intval($goodsId) != 0){
            $check["goods_id"] = ["eq",$goodsId];
        }
        switch ($goodsType){
            case "1":
                $check["goods_supplier_id"] = ["eq",0];
                break;
            case "2":
                $check["goods_supplier_id"] = ["neq",0];
                break;
        }
        $goodsList = Db::name("goods")
            ->where($check)
            ->order("goods_id DESC")
            ->paginate($this->pageNum,false,["query"=>$this->request->param()]);
        self::assign("goodsList",$goodsList);
        self::assign("goodsType",$goodsType);
        self::assign('goods_name',$goodsName);
        self::assign("goods_id",$goodsId);
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
}