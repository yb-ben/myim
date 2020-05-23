<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Swoole\Process;

class DanmuWatch extends Command
{

    protected $signature = 'danmu:watch';


    protected $description = 'list all danmu';

    protected $rooms = [
        'aza'=>'21696950',
        'roi'=>'21696953',
        'andou'=>'21224291',
        'saya'=>'21763344',
        'yukie'=>'21756924',
        'ichigo'=>'21452118',
        'ruki' => '21403609'
        //'hanon'=>'21669084',
    ];

    public function handle(){
        //Process::daemon();
        go(function(){

            while (true) {

                $ret = shell_exec('ps -ef | grep swoole:danmu | awk \'{if($3==1)print $2,$11;}\'');
                $ret = explode("\n",$ret);
                $ret = array_filter($ret);
                $data = [];
                foreach ($ret as $line){

                    list($pid, $alias) = explode(' ',$line);
                    $data[$alias] = $pid;
                }
                foreach ($this->rooms as $k => $v){
                    if (!isset($data[$k])) {
                        exec('php artisan swoole:danmu ' . $k . ' --daemon > /dev/null 2>&1 &');
                    }
                }
                sleep(30);
            }
        });
    }



}
