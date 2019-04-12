<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/11 0011
 * Time: 11:51
 */

namespace app\common\model;


use think\Config;
use think\Model;

class KeywordModel extends Model
{

    protected $table = 'keyword';

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->table = Config::get('database.prefix').$this->table;
     }

}