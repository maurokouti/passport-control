<?php

declare(strict_types=1);

namespace App\Service;

use App\Source\SourceInterface;
use App\Status\Status;
use App\Status\StatusStorageInterface;
use App\Storage\ItemStorageInterface;
use App\Stream\ItemLineGenerator;
use App\Stream\StreamLineGenerator;
use App\Stream\ValidateLineGenerator;
use Psr\Log\LoggerInterface;

class Updater
{
    public function __construct(
        private SourceInterface $source,
        private StatusStorageInterface $statusStorage,
        private ItemStorageInterface $itemStorage,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function run(): void
    {
        // print source information
        $this->logger?->info($this->source->getDescription());

        // check the source's last update time
        $sourceLastUpdate = $this->source->getLastUpdate();
        $this->logger?->info(sprintf('Source last update time %s', date('r', $sourceLastUpdate)));

        $currentStatus = $this->statusStorage->getStatus();
        if ($this->source->getLastUpdate() <= $currentStatus->lastUpdate) {
            $this->logger?->warning(sprintf(
                'Selected source has had no updates since %s',
                date('r', $currentStatus->lastUpdate)
            ));

            return;
        }

        $this->logger?->info('Update process has been started');

        $sourceStream = $this->source->getStream();
        // read source stream and extract single lines
        $parsedLines = (new StreamLineGenerator($sourceStream))->getLines();
        // validate lines
        $validLines = (new ValidateLineGenerator($parsedLines))->getLines();

        // extract series, number values from the string line
        $items = (new ItemLineGenerator($validLines))->getItems();

        // send items to the storage
        $storedCount = $this->itemStorage->updateItems($items);

        // close source stream
        $sourceStream->close();

        // collect parser info
        $parsedLinesResult = $parsedLines->getReturn();
        $this->logger?->info(sprintf('%d lines parsed', $parsedLinesResult['processed']));

        // collect validation info
        $validLinesResult = $validLines->getReturn();
        $this->logger?->info(sprintf('%d lines validated', $validLinesResult['processed']));
        $this->logger?->info(sprintf('%d invalid lines ignored', $validLinesResult['ignored']));

        // storage stats
        $this->logger?->info(sprintf('%d items saved', $storedCount));

        // save new status
        $this->statusStorage->setStatus(new Status(
            lastUpdate: $this->source->getLastUpdate(),
            items: $storedCount
        ));
    }
}
