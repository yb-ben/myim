<?php


namespace App\Models;


use App\User;
use Illuminate\Database\Eloquent\Model;

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


}
