<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentProcessor;

class Bkash implements PaymentProcessor
{
    public function __construct(
        public string $configuration = ''
    ) {
    }

    public function process(array $transactions): void
    {
    }
}
