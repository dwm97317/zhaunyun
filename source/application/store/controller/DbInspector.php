<?php
namespace app\store\controller;

use think\Controller;
use app\store\model\Ditch;

class DbInspector extends Controller
{
    public function index()
    {
        $list = Ditch::all();
        echo "<h1>Ditch Table Records</h1>";
        echo "<pre>";
        print_r($list);
        echo "</pre>";
    }
}
