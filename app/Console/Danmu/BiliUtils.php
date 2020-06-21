<?php


namespace App\Console\Danmu;


use DemeterChain\C;
use Swoole\Coroutine\Http\Client;

class BiliUtils
{

    /**
     * 获取弹幕地址
     * @param $roomId
     */
    public function getConfDanmu($roomId)
    {

        $client = new Client('api.live.bilibili.com', 443, true);
        $client->setHeaders([
            'Host' => 'api.live.bilibili.com',
            'origin' => 'https://live.bilibili.com',
            'referer' => 'https://live.bilibili.com/' . $roomId,
            'accept' => 'application/json, text/javascript, */*; q=0.01',
            'accept-encoding' => 'gzip, deflate, br',
            'accept-language' => 'zh-CN,zh;q=0.9',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36'
        ]);
        $client->set(['timeout' => 3]);
        $client->get("/room/v1/Danmu/getConf?room_id=${roomId}&platform=pc&player=web");
        $body = json_decode($client->body, JSON_OBJECT_AS_ARRAY);
        $client->close();
        return $body;

    }


    /**
     * 连接弹幕服务器
     * @param $host
     * @param $port
     * @param $roomId
     * @param $key
     */
    public function connectDanmuServer($host, $port, $roomId, $key,$alias)
    {
        $first = [
            'uid' => 0,
            'roomid' => intval($roomId),
            'protover' => 2,
            "platform" => "web",
            "clientver" => "1.12.0",
            "type" => 2,
            'key' => $key
        ];


        $heartBit = pack('H*','0000001f0010000100000002000000015b6f626a656374204f626a6563745d');

        $payload = json_encode($first);
        $len = strlen($payload);
        $payload= bin2hex($payload);
        $l =  str_pad( ''.dechex( $len + 16) ,8,'0',STR_PAD_LEFT);
        $payload = $l.'001000010000000700000001'.$payload;


        $ws = new Client($host, $port, true);
        $ws->setHeaders([
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'zh-CN,zh;q=0.9',
            'Host' => $host,
            'Origin' => 'https://' . $host,
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36'
        ]);
        $ws->set([
            'websocket_mask' => true,
            'socket_timeout' => 10,
            'socket_connect_timeout' => 5,
            'socket_read_timeout' => 5,
            'socket_write_timeout' => 5,
        ]);
        $ret = $ws->upgrade('/sub');
        if ($ret) {
            $payload = pack("H*",$payload);
            $ws->push($payload, WEBSOCKET_OPCODE_BINARY);
            $last= 0;
            $parser = new BiliPacketParser($roomId,$alias,true);

            while(true){
                $time = time();
                $frame = $ws->recv(1);
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



    public function relationStat($uid){
        $client = new Client('space.bilibili.com', 443, true);
        $client->setHeaders([
            'Host' => 'space.bilibili.com',
            'origin' => 'https://space.bilibili.com',
            'referer' => "https://space.bilibili.com/$uid/dynamic",
            'accept' => 'application/json, text/javascript, */*; q=0.01',
            'accept-encoding' => 'gzip, deflate, br',
            'accept-language' => 'zh-CN,zh;q=0.9',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36'
        ]);
        $client->set(['timeout' => 3]);
        $client->get("/x/relation/stat?vmid=$uid");
        $body = json_decode($client->body, JSON_OBJECT_AS_ARRAY);
        $client->close();
        return $body;

    }
}
