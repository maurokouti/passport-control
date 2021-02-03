<?php

declare(strict_types=1);

namespace App\Status;

class Status
{
    public function __construct(
        public int $lastUpdate = 0,
        public int $items = 0,
    ) {
    }
}
