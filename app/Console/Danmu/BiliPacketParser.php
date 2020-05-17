<?php


namespace App\Console\Danmu;


class BiliPacketParser
{

    protected $debug = false;

    protected $fp = null;

    protected $buffer = [];

    protected $roomId ;

    public function __construct($roomId , $debug = false)
    {
        $this->debug = $debug;
        $this->roomId = $roomId;
        $this->fp = fopen(storage_path()."/$roomId.danmu",'a+');

    }

    public function parse($str){
        if (empty($str)) {
            return;
        }
        //print_r( __LINE__) && var_dump(bin2hex($str));
        $header = substr($str,0,16);
        $header = bin2hex($header);
        $header = str_split($header,2);

        $body = substr($str,16);
        if('08' === $header[11]){
            //认证完成
            $body = json_decode($body,JSON_OBJECT_AS_ARRAY);
            if($body['code'] === 0){
                return;
            }
            return;
        }
        if('05' === $header[11]){

            if('00' !== $header[7]){

                $context = inflate_init(ZLIB_ENCODING_DEFLATE);
                $body = inflate_add($context,$body);
                $this->parseNotCompressData($body);
                $this->save();
            }
        }
    }


    protected function parseNotCompressData($str){

        $header = substr($str,0,16);
        $header = bin2hex($header);
        $header = str_split($header,2);//头
        //$this->debug && print_r(__LINE__) && var_dump($header);
        $len =  base_convert($header[0].$header[1].$header[2].$header[3],16,10);//长度
        $body = substr($str,16);

        $b = substr($body,0,$len-16);
        $d = json_decode($b,JSON_OBJECT_AS_ARRAY);

        if($d['cmd'] === 'DANMU_MSG'){
            $uid = $d['info'][2][0];
            if($uid === 421267475){
                $this->buffer[] = [$d['info'][1],$uid,$d['info'][2][1],date('Y-m-d H:i:s',$d['info'][9]['ts'])];
            }
        }
        else if($d['cmd'] === 'SUPER_CHAT_MESSAGE'){
            $uid = $d['data']['uid'];
            if($uid === 421267475){
                $this->buffer[] = [$uid,$d['data']['message'],$d['data']['user_info']['uname'],date('Y-m-d H:i:s',$d['data']['ts'])];
            }
        }
        $s = substr($body, $len - 16);
        if($s === ''){
            return;
        }
        return $this->parseNotCompressData($s);
    }


    protected function save(){
        go(function (){
            var_dump($this->buffer);
            $data = '';
            foreach ($this->buffer as $item){
                $data .= implode(',',$item).PHP_EOL;
            }
            echo \Swoole\Coroutine\System::fwrite($this->fp,$data);
            $this->buffer = [];
        });
    }


    public function __destruct()
    {
        fclose($this->fp);
    }
}
