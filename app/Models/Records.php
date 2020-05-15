<?php


namespace App\Models;


use App\User;
use Illuminate\Database\Eloquent\Model;

class Records extends Model
{


    protected $table = 'records';

    protected $fillable = [
        'content',
        'user_id',
        'created_at',
        'room_id',
        'type'
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function room(){
        return $this->belongsTo(Rooms::class,'room_id');
    }
}
