<?php

declare(strict_types=1);

namespace App\Storage;

class FileItemStorage implements ItemStorageInterface
{
    public function __construct(
        private string $filename
    ) {
    }

    /**
     * @param iterable $items
     * @throws \Exception
     * @return int Number of stored items.
     */
    public function updateItems(iterable $items): int
    {
        $counter = 0;
        $tempFilename = $this->filename . '.new';
        $fileHandler = fopen($tempFilename, 'w');
        /** @var Item $item */
        foreach ($items as $item) {
            $line = implode(',', [$item->series, $item->number]);
            $res = fputs($fileHandler, $line . PHP_EOL);
            if ($res === false) {
                throw new \Exception('Failed to write file ' . $tempFilename);
            }
            $counter++;
        }
        fclose($fileHandler);
        rename($tempFilename, $this->filename);

        return $counter;
    }

    public function getItems(iterable $items): array
    {
        if (!file_exists($this->filename)) {
            throw new \Exception('Failed to init file items storage. File not found. ' . $this->filename);
        }

        throw new \Exception('Method getItems is not implemented for File storage');

        return [];
    }
}
