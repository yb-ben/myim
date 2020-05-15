<?php


namespace App\Console\IM;


use Swoole\Process;
use Swoole\Process\Pool;

class Recorder
{

    protected $pool;

    protected $workerNum = 5;

    protected $queue = 'swoole:im:record';

    public function __construct($daemon = false)
    {
        $daemon && Process::daemon();
        $this->pool = new Pool($this->workerNum);
    }

    protected function setListener(){
        $this->pool->on('WorkerStart',function($pool,$workerId){
            echo "Worker#{$workerId} is started\n";
            $redis = new \Redis();
            $redis->pconnect('127.0.0.1', 6379);
            while (true) {
                $msg = $redis->brpop($this->queue, 2);
                if ( $msg == null){
                    sleep(0.5);
                    continue;
                }
                var_dump($msg);
            }
        });
        $this->pool->on('WorkerStop',function($pool,$workerId){
            echo "Worker#{$workerId} is stopped\n";
        });
    }

    protected function run(){
        $this->pool->start();
    }
}
