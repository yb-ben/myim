<?php


namespace App\Console\Commands;


use App\Console\Danmu\BiliUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Swoole\Process;

class EineWatcher extends Command
{
    protected $signature = 'eine:watcher {--daemon}';


    protected $description = 'watch eine fans';



    public function handle(){
        $this->option('daemon') && Process::daemon(true);
        $biliUtils = new BiliUtils();
        while(true){
            try{
                $info =  $biliUtils->relationStat('421267475');
                if ($info) {
                    if ($info['code'] === 0) {
                        $connection =DB::connection('eine');
                        $last = $connection->table('eine_followers')->orderby('created_at','desc')->limit(1)->first();
                        $increment = 0;
                        if (!empty($last)) {
                            $increment = $info['data']['follower'] - $last->followers;
                        }
                        if ($increment !== 0) {
                            $connection->table('eine_followers')->insert([
                                'followers' => $info['data']['follower'],
                                'created_at'=>time(),
                                'increment'=>$increment
                            ]);
                        }
                    }
                }
            }catch (\Throwable $throwable){
                Log::channel('eine')->error($throwable->getMessage(),$throwable->getTrace());
            }

            sleep(60);
        }

    }


}
