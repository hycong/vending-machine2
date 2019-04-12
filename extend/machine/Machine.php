<?php
/**
 * 生成机器二维码
 */

namespace machine;


use think\Config;
use think\Env;

class Machine
{

    public static function qrcode($id='')
    {
        include_once (Env::get('extend_path').'/phpqrcode.php');
        $URL_HEADER = 'http://'.Config::get('site_name').'/index/machine/index?id='.$id;
        $save_qr = '/qr/qrcode_'.$id.'.png';
        \QRcode::png($URL_HEADER,'.'.$save_qr,'L',8,2);//识别二维码之后存放到一个路径中
        return $save_qr;
    }

}