<?php
/**
 * Created by PhpStorm.
 * User: 张栋
 * Date: 2016/10/5
 * Time: 16:48
 */

namespace app\admin\model;

use think\Model;

class User extends Model
{
    public function user_info($pageNew=1)
    {
        $user = new User();
        return $user->page($pageNew,10)->select();
    }
}