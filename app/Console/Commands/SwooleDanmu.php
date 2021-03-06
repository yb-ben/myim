<?php

namespace App\Console\Commands;

use App\Console\Danmu\BiliPacketParser;
use App\Console\Danmu\BiliUtils;
use Illuminate\Console\Command;
use Swoole\Coroutine\Http\Client;
use Swoole\Process;
use Swoole\Timer;
use Swoole\WebSocket\Server;

class SwooleDanmu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:danmu {alias} {--daemon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'swoole websocket';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->option('daemon') && Process::daemon(true);
        go(function(){

            $alias = $this->argument('alias');
            $roomId = $this->rooms[$alias];
            if (empty($roomId)) {
                return;
            }
            $client = new Client('api.live.bilibili.com',443,true);
            $client->setHeaders([
                'Host'=>'api.live.bilibili.com',
                'origin'=>'https://live.bilibili.com',
                'referer'=>'https://live.bilibili.com/'.$roomId,
                'accept'=>'application/json, text/javascript, */*; q=0.01',
                'accept-encoding'=>'gzip, deflate, br',
                'accept-language'=>'zh-CN,zh;q=0.9',
                'user-agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36'
            ]);
            $client->set(['timeout'=>3]);
            $client->get("/room/v1/Danmu/getConf?room_id=${roomId}&platform=pc&player=web");
            $body = json_decode($client->body,JSON_OBJECT_AS_ARRAY);
            $client->close();
            if($body['code'] === 0 ){
                $server = $body['data']['host_server_list'][0];
                $key = $body['data']['token'];
                $first = [
                    'uid'=>0,
                    'roomid' => intval($roomId),
                    'protover'=>2,
                    "platform"=>"web",
                    "clientver"=>"1.12.0",
                    "type"=>2,
                    'key'=>$key
                ];

                $heartBit = pack('H*','0000001f0010000100000002000000015b6f626a656374204f626a6563745d');

                $payload = json_encode($first);
                $len = strlen($payload);
                $payload= bin2hex($payload);
                $l =  str_pad( ''.dechex( $len + 16) ,8,'0',STR_PAD_LEFT);
                $payload = $l.'001000010000000700000001'.$payload;

                $ws =  new Client($server['host'],$server['wss_port'],true);
                $ws->setHeaders([
                    'Accept-Encoding'=>'gzip, deflate, br',
                    'Accept-Language'=>'zh-CN,zh;q=0.9',
                    'Host'=>$server['host'],
                    'Origin'=>'https://'.$server['host'],
                    'User-Agent'=> 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36'
                ]);
                $ws->set([
                    'websocket_mask' => true,
                    'socket_timeout' => 10,
                    'socket_connect_timeout' => 5,
                    'socket_read_timeout' => 5,
                    'socket_write_timeout' => 5,
                ]);
                $ret = $ws->upgrade('/sub');
                if($ret){

                    $payload = pack("H*",$payload);

                    $ws->push($payload,WEBSOCKET_OPCODE_BINARY);

                        $last= 0;
                        $parser = new BiliPacketParser($roomId,$alias,true);
                        while(true){
                            $time = time();
                            $frame = $ws->recv(3);
                            if(is_object($frame)){
                                $d = json_decode($frame->data);
                                if (is_null($d)) {
                                    $parser->parse($frame->data);
                                }
                            }
                            if($time - $last > 30){
                                $last = $time;
                                $ws->push($heartBit,WEBSOCKET_OPCODE_BINARY);
                            }
                        }

                }
                $ws->close();
            }
        });
    }
}
