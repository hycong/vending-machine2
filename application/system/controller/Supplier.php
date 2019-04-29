<?php


namespace app\system\controller;


use app\common\model\SupplierModel;
use think\Db;
use think\Request;

class Supplier extends Common
{

    public function index(){
        $where = [];
        $supplier_name = input('supplier_name');
        $supplier_mobile = input('supplier_mobile');
        if($supplier_name) $where['supplier_name'] = ['like',"%$supplier_name%"];
        if($supplier_mobile) $where['supplier_mobile'] = $supplier_mobile;
        $supplier_list = Db::name('supplier')
            ->where($where)
            ->order('supplier_id desc')
            ->paginate(20,false,['query'=>request()->param()]);
        return $this->fetch('',[
            'supplier_list' => $supplier_list,
        ]);
    }


    public function create_supplier(){
        if(Request::instance()->isPost()){
            $post_data = input('post.');
            $map["supplier_mobile"] = $post_data["supplier_mobile"];
            if(Db::name("supplier")->where($map)->value("supplier_id")){
                return $this->error("该供货商已存在");
            }
            $result = $this->validate($post_data,'VSupplier.create');
            if(true !== $result){
                // 验证失败 输出错误信息
                return $this->error($result);
            }

            $post_data['supplier_password'] = md5($post_data['supplier_password']);
            $post_data['supplier_status'] = 1;
            $post_data['supplier_createTime'] = date('Y-m-d H:i:s');
            $SupplierModel = new SupplierModel();
            $SupplierModel->allowField(true)->data($post_data)->save();
            $id = $SupplierModel->supplier_id;
            if(!$id) returnState(100,'添加失败');
            return returnState(200,'添加成功',url('index'));
        }
        return $this->fetch();
    }


    public function edit_supplier(){
        $supplier_id = input('supplier_id');
        $SupplierModel = new SupplierModel();
        $info = $SupplierModel->where(['supplier_id'=>$supplier_id])->find();
        if(Request::instance()->isPost()){
            $post_data = input('post.');
            $map["supplier_mobile"] = $post_data["supplier_mobile"];
            $map["supplier_id"] = ['<>',$supplier_id];
            if(Db::name("supplier")->where($map)->value("supplier_id")){
                return returnState(100,"该供货商已存在");
            }
            $result = $this->validate($post_data,'VSupplier.edit');
            if(true !== $result){
                // 验证失败 输出错误信息
                return returnState(100,$result);
            }

            if($post_data['supplier_password']) $post_data['supplier_password'] = md5($post_data['supplier_password']);
            $handle = $SupplierModel->allowField(['supplier_name','supplier_mobile','supplier_password'])->save($post_data,['supplier_id' => $supplier_id]);
            if(!$handle) return returnState(100,'修改失败');
            return returnState(200,'修改成功',url('index'));
        }
        return $this->fetch('',[
            'info' => $info,
        ]);
    }

    /**
     * 禁用供货商
     */
    public function disabled_supplier(){
        if(Request::instance()->isPost()) {
            $supplier_id = input('supplier_id');
            $SupplierModel = new SupplierModel();
            $handle = $SupplierModel->where(['supplier_id' => $supplier_id,'supplier_status' => 1])->setField('supplier_status',2);
            if(!$handle) returnState(100,'设置失败');
            return returnState(200,'设置成功',url('index'));
        }
        return returnState(100,'非法请求');
    }

    /**
     * 恢复供货商
     */
    public function change_status(){
        if(Request::instance()->isPost()) {
            $supplier_id = input('supplier_id');
            $SupplierModel = new SupplierModel();
            $handle = $SupplierModel->where(['supplier_id' => $supplier_id,'supplier_status' => 2])->setField('supplier_status',1);
            if(!$handle) returnState(100,'设置失败');
            return returnState(200,'设置成功',url('index'));
        }
        return returnState(100,'非法请求');
    }

    /**
     * 恢复供货商
     */
    public function del_supplier(){
        if(Request::instance()->isPost()) {
            $supplier_id = input('supplier_id');
            $SupplierModel = new SupplierModel();
            $handle = $SupplierModel->where(['supplier_id' => $supplier_id,'supplier_status' => 2])->delete();
            if(!$handle) returnState(100,'删除失败');
            return returnState(200,'删除成功',url('index'));
        }
        return returnState(100,'非法请求');
    }

}