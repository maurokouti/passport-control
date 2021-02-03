<?php

declare(strict_types=1);

namespace App\Stream;

interface LineGeneratorInterface
{
    public function getLines(): \Generator;
}
