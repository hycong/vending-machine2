<?php
namespace app\common\model;

use think\Config;
use think\Model;

class GoodsModel extends Model
{
    protected $table = 'keyword';

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->table = Config::get('database.prefix').$this->table;
    }

}