<?php


namespace App\Console\Danmu;


class BiliPacketParser
{

    protected $debug = false;

    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }

    public function parse($str){
        if (empty($str)) {
            return $str;
        }
        var_dump(bin2hex($str));
        $header = substr($str,0,16);
        $header = bin2hex($header);
        $this->debug && var_dump($header);
        $header = str_split($header,2);
        $this->debug && var_dump($header);
        $body = substr($str,16);
        if('08' === $header[11]){
            //认证完成
            $body = json_decode($body);
            if($body['code'] === 0){
                return true;
            }
            return false;
        }
        if('05' === $header[11]){

            if('00' === $header[7]){
                return $this->parseNotCompressData($body);
            }else{
                $context = inflate_init(ZLIB_ENCODING_DEFLATE);
                $body = inflate_add($context,$body);
                return $this->parseNotCompressData($body);
            }
        }
    }


    protected function parseNotCompressData($str){
        static $ret;
        if(empty($ret)){$ret = [];}
        $header = substr($str,0,16);
        $header = bin2hex($header);
        $header = str_split($header,2);//头
        $len =  base_convert($header[2].$header[3],16,10);//长度
        $body = substr($str,16);

        $b = substr($body,0,$len-16);
        $this->debug && print_r($b);
        $d = json_decode($b,JSON_UNESCAPED_UNICODE);

        if($d['cmd'] === 'DANMU_MSG'){
            $ret[] = [$d['info'][1],$d['info'][2][0],$d['info'][2][1],date('Y-m-d H:i:s',$d['info'][9]['ts'])];
        }
        if($d['cmd'] === 'SUPER_CHAT_MESSAGE'){
            $ret[] = [$d['data']['uid'],$d['data']['message'],$d['data']['user_info']['uname'],date('Y-m-d H:i:s',$d['data']['ts'])];
        }
        $s = substr($body, $len - 16);
        if($s === ''){
            return $ret;
        }
        return $this->parseNotCompressData($s);
    }
}
