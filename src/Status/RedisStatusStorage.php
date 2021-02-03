<?php

declare(strict_types=1);

namespace App\Status;

use Redis;

class RedisStatusStorage implements StatusStorageInterface
{
    private const KEY_STATUS = 'status';

    public function __construct(
        private Redis $redis,
        private ?int $statusDb = 0,
    ) {
    }

    public function getStatus(): Status
    {
        $this->redis->select($this->statusDb);
        $status = $this->redis->hMGet(self::KEY_STATUS, ['lastUpdate', 'items']);

        return new Status(
            lastUpdate: (int) ($status['lastUpdate'] ?: 0),
            items: (int) ($status['items'] ?: 0),
        );
    }

    public function setStatus(Status $status): void
    {
        $this->redis->select($this->statusDb);
        $this->redis->hMSet(self::KEY_STATUS, [
            'lastUpdate' => $status->lastUpdate,
            'items' => $status->items,
        ]);
    }
}
