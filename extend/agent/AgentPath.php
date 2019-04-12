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

    public static function close_path(){
        static::$cache = [];
        Cache::set('agent_path',null);
    }


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

}