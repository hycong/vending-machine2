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

    protected $table = 'config';

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->table = Config::get('database.prefix').$this->table;
    }

    /**
     * 获取配置信息
     */
    public static function getConf($name = ''){
        $value = self::where([
            'conf_name' => $name
        ])->value('conf_content');
        if($value) $value = unserialize($value);
        return $value;
    }

    /**
     * 设置配置信息
     */
    public static function setConf($name = '',$value = []){
        $value = serialize($value);
        if(self::getConf($name)){
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