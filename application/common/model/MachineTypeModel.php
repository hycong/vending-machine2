<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/18
 * Time: 14:40
 */

namespace app\common\model;


use think\Config;
use think\Model;

class MachineTypeModel extends Model
{
    protected $table = 'nvwa_machine_type';

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->table = Config::get('database.prefix').$this->table;
    }
}