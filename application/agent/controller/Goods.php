<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/29
 * Time: 9:16
 */

namespace app\agent\controller;


use think\Db;

class Goods extends Common
{
    /**
     * 商品列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $goodsList = Db::name("goods")
            ->where([
                "goods_supplier_id"=>['neq',0]
            ])
            ->paginate($this->pageNum,false,["query"=>$this->request->param()]);
        self::assign("goodsList",$goodsList);
        return self::fetch();
    }


    public function createGoods()
    {
        if($this->request->isPost()){

        }
        return self::fetch();
    }


    public function editGoods()
    {
        if($this->request->isPost()){

        }
        return self::fetch();
    }
}