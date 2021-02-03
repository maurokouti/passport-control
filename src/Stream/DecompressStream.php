<?php

declare(strict_types=1);

namespace App\Stream;

use GuzzleHttp\Psr7\NoSeekStream;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\StreamDecoratorTrait;
use GuzzleHttp\Psr7\StreamWrapper;
use Psr\Http\Message\StreamInterface;

/**
 * Uses PHP's bzip2.decompress filter to decompress bzip2 content.
 *
 * This stream decorator converts the provided stream to a PHP stream resource,
 * then appends the bzip2.decompress filter. The stream is then converted back
 * to a Guzzle stream resource to be used as a Guzzle stream.
 *
 * @link http://php.net/manual/en/filters.compression.php
 */
final class DecompressStream implements StreamInterface
{
    use StreamDecoratorTrait;

    public function __construct(StreamInterface $stream)
    {
        $resource = StreamWrapper::getResource($stream);
        stream_filter_append($resource, 'bzip2.decompress', STREAM_FILTER_READ, ['small' => false]);
        $this->stream = $stream->isSeekable() ? new Stream($resource) : new NoSeekStream(new Stream($resource));
    }
}
