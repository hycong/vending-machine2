<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function p($row = [],$isExit = true){
    echo '<pre>'.print_r($row,true).'</pre>';
    if ($isExit) exit();
}

/**
 * @param int $code
 * @param string $msg
 * @param array $data
 * @param string $url
 * @return array
 */
function returnState($code = 100,$msg = '',$data = [],$url = ''){
    return [
        'state' => $code,
        'msg' => $msg,
        'data' => $data,
        'url' => $url,
    ];
}