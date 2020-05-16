<?php


namespace App\Console\IM;


use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;

class Server
{
    protected $server;

    protected $redis = null;

    protected $queue = 'swoole:im:record';

    protected $livetime = 60;

    protected $maxPacketSize = 1024*1024*1024;

    protected $map = [];
    protected $room = [];

    protected $config = [
     //   'daemonize' => 1,
        'log_file' =>  './storage/app/swoole_im.log',
        'pid_file' => './storage/app/server.pid'
    ];


    public function __construct($host = '0.0.0.0',$port = 9502)
    {
        $this->getRedisInstance();
        $this->server = new \Swoole\WebSocket\Server($host,$port,SWOOLE_PROCESS);
        $this->server->set($this->config);
        $this->setListener();

    }

    protected function getRedisInstance():\Redis
    {
            if($this->redis){
                return $this->redis;
            }
            $redis = new \Redis();
            $redis->pconnect('127.0.0.1',6379);
            $this->redis = $redis;
            return $redis;
    }


    public function run(){
        $this->server->start();
    }

    //设置监听函数
    protected function setListener(){
        $callbacks=[

            'open'=>function(\Swoole\WebSocket\Server $server, $request){
                echo '连接成功'.PHP_EOL;
            },
            'message'=>function(\Swoole\WebSocket\Server $server,Frame $frame){
                $time = time();
                $content = $frame->data;
                $msg =json_decode($content,JSON_OBJECT_AS_ARRAY);
                if(!isset($msg['type'])){
                    return $server->disconnect($frame->fd);
                }
                if ($msg['type'] === 1) {
                    //认证
                    if(empty($msg['token'])){
                        return $server->disconnect($frame->fd);
                    }
                    //检验token
                    $tokenInfo = explode('.', decrypt($msg['token']));
                    if(md5($tokenInfo[0]) !== $tokenInfo[1]){
                        return $server->disconnect($frame->fd);
                    }
                    $t = explode('.',$tokenInfo[0]);

                    if( $time - $t[0] > 3600){
                        return $server->disconnect($frame->fd);
                    }


                    //加入映射
                    $this->map[$frame->fd] = [ $time ,$t[1],$t[2]];
                    $this->room[$t[1]][]= $frame->fd;

                    $this->getRedisInstance()->zAdd('room:'.$t[1],$t[2],$time);

                }else{

                    $waitForRem = [];
                    $roomRem = [];
                    //移除过期连接
                    foreach ($this->map as $fd => $item){
                        if($time - $item[0] > $this->livetime){
                            unset($this->map[$fd]);

                            $waitForRem[$item[1]][] = $item[2];
                            foreach ($this->room[$item[1]] as &$room){
                                if($room === $fd){
                                    unset($room);
                                    if (empty($this->room[$item[1]])) {
                                        unset($this->room[$item[1]]);
                                        $roomRem[] = $item[1];
                                    }
                                    break;
                                }
                            }
                        }
                    }
                    //移除信息
                    foreach ($roomRem as $v){
                        $this->getRedisInstance()->del('room:'.$v);
                        if(isset($waitForRem[$v])){
                            unset($waitForRem[$v]);
                        }
                    }
                    foreach ($waitForRem as $k => $v){
                        $this->getRedisInstance()->zRem('room:'.$k,...$v);
                    }

                    if(!isset($this->map[$frame->fd])) {
                        return true;
                    }

                    if($msg['type'] === 3){
                        //心跳
                        $roomId = $this->map[$frame->fd][1];
                        $this->map[$frame->fd][0] = $time;
                        $this->room[$roomId] = $frame->fd;
                        $this->getRedisInstance()->zAdd('room:'.$roomId,$this->map[$frame->fd][2],$time);
                        return true;
                    }

                    if($msg['type'] === 2){
                        //消息
                        $roomId = $this->map[$frame->fd][1];
                        $send = json_encode(['s'=>0,'c'=>$content]);

                        foreach ($this->room[$roomId] as $fd){
                            $server->push($fd,$send);
                        }
                        return true;
                    }
                }

                //$this->getRedisInstance()->lpush($this->queue,$send);
            },
            'close'=>function(\Swoole\WebSocket\Server $server,$fd){
                echo $fd.'断开连接';
            },
            'shutdown'=>function(\Swoole\WebSocket\Server $server){
               // $this->getRedisInstance()->close();
            }
        ];

        foreach($callbacks as $event => $callback ){
            $this->server->on($event,$callback);
        }
    }


}
