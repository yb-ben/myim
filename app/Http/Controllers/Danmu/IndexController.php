<?php


namespace App\Http\Controllers\Danmu;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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


    public function store(Request $request){

        $data = $request->validate([
           'roomId' => 'required|integer|min:1',
           'alias' => 'required|max:10',
        ]);

        Artisan::call('swoole:danmu '.$data['roomId'].' '.$data['alias'].' --daemon');
        return redirect('/danmu/index');
    }


    public function destroy(Request $request,$roomId){

        $roomId = intval($roomId);
        $ret = Artisan::call('danmu:list');
        if(is_array($ret)){
            $target = 0;
            foreach ($ret as $item){
                if(intval($item[2]) == $roomId){
                    $target = $item[0];
                }
            }
            if($target){
                shell_exec('kill -15 '.$target);
            }
        }
        return redirect('/danmu/index');
    }


}
