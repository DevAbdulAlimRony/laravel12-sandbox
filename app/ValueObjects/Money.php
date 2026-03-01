<?php

declare(strict_types=1);

namespace App\ValueObjects;

class Money
{
    public function __construct(
        public string $currency = 'USD',
        public float $amount = 0.0,
    ) {
    }
}
