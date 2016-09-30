<?php

/**
 * Created by PhpStorm.
 * User: sinre
 * Date: 2016/9/19
 * Time: 17:57
 */
namespace app\admin\controller;

use app\admin\controller\Common;

//use think\View;
use app\admin\model\Admin;

class Index extends Common
{
    /**
     * 后台-登录页面
     */
    public function login()
    {
        if(request()->isPost()){
            $input = input('input/a');

            $admin = new Admin();

            $msg = $admin->login($input);

            if(!$msg){
                $this->error($admin->getError());
            }else{
                $this->success('登录成功', 'admin/index/index');
            }

        }else{
            return view('login');
        }
    }

    /**
     * 后台-首页
     */
    public function index()
    {
        return view('index');
    }

    /**
     * 后台-欢迎页面
     */
    public function welcome()
    {
        return view('welcome');
    }
}