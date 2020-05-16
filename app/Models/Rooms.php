<?php


namespace App\Models;


use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class Rooms extends Model
{

    public $timestamps = false;

    protected $table = 'rooms';

    protected $fillable = [
        'user_id',
        'max' ,
        'created_at',
        'name',
        'pwd',
        'current'
    ];


    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }



    public function createRoomByMysql($request,$data){
        if(Rooms::where('user_id',Auth::id())->count()){
            throw new \Exception('你已创建了一个房间');
        }
        return Rooms::create($data);
    }

    public function createRoomByRedis($request,$data){
        $no = Redis::incr('rooms_no');
        Redis::hMset('room:'.$no.':user:'.Auth::id(),'');
    }

}
