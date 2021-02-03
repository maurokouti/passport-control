<?php

declare(strict_types=1);

namespace App\Service;

use App\Storage\Item;
use App\Storage\ItemStorageInterface;
use App\Stream\ItemLineGenerator;
use App\Stream\StreamLineGenerator;
use App\Stream\ValidateLineGenerator;
use GuzzleHttp\Psr7\Utils;
use Workerman\Connection\ConnectionInterface;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;

class Server
{
    public function __construct(
        private ItemStorageInterface $storage,
        private ?int $maxInputSize = 1000,
    ) {
    }

    public function onMessage(ConnectionInterface $connection, Request $request)
    {
        try {
            $response = $this->handle($request);
        } catch (\Exception) {
            $response = new Response(500);
        } finally {
            $connection->send($response);
            $connection->close();
        }
    }

    private function handle(Request $request): Response
    {
        $body = $request->rawBody();
        $lines = preg_split('~\r?\n~', $body);

        if (count($lines) > $this->maxInputSize) {
            return new Response(413, [], sprintf('Max input size %d', $this->maxInputSize));
        }

        // read source stream and extract single lines
        $parsedLines = (new StreamLineGenerator(Utils::streamFor($body)))->getLines();
        // validate lines
        $validLines = (new ValidateLineGenerator($parsedLines))->getLines();
        // extract series, number values from the string line
        $items = (new ItemLineGenerator($validLines))->getItems();

        // get items from storage
        $result = $this->storage->getItems($items);

        if (count($result) == 0) {
            return new Response(204);
        }

        $responseBody = implode("\r\n", array_map(fn (Item $r) => sprintf('%s,%s', $r->series, $r->number), $result));

        return new Response(200, [], $responseBody);
    }
}
