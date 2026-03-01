<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PodcastProcessed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public string $title,
        public ?string $payload = null,
    ) {
    }
}
