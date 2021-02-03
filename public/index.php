<?php

declare(strict_types=1);

$container = require __DIR__ . '/../src/bootstrap.php';

$server = $container->get(\App\Service\Server::class);
$request = GuzzleHttp\Psr7\ServerRequest::fromGlobals();
$response = $server->handle($request);

echo $response->getBody();
