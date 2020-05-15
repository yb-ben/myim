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

    protected $config = [
        'daemonize' => 1,
        'log_file' =>  './storage/app/swoole_im.log',
        'pid_file' => './storage/app/server.pid'
    ];


    public function __construct($host = '0.0.0.0',$port = 9502)
    {
       // $this->getRedisInstance();
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
            'handshake'=>function(Request $request,Response $response){
                print_r($request->header);
                // print_r( $request->header );
                // if (如果不满足我某些自定义的需求条件，那么返回end输出，返回false，握手失败) {
                //    $response->end();
                //     return false;
                // }

                // websocket握手连接算法验证
                $secWebSocketKey = $request->header['sec-websocket-key'];
                $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
                if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
                    $response->end();
                    return false;
                }
                echo $request->header['sec-websocket-key'];
                $key = base64_encode(
                    sha1(
                        $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
                        true
                    )
                );

                $headers = [
                    'Upgrade' => 'websocket',
                    'Connection' => 'Upgrade',
                    'Sec-WebSocket-Accept' => $key,
                    'Sec-WebSocket-Version' => '13',
                ];

                // WebSocket connection to 'ws://127.0.0.1:9502/'
                // failed: Error during WebSocket handshake:
                // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
                if (isset($request->header['sec-websocket-protocol'])) {
                    $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
                }

                foreach ($headers as $key => $val) {
                    $response->header($key, $val);
                }

                $response->status(101);
                $response->end();
            },
            'open'=>function(\Swoole\WebSocket\Server $server, $request){
                $this->info($request->fd.'链接成功');
            },
            'message'=>function(Server $server,Frame $frame){
                $content = $frame->data;
                $send = json_encode(['s'=>0,'c'=>$content]);

                foreach ($server->connections as $connection){
                    $server->push($connection,$send);
                }
                //$this->getRedisInstance()->lpush($this->queue,$send);
            },
            'close'=>function(Server $server,$fd){
                $this->info($fd.'断开连接');
            },
            'shutdown'=>function(\Swoole\Server $server){
                $this->getRedisInstance()->close();
            }
        ];

        foreach($callbacks as $event => $callback ){
            $this->server->on($event,$callback);
        }
    }


}
