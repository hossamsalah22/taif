<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SubscribeToTopicsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public $user,
        public string $token
    ) {}

    public function handle(): void
    {
        fcm_subscribe_all_sync($this->user, $this->token);
    }
}
