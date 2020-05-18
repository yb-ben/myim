<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;

class DanmuList extends Command
{

    protected $signature = 'danmu:list';


    protected $description = 'list all danmu';


    public function handle(){

        $ret = shell_exec('ps -ef | grep danmu | awk \'{if($3==1)print $2,$5,$11,$12;}\'');
        $ret = explode("\n",$ret);
        $data = [];
        foreach ($ret as $line){
            $data[] = explode(',',$line);
        }
        return $data;
    }



}
