<?php

declare(strict_types=1);

namespace App\Stream;

interface ItemGeneratorInterface
{
    public function getItems(): \Generator;
}
