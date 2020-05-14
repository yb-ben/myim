<?php


namespace App\Console\IM;


class Server
{
    protected $server;

    protected $events = [
        'open' => null,
        'message' => null,
        'close' => null,
    ];

    public function __construct($host = '0.0.0.0',$port = 9502,array $callbacks = null)
    {
        $this->server = new \Swoole\WebSocket\Server($host,$port);
        throw_if($callbacks,\Exception::class,'回调函数不能为空');
        $this->setListener($callbacks);
    }



    public function run(){
        $this->server->start();
    }


    protected function setListener($callbacks){
        foreach($callbacks as $event => $callback ){
            if(isset($this->events[$event])){
                $this->events[$event] = $callback;
                $this->server->on($event,$callback);
            }
        }
    }
}
