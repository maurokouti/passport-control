<?php

declare(strict_types=1);

namespace App\Storage;

use App\Storage\Pilosa\Proto\ImportRequest;
use App\Storage\Pilosa\Proto\QueryRequest;

class PilosaItemStorage implements ItemStorageInterface
{
    private const FIELD = 'series';

    public function __construct(
        private string $url,
        private string $index = 'passport',
        private int $batchSize = 250_000,
    ) {
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function updateItems(iterable $items): int
    {
        $ch = curl_init(sprintf('%s/index/%s/field/%s/import', $this->url, $this->index, self::FIELD));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-protobuf',
            'Accept: application/x-protobuf',
        ]);

        $bufferColumnIDs = $bufferRowIDs = [];
        $bufferCounter = 0;
        $processedCounter = 0;
        foreach ($items as $item) {
            $bufferColumnIDs[] = $item->number;
            $bufferRowIDs[] = $item->series;
            $bufferCounter++;
            $processedCounter++;

            // flush buffer
            if ($bufferCounter > $this->batchSize) {
                $importRequest = (new ImportRequest())
                    ->setIndex($this->index)
                    ->setField(self::FIELD)
                    ->setColumnIDs($bufferColumnIDs)
                    ->setRowIDs($bufferRowIDs)
                ;

                curl_setopt($ch, CURLOPT_POSTFIELDS, $importRequest->serializeToString());
                curl_exec($ch);
                if (curl_errno($ch)) {
                    throw new \Exception('Curl error: ' . curl_error($ch));
                }
                $bufferRowIDs = $bufferColumnIDs = [];
                $bufferCounter = 0;
            }
        }
        // final flush
        $importRequest = (new ImportRequest())
            ->setIndex($this->index)
            ->setField(self::FIELD)
            ->setColumnIDs($bufferColumnIDs)
            ->setRowIDs($bufferRowIDs)
        ;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $importRequest->serializeToString());
        curl_exec($ch);

        return $processedCounter;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function getItems(iterable $items): array
    {
        throw new \Exception('Method getItems is not implemented for Pilosa storage');

//        $query = (new QueryRequest())->setQuery(sprintf(
//            'GroupBy(Rows(series, column=%d), filter=Row(series=%04d), limit=1)',
//            $item->number,
//            $item->series,
//        ));
    }
}
