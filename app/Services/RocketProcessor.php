<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentProcessor;

class RocketProcessor implements PaymentProcessor
{
    public function process(array $transactions): void
    {
    }
}
