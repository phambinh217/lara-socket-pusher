<?php

return [
    'tcp' => 'tcp://' . env('PUSHER_TPC_HOST', '127.0.0.1:5555'),
    'host' => env('PUSHER_HOST', '0.0.0.0:1996'),
];
