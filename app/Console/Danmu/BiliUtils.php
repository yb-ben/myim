<?php


namespace App\Console\Danmu;


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
    public function connectDanmuServer($host, $port, $roomId, $key)
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

        $heartBit = pack('H*', '0000001f0010000100000002000000015b6f626a656374204f626a6563745d');

        $payload = json_encode($first);
        $len = strlen($payload);
        $payload = bin2hex($payload);
        $l = str_pad('' . dechex($len + 16), 8, '0', STR_PAD_LEFT);
        $payload = $l . '001000010000000700000001' . $payload;

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
            $payload = pack("H*", $payload);
            $ws->push($payload, WEBSOCKET_OPCODE_BINARY);
            $last = 0;
            while (true) {
                $time = time();
                $frame = $ws->recv(1);
                var_dump($frame);
                if ($time - $last >= 30) {
                    $last = $time;
                    $ws->push($heartBit, WEBSOCKET_OPCODE_BINARY);
                }
            }
        }
        $ws->close();
    }

    public static function parseMsg($str)
    {
        static $temp, $context;
        if (empty($temp)) {
            $temp = [];
        }
        if (empty($context)) {
            $context = inflate_init(ZLIB_ENCODING_DEFLATE);
        }

        $header = substr($str, 0, 32);
        $header = str_split($header, 2);//头
        $len = base_convert($header[2] . $header[3], 16, 10);//长度
        $body = substr($str, 32);
        $body = hex2bin($body);
        if ('00' === $header[7]) {
            $b = substr($body, 0, $len - 16);

            $d = json_decode($b, JSON_UNESCAPED_UNICODE);

            if ($d['cmd'] === 'DANMU_MSG') {
                $temp[] = [$d['info'][1], $d['info'][2][0], $d['info'][2][1], date('Y-m-d H:i:s', $d['info'][9]['ts'])];
            }
            $s = substr($body, $len - 16);
            if ($s === '') {
                return $temp;
            }
            return parseMsg(bin2hex($s));

        }
        $body = inflate_add($context, $body);
        return parseMsg(bin2hex($body));
    }

}
