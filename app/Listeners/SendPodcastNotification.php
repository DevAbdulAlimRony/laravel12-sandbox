<?php

namespace App\Listeners;

use App\Events\PodcastProcessed;

class SendPodcastNotification
{
    public function handle(PodcastProcessed $event): void
    {
    }
}
