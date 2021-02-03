<?php

declare(strict_types=1);

namespace App\Storage;

interface ItemStorageInterface
{
    /**
     * @param iterable|iterable<Item> $items
     * @return int Number of stored items
     */
    public function updateItems(iterable $items): int;

    /**
     * @param iterable|iterable<Item> $items
     * @return array
     */
    public function getItems(iterable $items): array;
}
