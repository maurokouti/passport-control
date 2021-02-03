<?php

declare(strict_types=1);

namespace App\Source;

use App\Stream\DecompressStream;
use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;

class HttpSource implements SourceInterface
{
    public function __construct(
        private string $url,
    ) {
    }

    public function getStream(): StreamInterface
    {
        $client = new Client();

        $response = $client->request('GET', $this->url, [
            'verify' => false,
            'stream' => true,
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            throw new \Exception('Bad HTTP status code ' . $statusCode);
        }

        return new DecompressStream($response->getBody());
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return sprintf('HTTP source %s', $this->url);
    }

    public function getLastUpdate(): int
    {
        $client = new Client();
        $response = $client->request('HEAD', $this->url, [
            'verify' => false,
        ]);
        $lastModifiedHeader = $response->getHeader('Last-Modified')[0] ?? '0';

        return strtotime($lastModifiedHeader);
    }
}
