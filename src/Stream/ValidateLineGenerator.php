<?php

declare(strict_types=1);

namespace App\Stream;

class ValidateLineGenerator implements LineGeneratorInterface
{
    private const VALID_LINE_REGEXP = '~^\d{4},\d{6}~';

    public function __construct(
        private iterable $lines,
    ) {
    }

    public function getLines(): \Generator
    {
        $result = [
            'processed' => 0,
            'ignored' => 0,
        ];

        foreach ($this->lines as $line) {
            $result['processed']++;

            if (!preg_match(self::VALID_LINE_REGEXP, $line)) {
                $result['ignored']++;

                continue;
            }

            yield $line;
        }

        return $result;
    }
}
