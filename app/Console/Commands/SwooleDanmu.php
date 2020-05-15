<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Swoole\Coroutine\Http\Client;
use Swoole\WebSocket\Server;

class SwooleDanmu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:danmu {roomId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'swoole websocket';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        go(function(){
            $roomId = $this->argument('roomId');
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
            print_r($body);
            $client->close();
            if($body['code'] === 0 ){
                $server = $body['data']['host_server_list'][0];
                $key = $body['data']['token'];
                $first = [
                    'uid'=>'4906232',
                    'roomId' => $roomId,
                    'protover'=>2,
                    "platform"=>"web",
                    "clientver"=>"1.12.0",
                    "type"=>2,
                    'token'=>$key
                ];

                $heartBit = hex2bin('0000001f0010000100000002000000015b6f626a656374204f626a6563745d');

                $payload= bin2hex(json_encode($first));

                $payload = '0000'.dechex(strlen($payload) + 32).'001000010000000700000001'.$payload;


                $ws =  new Client($server['host'],$server['wss_port'],true);
                $ret = $ws->upgrade('/sub');
                if($ret){
                    if(strlen($payload) % 2){
                       $payload = pack("H*",$payload);
                    }else{
                       $payload = hex2bin($payload);
                    }
                    $ws->push(hex2bin($payload),WEBSOCKET_OPCODE_BINARY);
                    go(function ()use($ws){
                        while(true){
                            $msg = $ws->recv(3);
                            var_dump($msg);
                        }
                    });
                    go(function ()use($ws,$heartBit){
                       while(true){
                           $ws->push($heartBit,WEBSOCKET_OPCODE_BINARY);
                           var_dump(1);
                           sleep(1);
                       }
                    });
                }
            }
        });
    }
}
