<?php

declare(strict_types=1);

namespace App\Status;

class DummyStatusStorage implements StatusStorageInterface
{
    public function __construct(
    ) {
    }

    public function getStatus(): Status
    {
        return new Status(
            lastUpdate: 0,
            items: 0,
        );
    }

    public function setStatus(Status $status): void
    {
    }
}
