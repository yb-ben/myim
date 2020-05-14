<?php


namespace App\Console\IM;


class Server
{
    protected $server;



    public function __construct($host = '0.0.0.0',$port = 9502,array $callbacks = null)
    {
        $this->server = new \Swoole\WebSocket\Server($host,$port);
        throw_if(empty($callbacks),\Exception::class,'回调函数不能为空');
        $this->setListener($callbacks);
    }



    public function run(){
        go(function(){
            $this->server->start();
        });
    }


    protected function setListener($callbacks){
        foreach($callbacks as $event => $callback ){
            $this->server->on($event,$callback);
        }
    }
}
