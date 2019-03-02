<?php

namespace Phambinh\LaraSocketPusher\Facades;

use Illuminate\Support\Facades\Facade;

class Pusher extends Facade
{

    public static function getFacadeAccessor()
    {
        return 'lara-socket-pusher';
    }
}