<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/8 0008
 * Time: 9:55
 */

namespace app\common\model;


use think\Model;

class MachineModel extends Model
{
    protected $table = 'machine';

    public function getAgent(){
//        hasOne('关联模型名','外键名','主键名',['模型别名定义'],'join类型');
        return $this->hasOne('agent','a','agent_id');
    }

}