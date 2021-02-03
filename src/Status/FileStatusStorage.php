<?php

declare(strict_types=1);

namespace App\Status;

class FileStatusStorage implements StatusStorageInterface
{
    public function __construct(
        private string $path,
    ) {
    }

    public function getStatus(): Status
    {
        if (!file_exists($this->path)) {
            return new Status();
        }

        $decodedStatus = \json_decode(
            file_get_contents($this->path),
        );

        return new Status(
            lastUpdate: $decodedStatus->lastUpdate,
            items: $decodedStatus->items,
        );
    }

    public function setStatus(Status $status): void
    {
        file_put_contents(
            $this->path,
            \json_encode($status),
        );
    }
}
