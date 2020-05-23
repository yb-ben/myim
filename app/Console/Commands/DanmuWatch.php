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
        Process::daemon();
        while (true) {

            $ret = shell_exec('ps -ef | grep danmu | awk \'{if($3==1)print $2,$11;}\'');
            $ret = explode("\n",$ret);
            $data = [];
            foreach ($ret as $line){
                $data[] = explode(' ',$line);

            }
            foreach ($this->rooms as $k => $v){
                if (!isset($data[$k])) {
                    shell_exec('php artisan swoole:danmu ' . $k . ' --daemon');
                }
            }
            sleep(60);
        }
    }



}
