<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/10 0010
 * Time: 17:20
 */

namespace app\common\model;


use think\Config;
use think\Model;

class ConfModel extends Model
{

    protected $table = 'nvwa_config';

    /**
     * 获取配置信息
     */
    public static function getConf($name = ''){
        $value = self::where([
            'conf_name' => $name
        ])->order('conf_id desc')->value('conf_content');
        if($value) $value = unserialize($value);
        return $value;
    }

    /**
     * 设置配置信息
     */
    public static function setConf($name = '',$value = ''){
        if($value) $value = serialize($value);
        if(self::where(['conf_name'=>$name])->find()){
            self::where([
                'conf_name' => $name
            ])->update(['conf_content'=>$value]);
        }else{
            self::insert([
                'conf_name' => $name,
                'conf_content' => $value,
            ]);
        }
        return true;
    }


}