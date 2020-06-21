<?php


namespace App\Console\Model\Eine;


use Illuminate\Database\Eloquent\Model;

class Followers extends Model
{

    protected $fillable = [
        'followers', 'created_at', 'increment',
    ];

    protected $table = 'eine_followers';

}
