<?php

/**
 * Created by PhpStorm.
 * User: sinre
 * Date: 2016/9/22
 * Time: 17:59
 */

namespace app\admin\model;

use think\Model;

class Admin extends Model
{
    public function login($input)
    {
        $where['username'] = $input['username'];

        $user = Admin::where($where)->find();

        $password = MD5($input['password']);

        if($user['disable'] == 1){
            $this->error = '账号被禁用，请联系管理员';
            return false;
        }

        if($password == $user['password']){
            session('admin',$user);
            return true;
        }else{
            $this->error = '账号或密码错误';
            return false;
        }
    }
}