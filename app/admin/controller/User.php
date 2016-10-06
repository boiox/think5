<?php
/**
 * Created by PhpStorm.
 * User: 张栋
 * Date: 2016/10/5
 * Time: 16:46
 */

namespace app\admin\controller;

use app\admin\controller\Common;

class User extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $pageNew = input('pageNew',1);
        $user = model('User');
        $data = $user->user_info($pageNew);

        return view('index',['data'=>$data]);

    }
}