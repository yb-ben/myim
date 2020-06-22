<?php


namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EineFollowersStatistics extends Command
{
    protected $signature = 'eine:followersstat';


    protected $description = 'stat eine fans';


    public function handle(){

        $time = date('Y-m-d H:i:s', time());
        $connection = DB::connection('eine');
        $ret = $connection->table('eine_followers')
            ->select(DB::raw('SUM(`increment`) as total,SUM(IF(`increment`<0,increment,0)) as decrement '))
            ->whereRaw(' created_at > UNIX_TIMESTAMP(CAST(SYSDATE()AS DATE)- INTERVAL 1 DAY) AND  created_at < UNIX_TIMESTAMP(CAST(SYSDATE()AS DATE))')
            ->first()
        ;

        $last = $connection->table('eine_followers')
            ->select(['followers',])
            ->whereRaw('created_at > UNIX_TIMESTAMP(CAST(SYSDATE()AS DATE)- INTERVAL 1 DAY) AND  created_at < UNIX_TIMESTAMP(CAST(SYSDATE()AS DATE))')
            ->orderBy('created_at','desc')
            ->first()
        ;

        $connection->table('eine_followers_days')->insert([
            'datetime' => $time,
            'increment' => $ret->total,
            'decrement'=>$ret->decrement,
            'followers'=>$last->followers
        ]);


    }
}
