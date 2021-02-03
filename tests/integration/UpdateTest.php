<?php

declare(strict_types=1);

class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testUpdate()
    {
        $statusStorage = $this->buildStatusStorage();
        $source = $this->buildSource();
        $itemStorage = $this->buildStorage();
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $updater = new \App\Service\Updater(
            source: $source,
            statusStorage: $statusStorage,
            itemStorage: $itemStorage,
            logger: $logger,
        );

        $updater->run();

        $this->assertEquals(3, $statusStorage->getStatus()->items);
        $this->assertCount(2, $itemStorage->getItems([
            new \App\Storage\Item('1234', '123456'),
            new \App\Storage\Item('4321', '654321'),
        ]));
    }

    private function buildStorage(): \App\Storage\ItemStorageInterface
    {
        return new class() implements \App\Storage\ItemStorageInterface {
            private array $items = [];

            public function updateItems(iterable $items): int
            {
                $this->items = [];
                foreach ($items as $item) {
                    $this->items[] = $item;
                }

                return count($this->items);
            }

            public function getItems(iterable $items): array
            {
                $result = [];
                foreach ($items as $searchItem) {
                    foreach ($this->items as $storedItem) {
                        if ($searchItem->series == $storedItem->series && $searchItem->number == $storedItem->number) {
                            $result[] = $searchItem;
                        }
                    }
                }

                return $result;
            }
        };
    }

    private function buildStatusStorage(): \App\Status\StatusStorageInterface
    {
        return new class() implements \App\Status\StatusStorageInterface {
            private App\Status\Status $status;

            public function __construct()
            {
                $this->status = new \App\Status\Status(
                    lastUpdate: time() - 3600,
                    items: 0,
                );
            }

            public function getStatus(): \App\Status\Status
            {
                return $this->status;
            }

            public function setStatus(\App\Status\Status $status): void
            {
                $this->status = $status;
            }
        };
    }

    private function buildSource(): \App\Source\SourceInterface
    {
        return new class() implements \App\Source\SourceInterface {
            public function getStream(): \Psr\Http\Message\StreamInterface
            {
                return \GuzzleHttp\Psr7\Utils::streamFor(
                    <<<STREAM
SERIES,NUMBER
1111,222222
1234,123456
4321,654321
SSSS,NNNNNN
STREAM
                );
            }

            public function getDescription(): string
            {
                return 'Test source';
            }

            public function getLastUpdate(): int
            {
                return time();
            }
        };
    }
}
