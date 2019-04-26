<?php
namespace app\system\controller;

use app\common\model\AgentGoodsModel;
use think\Db;
use think\Request;

class Agentgoods extends Common
{

    public function index(){
        $goods_id = input('goods_id');
        $goods_name = input('goods_name');
        if($goods_id) $map['g.goods_id'] = $goods_id;
        if($goods_name) $map['g.goods_name'] = ['like',"%$goods_name%"];
        $map['g.goods_status'] = ['<>', 3];
        $goods_list = Db::name('agent_goods')->alias('g')
            ->join('supplier s', 's.supplier_id = g.goods_supplier_id', 'left')
            ->where($map)
            ->field('g.*,s.supplier_name')
            ->order('g.goods_id DESC')
            ->paginate($this->pageNum, false, ['query' => request()->param()]);
        return $this->fetch('',[
            'goods_list' => $goods_list
        ]);
    }


    /**
     * 修改商品
     */
    public function edit_goods(){
        $goods_id = input('goods_id');
        $info = Db::name('agent_goods')->alias('g')
            ->join('supplier s','g.goods_supplier_id = s.supplier_id','left')
            ->field('g.*,s.supplier_name')
            ->where(['g.goods_id'=>$goods_id])->find();
        if(Request::instance()->isPost()){
            $post_data = input('post.');
            $AgentGoodsModel = new AgentGoodsModel();
            $result = $this->validate($post_data,'VAgentGoods.edit');
            if(true !== $result){
                // 验证失败 输出错误信息
                return returnState(100,$result);
            }
            $handle = $AgentGoodsModel->allowField(true)->save($post_data,['goods_id'=>$goods_id]);
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
     * 下架
     */
    public function downGoods(){
        $goods_id = input('goods_id');
        $AgentGoodsModel = new AgentGoodsModel();
        if(Request::instance()->isPost()){
            $handle = $AgentGoodsModel->allowField(['goods_status'])->save(['goods_status' => 2],['goods_id'=>$goods_id,'goods_status' => 1]);
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
        $AgentGoodsModel = new AgentGoodsModel();
        if(Request::instance()->isPost()){
            $handle = $AgentGoodsModel->allowField(['goods_status'])->save(['goods_status' => 1],['goods_id'=>$goods_id,'goods_status' => 2]);
            if(!$handle) return returnState(100,'上架失败');
            return returnState(200,'上架成功',url('index'));
        }
        return returnState(100, '非法请求！');
    }

    /**
     * 删除
     */
    public function delGoods(){
        $goods_id = input('goods_id');
        $AgentGoodsModel = new AgentGoodsModel();
        if(Request::instance()->isPost()){
            $handle = $AgentGoodsModel->where(['goods_id'=>$goods_id,'goods_status' => 2])->delete();
            if(!$handle) return returnState(100,'删除失败');
            return returnState(200,'删除成功',url('index'));
        }
        return returnState(100, '非法请求！');
    }




}