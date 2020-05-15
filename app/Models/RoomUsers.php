<?php


namespace App\Models;


use App\User;
use Illuminate\Database\Eloquent\Model;

class RoomUsers extends Model
{

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $table = 'room_users';

    protected $fillable = [
        'user_id',
        'room_id'
    ];

    public function users(){
        return $this->belongsToMany(User::class,'user_id');
    }
}
