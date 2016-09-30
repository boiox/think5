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

function MosaicAjax1($model,$data,$time=array())
{
    $ajax = array();
    $ajax['module'] = $model;
    if($time){
        $ajax['xAxis'] = $time;
    }
    $ajax['items'] = $data;

    return $ajax;
}
