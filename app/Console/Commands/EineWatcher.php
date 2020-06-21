<?php


namespace App\Console\Commands;


use App\Console\Danmu\BiliUtils;
use Illuminate\Console\Command;
use Swoole\Process;

class EineWatcher extends Command
{
    protected $signature = 'eine:watcher {--daemon}';


    protected $description = 'watch eine fans';



    public function handle(){
        $this->option('daemon') && Process::daemon(true);
        go(function() {

            $biliUtils = new BiliUtils();
            $ret = $biliUtils->relationStat('421267475');
            print_r($ret);

        });
    }


}
