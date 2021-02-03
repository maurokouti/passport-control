<?php

declare(strict_types=1);

namespace App\Source;

use App\Stream\DecompressStream;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

class FileSource implements SourceInterface
{
    public function __construct(
        private string $path,
    ) {
        if (!file_exists($this->path)) {
            throw new \Exception('File not found ' . $this->path);
        }
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function getStream(): StreamInterface
    {
        $fileHandler = fopen($this->path, 'rb');
        $fileStream = Utils::streamFor($fileHandler);

        return new DecompressStream($fileStream);
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return sprintf('File source %s', $this->path);
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function getLastUpdate(): int
    {
        return filemtime($this->path);
    }
}
