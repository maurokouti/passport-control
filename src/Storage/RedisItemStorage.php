<?php

declare(strict_types=1);

namespace App\Storage;

use Redis;

class RedisItemStorage implements ItemStorageInterface
{
    public function __construct(
        private Redis $redis,
        private int $primaryDb = 2,
        private int $temporaryDb = 3,
        private int $batchSize = 50000,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function updateItems(iterable $items): int
    {
        $this->redis->select($this->temporaryDb);
        $this->redis->multi(Redis::PIPELINE);

        $counter = 0;
        $bufferCounter = 0;
        foreach ($items as $item) {
            $this->redis->setBit($item->series, (int) $item->number, true);
            $bufferCounter++;
            $counter++;

            if ($bufferCounter > $this->batchSize) {
                $this->redis->exec();
                $this->redis->multi(Redis::PIPELINE);
                $bufferCounter = 0;
            }
        }
        // flush the rest
        $this->redis->exec();

        // rotate DBs
        if ($this->redis->swapdb($this->primaryDb, $this->temporaryDb)) {
            $this->redis->select($this->temporaryDb); // explicitly select temporary DB
            $this->redis->flushDb();
        }

        return $counter;
    }

    /**
     * @inheritdoc
     */
    public function getItems(iterable $items): array
    {
        $buffer = [];
        $this->redis->select($this->primaryDb);
        $this->redis->multi(Redis::PIPELINE);

        foreach ($items as $item) {
            $buffer[] = $item;
            $this->redis->getBit($item->series, (int) $item->number);
        }

        $response = $this->redis->exec();
        $result = [];
        foreach ($response as $index => $bitSet) {
            if ($bitSet === 1) {
                $result[] = $buffer[$index];
            }
        }

        return $result;
    }
}
