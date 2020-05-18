<?php


namespace App\Http\Controllers\Danmu;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class IndexController extends Controller
{

    /**
     * 首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(){
        $ret = Artisan::call('danmu:list');

        return view('danmu.index',['list'=>$ret]);
    }


}
