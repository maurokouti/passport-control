<?php

declare(strict_types=1);

namespace App\Status;

interface StatusStorageInterface
{
    /**
     * Get the current status.
     *
     * @return Status
     */
    public function getStatus(): Status;

    /**
     * Set and persist the new status.
     *
     * @param Status $status
     */
    public function setStatus(Status $status): void;
}
