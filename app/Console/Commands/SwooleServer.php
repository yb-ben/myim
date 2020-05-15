<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Swoole\WebSocket\Server;

class SwooleServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:server';

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
        app(\App\Console\IM\Server::class,

                [
                    'host'=>'0.0.0.0',
                    'post'=>9502
                ]

            )
            ->run();
    }
}
