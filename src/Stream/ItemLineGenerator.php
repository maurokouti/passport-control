<?php

declare(strict_types=1);

namespace App\Stream;

use App\Storage\Item;

class ItemLineGenerator implements ItemGeneratorInterface
{
    public function __construct(
        private iterable $lines,
    ) {
    }

    public function getItems(): \Generator
    {
        $result = ['processed' => 0];

        foreach ($this->lines as $line) {
            [$series, $number] = explode(',', $line);
            yield new Item($series, $number);
            $result['processed']++;
        }

        return $result;
    }
}
