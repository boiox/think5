<?php
/**
 * Created by PhpStorm.
 * User: sinre
 * Date: 2016/9/22
 * Time: 10:52
 */

namespace app\admin\controller;

use think\Controller;

class Common extends Controller
{
    protected $admin;
    public function __construct()
    {
        parent::__construct();
        $this->admin = session('admin');
        self::check_admin();
        self::admin_public();
        //self::template_common();
        //self::_page();
    }

    /**
     * 验证用户登录情况
     * @return bool
     */
    final function check_admin()
    {
        $module_name = strtolower(request()->module());             //模块名称
        $controller_name = strtolower(request()->controller());     //控制器名称
        $action_name = strtolower(request()->action());             //方法名称

        if($module_name=='admin' && $controller_name=='index' && in_array($action_name, array('login'))) {
            return true;
        } else {
            if(!isset($this->admin['id']) || !$this->admin['id']) {
                $this->redirect(url('/admin/index/login'));
            }
        }
    }

    /**
     * 发送头部和底部文件到前端
     */
    final function admin_public()
    {
        //$this->assign('admin_header', APP_PATH . 'admin/view/public/public_header.html');
        //$this->assign('admin_footer', APP_PATH . 'admin/view/public/public_footer.html');
        $this->assign('admin_base','public/base');

    }
}