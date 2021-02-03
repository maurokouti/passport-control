<?php

declare(strict_types=1);

namespace App\Storage;

class Item
{
    public function __construct(
        public string $series,
        public string $number,
    ) {
    }

    public function __toString(): string
    {
        return sprintf('%04d,%06d', $this->series, $this->number);
    }
}
