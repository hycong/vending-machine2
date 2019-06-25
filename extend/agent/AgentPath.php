<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/12 0012
 * Time: 11:25
 */

namespace agent;


use think\Cache;
use think\Db;

class AgentPath
{
    protected static $cache;

    /**
     * 查询加盟商下级ID
     * @param $id
     * @param bool $is_self
     * @return array|mixed
     */
    public static function get_path($id,$is_self = true){
        static::$cache = Cache::get('agent_path');
        $returndata = [];
        if(isset(static::$cache[$id])){
            $tmp = static::$cache[$id];
            $returndata = static::$cache[$id] != '' ? json_decode($tmp,true) : [];
        }else{
            $info = [];
            $agent_path = Db::name('agent')->where(['agent_id'=>$id])->value('agent_path');
            $info = $agent_path ? explode(',',$agent_path) : [];
            $returndata = $info;
            static::$cache[$id] = $returndata ? json_encode($returndata) : '';
            Cache::set('agent_path',static::$cache);
        }
        if($is_self){
            $returndata[] = $id;
        }
        return $returndata;
    }

    /**
     * 清楚加盟商缓存
     */
    public static function close_path(){
        static::$cache = [];
        Cache::set('agent_path',null);
    }

    /**
     * 设置下级ID信息
     * @param $new_id       新加盟商ID
     * @param $id           创建者ID
     * @param $level        创建者等级
     * @return bool
     */
    public static function set_level_path($new_id,$id,$level){
        for ($i=$level;$i>=1;$i--){
            $info = Db::name('agent')->where(['agent_id'=>$id])->find();
            Db::name('agent')->where(['agent_id'=>$id])->update([
                'agent_path' => $info['agent_path'].','.$new_id,
            ]);
            $id = $info['agent_parent_id'];
            if($info['agent_level'] == 1) break;
        }
        self::close_path();
        return true;
    }

    /**
     * 清楚删除的下级ID
     * @param $del_id       删除加盟商ID
     * @param $pid          删除加盟商上级ID
     * @param $level        删除加盟商等级
     * @return bool
     */
    public static function del_level_path($del_id,$pid,$level){
        for ($i=$level;$i>=1;$i--){
            $info = Db::name('agent')->where(['agent_id'=>$pid])->find();
            $info['agent_path'] = explode(',',$info['agent_path']);
            $key = array_search($del_id, $info['agent_path']);
            if ($key !== false) unset($info['agent_path'][$key]);
            Db::name('agent')->where(['agent_id'=>$pid])->update([
                'agent_path' => implode(',',$info['agent_path']),
            ]);
            $pid = $info['agent_parent_id'];
            if($info['agent_level'] == 1) break;
        }
        self::close_path();
        return true;
    }


    /**
     * 获取最上级ID
     * @param $id
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getLastParentAgent($id)
    {
        $agent = Db::name("agent")->where("agent_id",$id)->field("agent_level,agent_parent_id")->find();
        $agent_id = 0;
        switch ($agent["agent_level"]){
            case "2":
                $agent_id = Db::name("agent")->where("agent_id",$agent["agent_parent_id"])->value("agent_id");
                break;
            case "3":
                $agent_parent_id = Db::name("agent")->where("agent_id",$agent["agent_parent_id"])->value("agent_parent_id");
                $agent_id = Db::name("agent")->where("agent_id",$agent_parent_id)->value("agent_id");
                break;
        }
        return $agent_id;
    }


}