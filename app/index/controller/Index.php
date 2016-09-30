<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $data = db('admin')->find();
        dump($data);
    }
}
