<?php

declare(strict_types=1);

use App\Service\Server;
use Workerman\Worker;

$container = require __DIR__ . '/../src/bootstrap.php';

$server = $container->get(Server::class);

Worker::$pidFile = '/dev/null';
Worker::$logFile = '/dev/null';

$httpWorker = new Worker(sprintf(
    'http://%s:%d',
    $container->get('http.address'),
    $container->get('http.port'),
));
$httpWorker->count = 8;
$httpWorker->onMessage = [$server, 'onMessage'];

Worker::runAll();
