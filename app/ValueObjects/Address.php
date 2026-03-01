<?php

namespace App\ValueObjects;

/**
 * Stub for documentation. Address value object used by AsAddress cast.
 */
class Address
{
    public function __construct(
        public readonly string $lineOne,
        public readonly string $lineTwo
    ) {}

    public static function create(array $attributes): self
    {
        return new self(
            $attributes['address_line_one'] ?? '',
            $attributes['address_line_two'] ?? ''
        );
    }
}
