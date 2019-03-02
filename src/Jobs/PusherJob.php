<?php

namespace Phambinh\LaraSocketPusher\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Phambinh\LaraSocketPusher\Facades\Pusher;

class PusherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $type;

    public $data;

    public $usersId;

    public function __construct($type, $data, $usersId = [])
    {
        $this->type = $type;
        $this->data = $data;
        $this->usersId = $usersId;
    }

    public function handle()
    {
        Pusher::push($this->type, $this->data, $this->usersId);
    }
}
