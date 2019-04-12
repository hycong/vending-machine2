<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/1 0001
 * Time: 14:10
 */

namespace cache;


use think\Db;
use think\facade\Cache;

class GetCache
{

    public static function getCache($key = 'wechat'){
        $cache = Cache::get('cache_'.$key);
        if($cache){
            return $cache;
        }else{
            $option = Db::name('config')->where('conf_name',$key)->value('conf_content');
            $option = unserialize($option);
            Cache::set('cache_'.$key,$option);
            return $option;
        }
    }

    public static function closeCache($key = 'wechat'){
        Cache::set('cache_'.$key,null);
    }

}