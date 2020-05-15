<?php


namespace App\Http\Controllers\IM;


use App\Http\Controllers\Controller;
use App\Models\Rooms;
use App\Models\RoomUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{

    public function create(){
        return view('IM.create');
    }

    /**
     * 创建房间
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function store(Request $request){
        $data = [
            'user_id' => Auth::id(),
            'created_at' => time(),
            'max' => $request->input('max',20),
            'name' => $request->input('name',null),
            'pwd' => $request->input('pwd',null),
        ];

        $res = Validator::make($data,[
            'max' => 'required|numeric|min:2|max:20',
            'name'=> 'required|string|min:1|max:20',
            'pwd' => 'max:16'
        ]);
        if($res->fails()){
            return redirect('IM/create')->withErrors($res)->withInput();
        }

        if(Rooms::where('user_id',Auth::id())->count()){
            if(!$request->isJson()){
                $res->errors()->add('id','你已创建了一个房间');
                return redirect('IM/create')->withErrors($res)->withInput();
            }
            return response()->json(['msg'=>'你已创建了一个房间','status'=>400]);
        }

        Rooms::create($data);

        if(!$request->isJson()){
            return view('home');
        }
        return \response()->json(['msg'=>'success','status'=> 0 ]);

    }


    /**
     * 删除房间
     * @param Request $request
     * @param $roomId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function destroy(Request $request,$roomId){
        try{

            $room = Rooms::where('room_id',$roomId)->findOrFail($roomId);
            $room->destroy();
            if(!$request->isJson()){
                return view('home');
            }

            return response()->json(['msg'=> 'success','status' => 0]);
        }catch (\Throwable $throwable){

            return response()->json(['msg'=>$throwable->getMessage(),'status'=> 400 ]);
        }
    }

    /**
     * 房间列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request){
        $rooms = Rooms::with(['user'])
            ->select(['id','user_id','created_at','max','name','desc','current'])
            ->simplePaginate($request->input('limit',10));
        if(!$request->isJson()){
            return view('home',['rooms'=>$rooms]);
        }
        return \response()->json(['data'=>$rooms,'msg'=>'success','status'=>0]);
    }

    /**
     * 进入房间页
     * @param Request $request
     * @param $roomId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request,$roomId){
        $room =Rooms::findOrFail($roomId);

        $roomUsers = RoomUsers::with(['users'])->select()->get();

        $s = implode('.',[time(),$roomId ,Auth::id()]);
        $token = encrypt($s .'.'. md5($s));

        return view('IM.show',['room'=>$room ,'roomUsers' => $roomUsers ,'token' => $token]);
    }

    /**
     * 编辑页数据
     * @param Request $request
     * @param $roomId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function edit(Request $request,$roomId){
        try{

            $room = Rooms::where('user_id',Auth::id())->select(['id','user_id','name','pwd','max','created_at'])->findOrFail($roomId);
            return response()->json(['msg'=>'success','status'=> 0, 'data'=>$room]);
        }catch (\Throwable $throwable){

            return response()->json(['msg'=>$throwable->getMessage(),'status'=> 400 ]);
        }
    }


    /**
     * 更新房间信息
     * @param Request $request
     * @param $roomId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function update(Request $request,$roomId){
        $data = [
            'max' => $request->input('max',20),
            'name' => $request->input('name',null),
            'pwd' => $request->input('pwd',null),
        ];
        try{
            $room = Rooms::where('user_id',Auth::id())->findOrFail($roomId);
            $room->fill($data)->save();
            if(!$request->isJson()){
                return view('home');
            }
            return \response()->json(['msg'=>'success','status'=> 0 ]);

        }catch (\Throwable $throwable){

            return response()->json(['msg'=>$throwable->getMessage(),'status'=> 400 ]);
        }
    }
}
