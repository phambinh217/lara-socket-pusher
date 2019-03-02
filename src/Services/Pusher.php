<?php

namespace Phambinh\LaraSocketPusher\Services;

use ZMQContext;
use ZMQ;

class Pusher
{
    protected $context;

    protected $socket;

    public function __construct()
    {
        $this->context = new ZMQContext();
        $this->socket = $this->context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
        $this->socket->connect(config('pusher.tcp'));
    }

    public function push($type, $data, $usersId)
    {
        $data = json_encode([
            'type' => $type,
            'data' => $data,
            'users_id' => $usersId
        ]);
        $this->socket->send($data);
    }
}
