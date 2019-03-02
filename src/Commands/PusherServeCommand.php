<?php

namespace Phambinh\LaraSocketPusher\Commands;

use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;
use Phambinh\LaraSocketPusher\WebSocket\Pusher;
use React\Socket\Server;
use React\ZMQ\Context;
use ZMQ;
use Exception;

class PusherServeCommand extends Command
{
    protected $signature = 'pusher:serve';

    protected $description = 'Init pusher serve';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (!file_exists(config_path('pusher.php'))) {
            throw new Exception('Vui lòng pushlish config cho gói lara-socket-pusher');
        }

        echo 'Chat server init on ' . config('pusher.host') . "\n";

        $loop = LoopFactory::create();
        $pusher = new Pusher;

        $context = new Context($loop);
        $pull = $context->getSocket(ZMQ::SOCKET_PULL);
        $pull->bind(config('pusher.tcp'));
        $pull->on('message', array($pusher, 'onTrigger'));

        $socket = new Reactor(config('pusher.host'), $loop);

        $server = new IoServer(
            new HttpServer(
                new WsServer(
                    $pusher
                )
            ), $socket, $loop
        );

        $server->run();
    }
}
