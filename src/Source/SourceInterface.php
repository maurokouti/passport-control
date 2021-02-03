<?php

declare(strict_types=1);

namespace App\Source;

use Psr\Http\Message\StreamInterface;

interface SourceInterface
{
    /**
     * @return StreamInterface Stream from the source content.
     */
    public function getStream(): StreamInterface;

    /**
     * @return string String contains the source description.
     */
    public function getDescription(): string;

    /**
     * @return int Timestamp of the last source update in Unix format.
     */
    public function getLastUpdate(): int;
}
