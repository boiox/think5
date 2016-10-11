<?php
/**
 * Created by PhpStorm.
 * User: sinre
 * Date: 2016/10/11
 * Time: 14:55
 */

namespace app\index\controller;

use think\Controller;

use extend\wechat\TPWechat;

class Wechat extends Controller
{
    public $user;
    public $wechat;
    public $type, $base_url, $userinfo_url, $oauth_url;
    public $third_url, $third_callback_url;
    public $prefix;

    public function __construct() {

        parent::__construct();
        $this->prefix = '';                      //活动（项目）前缀

        if(is_wechat()){
            $this->type = 'base';
        }else{
            $this->type = 'cookie';
        }
        //$this->type = 'base'; // userinfo-用户信息详情；base-用户openid；third-第三方授权openid；cookie-记录cookie; 'appid'=>'wx142e7e372fe18409','appsecret'=>'d4624c36b6795d1d99dcf0547af5443d'

        $this->wechat = new TPWechat(array('appid'=>'wxdeb774df82357027','appsecret'=>'c128eab69dab2d3fba253b1332648fb1'));
        $this->base_url = url('base', '', true, true);
        $this->userinfo_url = url('userinfo', '', true, true);
        $this->oauth_url = url('oauth_userinfo', '', true, true);
        $this->third_url = 'http://game.sinreweb.com/Oauth/Oauth/authorize_base';
        $this->third_callback_url = url('third_callback', '',false, true);

        $this->user = session($this->prefix.'user') ? session($this->prefix.'user') : cookie($this->prefix.'user');

        self::check_user();
    }

    // 验证用户
    private function check_user() {

        $controller_name = strtolower(request()->controller());
        $action_name = strtolower(request()->action());

        if($controller_name=='wechat' && in_array($action_name, array('base','userinfo','oauth_userinfo','third_callback'))) return true;

        // 用户不存在
        if(empty($this->user['id'])) {

            if($this->type=='cookie') {

                session('action',0);

                $this->_cookie_user();

            } else {
                session($this->prefix.'from',I('get.from'));
                session($this->prefix.'jumpurl',SITE_PROTOCOL.SITE_URL.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);

                $this->redirect_wx($this->type);
            }
        }else{
            if($this->type=='cookie'){
                session('action',1);
            }
        }
    }

    // openid
    public function base() {

        $access_token = $this->wechat->getOauthAccessToken();
        $state = I('get.state');

        if($access_token['openid']) {

            $data['openid'] = $access_token['openid'];
            $data['state'] = $state;
            $this->_oauth_callback($data);
        }

        $this->redirect_wx('base');
    }

    // 关注用户信息
    public function userinfo() {

        $access_token = $this->wechat->getOauthAccessToken();
        $state = input('get.state');
        if($access_token['openid']) {

            // 拉取用户信息
            $userinfo = $this->wechat->getUserInfo($access_token['openid']);
            if($userinfo['subscribe']==1) {

                $userinfo['state'] = $state;
                $this->_oauth_callback($userinfo);
            }

            $this->redirect_wx('oauth');
        }

        $this->redirect_wx('userinfo');
    }

    // 未关注用户信息
    public function oauth_userinfo() {

        $access_token = $this->wechat->getOauthAccessToken();
        $state = input('get.state');

        if($access_token['access_token'] && $access_token['openid']) {

            $userinfo = $this->wechat->getOauthUserinfo($access_token['access_token'], $access_token['openid']);
            if($userinfo['openid']) {

                $userinfo['state'] = $state;
                $userinfo['subscribe'] = 0;
                $this->_oauth_callback($userinfo);
            }
        }

        $this->redirect_wx('oauth');
    }

    // 第三方获取用户信息
    public function third_callback() {

        $sign = input('get.sign');
        if($sign) {
            $Des = new \extend\sinre\Crypt3Des('0E25CD08D6AB320B7FCCC0F42E6CBD12', 'ECB', 'off');
            $sign = $Des->decrypt($sign, 'hex');
            if($sign) {
                $userinfo = json_decode($sign, true);
                $this->_oauth_callback($userinfo);
            }
        }

        redirect($this->third_url.'?url='.$this->third_callback_url);
    }

    // 用户信息处理-oauth
    private function _oauth_callback($user) {

        $ip = get_ip(0, true);
        $db_user = model('user');
        $db_user->startTrans();
        $userinfo = $db_user->lock(true)->where(array('openid'=>$user['openid']))->field('id,openid')->find();
        if($userinfo) {
            session('action',1);        //老用户
            $db_user->where(array('id'=>$userinfo['id']))->save(array('lasttime'=>date('Y-m-d H:i:s'),'lastip'=>$ip));
        } else {
            session('action',0);        //新用户
            $userinfo['openid'] = $user['openid'];
            //$userinfo['nickname'] = $user['nickname'];
            //$userinfo['headimgurl'] = $user['headimgurl'];
            //$userinfo['subscribe'] = $user['subscribe'];
            //$userinfo['base64name'] = base64_encode($user['nickname']);
            $userinfo['addtime'] = date('Y-m-d H:i:s');
            $userinfo['addip'] = $ip;
            $userinfo['from'] = session($this->prefix.'from') ? session($this->prefix.'from') : 0;
            $userinfo = array_filter($userinfo);
            $userinfo['id'] = $db_user->add($userinfo);
        }
        $db_user->commit();

        session($this->prefix.'user', array('id'=>$userinfo['id'],'openid'=>$userinfo['openid']));

        $url = session($this->prefix.'jumpurl');
        if($url) {
            redirect($url);
        } else {
            $this->redirect('Index/index');
        }
    }

    // 用户信息处理-cookie
    private function _cookie_user() {

        $ip = get_ip(0, true);
        $db_user = M('user');
        $userinfo['addtime'] = date('Y-m-d H:i:s');
        $userinfo['addip'] = $ip;
        $userinfo['from'] = I('get.from') ? I('get.from'):'';
        $userid = $db_user->add($userinfo);

        cookie($this->prefix.'user', array('id'=>$userid), 86400*365);
    }

    // 跳转微信
    private function redirect_wx($type='base') {

        if($type=='base') {
            $url = $this->base_url;
            $scope = 'snsapi_base';
        }else if($type=='userinfo'){
            $url = $this->userinfo_url;
            $scope = 'snsapi_base';
        } else if($type=='oauth'){
            $url = $this->oauth_url;
            $scope = 'snsapi_userinfo';
        } else {
            redirect($this->third_url.'?url='.$this->third_callback_url);
        }

        $oauthurl = $this->wechat->getOauthRedirect($url, '', $scope);
        redirect($oauthurl, 0, '授权中...');
    }
}