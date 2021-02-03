<?php

declare(strict_types=1);

namespace App\Stream;

use Psr\Http\Message\StreamInterface;

class StreamLineGenerator implements LineGeneratorInterface
{
    public function __construct(
        private StreamInterface $stream,
        private int $bufferSize = 8192,
    ) {
    }

    public function getLines(): \Generator
    {
        $result = [
            'processed' => 0,
            'ignored' => 0,
        ];

        $buffer = '';
        while (!$this->stream->eof()) {
            $buffer .= $this->stream->read($this->bufferSize);
            $chunks = preg_split('~\r?\n~', $buffer);

            // last chunk may have incomplete line
            for ($i = 0; $i < count($chunks) - 1; $i++) {
                $result['processed']++;
                yield $chunks[$i];
            }
            $buffer = $chunks[array_key_last($chunks)];
        }

        // yield the buffer with the last chunk
        if (!empty($buffer)) {
            $result['processed']++;
            yield $buffer;
        }

        return $result;
    }
}
